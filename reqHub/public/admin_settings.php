<?php
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../database/db.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die('Access denied');
}

$currentUser = getCurrentUser();

if ($currentUser['reqhub_role'] !== 'Admin') {
    http_response_code(403);
    die('Access denied: Admin only');
}

$pdo   = ReqHubDatabase::getConnection('reqhub');
$hrPdo = ReqHubDatabase::getConnection('hr');

// Roles that support system assignment
$rolesWithSystemAssignment = ['Approver', 'Requestor'];

// Roles that support department assignment
$rolesWithDepartmentAssignment = ['Reviewer'];

// --- Fetch users (exclude SYSTEM) ---
$users = $pdo->query("SELECT id, employee_id, reqhub_role, is_active FROM users WHERE employee_id != 'SYSTEM' ORDER BY employee_id")->fetchAll(PDO::FETCH_ASSOC);
if (!$users) $users = [];

// HR names
$userNames = [];
try {
    $hrUsers = $hrPdo->query("
        SELECT bi_empno, CONCAT(COALESCE(bi_empfname,''),' ',COALESCE(bi_empmname,''),' ',COALESCE(bi_emplname,'')) as full_name
        FROM tbl201_basicinfo
    ")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($hrUsers as $u) $userNames[$u['bi_empno']] = trim($u['full_name']);
} catch (Exception $e) {
}

// Department descriptions
$deptDescriptions = [];
try {
    $hrDeptNames = $hrPdo->query("SELECT Dept_Code, Dept_Name FROM tbl_department WHERE Dept_Name IS NOT NULL AND Dept_Name != ''")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($hrDeptNames as $d) $deptDescriptions[strtoupper(trim($d['Dept_Code']))] = trim($d['Dept_Name']);
} catch (Exception $e) {
}

// User departments
$userDepartments = [];
try {
    $hrDepts = $hrPdo->query("
        SELECT j.jrec_empno, j.jrec_department
        FROM tbl201_jobrec j
        INNER JOIN (
            SELECT jrec_empno, MAX(jrec_id) AS max_id
            FROM tbl201_jobrec
            WHERE jrec_department IS NOT NULL AND jrec_department != ''
            GROUP BY jrec_empno
        ) latest ON j.jrec_empno = latest.jrec_empno AND j.jrec_id = latest.max_id
    ")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($hrDepts as $row) {
        $code = strtoupper(trim($row['jrec_department']));
        $userDepartments[$row['jrec_empno']] = $deptDescriptions[$code] ?? $row['jrec_department'];
    }
} catch (Exception $e) {
}

foreach ($users as &$user) {
    $user['user_name']     = $userNames[$user['employee_id']] ?? $user['employee_id'];
    $user['hr_department'] = $userDepartments[$user['employee_id']] ?? '';
}
unset($user);

usort($users, fn($a, $b) => strcasecmp($a['user_name'], $b['user_name']));

// Systems
$systems = $pdo->query("SELECT id, name FROM systems ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$systemDescriptions = [];
try {
    $hrSystems = $hrPdo->query("SELECT system_id, sys_desc FROM tbl_systems")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($hrSystems as $sys) $systemDescriptions[strtolower($sys['system_id'])] = $sys['sys_desc'];
} catch (Exception $e) {
}

$systemsWithFullNames = [];
foreach ($systems as $system) {
    $system['full_name'] = $systemDescriptions[strtolower(trim($system['name']))] ?? $system['name'];
    $systemsWithFullNames[] = $system;
}
$systems = $systemsWithFullNames;

// Active HR departments for Reviewer assignment
$hrActiveDepartments = [];
try {
    $hrActiveDepartments = $hrPdo->query("
        SELECT Dept_Code, Dept_Name
        FROM tbl_department
        WHERE Dept_Name IS NOT NULL AND Dept_Name != ''
          AND Dept_Stat = 'active'
        ORDER BY Dept_Name
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("HR active departments fetch failed: " . $e->getMessage());
}

$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$actions     = $pdo->query("SELECT id, name FROM actions ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$modules     = $pdo->query("SELECT id, name FROM modules ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$roles       = $pdo->query("SELECT id, name FROM roles ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Module actions
$moduleActions = [];
foreach ($modules as $module) {
    $stmt = $pdo->prepare("SELECT a.id AS action_id, a.name FROM module_actions ma LEFT JOIN actions a ON ma.action_id = a.id WHERE ma.module_id = ?");
    $stmt->execute([$module['id']]);
    $moduleActions[$module['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$moduleAssignments = [];
foreach ($moduleActions as $moduleId => $actionsList) {
    foreach ($actionsList as $a) $moduleAssignments[$moduleId][] = $a['action_id'];
}

// Role permissions — system-scoped
// Structure: $roleAssignments[role_id][system_id] = [permissions]
$roleAssignments = [];
foreach ($roles as $role) {
    $stmt = $pdo->prepare("
        SELECT rp.module_id, rp.action_id, rp.system_id,
               m.name AS module_name, a.name AS action_name
        FROM role_permissions rp
        LEFT JOIN modules m ON rp.module_id = m.id
        LEFT JOIN actions a ON rp.action_id = a.id
        WHERE rp.role_id = ?
        ORDER BY rp.system_id, m.name, a.name
    ");
    $stmt->execute([$role['id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by system_id (NULL = legacy/global)
    $grouped = [];
    foreach ($rows as $row) {
        $sysKey = $row['system_id'] ?? 'null';
        if (!isset($grouped[$sysKey])) $grouped[$sysKey] = [];
        $grouped[$sysKey][] = $row;
    }
    $roleAssignments[$role['id']] = $grouped;
}

// System roles
$systemRoles = [];
foreach ($systems as $system) {
    $stmt = $pdo->prepare("SELECT sr.role_id, r.name AS role_name FROM system_roles sr LEFT JOIN roles r ON sr.role_id = r.id WHERE sr.system_id = ? ORDER BY r.name");
    $stmt->execute([$system['id']]);
    $systemRoles[$system['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Approver/Requestor/Reviewer assignments
$approverAssignments = [];
foreach ($users as $user) {
    if (!isset($user['id'])) continue;
    $stmt = $pdo->prepare("SELECT system_id, department_id FROM user_approver_assignments WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $approverAssignments[$user['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$defaultActionIds = [];
foreach ($actions as $act) {
    if (in_array(strtolower($act['name']), ['add', 'edit', 'delete', 'view'])) {
        $defaultActionIds[$act['id']] = $act['name'];
    }
}

// HR users for Add User dropdown
$hrUsersList = [];
try {
    $hrUsersList = $hrPdo->query("
        SELECT bi.bi_empno,
               CONCAT(COALESCE(bi.bi_empfname,''),' ',COALESCE(bi.bi_emplname,'')) as full_name
        FROM tbl201_basicinfo bi
        INNER JOIN tngc_hrd2.tbl201_jobinfo ji ON ji.ji_empno = bi.bi_empno
        WHERE bi.datastat = 'current' AND ji.ji_remarks = 'Active'
        ORDER BY bi.bi_empfname ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("HR users list fetch failed: " . $e->getMessage());
}
?>

<?php include(__DIR__ . '/../includes/header.php'); ?>

<div class="container mt-4">
    <h4 class="mb-4">Admin Settings</h4>

    <ul class="nav nav-tabs mb-3" id="adminTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#actionsTab" type="button">Actions</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#modulesTab" type="button">Modules</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rolesTab" type="button">Roles</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#systemsTab" type="button">Systems</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#userSettingsTab" type="button">User Settings</button></li>
    </ul>

    <div class="tab-content">

        <!-- ACTIONS TAB -->
        <div class="tab-pane fade show active" id="actionsTab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-action="addAction">Add Action</button>
                    <button class="btn btn-outline-danger" id="toggleDeleteModeActions">Delete Mode</button>
                </div>
                <input type="text" class="form-control search-input" id="searchActions" placeholder="Search actions..." style="max-width:300px;">
            </div>
            <div class="actions-grid">
                <?php foreach ($actions as $action): ?>
                    <div class="action-item card p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="editable-label" data-type="action" data-id="<?= $action['id'] ?>" title="Click to edit"><?= htmlspecialchars($action['name']) ?></span>
                            <div><button class="btn btn-sm btn-danger action-delete-btn" data-action="deleteAction" data-id="<?= $action['id'] ?>">×</button></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- MODULES TAB -->
        <div class="tab-pane fade" id="modulesTab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-primary" data-action="addModule">Add Module</button>
                <input type="text" class="form-control search-input" id="searchModules" placeholder="Search modules..." style="max-width:300px;">
            </div>
            <div class="modules-grid">
                <?php foreach ($modules as $module): ?>
                    <div class="module-item card p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2 module-item-header">
                            <div class="title d-flex align-items-center flex-shrink-1 overflow-hidden">
                                <button class="btn btn-sm btn-outline-secondary me-2 toggle-module flex-shrink-0">+</button>
                                <span class="editable-label flex-grow-1" data-type="module" data-id="<?= $module['id'] ?>" title="Click to edit"><?= htmlspecialchars($module['name']) ?></span>
                            </div>
                            <div class="btn-group flex-shrink-0">
                                <button class="btn btn-sm btn-danger" data-action="deleteModule" data-module-id="<?= $module['id'] ?>">×</button>
                            </div>
                        </div>
                        <div class="module-actions mt-2" style="display:none;">
                            <?php if (!empty($moduleActions[$module['id']])): ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($moduleActions[$module['id']] as $action): ?>
                                        <span class="badge bg-light text-dark" style="font-weight:normal;">• <?= htmlspecialchars($action['name']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?><span class="text-muted">No actions assigned</span><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ROLES TAB -->
        <div class="tab-pane fade" id="rolesTab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-primary" data-action="addRole">Add Role</button>
                <input type="text" class="form-control search-input" id="searchRoles" placeholder="Search roles..." style="max-width:300px;">
            </div>
            <div class="roles-grid">
                <?php foreach ($roles as $role): ?>
                    <div class="role-item card p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-sm btn-outline-secondary me-2 toggle-role">+</button>
                                <span class="editable-label" data-type="role" data-id="<?= $role['id'] ?>" title="Click to edit"><?= htmlspecialchars($role['name']) ?></span>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-danger" data-action="deleteRole" data-role-id="<?= $role['id'] ?>">×</button>
                            </div>
                        </div>
                        <div class="role-permissions mt-2" style="display:none;">
                            <?php
                            $ra = $roleAssignments[$role['id']] ?? [];
                            if (!empty($ra)):
                                foreach ($ra as $sysKey => $perms):
                                if ($sysKey === 'null') continue; // hide global
                                $sysLabel = '(unknown system)';
                                    if ($sysKey !== 'null') {
                                        foreach ($systems as $s) {
                                            if ($s['id'] == $sysKey) {
                                                $sysLabel = $s['full_name'] ?? $s['name'];
                                                break;
                                            }
                                        }
                                    }
                                    echo '<div class="mb-2"><span class="badge bg-secondary mb-1">' . htmlspecialchars($sysLabel) . '</span>';
                                    $currentModule = '';
                                    foreach ($perms as $item):
                                        if ($item['module_name'] !== $currentModule):
                                            if ($currentModule !== '') echo '</div>';
                                            $currentModule = $item['module_name'];
                                            echo '<span class="d-block ps-3" style="font-weight:normal;">' . htmlspecialchars($currentModule) . '</span>';
                                            echo '<div class="d-flex flex-wrap gap-2 ps-3">';
                                        endif;
                                        if ($item['action_name']):
                                            echo '<span class="badge bg-light text-dark" style="font-weight:normal;">• ' . htmlspecialchars($item['action_name']) . '</span>';
                                        endif;
                                    endforeach;
                                    if ($currentModule !== '') echo '</div>';
                                    echo '</div>';
                                endforeach;
                            else:
                                echo '<span class="text-muted">No permissions assigned</span>';
                            endif;
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SYSTEMS TAB -->
        <div class="tab-pane fade" id="systemsTab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-primary" data-action="addSystem">Add System</button>
                <input type="text" class="form-control search-input" id="searchSystems" placeholder="Search systems..." style="max-width:300px;">
            </div>
            <div class="systems-list">
                <?php foreach ($systems as $system): ?>
                    <div class="system-item card p-3 mb-3" data-system-id="<?= $system['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center flex-grow-1">
                                <button class="btn btn-sm btn-outline-secondary me-2 toggle-system-roles">+</button>
                                <span class="editable-label flex-grow-1" data-type="system" data-id="<?= $system['id'] ?>" title="Click to edit"><?= htmlspecialchars($system['full_name'] ?? $system['name']) ?></span>
                            </div>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-secondary" data-action="duplicateSystem" data-system-id="<?= $system['id'] ?>" data-system-name="<?= htmlspecialchars($system['full_name'] ?? $system['name'], ENT_QUOTES) ?>">Duplicate</button>
                                <button class="btn btn-sm btn-danger" data-action="deleteSystem" data-system-id="<?= $system['id'] ?>">×</button>
                            </div>
                        </div>
                        <div class="system-roles-container mt-3" style="display:none;">
                            <strong class="d-block mb-2">▼ Roles</strong>
                            <div class="ps-3">
                                <?php
                                $sysRoles = $systemRoles[$system['id']] ?? [];
                                if (!empty($sysRoles)):
                                    foreach ($sysRoles as $sysRole):
                                        $roleId = $sysRole['role_id'];
                                        $roleName = $sysRole['role_name'];
                                        $perms = $roleAssignments[$roleId] ?? [];
                                ?>
                                        <div class="system-role-item mb-3">
                                            <button class="btn btn-sm btn-outline-secondary me-2 toggle-system-role-modules">+</button>
                                            <span><?= htmlspecialchars($roleName) ?></span>
                                            <div class="system-role-modules mt-2 ps-3" style="display:none;">
                                                <?php
                                                $modulesByName = [];
                                                $flatPerms = [];
                                                foreach ($perms as $sysKey => $sysRows) {
                                                    foreach ($sysRows as $p) {
                                                        $flatPerms[] = $p;
                                                    }
                                                }
                                                foreach ($flatPerms as $p) {
                                                    if (!isset($modulesByName[$p['module_id']])) {
                                                        $modulesByName[$p['module_id']] = ['name' => $p['module_name'], 'actions' => []];
                                                    }
                                                    if ($p['action_name']) $modulesByName[$p['module_id']]['actions'][] = $p['action_name'];
                                                }
                                                foreach ($modulesByName as $modId => $modData): ?>
                                                    <div class="system-module-item mb-2">
                                                        <span><?= htmlspecialchars($modData['name']) ?></span>
                                                        <div class="mt-1 ps-3">
                                                            <?php foreach ($modData['actions'] as $actName): ?>
                                                                <span class="badge bg-light text-dark me-2 mb-2">• <?= htmlspecialchars($actName) ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                <?php
                                    endforeach;
                                else: echo '<span class="text-muted">No roles assigned to this system</span>';
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- USER SETTINGS TAB -->
        <div class="tab-pane fade" id="userSettingsTab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-primary" data-action="addUser">Add User</button>
                <div class="d-flex gap-2 align-items-center">
                    <input type="text" class="form-control search-input" id="searchUsers" placeholder="Search users..." style="max-width:300px;">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterUsersBtn">Filter</button>
                        <div class="dropdown-menu p-3" style="min-width:220px;" id="filterUsersDropdown">
                            <div class="mb-2 fw-semibold">Status</div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input user-active-filter" type="checkbox" value="active" id="filterActive">
                                    <label class="form-check-label" for="filterActive">Active</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input user-active-filter" type="checkbox" value="inactive" id="filterInactive">
                                    <label class="form-check-label" for="filterInactive">Inactive</label>
                                </div>
                            </div>
                            <div class="mb-2 fw-semibold">Role</div>
                            <div class="mb-3">
                                <?php foreach (['No Access', 'Requestor', 'Reviewer', 'Approver', 'Admin'] as $roleOption): ?>
                                    <div class="form-check">
                                        <input class="form-check-input user-role-filter" type="checkbox" value="<?= strtolower($roleOption) ?>" id="roleFilter<?= md5($roleOption) ?>">
                                        <label class="form-check-label" for="roleFilter<?= md5($roleOption) ?>"><?= $roleOption ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mb-2 fw-semibold">Department</div>
                            <div style="max-height:200px; overflow-y:auto;">
                                <?php
                                $distinctHrDepts = array_unique(array_filter(array_values($userDepartments)));
                                sort($distinctHrDepts);
                                foreach ($distinctHrDepts as $deptName):
                                    $deptKey = htmlspecialchars(strtolower($deptName));
                                    $deptId  = 'deptFilter' . md5($deptName);
                                ?>
                                    <div class="form-check">
                                        <input class="form-check-input user-dept-filter" type="checkbox" value="<?= $deptKey ?>" id="<?= $deptId ?>">
                                        <label class="form-check-label" for="<?= $deptId ?>"><?= htmlspecialchars($deptName) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-2 d-flex gap-2">
                                <button class="btn btn-sm btn-outline-secondary" id="clearDeptFilter">Clear</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="users-list users-grid">
                <?php foreach ($users as $user):
                    if (!isset($user['id']) || !isset($user['employee_id'])) continue;
                    $userAssignments = $approverAssignments[$user['id']] ?? [];
                    $hasAssignments = in_array($user['reqhub_role'], array_merge($rolesWithSystemAssignment, $rolesWithDepartmentAssignment));
                ?>
                    <div class="user-item card p-3 mb-2" data-user-id="<?= $user['id'] ?>" data-hr-dept="<?= htmlspecialchars(strtolower($user['hr_department'])) ?>" data-role="<?= htmlspecialchars(strtolower($user['reqhub_role'])) ?>" data-active="<?= $user['is_active'] ? 'active' : 'inactive' ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center">
                                    <?php if ($hasAssignments): ?>
                                        <button class="btn btn-sm btn-outline-secondary me-2 toggle-user-approvals">+</button>
                                    <?php endif; ?>
                                    <div class="d-flex flex-column">
                                        <strong class="mb-1"><?= htmlspecialchars($user['user_name'] ?? $user['employee_id']) ?></strong>
                                        <small class="text-muted"><?= htmlspecialchars($user['employee_id']) ?></small>
                                        <small class="text-muted user-role-label"><?= htmlspecialchars($user['reqhub_role']) ?></small>
                                    </div>
                                </div>
                                <!-- System/department assignments (collapsible) — always rendered so JS toggle works after role change -->
                                <div class="user-approvals mt-2" style="display:none; margin-left:30px;">
                                    <small class="text-muted d-block mb-1">Assigned to:</small>
                                    <div class="ps-2">
                                        <?php if ($hasAssignments && !empty($userAssignments)): ?>
                                            <?php
                                            $isReviewer = ($user['reqhub_role'] === 'Reviewer');
                                            $shownIds = [];
                                            foreach ($userAssignments as $assignment) {
                                                if ($isReviewer) {
                                                    $deptCode = $assignment['department_id'] ?? null;
                                                    if (!$deptCode || in_array($deptCode, $shownIds)) continue;
                                                    $shownIds[] = $deptCode;
                                                    $deptLabel = $deptDescriptions[strtoupper(trim($deptCode))] ?? $deptCode;
                                                    echo '<small class="d-block mb-1"><strong>Department:</strong> ' . htmlspecialchars($deptLabel) . '</small>';
                                                } else {
                                                    $sysId = $assignment['system_id'] ?? null;
                                                    if (!$sysId || in_array($sysId, $shownIds)) continue;
                                                    $shownIds[] = $sysId;
                                                    $sysName = '';
                                                    foreach ($systems as $s) {
                                                        if ($s['id'] == $sysId) {
                                                            $sysName = $s['full_name'] ?? $s['name'];
                                                            break;
                                                        }
                                                    }
                                                    echo '<small class="d-block mb-1"><strong>System:</strong> ' . htmlspecialchars($sysName) . '</small>';
                                                }
                                            }
                                            ?>
                                        <?php else: ?>
                                            <small class="text-muted">No assignments yet</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="ms-3">
                                <button class="btn btn-sm btn-secondary me-2"
                                    data-action="editUser"
                                    data-user-id="<?= $user['id'] ?>"
                                    data-name="<?= htmlspecialchars($user['employee_id'], ENT_QUOTES) ?>"
                                    data-user-role="<?= htmlspecialchars($user['reqhub_role']) ?>">Edit</button>
                                <button class="btn btn-sm btn-danger" data-action="deleteUser" data-user-id="<?= $user['id'] ?>">×</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div><!-- end tab-content -->
</div>

<!-- MODAL -->
<div class="modal fade" id="accessTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="accessTypeForm" method="post" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accessTypeModalLabel">Manage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="overflow:hidden;">
                    <input type="hidden" id="modalAction" name="action">
                    <input type="hidden" id="modalRole" name="role_id">
                    <input type="hidden" id="modalModule" name="module_id">
                    <input type="hidden" id="modalUser" name="user_id">
                    <input type="hidden" id="modalSystem" name="system_id">
                    <input type="hidden" id="modalActionId" name="id">

                    <div id="modalInputGroup" class="mb-3" style="display:none;">
                        <label for="modalInput" class="form-label" id="modalInputLabel"></label>
                        <input type="text" class="form-control" name="name" id="modalInput" placeholder="Enter name" autocomplete="off" spellcheck="false">
                    </div>

                    <div class="row" style="display:flex;">
                        <div class="col-md-8" style="overflow-y:auto; max-height:60vh;">
                            <div id="permissionsContainer"></div>
                        </div>
                        <div class="col-md-4 border-start" style="display:none; overflow:visible;" id="summaryColumn">
                            <h6>Selected</h6>
                            <div id="modalSummary" class="small"><em>None selected</em></div>
                        </div>
                    </div>

                    <p id="modalWarning" class="text-danger" style="display:none;"></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Confirm</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* ACTIONS */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
    }

    .action-item {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background: #fff;
    }

    .action-item .action-delete-btn {
        display: none;
    }

    /* MODULES */
    .modules-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .module-item {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background: #fff;
    }

    /* ROLES */
    .roles-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .role-item {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background: #fff;
    }

    /* SYSTEMS */
    .systems-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .system-item {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background: #fff;
    }

    .system-role-item {
        border-left: 3px solid #dee2e6;
        padding-left: 0.75rem;
        margin-bottom: 1rem;
    }

    .system-module-item {
        border-left: 2px dashed #dee2e6;
        padding-left: 0.75rem;
    }

    /* USERS */
    .users-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        align-items: start;
    }

    @media(max-width:992px) {
        .users-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:576px) {
        .users-grid {
            grid-template-columns: 1fr;
        }
    }

    .user-item {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background: #fff;
    }

    .user-approvals {
        background-color: #f8f9fa;
        border-left: 3px solid #dee2e6;
        padding-left: 0.75rem;
    }

    /* EDITABLE */
    .editable-label {
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        transition: background-color 0.2s;
        font-weight: normal;
    }

    .editable-label:hover {
        background-color: #f0f0f0;
    }

    .editable-label[contenteditable="true"] {
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        outline: none;
    }

    /* MODAL */
    #permissionsContainer {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        width: 100%;
        max-height: 60vh;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .role-modal-module-card {
        border: 1px solid #dee2e6;
        border-radius: 0.75rem;
        padding: 0.75rem;
        background: #fff;
        display: flex;
        flex-direction: column;
        min-width: 0;
        width: 100%;
    }

    .role-modal-action-item {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
        width: 100%;
    }

    .module-modal-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px 16px;
    }

    #summaryColumn {
        max-height: calc(60vh - 40px);
        height: calc(60vh - 40px);
        overflow-y: auto;
        padding-right: 10px;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    #summaryColumn::-webkit-scrollbar {
        display: none;
    }

    #modalSummary {
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-height: calc(60vh - 80px);
        overflow-y: auto;
    }

    #accessTypeModal .modal-body {
        overflow: visible !important;
        max-height: none !important;
    }

    #accessTypeModal .modal-dialog.modal-sm {
        max-width: 300px !important;
    }

    #accessTypeModal .modal-dialog.modal-md {
        max-width: 500px !important;
    }

    #accessTypeModal .modal-dialog.modal-lg {
        max-width: 800px !important;
    }

    #accessTypeModal .modal-dialog.modal-xl {
        max-width: 1200px !important;
    }

    @media(max-width:1200px) {

        .modules-grid,
        .roles-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include(__DIR__ . '/../includes/footer.php'); ?>

<script>
    if (typeof $ === 'undefined') {
        console.error('jQuery is NOT loaded!');
    }

    $(function() {
        const getModal = () => bootstrap.Modal.getOrCreateInstance(document.getElementById('accessTypeModal'));

        let roleAssignments = <?= json_encode($roleAssignments) ?>;
        let moduleAssignments = <?= json_encode($moduleAssignments ?? []) ?>;
        let approverAssignments = <?= json_encode($approverAssignments) ?>;
        let systemRoles = <?= json_encode($systemRoles) ?>;

        // Roles that show system assignment UI
        const rolesWithSystemAssignment = ['Approver', 'Requestor'];
        // Roles that show department assignment UI
        const rolesWithDepartmentAssignment = ['Reviewer'];

        // Active HR departments for Reviewer assignment
        const hrActiveDepartments = <?= json_encode($hrActiveDepartments) ?>;

        let duplicateSystemCounter = {};

        // ============================================================
        // HELPERS
        // ============================================================
        function htmlEscape(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        function updateModalSummary() {
            const action = $('#modalAction').val();
            if (!action.includes('Module') && !action.includes('Role') && !action.includes('System')) {
                $('#summaryColumn').hide();
                return;
            }
            $('#summaryColumn').show();
            const summary = $('#modalSummary');

            // Role: summarize all panels
            if (action.includes('Role')) {
                let html = '<strong>Permissions Summary</strong><br>';
                let hasAny = false;

                $('#permissionsContainer .role-system-panel').each(function() {
                    const sysKey = ($(this).data('panel-system-id') ?? 'null').toString();
                    const isGlobal = sysKey === 'null';
                    const tabBtn = $(`.role-panel-tab[data-tab-target="panel-${sysKey}"]`);
                    const sysLabel = isGlobal ? 'Global' : tabBtn.text().trim();
                    const isActive = tabBtn.hasClass('active');

                    const moduleBlocks = [];
                    $(this).find('.role-modal-module-card').each(function() {
                        const moduleName = $(this).find('.role-modal-module-header label').text().trim();
                        const checkedActions = $(this).find('.role-permission-checkbox:checked').map(function() {
                            return $(this).closest('label').find('span').text().trim();
                        }).get();
                        if (checkedActions.length > 0) moduleBlocks.push({ moduleName, checkedActions });
                    });

                    if (moduleBlocks.length > 0) {
                        hasAny = true;
                        const highlight = isActive ? 'font-weight:bold; color:#0d6efd;' : 'font-weight:normal; color:#555;';
                        html += `<div style="margin-top:8px; padding:4px 6px; background:${isActive ? '#eef4ff' : '#f8f9fa'}; border-radius:4px; border-left:3px solid ${isActive ? '#0d6efd' : '#ccc'}">`;
                        html += `<span style="${highlight}">${htmlEscape(sysLabel)}</span>`;
                        moduleBlocks.forEach(({ moduleName, checkedActions }) => {
                            html += `<div style="margin-left:10px; margin-top:4px;"><strong style="font-size:0.8rem;">• ${htmlEscape(moduleName)}</strong>`;
                            checkedActions.forEach(a => {
                                html += `<div style="margin-left:16px; font-size:0.78rem; color:#555;">◦ ${htmlEscape(a)}</div>`;
                            });
                            html += '</div>';
                        });
                        html += '</div>';
                    }
                });

                summary.html(hasAny ? html : '<em>None selected</em>');
                return;
            }

            let selected = [],
                label = '';

            if (action.includes('Module')) {
                selected = $('.module-action-checkbox:checked').map(function() {
                    return $(this).closest('label').find('span').text();
                }).get();
                label = 'Actions';
            } else if (action.includes('System')) {
                selected = $('.system-role-checkbox:checked').map(function() {
                    return $(this).closest('label').find('span').text();
                }).get();
                label = 'Roles';
            }

            if (selected.length === 0) {
                summary.html('<em>None selected</em>');
                return;
            }
            let html = '<strong>' + label + '</strong><br>';
            selected.forEach(s => {
                html += `<div style="margin-left:10px;">• ${htmlEscape(s)}</div>`;
            });
            summary.html(html);
        }

        // ============================================================
        // DELETE MODE - ACTIONS TAB
        // ============================================================
        $('#toggleDeleteModeActions').on('click', function() {
            const isActive = $(this).hasClass('btn-danger');
            if (isActive) {
                $(this).removeClass('btn-danger').addClass('btn-outline-danger').text('Delete Mode');
                $('.action-item .action-delete-btn').hide();
            } else {
                $(this).removeClass('btn-outline-danger').addClass('btn-danger').text('Exit Delete Mode');
                $('.action-item .action-delete-btn').show();
            }
        });

        // ============================================================
        // COLLAPSIBLES
        // ============================================================
        $(document).on('click', '.toggle-module', function(e) {
            e.stopPropagation();
            $(this).closest('.module-item').find('.module-actions').slideToggle(150);
            $(this).text($(this).text() === '+' ? '-' : '+');
        });
        $(document).on('click', '.toggle-role', function(e) {
            e.stopPropagation();
            $(this).closest('.role-item').find('.role-permissions').slideToggle(150);
            $(this).text($(this).text() === '+' ? '-' : '+');
        });
        $(document).on('click', '.toggle-system-roles', function(e) {
            e.stopPropagation();
            const systemItem = $(this).closest('.system-item');
            const container = systemItem.find('.system-roles-container');
            const systemId = systemItem.data('system-id').toString();

            // Rebuild content from live roleAssignments + systemRoles on every open
            if ($(this).text() === '+') {
                const roles = systemRoles[systemId] || [];
                if (roles.length === 0) {
                    container.html('<span class="text-muted">No roles assigned to this system</span>');
                } else {
                    let html = '<strong class="d-block mb-2">▼ Roles</strong><div class="ps-3">';
                    roles.forEach(sysRole => {
                        const roleId = sysRole.role_id;
                        const roleName = sysRole.role_name;
                        const perms = roleAssignments[roleId] || {};
                        const scopedPerms = perms[systemId] || [];

                        html += `<div class="system-role-item mb-3">
                            <button class="btn btn-sm btn-outline-secondary me-2 toggle-system-role-modules">+</button>
                            <span>${htmlEscape(roleName)}</span>
                            <div class="system-role-modules mt-2 ps-3" style="display:none;">`;

                        if (scopedPerms.length === 0) {
                            html += '<span class="text-muted small">No permissions assigned</span>';
                        } else {
                            const modulesByName = {};
                            scopedPerms.forEach(p => {
                                if (!modulesByName[p.module_id]) modulesByName[p.module_id] = { name: p.module_name || '', actions: [] };
                                if (p.action_name) modulesByName[p.module_id].actions.push(p.action_name);
                            });
                            Object.keys(modulesByName).forEach(modId => {
                                const mod = modulesByName[modId];
                                html += `<div class="system-module-item mb-2">
                                    <span>${htmlEscape(mod.name)}</span>
                                    <div class="mt-1 ps-3">`;
                                mod.actions.forEach(a => {
                                    html += `<span class="badge bg-light text-dark me-2 mb-2">• ${htmlEscape(a)}</span>`;
                                });
                                html += `</div></div>`;
                            });
                        }
                        html += `</div></div>`;
                    });
                    html += '</div>';
                    container.html(html);
                }
            }

            container.slideToggle(150);
            $(this).text($(this).text() === '+' ? '-' : '+');
        });
        $(document).on('click', '.toggle-system-role-modules', function(e) {
            e.stopPropagation();
            $(this).closest('.system-role-item').find('.system-role-modules').slideToggle(150);
            $(this).text($(this).text() === '+' ? '-' : '+');
        });
        $(document).on('click', '.toggle-user-approvals', function(e) {
            e.stopPropagation();
            $(this).closest('.user-item').find('.user-approvals').slideToggle(150);
            $(this).text($(this).text() === '+' ? '-' : '+');
        });

        // ============================================================
        // CARD CLICKS → MODAL
        // ============================================================
        $(document).on('click', '.module-item', function(e) {
            if ($(e.target).closest('button').length > 0 || $(e.target).is('.toggle-module') || $(e.target).is('.editable-label')) return;
            const moduleId = $(this).find('.editable-label').data('id');
            const moduleName = $(this).find('.editable-label').text();
            if (!moduleId) return;

            $('#modalAction').val('editModule');
            $('#modalModule').val(moduleId);
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('Module Name');
            $('#modalInput').val(moduleName.trim()).attr('disabled', true);

            let html = '<div class="d-flex justify-content-between align-items-center mb-2"><strong>Select Actions</strong><input type="text" class="form-control" id="searchModuleActionsEdit" placeholder="Search..." style="max-width:250px;"></div><div class="module-modal-grid">';
            $('.action-item').each(function() {
                const aid = $(this).find('.editable-label').data('id');
                const aname = $(this).find('.editable-label').text().trim();
                html += `<label class="role-modal-action-item" data-action-name="${aname.toLowerCase()}"><input type="checkbox" class="module-action-checkbox" value="${aid}"><span>${htmlEscape(aname)}</span></label>`;
            });
            html += '</div>';
            $('#permissionsContainer').html(html);

            if (moduleAssignments[moduleId]) {
                moduleAssignments[moduleId].forEach(aid => {
                    $(`.module-action-checkbox[value="${aid}"]`).prop('checked', true);
                });
            }

            setTimeout(() => {
                $(document).off('keyup', '#searchModuleActionsEdit').on('keyup', '#searchModuleActionsEdit', function() {
                    const q = $(this).val().toLowerCase();
                    $('.role-modal-action-item').each(function() {
                        $(this).toggle($(this).data('action-name').includes(q));
                    });
                });
                $(document).off('change', '.module-action-checkbox').on('change', '.module-action-checkbox', updateModalSummary);
                updateModalSummary();
            }, 50);

            $('#accessTypeModal .modal-dialog').removeClass('modal-sm modal-md modal-lg modal-xl').addClass('modal-xl');
            getModal().show();
        });

        function buildSystemBlock(systemId, roleId, blockIndex) {
            let html = `<div class="role-system-block card p-3 mb-3" data-system-id="${systemId}" data-block-index="${blockIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 fw-semibold">System:</label>
                    <span class="badge bg-secondary">${htmlEscape($(`[data-system-id="${systemId}"]`).find('.editable-label').text().trim())}</span>
                </div>
                <button type="button" class="btn btn-sm btn-danger remove-system-block" data-system-id="${systemId}">Remove</button>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Select Modules</strong>
                <input type="text" class="form-control searchRoleModuleBlockEdit" placeholder="Search..." style="max-width:250px;">
            </div>`;
            $('.module-item').each(function() {
                const mid = $(this).find('.editable-label').data('id');
                const mname = $(this).find('.editable-label').text().trim();
                html += `<div class="role-modal-module-card"><div class="role-modal-module-header"><label><input type="checkbox" class="module-master-checkbox-block" data-block="${blockIndex}" data-module="${mid}"><strong>${htmlEscape(mname)}</strong></label></div><div class="d-flex flex-wrap gap-2 ps-3">`;
                (moduleAssignments[mid] || []).forEach(aid => {
                    const actionItem = $(`.action-item`).find(`[data-id="${aid}"]`).closest('.action-item');
                    if (actionItem.length) {
                        const aname = actionItem.find('.editable-label').text().trim();
                        html += `<label class="badge bg-light text-dark"><input type="checkbox" class="role-permission-checkbox-block" data-block="${blockIndex}" data-module="${mid}" value="${aid}"><span style="margin-left:0.35rem;">${htmlEscape(aname)}</span></label>`;
                    }
                });
                html += '</div></div>';
            });
            html += '</div>';
            return html;
        }

        $(document).on('click', '.role-item', function(e) {
            if ($(e.target).closest('button').length > 0 || $(e.target).is('.toggle-role') || $(e.target).is('.editable-label')) return;
            const roleId = $(this).find('.editable-label').data('id');
            const roleName = $(this).find('.editable-label').text();
            if (!roleId) return;

            $('#accessTypeModal .modal-dialog').removeClass('modal-sm modal-md modal-lg modal-xl').addClass('modal-xl');
            $('#modalAction').val('editRole');
            $('#modalRole').val(roleId);
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('Role Name');
            $('#modalInput').val(roleName.trim()).attr('disabled', true);

            buildRoleModalPanels(roleId);
            getModal().show();
        });

        function loadSystemBlockPermissions(systemId, blockIndex, roleId) {
            if (!roleId || !roleAssignments[roleId]) return;
            const sysKey = systemId === null ? 'null' : systemId.toString();
            const permissions = roleAssignments[roleId][sysKey] || [];
            permissions.forEach(p => {
                $(`.role-permission-checkbox-block[data-block="${blockIndex}"][data-module="${p.module_id}"][value="${p.action_id}"]`).prop('checked', true);
            });
            $(`.module-master-checkbox-block[data-block="${blockIndex}"]`).each(function() {
                const mid = $(this).data('module');
                $(this).prop('checked', $(`.role-permission-checkbox-block[data-block="${blockIndex}"][data-module="${mid}"]:checked`).length > 0);
            });
        }

        // $(document).on('click', '.system-item', function(e) {
        //     if ($(e.target).closest('button').length > 0 || $(e.target).is('.toggle-system-roles') || $(e.target).is('.editable-label')) return;
        //     const systemId = $(this).find('.editable-label').data('id');
        //     const systemName = $(this).find('.editable-label').text();
        //     if (!systemId) return;

        //     $('#accessTypeModal .modal-dialog').removeClass('modal-sm modal-md modal-lg modal-xl').addClass('modal-xl');
        //     $('#modalAction').val('editSystem');
        //     $('#modalSystem').val(systemId);
        //     $('#modalInputGroup').show();
        //     $('#modalInputLabel').text('System Name');
        //     $('#modalInput').val(systemName.trim()).attr('disabled', true);

        //     const assignedRoles = systemRoles[systemId] ? systemRoles[systemId].map(r => r.role_id) : [];
        //     let html = '<div class="d-flex justify-content-between align-items-center mb-2"><strong>Assign Roles to This System</strong><input type="text" class="form-control" id="searchSystemRolesEdit" placeholder="Search..." style="max-width:250px;"></div><div class="module-modal-grid">';
        //     $('.role-item').each(function() {
        //         const rid = $(this).find('.editable-label').data('id');
        //         const rname = $(this).find('.editable-label').text().trim();
        //         html += `<label class="role-modal-action-item" data-role-name="${rname.toLowerCase()}"><input type="checkbox" class="system-role-checkbox" value="${rid}" ${assignedRoles.includes(parseInt(rid)) ? 'checked' : ''}><span>${htmlEscape(rname)}</span></label>`;
        //     });
        //     html += '</div>';
        //     $('#permissionsContainer').html(html);

        //     setTimeout(() => {
        //         $(document).off('keyup', '#searchSystemRolesEdit').on('keyup', '#searchSystemRolesEdit', function() {
        //             const q = $(this).val().toLowerCase();
        //             $('.role-modal-action-item').each(function() {
        //                 $(this).toggle($(this).data('role-name').includes(q));
        //             });
        //         });
        //         $(document).off('change', '.system-role-checkbox').on('change', '.system-role-checkbox', updateModalSummary);
        //         updateModalSummary();
        //     }, 100);
        //     getModal().show();
        // });

        // ============================================================
        // INLINE EDITING
        // ============================================================
        $(document).on('click', '.editable-label', function(e) {
            e.stopPropagation();
            if ($(this).attr('contenteditable') === 'true') return;
            const originalText = $(this).text();
            const type = $(this).data('type');
            const id = $(this).data('id');
            $(this).attr('contenteditable', 'true').focus();

            setTimeout(() => {
                const range = document.createRange();
                const elem = $(this)[0];
                if (elem && elem.childNodes.length > 0) {
                    range.selectNodeContents(elem);
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }, 10);

            const saveEdit = () => {
                const newText = $(this).text().trim();
                if (!newText) {
                    $(this).text(originalText);
                    $(this).attr('contenteditable', 'false');
                    return;
                }
                if (newText === originalText) {
                    $(this).attr('contenteditable', 'false');
                    return;
                }
                $(this).attr('contenteditable', 'false');
                $.post('/zen/reqHub/actions/inline_edit_action.php', {
                    action: 'editLabel',
                    type,
                    id,
                    new_name: newText
                }, function(res) {
                    if (!res.success) $(this).text(originalText);
                    else $(this).text(res.new_name);
                }, 'json');
            };

            $(this).on('blur', saveEdit);
            $(this).on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $(this).off('blur', saveEdit);
                    saveEdit.call(this);
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    $(this).text(originalText);
                    $(this).attr('contenteditable', 'false');
                    $(this).off('blur', saveEdit);
                }
            });
        });

        $(document).on('click', function() {
            $('.editable-label[contenteditable="true"]').each(function() {
                $(this).trigger('blur');
            });
        });

        // ============================================================
        // DELETE HANDLER
        // ============================================================
        $(document).on('click', '[data-action^="delete"]', function() {
            if (!confirm('Are you sure?')) return;
            const button = $(this),
                action = button.data('action');
            let url = '',
                data = {
                    action
                };
            switch (action) {
                case 'deleteAction':
                    url = '/zen/reqHub/actions/action_action.php';
                    data.id = button.data('id');
                    break;
                case 'deleteModule':
                    url = '/zen/reqHub/actions/module_action.php';
                    data.module_id = button.data('module-id');
                    break;
                case 'deleteRole':
                    url = '/zen/reqHub/actions/role_action.php';
                    data.role_id = button.data('role-id');
                    break;
                case 'deleteSystem':
                    url = '/zen/reqHub/actions/system_action.php';
                    data.system_id = button.data('system-id');
                    break;
                case 'deleteUser':
                    url = '/zen/reqHub/actions/user_action.php';
                    data.user_id = button.data('user-id');
                    break;
                default:
                    return;
            }
            $.post(url, data, function(res) {
                if (!res.success) {
                    alert(res.message || 'Failed to delete');
                    return;
                }
                switch (action) {
                    case 'deleteAction':
                        button.closest('.action-item').slideUp(200, function() {
                            $(this).remove();
                        });
                        break;
                    case 'deleteModule':
                        button.closest('.module-item').slideUp(200, function() {
                            $(this).remove();
                        });
                        delete moduleAssignments[data.module_id];
                        break;
                    case 'deleteRole':
                        button.closest('.role-item').slideUp(200, function() {
                            $(this).remove();
                        });
                        delete roleAssignments[data.role_id];
                        break;
                    case 'deleteSystem':
                        button.closest('.system-item').slideUp(200, function() {
                            $(this).remove();
                        });
                        delete systemRoles[data.system_id];
                        break;
                    case 'deleteUser':
                        button.closest('.user-item').slideUp(200, function() {
                            $(this).remove();
                        });
                        break;
                }
            }, 'json');
        });

        // ============================================================
        // OPEN MODAL (Add/Edit buttons)
        // ============================================================
        $(document).on('click', '[data-action]:not([data-action^="delete"]):not([data-action="duplicateSystem"])', function() {
            const action = $(this).data('action');
            const modalDialog = $('#accessTypeModal .modal-dialog');
            modalDialog.removeClass('modal-sm modal-md modal-lg modal-xl');
            void modalDialog[0].offsetWidth;

            if (action.includes('Action')) modalDialog.addClass('modal-sm');
            else if (action.includes('Role')) modalDialog.addClass('modal-xl');
            else if (action.includes('Module')) modalDialog.addClass('modal-xl');
            else if (action.includes('System')) modalDialog.addClass('modal-xl');
            else if (action.includes('User')) modalDialog.addClass('modal-lg');
            else modalDialog.addClass('modal-md');

            $('#modalAction').val(action);
            $('#modalRole').val($(this).data('role-id') || '');
            $('#modalModule').val($(this).data('module-id') || '');
            $('#modalUser').val($(this).data('user-id') || '');
            $('#modalSystem').val($(this).data('system-id') || '');
            $('#modalActionId').val($(this).data('id') || '');
            const name = ($(this).data('name') || '').toString();

            $('#modalWarning, #modalInputGroup').hide();

            // ---- Action ----
            if (action.includes('Action')) {
                $('#modalInputGroup').show();
                $('#modalInputLabel').text('Action Name');
                $('#modalInput').val(name).attr('placeholder', 'Enter name').focus();
            }

            // ---- Module ----
            if (action.includes('Module')) {
                $('#modalInputGroup').show();
                $('#modalInputLabel').text('Module Name');
                $('#modalInput').val(name.trim()).attr('placeholder', 'Enter name');
                $('#modalInput').attr('disabled', action === 'editModule');
                if (action !== 'editModule') $('#modalInput').focus();

                let html = '<div class="d-flex justify-content-between align-items-center mb-2"><strong>Select Actions</strong><input type="text" class="form-control" id="searchModuleActions" placeholder="Search..." style="max-width:250px;"></div><div class="module-modal-grid">';
                $('.action-item').each(function() {
                    const aid = $(this).find('.editable-label').data('id');
                    const aname = $(this).find('.editable-label').text().trim();
                    html += `<label class="role-modal-action-item" data-action-name="${aname.toLowerCase()}"><input type="checkbox" class="module-action-checkbox" value="${aid}"><span>${htmlEscape(aname)}</span></label>`;
                });
                html += '</div>';
                $('#permissionsContainer').html(html);

                const moduleId = $(this).data('module-id');
                if (moduleId && moduleAssignments[moduleId]) {
                    moduleAssignments[moduleId].forEach(aid => {
                        $(`.module-action-checkbox[value="${aid}"]`).prop('checked', true);
                    });
                }

                setTimeout(() => {
                    $(document).off('keyup', '#searchModuleActions').on('keyup', '#searchModuleActions', function() {
                        const q = $(this).val().toLowerCase();
                        $('.role-modal-action-item').each(function() {
                            $(this).toggle($(this).data('action-name').includes(q));
                        });
                    });
                    $('#permissionsContainer').off('change', '.module-action-checkbox').on('change', '.module-action-checkbox', updateModalSummary);
                    updateModalSummary();
                }, 100);
            }

            // ---- Role ----
            if (action.includes('Role')) {
                $('#modalInputGroup').show();
                $('#modalInputLabel').text('Role Name');
                $('#modalInput').val(name.trim()).attr('placeholder', 'Enter name');
                $('#modalInput').attr('disabled', action === 'editRole');
                if (action !== 'editRole') $('#modalInput').focus();

                const roleId = $(this).data('role-id') || null;
                buildRoleModalPanels(roleId);
            }

            // ---- System ----
            // Role assignment via system modal disabled — managed from Roles tab instead
            if (action.includes('System')) {
                if (action === 'editSystem') return; // editing disabled
                // addSystem: name only
                $('#modalInputGroup').show();
                $('#modalInputLabel').text('System Name');
                $('#modalInput').val('').attr('placeholder', 'Enter name').attr('disabled', false).focus();
                $('#permissionsContainer').html('');
                getModal().show();
                return;
            }

            // // ---- System ----
            // if (action.includes('System')) {
            //     $('#modalInputGroup').show();
            //     $('#modalInputLabel').text('System Name');
            //     $('#modalInput').val(name.trim()).attr('placeholder', 'Enter name');
            //     $('#modalInput').attr('disabled', action === 'editSystem');
            //     if (action !== 'editSystem') $('#modalInput').focus();

            //     const systemId = $(this).data('system-id');
            //     const assignedRoles = systemRoles[systemId] ? systemRoles[systemId].map(r => r.role_id) : [];
            //     let html = '<div class="d-flex justify-content-between align-items-center mb-2"><strong>Assign Roles to This System</strong><input type="text" class="form-control" id="searchSystemRoles" placeholder="Search..." style="max-width:250px;"></div><div class="module-modal-grid">';
            //     $('.role-item').each(function() {
            //         const rid = $(this).find('.editable-label').data('id');
            //         const rname = $(this).find('.editable-label').text().trim();
            //         html += `<label class="role-modal-action-item" data-role-name="${rname.toLowerCase()}"><input type="checkbox" class="system-role-checkbox" value="${rid}" ${assignedRoles.includes(parseInt(rid)) ? 'checked' : ''}><span>${htmlEscape(rname)}</span></label>`;
            //     });
            //     html += '</div>';
            //     $('#permissionsContainer').html(html);

            //     setTimeout(() => {
            //         $(document).off('keyup', '#searchSystemRoles').on('keyup', '#searchSystemRoles', function() {
            //             const q = $(this).val().toLowerCase();
            //             $('.role-modal-action-item').each(function() {
            //                 $(this).toggle($(this).data('role-name').includes(q));
            //             });
            //         });
            //         $('#permissionsContainer').off('change', '.system-role-checkbox').on('change', '.system-role-checkbox', updateModalSummary);
            //         updateModalSummary();
            //     }, 100);
            //     getModal().show();
            //     return;
            // }

            // ---- User ----
            if (action.includes('User')) {
                $('#modalInputGroup').show();
                $('#modalInputLabel').text('Employee');

                if (action === 'addUser') {
                    $('#modalInput').replaceWith(`<select class="form-select" name="name" id="modalInput">
                    <option value="">-- Select Employee --</option>
                    <?php foreach ($hrUsersList as $hrUser): ?>
                    <option value="<?= htmlspecialchars($hrUser['bi_empno']) ?>">
                        <?= htmlspecialchars(trim($hrUser['full_name'])) ?> (<?= htmlspecialchars($hrUser['bi_empno']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>`);
                } else {
                    if ($('#modalInput').is('select')) {
                        $('#modalInput').replaceWith('<input type="text" class="form-control" name="name" id="modalInput" autocomplete="off" spellcheck="false">');
                    }
                    $('#modalInput').val(name.trim()).attr('disabled', true);
                }

                const userType = $(this).data('user-role') || 'Requestor';
                const userId = $(this).data('user-id') || '';

                // User type select
                let html = `<div class="mt-3">
                <label class="form-label">User Type</label>
                <select name="user_type" class="form-select user-type-select" required>
                    <option value="No Access"  ${userType==='No Access'  ?'selected':''}>No Access</option>
                    <option value="Requestor"  ${userType==='Requestor'  ?'selected':''}>Requestor</option>
                    <option value="Approver"   ${userType==='Approver'   ?'selected':''}>Approver</option>
                    <option value="Admin"      ${userType==='Admin'      ?'selected':''}>Admin</option>
                    <option value="Reviewer"   ${userType==='Reviewer'   ?'selected':''}>Reviewer</option>
                </select>
            </div>`;

                // Collect existing system assignments
                const selectedSystems = [];
                if (userId && approverAssignments[parseInt(userId)]) {
                    approverAssignments[parseInt(userId)].forEach(a => {
                        if (a.system_id && !selectedSystems.includes(parseInt(a.system_id))) {
                            selectedSystems.push(parseInt(a.system_id));
                        }
                    });
                }

                // Collect existing department assignments (for Reviewer)
                const selectedDepts = [];
                if (userId && approverAssignments[parseInt(userId)]) {
                    approverAssignments[parseInt(userId)].forEach(a => {
                        if (a.department_id && !selectedDepts.includes(a.department_id)) {
                            selectedDepts.push(a.department_id);
                        }
                    });
                }

                // System checkboxes (shown for Approver and Requestor)
                html += `<div class="mt-3 system-assignment-selectors" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">System(s)</label>
                    <div class="form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="selectAllSystems">
                        <label class="form-check-label small text-muted" for="selectAllSystems">Select All</label>
                    </div>
                </div>
                <div class="border rounded p-2 bg-light" style="max-height:200px; overflow-y:auto;">`;
                $('.system-item').each(function() {
                    const sysId = $(this).data('system-id');
                    const sysName = $(this).find('.editable-label').text().trim();
                    const isSelected = selectedSystems.includes(parseInt(sysId));
                    html += `<div class="form-check">
                    <input type="checkbox" class="form-check-input system-checkbox" id="system-${sysId}" value="${sysId}" ${isSelected ? 'checked' : ''}>
                    <label class="form-check-label" for="system-${sysId}">${htmlEscape(sysName)}</label>
                </div>`;
                });
                html += `</div></div>`;

                // System checkboxes (shown for Reviewer alongside departments)
                html += `<div class="mt-3 reviewer-system-assignment-selectors" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">System(s)</label>
                    <div class="form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="selectAllReviewerSystems">
                        <label class="form-check-label small text-muted" for="selectAllReviewerSystems">Select All</label>
                    </div>
                </div>
                <div class="border rounded p-2 bg-light" style="max-height:200px; overflow-y:auto;">`;
                $('.system-item').each(function() {
                    const sysId = $(this).data('system-id');
                    const sysName = $(this).find('.editable-label').text().trim();
                    const isSelected = selectedSystems.includes(parseInt(sysId));
                    html += `<div class="form-check">
                        <input type="checkbox" class="form-check-input reviewer-system-checkbox" id="reviewer-system-${sysId}" value="${sysId}" ${isSelected ? 'checked' : ''}>
                        <label class="form-check-label" for="reviewer-system-${sysId}">${htmlEscape(sysName)}</label>
                    </div>`;
                });
                html += `</div></div>`;

                // Department checkboxes (shown for Reviewer)
                html += `<div class="mt-3 department-assignment-selectors" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">Department(s)</label>
                    <div class="form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="selectAllDepts">
                        <label class="form-check-label small text-muted" for="selectAllDepts">Select All</label>
                    </div>
                </div>
                <div class="border rounded p-2 bg-light" style="max-height:200px; overflow-y:auto;">`;
                if (hrActiveDepartments.length > 0) {
                    hrActiveDepartments.forEach(dept => {
                        const deptCode = htmlEscape(dept.Dept_Code);
                        const deptName = htmlEscape(dept.Dept_Name);
                        const isSelected = selectedDepts.includes(dept.Dept_Code);
                        html += `<div class="form-check">
                        <input type="checkbox" class="form-check-input department-checkbox" id="dept-${deptCode}" value="${deptCode}" ${isSelected ? 'checked' : ''}>
                        <label class="form-check-label" for="dept-${deptCode}">${deptName}</label>
                    </div>`;
                    });
                } else {
                    html += `<span class="text-muted small">No active departments found</span>`;
                }
                html += `</div></div>`;

                $('#permissionsContainer').html(html);

                const updateSelectors = () => {
                    const type = $('.user-type-select').val();
                    if (rolesWithSystemAssignment.includes(type)) {
                        $('.system-assignment-selectors').show();
                        $('.department-assignment-selectors').hide();
                        $('.reviewer-system-assignment-selectors').hide();
                        $('.department-checkbox').prop('checked', false);
                        $('.reviewer-system-checkbox').prop('checked', false);
                    } else if (rolesWithDepartmentAssignment.includes(type)) {
                        $('.department-assignment-selectors').show();
                        $('.reviewer-system-assignment-selectors').show();
                        $('.system-assignment-selectors').hide();
                        $('.system-checkbox').prop('checked', false);
                    } else {
                        $('.system-assignment-selectors').hide();
                        $('.department-assignment-selectors').hide();
                        $('.reviewer-system-assignment-selectors').hide();
                        $('.system-checkbox').prop('checked', false);
                        $('.department-checkbox').prop('checked', false);
                        $('.reviewer-system-checkbox').prop('checked', false);
                    }
                };

                updateSelectors();
                $(document).off('change', '.user-type-select').on('change', '.user-type-select', updateSelectors);

                // Select All — systems
                $(document).off('change', '#selectAllSystems').on('change', '#selectAllSystems', function() {
                    $('.system-checkbox').prop('checked', $(this).is(':checked'));
                });
                $(document).off('change', '.system-checkbox').on('change', '.system-checkbox', function() {
                    const total = $('.system-checkbox').length;
                    const checked = $('.system-checkbox:checked').length;
                    $('#selectAllSystems').prop('checked', total > 0 && checked === total)
                                         .prop('indeterminate', checked > 0 && checked < total);
                });

                // Select All — reviewer systems
                $(document).off('change', '#selectAllReviewerSystems').on('change', '#selectAllReviewerSystems', function() {
                    $('.reviewer-system-checkbox').prop('checked', $(this).is(':checked'));
                });
                $(document).off('change', '.reviewer-system-checkbox').on('change', '.reviewer-system-checkbox', function() {
                    const total = $('.reviewer-system-checkbox').length;
                    const checked = $('.reviewer-system-checkbox:checked').length;
                    $('#selectAllReviewerSystems').prop('checked', total > 0 && checked === total)
                                                  .prop('indeterminate', checked > 0 && checked < total);
                });

                // Select All — departments
                $(document).off('change', '#selectAllDepts').on('change', '#selectAllDepts', function() {
                    $('.department-checkbox').prop('checked', $(this).is(':checked'));
                });
                $(document).off('change', '.department-checkbox').on('change', '.department-checkbox', function() {
                    const total = $('.department-checkbox').length;
                    const checked = $('.department-checkbox:checked').length;
                    $('#selectAllDepts').prop('checked', total > 0 && checked === total)
                                        .prop('indeterminate', checked > 0 && checked < total);
                });

                // Seed the Select All checkbox state based on pre-checked items
                setTimeout(() => {
                    const sysTotal = $('.system-checkbox').length;
                    const sysChecked = $('.system-checkbox:checked').length;
                    $('#selectAllSystems').prop('checked', sysTotal > 0 && sysChecked === sysTotal)
                                         .prop('indeterminate', sysChecked > 0 && sysChecked < sysTotal);
                    const deptTotal = $('.department-checkbox').length;
                    const deptChecked = $('.department-checkbox:checked').length;
                    $('#selectAllDepts').prop('checked', deptTotal > 0 && deptChecked === deptTotal)
                                        .prop('indeterminate', deptChecked > 0 && deptChecked < deptTotal);
                }, 50);
            }

            getModal().show();

            if ($('#modalAction').val().includes('User')) {
                setTimeout(() => {
                    $('#modalInput').focus();
                }, 100);
            }
        });

        // ============================================================
        // RE-APPLY SIZE ON SHOW
        // ============================================================
        $('#accessTypeModal').on('show.bs.modal', function() {
            const action = $('#modalAction').val();
            const d = $('#accessTypeModal .modal-dialog');
            d.removeClass('modal-sm modal-md modal-lg modal-xl');
            if (action.includes('Action')) d.addClass('modal-sm');
            else if (action.includes('Role')) d.addClass('modal-xl');
            else if (action.includes('Module')) d.addClass('modal-xl');
            else if (action.includes('System')) d.addClass('modal-xl');
            else if (action.includes('User')) d.addClass('modal-lg');
            else d.addClass('modal-md');
        });

        // ============================================================
        // DUPLICATE SYSTEM
        // ============================================================
        $(document).on('click', '[data-action="duplicateSystem"]', function() {
            const sourceSystemId = $(this).data('system-id');
            const sourceSystemName = $(this).data('system-name');
            if (!duplicateSystemCounter[sourceSystemId]) duplicateSystemCounter[sourceSystemId] = 1;
            else duplicateSystemCounter[sourceSystemId]++;
            const newSystemName = sourceSystemName + ' Copy ' + duplicateSystemCounter[sourceSystemId];

            $.post('/zen/reqHub/actions/system_action.php', {
                action: 'duplicateSystem',
                source_system_id: sourceSystemId,
                new_system_name: newSystemName
            }, function(res) {
                if (!res.success) {
                    alert(res.message);
                    return;
                }
                const rolesHtml = buildRolesHtml(systemRoles[sourceSystemId] || []);
                $('.systems-list').append(buildSystemCardHtml(res.id, res.name || newSystemName, rolesHtml));
                systemRoles[res.id] = systemRoles[sourceSystemId] || [];
                alert('System duplicated: ' + newSystemName);
            }, 'json');
        });

        // ============================================================
        // CHECKBOX SUMMARY LISTENERS
        // ============================================================
        $(document).on('change', '.module-action-checkbox', updateModalSummary);
        $(document).on('change', '.system-role-checkbox', updateModalSummary);

        // ============================================================
        // SEARCH
        // ============================================================
        $(document).on('keyup', '#searchActions', function() {
            const q = $(this).val().toLowerCase();
            $('.action-item').each(function() {
                $(this).toggle($(this).find('.editable-label').text().toLowerCase().includes(q));
            });
        });
        $(document).on('keyup', '#searchModules', function() {
            const q = $(this).val().toLowerCase();
            $('.module-item').each(function() {
                $(this).toggle($(this).find('.editable-label').text().toLowerCase().includes(q));
            });
        });
        $(document).on('keyup', '#searchRoles', function() {
            const q = $(this).val().toLowerCase();
            $('.role-item').each(function() {
                $(this).toggle($(this).find('.editable-label').text().toLowerCase().includes(q));
            });
        });
        $(document).on('keyup', '#searchSystems', function() {
            const q = $(this).val().toLowerCase();
            $('.system-item').each(function() {
                $(this).toggle($(this).find('.editable-label').text().toLowerCase().includes(q));
            });
        });

        function applyUserFilters() {
            const query = $('#searchUsers').val().toLowerCase();
            const checkedDepts = $('.user-dept-filter:checked').map(function() {
                return $(this).val();
            }).get();
            const checkedRoles = $('.user-role-filter:checked').map(function() {
                return $(this).val();
            }).get();
            const checkedActive = $('.user-active-filter:checked').map(function() {
                return $(this).val();
            }).get();
            $('.user-item').each(function() {
                const nameText = $(this).find('strong').first().text().toLowerCase();
                const matchSearch = nameText.includes(query);
                let matchDept = true;
                if (checkedDepts.length > 0) {
                    const userDept = ($(this).data('hr-dept') || '').toString().toLowerCase();
                    matchDept = checkedDepts.includes(userDept);
                }
                let matchRole = true;
                if (checkedRoles.length > 0) {
                    const userRole = ($(this).data('role') || '').toString().toLowerCase();
                    matchRole = checkedRoles.includes(userRole);
                }
                let matchActive = true;
                if (checkedActive.length > 0) {
                    const userActive = ($(this).data('active') || '').toString().toLowerCase();
                    matchActive = checkedActive.includes(userActive);
                }
                $(this).toggle(matchSearch && matchDept && matchRole && matchActive);
            });
        }
        $(document).on('keyup', '#searchUsers', applyUserFilters);
        $(document).on('change', '.user-dept-filter', applyUserFilters);
        $(document).on('change', '.user-role-filter', applyUserFilters);
        $(document).on('change', '.user-active-filter', applyUserFilters);
        $(document).on('click', '#clearDeptFilter', function() {
            $('.user-dept-filter').prop('checked', false);
            $('.user-role-filter').prop('checked', false);
            $('.user-active-filter').prop('checked', false);
            applyUserFilters();
        });
        $(document).on('click', '#filterUsersBtn', function(e) {
            e.stopPropagation();
            $('#filterUsersDropdown').toggleClass('show');
        });
        $(document).on('click', '#filterUsersDropdown', function(e) {
            e.stopPropagation();
        });
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#filterUsersBtn, #filterUsersDropdown').length) $('#filterUsersDropdown').removeClass('show');
        });

        // ============================================================
        // MODAL CLOSE
        // ============================================================
        $(document).on('click', '[data-bs-dismiss="modal"]', function() {
            getModal().hide();
        });
        $(document).on('click', '.btn-close', function() {
            getModal().hide();
        });
        $('#accessTypeModal').on('hidden.bs.modal', function() {
            $('#accessTypeForm')[0].reset();
            if ($('#modalInput').is('select')) {
                $('#modalInput').replaceWith('<input type="text" class="form-control" name="name" id="modalInput" placeholder="Enter name" autocomplete="off" spellcheck="false">');
            }
            $('#permissionsContainer').empty();
            $('#modalWarning, #modalInputGroup').hide();
            $('#modalSummary').html('<em>None selected</em>');
            $('#summaryColumn').hide();
            $(document).off('change', '.user-type-select');
        });

        // ============================================================
        // HTML BUILDERS (for dynamic DOM updates)
        // ============================================================
        function buildRolesHtml(roles) {
            if (!roles || roles.length === 0) return '<span class="text-muted">No roles assigned</span>';
            let html = '<strong class="d-block mb-2">▼ Roles</strong><div class="ps-3">';
            roles.forEach(sysRole => {
                const roleId = sysRole.role_id;
                const roleName = sysRole.role_name;
                const perms = roleAssignments[roleId] || [];
                html += `<div class="system-role-item mb-3"><button class="btn btn-sm btn-outline-secondary me-2 toggle-system-role-modules">+</button><span>${htmlEscape(roleName)}</span><div class="system-role-modules mt-2 ps-3" style="display:none;">`;
                const modulesByName = {};
                perms.forEach(p => {
                    if (!modulesByName[p.module_id]) modulesByName[p.module_id] = {
                        name: p.module_name || '',
                        actions: []
                    };
                    if (p.action_name) modulesByName[p.module_id].actions.push(p.action_name);
                });
                Object.keys(modulesByName).forEach(modId => {
                    const modData = modulesByName[modId];
                    html += `<div class="system-module-item mb-2"><span>${htmlEscape(modData.name)}</span><div class="mt-1 ps-3">`;
                    modData.actions.forEach(actName => {
                        html += `<span class="badge bg-light text-dark me-2 mb-2">• ${htmlEscape(actName)}</span>`;
                    });
                    html += '</div></div>';
                });
                html += '</div></div>';
            });
            html += '</div>';
            return html;
        }

        // buildSystemCardHtml: simplified — no editable label, no duplicate, no roles container (display-only tab)
        function buildSystemCardHtml(id, name) {
            return `<div class="system-item card p-3 mb-3" data-system-id="${id}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center flex-grow-1">
                    <span class="flex-grow-1 ps-1 system-display-name">${htmlEscape(name)}</span>
                </div>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-danger" data-action="deleteSystem" data-system-id="${id}">×</button>
                </div>
            </div>
        </div>`;
        }

        // ============================================================
        // ROLE MODAL PANEL BUILDER
        // ============================================================
        function buildRoleModalPanels(roleId) {
            // Determine which systems this role belongs to
            const assignedSystemIds = [];
            if (roleId && roleAssignments[roleId]) {
                Object.keys(roleAssignments[roleId]).forEach(sysKey => {
                    if (sysKey !== 'null') assignedSystemIds.push(sysKey);
                });
            }

            // Build tab bar: Global + one per assigned system + Add System button
            let tabBarHtml = '<div class="d-flex align-items-center gap-1 flex-wrap mb-3" id="rolePanelTabBar">';
            // tabBarHtml += `<button type="button" class="btn btn-sm role-panel-tab active" data-tab-target="panel-null">Global</button>`;
            assignedSystemIds.forEach(sysId => {
                const sysName = $(`.system-item[data-system-id="${sysId}"]`).find('.editable-label').text().trim();
                tabBarHtml += `<button type="button" class="btn btn-sm role-panel-tab" data-tab-target="panel-${sysId}">${htmlEscape(sysName)}</button>`;
            });
            tabBarHtml += `<div class="position-relative ms-1" id="addSystemPanelWrapper" style="flex-shrink:0;">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="addSystemPanelBtn">+ Add System</button>
                <div id="addSystemPanelMenu" style="display:none; position:fixed; z-index:9999;
                    background:#fff; border:1px solid #dee2e6;
                    border-radius:6px; min-width:180px; padding:4px 0; box-shadow:0 4px 12px rgba(0,0,0,.15);">`;
            $('.system-item').each(function() {
                const sysId = $(this).data('system-id').toString();
                const sysName = $(this).find('.editable-label').text().trim();
                const alreadyAdded = assignedSystemIds.includes(sysId);
                tabBarHtml += `<button type="button" class="add-system-panel-item"
                    style="display:${alreadyAdded ? 'none' : 'block'}; width:100%; text-align:left;
                    padding:6px 14px; background:none; border:none; cursor:pointer;
                    color:var(--color-text-primary); font-size:13px;"
                    data-sys-id="${sysId}" data-sys-name="${htmlEscape(sysName)}">${htmlEscape(sysName)}</button>`;
            });
            tabBarHtml += `</div></div></div>`;

            // Build panels (one per tab, all rendered but hidden)
            const allPanelSysKeys = [...assignedSystemIds.map(String)];
            let panelsHtml = '';
            allPanelSysKeys.forEach((sysKey, idx) => {
                const isGlobal = sysKey === 'null';
                const sysLabel = isGlobal ? 'Global' : $(`.system-item[data-system-id="${sysKey}"]`).find('.editable-label').text().trim();
                panelsHtml += `<div class="role-system-panel" data-panel-system-id="${sysKey}" id="panel-${sysKey}" style="${idx !== 0 ? 'display:none;' : ''}">`;
                panelsHtml += `<div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">${isGlobal ? 'Permissions here apply to all systems this role is assigned to.' : `Permissions exclusive to <strong>${htmlEscape(sysLabel)}</strong>.`}</span>
                    <div class="d-flex gap-2 align-items-center">
                        ${!isGlobal ? `<button type="button" class="btn btn-sm btn-outline-danger remove-system-panel-tab" data-sys-key="${sysKey}">Remove</button>` : ''}
                        <input type="text" class="form-control form-control-sm role-module-search" placeholder="Search modules..." style="max-width:200px;">
                    </div>
                </div>`;
                panelsHtml += `<div class="role-module-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px;">`;
                $('.module-item').each(function() {
                    const mid = $(this).find('.editable-label').data('id');
                    const mname = $(this).find('.editable-label').text().trim();
                    panelsHtml += `<div class="role-modal-module-card" data-module-name="${mname.toLowerCase()}">
                        <div class="role-modal-module-header d-flex align-items-center gap-1 mb-1">
                            <label style="cursor:pointer; display:flex; align-items:center; gap:4px; margin:0; font-size:0.82rem; font-weight:600;">
                                <input type="checkbox" class="module-master-checkbox" data-panel="${sysKey}" data-module="${mid}">
                                ${htmlEscape(mname)}
                            </label>
                        </div>
                        <div class="d-flex flex-column gap-1">`;
                    (moduleAssignments[mid] || []).forEach(aid => {
                        const actionItem = $(`.action-item .editable-label[data-id="${aid}"]`).closest('.action-item');
                        if (actionItem.length) {
                            const aname = actionItem.find('.editable-label').text().trim();
                            panelsHtml += `<label class="role-modal-action-item" style="font-size:0.78rem; padding:2px 6px;">
                                <input type="checkbox" class="role-permission-checkbox" data-panel="${sysKey}" data-module="${mid}" value="${aid}">
                                <span>${htmlEscape(aname)}</span>
                            </label>`;
                        }
                    });
                    panelsHtml += `</div></div>`;
                });
                panelsHtml += `</div></div>`;
            });

            $('#permissionsContainer').html(tabBarHtml + panelsHtml);

            // Load saved permissions into checkboxes
            if (roleId && roleAssignments[roleId]) {
                allPanelSysKeys.forEach(sysKey => {
                    const perms = roleAssignments[roleId][sysKey] || [];
                    perms.forEach(p => {
                        $(`#panel-${sysKey} .role-permission-checkbox[data-module="${p.module_id}"][value="${p.action_id}"]`).prop('checked', true);
                    });
                    // Sync master checkboxes
                    $(`#panel-${sysKey} .module-master-checkbox`).each(function() {
                        const mid = $(this).data('module');
                        const total = $(`#panel-${sysKey} .role-permission-checkbox[data-module="${mid}"]`).length;
                        const checked = $(`#panel-${sysKey} .role-permission-checkbox[data-module="${mid}"]:checked`).length;
                        $(this).prop('checked', checked === total && total > 0);
                        $(this).prop('indeterminate', checked > 0 && checked < total);
                    });
                });
            }

            updateModalSummary();

            // ── Tab switching ──
            $(document).off('click', '.role-panel-tab').on('click', '.role-panel-tab', function() {
                $('.role-panel-tab').removeClass('active btn-dark btn-secondary').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('active btn-dark');
                const target = $(this).data('tab-target');
                $('.role-system-panel').hide();
                $(`#${target}`).show();
                updateModalSummary();
            });
            $(document).off('click.addSysBtn').on('click.addSysBtn', '#addSystemPanelBtn', function(e) {
                e.stopPropagation();
                const menu = $('#addSystemPanelMenu');
                if (menu.is(':visible')) {
                    menu.hide();
                    return;
                }
                const rect = $(this)[0].getBoundingClientRect();
                menu.css({ top: rect.bottom + 4, left: rect.left }).show();
            });

            $(document).off('click.closeSysMenu').on('click.closeSysMenu', function() {
                $('#addSystemPanelMenu').hide();
            });
            // Style active tab on init
            $('.role-panel-tab.active').removeClass('btn-outline-secondary').addClass('btn-dark');
            $('.role-panel-tab:not(.active)').addClass('btn-outline-secondary');

            // ── Add System panel ──
            $(document).off('click', '.add-system-panel-item').on('click', '.add-system-panel-item', function() {
                const sysId = $(this).data('sys-id').toString();
                const sysName = $(this).data('sys-name');

                $(this)
                    .prop('disabled', true)
                    .css({ opacity: 0.5, cursor: 'not-allowed' })
                    .text(sysName + ' ✓');

                $('#addSystemPanelMenu').hide();

                let newPanel = `<div class="role-system-panel" data-panel-system-id="${sysId}" id="panel-${sysId}" style="display:none;">`;
                newPanel += `<div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Permissions exclusive to <strong>${htmlEscape(sysName)}</strong>.</span>
                    <div class="d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-system-panel-tab" data-sys-key="${sysId}">Remove</button>
                        <input type="text" class="form-control form-control-sm role-module-search" placeholder="Search modules..." style="max-width:200px;">
                    </div>
                </div>`;
                newPanel += `<div class="role-module-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px;">`;
                $('.module-item').each(function() {
                    const mid = $(this).find('.editable-label').data('id');
                    const mname = $(this).find('.editable-label').text().trim();
                    newPanel += `<div class="role-modal-module-card" data-module-name="${mname.toLowerCase()}">
                        <div class="role-modal-module-header d-flex align-items-center gap-1 mb-1">
                            <label style="cursor:pointer; display:flex; align-items:center; gap:4px; margin:0; font-size:0.82rem; font-weight:600;">
                                <input type="checkbox" class="module-master-checkbox" data-panel="${sysId}" data-module="${mid}">
                                ${htmlEscape(mname)}
                            </label>
                        </div>
                        <div class="d-flex flex-column gap-1">`;
                    (moduleAssignments[mid] || []).forEach(aid => {
                        const actionItem = $(`.action-item .editable-label[data-id="${aid}"]`).closest('.action-item');
                        if (actionItem.length) {
                            const aname = actionItem.find('.editable-label').text().trim();
                            newPanel += `<label class="role-modal-action-item" style="font-size:0.78rem; padding:2px 6px;">
                                <input type="checkbox" class="role-permission-checkbox" data-panel="${sysId}" data-module="${mid}" value="${aid}">
                                <span>${htmlEscape(aname)}</span>
                            </label>`;
                        }
                    });
                    newPanel += `</div></div>`;
                });
                newPanel += `</div></div>`;

                $('#permissionsContainer').append(newPanel);

                $('#addSystemPanelWrapper').before(
                    `<button type="button" class="btn btn-sm btn-outline-secondary role-panel-tab" data-tab-target="panel-${sysId}">${htmlEscape(sysName)}</button>`
                );

                $('.role-panel-tab').removeClass('active btn-dark').addClass('btn-outline-secondary');
                $(`.role-panel-tab[data-tab-target="panel-${sysId}"]`).removeClass('btn-outline-secondary').addClass('active btn-dark');
                $('.role-system-panel').hide();
                $(`#panel-${sysId}`).show();

                updateModalSummary();
            });

            // ── Remove system panel tab ──
            $(document).off('keyup', '.role-module-search').on('keyup', '.role-module-search', function() {
                const q = $(this).val().toLowerCase();
                $(this).closest('.role-system-panel').find('.role-modal-module-card').each(function() {
                    $(this).toggle($(this).data('module-name').includes(q));
                });
            });

            $(document).off('click', '.remove-system-panel-tab').on('click', '.remove-system-panel-tab', function() {
                const sysKey = $(this).data('sys-key').toString();

                $(`#panel-${sysKey}`).remove();
                $(`.role-panel-tab[data-tab-target="panel-${sysKey}"]`).remove();

                const $menuItem = $(`.add-system-panel-item[data-sys-id="${sysKey}"]`);
                $menuItem
                    .prop('disabled', false)
                    .css({ opacity: '', cursor: '' })
                    .text($menuItem.data('sys-name'));

                $('.role-panel-tab').removeClass('active btn-dark').addClass('btn-outline-secondary');
               const $firstTab = $('.role-panel-tab').first();
                $firstTab.removeClass('btn-outline-secondary').addClass('active btn-dark');
                $('.role-system-panel').hide();
                $(`#${$firstTab.data('tab-target')}`).show();

                updateModalSummary();
            });

            // ── Module master checkbox ──
            $(document).off('change', '.module-master-checkbox').on('change', '.module-master-checkbox', function() {
                const mid = $(this).data('module');
                const panel = $(this).data('panel');
                $(`#panel-${panel} .role-permission-checkbox[data-module="${mid}"]`).prop('checked', $(this).is(':checked'));
                updateModalSummary();
            });

            // ── Action checkbox → sync master ──
            $(document).off('change', '.role-permission-checkbox').on('change', '.role-permission-checkbox', function() {
                const mid = $(this).data('module');
                const panel = $(this).data('panel');
                const total = $(`#panel-${panel} .role-permission-checkbox[data-module="${mid}"]`).length;
                const checked = $(`#panel-${panel} .role-permission-checkbox[data-module="${mid}"]:checked`).length;
                $(`#panel-${panel} .module-master-checkbox[data-module="${mid}"]`).prop('checked', checked === total && total > 0).prop('indeterminate', checked > 0 && checked < total);
                updateModalSummary();
            });
        }

        // ============================================================
        // FORM SUBMIT
        // ============================================================
        $('#accessTypeForm').submit(function(e) {
            e.preventDefault();
            const action = $('#modalAction').val();
            const submitBtn = $('#submitBtn');
            const origText = submitBtn.text();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

            let url = '',
                data = {
                    action
                };
            const nameVal = $('#modalInput').val().trim();

            if (['Role', 'Action', 'Module', 'System', 'User'].some(a => action.includes(a)) && !nameVal) {
                alert('Name cannot be empty');
                submitBtn.prop('disabled', false).text(origText);
                return;
            }
            data.name = nameVal;

            if (action.includes('Role')) {
                data.role_id = $('#modalRole').val();

                const systemPermissions = [];

                $('#permissionsContainer .role-system-panel').each(function() {
                    const panelSystemId = $(this).data('panel-system-id');
                    const permissions = [];
                    $(this).find('.role-permission-checkbox:checked').each(function() {
                        const moduleId = $(this).data('module');
                        const actionId = $(this).val();
                        const moduleName = $(this).closest('.role-modal-module-card').find('.role-modal-module-header strong').text().trim();
                        const actionName = $(this).closest('label').find('span').text().trim();
                        permissions.push({ module_id: moduleId, action_id: actionId, module_name: moduleName, action_name: actionName });
                    });
                    systemPermissions.push({
                        system_id: panelSystemId === 'null' ? null : panelSystemId,
                        permissions: permissions
                    });
                });

                data.system_permissions = JSON.stringify(systemPermissions);

                url = '/zen/reqHub/actions/role_action.php';
            }
            if (action.includes('Action')) {
                url = '/zen/reqHub/actions/action_action.php';
                data.id = $('#modalActionId').val();
            }
            if (action.includes('Module')) {
                url = '/zen/reqHub/actions/module_action.php';
                data.module_id = $('#modalModule').val();
                data.selected_actions = [];
                $('.module-action-checkbox:checked').each(function() {
                    data.selected_actions.push($(this).val());
                });
            }
            if (action.includes('System')) {
                url = '/zen/reqHub/actions/system_action.php';
                data.system_id = $('#modalSystem').val();
                data.role_ids = [];
                $('.system-role-checkbox:checked').each(function() {
                    data.role_ids.push($(this).val());
                });
            }
            if (action.includes('User')) {
                url = '/zen/reqHub/actions/user_action.php';
                data.user_id = $('#modalUser').val();
                data.user_type = $('#permissionsContainer select[name="user_type"]').val();
                data.system_ids = [];
                data.department_codes = [];
                $('#permissionsContainer .system-checkbox:checked, #permissionsContainer .reviewer-system-checkbox:checked').each(function() {
                    data.system_ids.push($(this).val().toString());
                });
                $('#permissionsContainer .department-checkbox:checked').each(function() {
                    data.department_codes.push($(this).val().toString());
                });
            }

            $.post(url, data, function(res) {
                submitBtn.prop('disabled', false).text(origText);
                if (!res.success) {
                    alert(res.message);
                    return;
                }

                if (action === 'addAction') {
                    const dn = res.name || data.name;
                    const html = `<div class="action-item card p-3 mb-2"><div class="d-flex justify-content-between align-items-center"><span class="editable-label" data-type="action" data-id="${res.id}">${htmlEscape(dn)}</span><div><button class="btn btn-sm btn-danger action-delete-btn" data-action="deleteAction" data-id="${res.id}">×</button></div></div></div>`;
                    $('.actions-grid').append(html);
                    if ($('#toggleDeleteModeActions').hasClass('btn-danger')) {
                        $('.actions-grid .action-item:last .action-delete-btn').show();
                    }
                }
                if (action === 'editAction') {
                    $(`.action-item .editable-label[data-id="${res.id}"]`).text(res.name || data.name);
                }

                if (action === 'addModule') {
                    const dn = res.name || data.name;
                    let actionsHtml = data.selected_actions && data.selected_actions.length > 0 ?
                        '<div class="d-flex flex-wrap gap-2">' + data.selected_actions.map(aid => {
                            const aItem = $(`.action-item .editable-label[data-id="${aid}"]`).closest('.action-item');
                            return aItem.length ? `<span class="badge bg-light text-dark" style="font-weight:normal;">• ${htmlEscape(aItem.find('.editable-label').text().trim())}</span>` : '';
                        }).join('') + '</div>' :
                        '<span class="text-muted">No actions assigned</span>';
                    const html = `<div class="module-item card p-3 mb-2"><div class="d-flex justify-content-between align-items-center mb-2 module-item-header"><div class="title d-flex align-items-center flex-shrink-1 overflow-hidden"><button class="btn btn-sm btn-outline-secondary me-2 toggle-module flex-shrink-0">+</button><span class="editable-label flex-grow-1" data-type="module" data-id="${res.id}">${htmlEscape(dn)}</span></div><div class="btn-group flex-shrink-0"><button class="btn btn-sm btn-danger" data-action="deleteModule" data-module-id="${res.id}">×</button></div></div><div class="module-actions mt-2" style="display:none;">${actionsHtml}</div></div>`;
                    $('.modules-grid').append(html);
                    moduleAssignments[res.id] = data.selected_actions ? [...data.selected_actions] : [];
                }
                if (action === 'editModule') {
                    const card = $(`.module-item .editable-label[data-id="${res.id}"]`).closest('.module-item');
                    card.find('.editable-label').text(res.name || data.name);
                    let actionsHtml = data.selected_actions && data.selected_actions.length > 0 ?
                        '<div class="d-flex flex-wrap gap-2">' + data.selected_actions.map(aid => {
                            const aItem = $(`.action-item .editable-label[data-id="${aid}"]`).closest('.action-item');
                            return aItem.length ? `<span class="badge bg-light text-dark" style="font-weight:normal;">• ${htmlEscape(aItem.find('.editable-label').text().trim())}</span>` : '';
                        }).join('') + '</div>' :
                        '<span class="text-muted">No actions assigned</span>';
                    card.find('.module-actions').html(actionsHtml);
                    moduleAssignments[res.id] = data.selected_actions ? [...data.selected_actions] : [];
                }

                if (action === 'addRole') {
                    const dn = res.name || data.name;
                    roleAssignments[res.id] = {};

                    const parsedSysPerms = JSON.parse(data.system_permissions || '[]');

                    parsedSysPerms.forEach(sp => {
                        const sysKey = sp.system_id || 'null';
                        roleAssignments[res.id][sysKey] = (sp.permissions || []).map(p => ({
                            module_id: parseInt(p.module_id),
                            action_id: parseInt(p.action_id),
                            module_name: p.module_name || '',
                            action_name: p.action_name || '',
                            system_id: sp.system_id || null
                        }));
                    });

                    let permHtml = '';
                    if (parsedSysPerms.length > 0) {
                        parsedSysPerms.forEach(sp => {
                            let sysLabel = 'Global';
                            if (sp.system_id) {
                                $('.system-item').each(function() {
                                    if ($(this).data('system-id') == sp.system_id) {
                                        sysLabel = $(this).find('.editable-label').text().trim();
                                        return false;
                                    }
                                });
                            }
                            permHtml += `<div class="mb-2"><span class="badge bg-secondary mb-1">${htmlEscape(sysLabel)}</span>`;
                            const byMod = {};
                            (sp.permissions || []).forEach(p => {
                                if (!byMod[p.module_id]) byMod[p.module_id] = {
                                    name: p.module_name || '',
                                    actions: []
                                };
                                byMod[p.module_id].actions.push(p.action_name || '');
                            });
                            Object.keys(byMod).forEach(mid => {
                                permHtml += `<span class="d-block ps-3" style="font-weight:normal;">${htmlEscape(byMod[mid].name)}</span><div class="d-flex flex-wrap gap-2 ps-3">${byMod[mid].actions.map(a => `<span class="badge bg-light text-dark" style="font-weight:normal;">• ${htmlEscape(a)}</span>`).join('')}</div>`;
                            });
                            permHtml += '</div>';
                        });
                    } else {
                        permHtml = '<span class="text-muted">No permissions assigned</span>';
                    }
                    const html = `<div class="role-item card p-3 mb-2"><div class="d-flex justify-content-between align-items-center mb-2"><div class="d-flex align-items-center"><button class="btn btn-sm btn-outline-secondary me-2 toggle-role">+</button><span class="editable-label" data-type="role" data-id="${res.id}">${htmlEscape(dn)}</span></div><div><button class="btn btn-sm btn-danger" data-action="deleteRole" data-role-id="${res.id}">×</button></div></div><div class="role-permissions mt-2" style="display:none;">${permHtml}</div></div>`;
                    $('.roles-grid').append(html);
                }
                if (action === 'editRole') {
                    const card = $(`.role-item .editable-label[data-id="${res.id}"]`).closest('.role-item');
                    card.find('.editable-label').text(res.name || data.name);

                    if (!roleAssignments[res.id]) roleAssignments[res.id] = {};

                    (JSON.parse(data.system_permissions || '[]')).forEach(sp => {
                        const sysKey = sp.system_id || 'null';
                        roleAssignments[res.id][sysKey] = (sp.permissions || []).map(p => ({
                            module_id: parseInt(p.module_id),
                            action_id: parseInt(p.action_id),
                            module_name: p.module_name || '',
                            action_name: p.action_name || '',
                            system_id: sp.system_id || null
                        }));
                    });

                    let permHtml = '';
                    const allSysKeys = Object.keys(roleAssignments[res.id]);
                    if (allSysKeys.length > 0) {
                        allSysKeys.forEach(sk => {
                            if (sk === 'null') return; // hide global
                            const sysPerms = roleAssignments[res.id][sk];
                            if (!sysPerms || sysPerms.length === 0) return;
                            let sysLabel = 'Global';
                            if (sk !== 'null') {
                                $('.system-item').each(function() {
                                    if ($(this).data('system-id') == sk) {
                                        sysLabel = $(this).find('.editable-label').text().trim();
                                        return false;
                                    }
                                });
                            }
                            permHtml += `<div class="mb-2"><span class="badge bg-secondary mb-1">${htmlEscape(sysLabel)}</span>`;
                            const byMod = {};
                            sysPerms.forEach(p => {
                                if (!byMod[p.module_id]) byMod[p.module_id] = {
                                    name: p.module_name || '',
                                    actions: []
                                };
                                byMod[p.module_id].actions.push(p.action_name || '');
                            });
                            Object.keys(byMod).forEach(mid => {
                                permHtml += `<span class="d-block ps-3" style="font-weight:normal;">${htmlEscape(byMod[mid].name)}</span>`;
                                permHtml += `<div class="d-flex flex-wrap gap-2 ps-3">${byMod[mid].actions.map(a => `<span class="badge bg-light text-dark" style="font-weight:normal;">• ${htmlEscape(a)}</span>`).join('')}</div>`;
                            });
                            permHtml += '</div>';
                        });
                    }
                    if (!permHtml) permHtml = '<span class="text-muted">No permissions assigned</span>';
                    card.find('.role-permissions').html(permHtml);
                }

                if (action === 'addSystem') {
                    const dn = res.name || data.name;
                    systemRoles[res.id] = [];
                    $('.systems-list').append(buildSystemCardHtml(res.id, dn));
                }
                // editSystem: systems tab is now display-only; just update the name label
                if (action === 'editSystem') {
                    const dn = res.name || data.name;
                    $(`.system-item[data-system-id="${res.id}"] .system-display-name`).text(dn);
                }

                if (action === 'addUser') {
                    const hasAssignment = rolesWithSystemAssignment.includes(res.user_type) || rolesWithDepartmentAssignment.includes(res.user_type);
                    let toggleHtml = hasAssignment ? '<button class="btn btn-sm btn-outline-secondary me-2 toggle-user-approvals">+</button>' : '';
                    let approvalsContent = '';
                    if (hasAssignment && res.assignments && res.assignments.length > 0) {
                        const isReviewer = res.user_type === 'Reviewer';
                        res.assignments.forEach(a => {
                            if (isReviewer) {
                                approvalsContent += `<small class="d-block mb-1"><strong>Department:</strong> ${htmlEscape(a.dept_name || a.department_id)}</small>`;
                            } else {
                                let sysName = '';
                                <?php foreach ($systems as $sys): ?>
                                    if (<?= $sys['id'] ?> == a.system_id) sysName = '<?= htmlspecialchars($sys['full_name'] ?? $sys['name']) ?>';
                                <?php endforeach; ?>
                                approvalsContent += `<small class="d-block mb-1"><strong>System:</strong> ${htmlEscape(sysName)}</small>`;
                            }
                        });
                    } else {
                        approvalsContent = '<small class="text-muted">No assignments yet</small>';
                    }
                    const approvalsDiv = `<div class="user-approvals mt-2" style="display:none; margin-left:30px;"><small class="text-muted d-block mb-1">Assigned to:</small><div class="ps-2">${approvalsContent}</div></div>`;
                    const html = `<div class="user-item card p-3 mb-2" data-user-id="${res.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center">${toggleHtml}
                                <div class="d-flex flex-column">
                                    <strong class="mb-1">${htmlEscape(res.name || data.name)}</strong>
                                    <small class="text-muted">${htmlEscape(res.employee_id || '')}</small>
                                    <small class="text-muted user-role-label">${htmlEscape(res.user_type || '')}</small>
                                </div>
                            </div>
                            ${approvalsDiv}
                        </div>
                        <div class="ms-3">
                            <button class="btn btn-sm btn-secondary me-2" data-action="editUser" data-user-id="${res.id}" data-name="${htmlEscape(res.employee_id || '')}" data-user-role="${htmlEscape(res.user_type || '')}">Edit</button>
                            <button class="btn btn-sm btn-danger" data-action="deleteUser" data-user-id="${res.id}">×</button>
                        </div>
                    </div>
                </div>`;
                    $('.users-list').append(html);
                    approverAssignments[res.id] = res.assignments || [];
                }

                if (action === 'editUser') {
                    const item = $(`.user-item[data-user-id="${res.id}"]`);
                    approverAssignments[res.id] = res.assignments || [];
                    item.find('[data-action="editUser"]').data('name', res.employee_id || '').data('user-role', res.user_type || '');
                    item.data('role', (res.user_type || '').toLowerCase());

                    const hasAssignment = rolesWithSystemAssignment.includes(res.user_type) || rolesWithDepartmentAssignment.includes(res.user_type);
                    const isReviewer = res.user_type === 'Reviewer';

                    if (hasAssignment) {
                        if (item.find('.toggle-user-approvals').length === 0) {
                            item.find('.flex-grow-1 > .d-flex.align-items-center').first().prepend('<button class="btn btn-sm btn-outline-secondary me-2 toggle-user-approvals">+</button>');
                        }
                        let approvalsContent = '';
                        if (res.assignments && res.assignments.length > 0) {
                            res.assignments.forEach(a => {
                                if (isReviewer) {
                                    approvalsContent += `<small class="d-block mb-1"><strong>Department:</strong> ${htmlEscape(a.dept_name || a.department_id)}</small>`;
                                } else {
                                    let sysName = '';
                                    <?php foreach ($systems as $sys): ?>
                                        if (<?= $sys['id'] ?> == a.system_id) sysName = '<?= htmlspecialchars($sys['full_name'] ?? $sys['name']) ?>';
                                    <?php endforeach; ?>
                                    approvalsContent += `<small class="d-block mb-1"><strong>System:</strong> ${htmlEscape(sysName)}</small>`;
                                }
                            });
                        } else {
                            approvalsContent = '<small class="text-muted">No assignments yet</small>';
                        }
                        item.find('.user-approvals .ps-2').html(approvalsContent);
                    } else {
                        item.find('.toggle-user-approvals').remove();
                        item.find('.user-approvals').hide().find('.ps-2').html('<small class="text-muted">No assignments yet</small>');
                    }
                    item.find('.user-role-label').text(res.user_type);
                }

                getModal().hide();
                $('#accessTypeForm')[0].reset();
                $('#permissionsContainer').html('');
                $('#modalInputGroup').hide();
            }, 'json').always(function() {
                submitBtn.prop('disabled', false).text(origText);
            }).fail(function(xhr, status, error) {
                console.error('AJAX Error:', status, error, xhr.responseText);
                alert('Error: ' + error);
                submitBtn.prop('disabled', false).text(origText);
            });
        });

    });
</script>