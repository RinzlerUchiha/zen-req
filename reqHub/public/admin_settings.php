<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

if (!isAuthenticated()) {
    http_response_code(403);
    die('Access denied');
}

$currentUser = getCurrentUser();

if ($currentUser['reqhub_role'] !== 'Admin') {
    http_response_code(403);
    die('Access denied: Admin only');
}

$pdo = ReqHubDatabase::getConnection('reqhub');
$hrPdo = ReqHubDatabase::getConnection('hr'); // HR database connection

// --- Fetch data ---
// Get users from reqhub (exclude SYSTEM user)
$users = $pdo->query("SELECT id, employee_id, reqhub_role FROM users WHERE employee_id != 'SYSTEM' ORDER BY employee_id")->fetchAll(PDO::FETCH_ASSOC);

if (!$users) {
    $users = [];
    error_log("WARNING: No users found in query");
}

// Get full names from HR database
$userNames = [];
try {
    $hrUsers = $hrPdo->query("
        SELECT bi_empno, CONCAT(COALESCE(bi_empfname, ''), ' ', COALESCE(bi_empmname, ''), ' ', COALESCE(bi_emplname, '')) as full_name
        FROM tbl201_basicinfo
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($hrUsers as $user) {
        $userNames[$user['bi_empno']] = trim($user['full_name']);
    }
} catch (Exception $e) {
    // If query fails, names will be empty and fall back to employee_id
}

// Add user_name to users
foreach ($users as &$user) {
    $user['user_name'] = $userNames[$user['employee_id']] ?? $user['employee_id'];
}
unset($user); // Fix PHP foreach reference bug

// Get systems from reqhub, then enrich with descriptions from HR database
$systems = $pdo->query("SELECT id, name FROM systems ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch system descriptions from HR database
$systemDescriptions = [];
try {
    $hrSystems = $hrPdo->query("
        SELECT system_id, sys_desc
        FROM tbl_systems
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($hrSystems as $sys) {
        // Store by system_id (which is the code like HRIS, abs, ATD, etc)
        $key = strtolower($sys['system_id']);
        $systemDescriptions[$key] = $sys['sys_desc'];
    }
} catch (Exception $e) {
    // If query fails, descriptions will be empty and fall back to system name
}

// Add full_name to systems by matching with system_id (code)
$systemsWithFullNames = [];
foreach ($systems as $system) {
    $systemCodeLower = strtolower(trim($system['name']));
    $system['full_name'] = $systemDescriptions[$systemCodeLower] ?? $system['name'];
    $systemsWithFullNames[] = $system;
}
$systems = $systemsWithFullNames;

// Test: output full_name to HTML comment so we can see if it's being set
error_log("Systems with full_name:");
foreach ($systems as $sys) {
    error_log("ID: {$sys['id']}, Name: {$sys['name']}, FullName: {$sys['full_name']}");
}
$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$actions = $pdo->query("SELECT id, name FROM actions ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$modules = $pdo->query("SELECT id, name FROM modules ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$roles = $pdo->query("SELECT id, name FROM roles ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch module_actions ---
$moduleActions = [];
foreach ($modules as $module) {
    $stmt = $pdo->prepare("
        SELECT a.id AS action_id, a.name
        FROM module_actions ma
        LEFT JOIN actions a ON ma.action_id = a.id
        WHERE ma.module_id = ?
    ");
    $stmt->execute([$module['id']]);
    $moduleActions[$module['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Build module -> action_id map ---
$moduleAssignments = [];
foreach ($moduleActions as $moduleId => $actionsList) {
    foreach ($actionsList as $a) {
        $moduleAssignments[$moduleId][] = $a['action_id'];
    }
}

// --- Fetch role_permissions ---
$roleAssignments = [];
foreach ($roles as $role) {
    $stmt = $pdo->prepare("
        SELECT rp.module_id, rp.action_id, m.name AS module_name, a.name AS action_name
        FROM role_permissions rp
        LEFT JOIN modules m ON rp.module_id = m.id
        LEFT JOIN actions a ON rp.action_id = a.id
        WHERE rp.role_id = ?
    ");
    $stmt->execute([$role['id']]);
    $roleAssignments[$role['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Fetch system_roles (for Systems tab) ---
$systemRoles = [];
foreach ($systems as $system) {
    $stmt = $pdo->prepare("
        SELECT sr.role_id, r.name AS role_name
        FROM system_roles sr
        LEFT JOIN roles r ON sr.role_id = r.id
        WHERE sr.system_id = ?
        ORDER BY r.name
    ");
    $stmt->execute([$system['id']]);
    $systemRoles[$system['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Fetch approver assignments for each user ---
$approverAssignments = [];
if (!empty($users)) {
    foreach ($users as $user) {
        if (!isset($user['id'])) {
            error_log("WARNING: User record missing 'id' field: " . json_encode($user));
            continue;
        }
        $stmt = $pdo->prepare("
            SELECT system_id, department_id
            FROM user_approver_assignments
            WHERE user_id = ?
        ");
        $stmt->execute([$user['id']]);
        $approverAssignments[$user['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// --- Fetch default actions (Add, Edit, Delete, View) ---
$defaultActionIds = [];
foreach ($actions as $act) {
    if (in_array(strtolower($act['name']), ['add', 'edit', 'delete', 'view'])) {
        $defaultActionIds[$act['id']] = $act['name'];
    }
}
?>

<?php include (__DIR__ . '/../includes/header.php'); ?>

<div class="container mt-4">
    <h4 class="mb-4">Admin Settings</h4>

    <!-- UNIFIED TABS -->
    <ul class="nav nav-tabs mb-3" id="adminTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#actionsTab" type="button">Actions</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#modulesTab" type="button">Modules</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rolesTab" type="button">Roles</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#systemsTab" type="button">Systems</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#userSettingsTab" type="button">User Settings</button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ACTIONS TAB -->
        <div class="tab-pane fade show active" id="actionsTab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-action="addAction">Add Action</button>
                    <button class="btn btn-outline-danger" id="toggleDeleteModeActions">Delete Mode</button>
                </div>
                <input type="text" class="form-control search-input" id="searchActions" placeholder="Search actions..." style="max-width: 300px;">
            </div>
            <div class="actions-grid">
                <?php foreach ($actions as $action): ?>
                    <div class="action-item card p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="editable-label" 
                                    data-type="action" 
                                    data-id="<?= $action['id'] ?>" 
                                    title="Click to edit">
                                <?= htmlspecialchars($action['name']) ?>
                            </span>
                            <div>
                                <button class="btn btn-sm btn-danger action-delete-btn" data-action="deleteAction" data-id="<?= $action['id'] ?>">×</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- MODULES TAB -->
        <div class="tab-pane fade" id="modulesTab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-primary" data-action="addModule">Add Module</button>
                <input type="text" class="form-control search-input" id="searchModules" placeholder="Search modules..." style="max-width: 300px;">
            </div>
            <div class="modules-grid">
                <?php foreach ($modules as $module): ?>
                    <div class="module-item card p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2 module-item-header">
                            <div class="title d-flex align-items-center flex-shrink-1 overflow-hidden">
                                <button class="btn btn-sm btn-outline-secondary me-2 toggle-module flex-shrink-0">+</button>
                                <span class="editable-label flex-grow-1"
                                        data-type="module"
                                        data-id="<?= $module['id'] ?>"
                                        title="Click to edit">
                                    <?= htmlspecialchars($module['name']) ?>
                                </span>
                            </div>
                            <div class="btn-group flex-shrink-0">
                                <button class="btn btn-sm btn-danger" data-action="deleteModule" data-module-id="<?= $module['id'] ?>">×</button>
                            </div>
                        </div>

                        <!-- Collapsible actions list -->
                        <div class="module-actions mt-2" style="display:none;">
                            <?php if(!empty($moduleActions[$module['id']])): ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach($moduleActions[$module['id']] as $action): ?>
                                        <span class="badge bg-light text-dark">
                                            <input type="checkbox" checked disabled class="me-1">
                                            <?= htmlspecialchars($action['name']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">No actions assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ROLES TAB -->
        <div class="tab-pane fade" id="rolesTab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-primary" data-action="addRole">Add Role</button>
                <input type="text" class="form-control search-input" id="searchRoles" placeholder="Search roles..." style="max-width: 300px;">
            </div>
            <div class="roles-grid">
                <?php foreach ($roles as $role): ?>
                    <div class="role-item card p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-sm btn-outline-secondary me-2 toggle-role">+</button>
                                <span class="editable-label"
                                        data-type="role"
                                        data-id="<?= $role['id'] ?>"
                                        title="Click to edit">
                                    <?= htmlspecialchars($role['name']) ?>
                                </span>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-danger" data-action="deleteRole" data-role-id="<?= $role['id'] ?>">×</button>
                            </div>
                        </div>

                        <!-- Collapsible permissions -->
                        <div class="role-permissions mt-2" style="display:none;">
                            <?php
                            $ra = $roleAssignments[$role['id']] ?? [];
                            if(!empty($ra)):
                                $currentModule = '';
                                foreach ($ra as $item):
                                    if ($item['module_name'] !== $currentModule):
                                        if ($currentModule !== '') echo '</div>';
                                        $currentModule = $item['module_name'];
                                        echo '<strong class="d-block mt-2 ps-3">' . htmlspecialchars($currentModule) . '</strong>';
                                        echo '<div class="d-flex flex-wrap gap-2 ps-3">';
                                    endif;
                                    if ($item['action_name']):
                                        echo '<span class="badge bg-light text-dark">';
                                        echo '<input type="checkbox" checked disabled class="me-1"> ';
                                        echo htmlspecialchars($item['action_name']);
                                        echo '</span>';
                                    endif;
                                endforeach;
                                if ($currentModule !== '') echo '</div>';
                            else
                                echo '<span class="text-muted">No permissions assigned</span>';
                            endif;
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SYSTEMS TAB (NEW) -->
        <div class="tab-pane fade" id="systemsTab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-primary" data-action="addSystem">Add System</button>
                <input type="text" class="form-control search-input" id="searchSystems" placeholder="Search systems..." style="max-width: 300px;">
            </div>
            <div class="systems-list">
                <!-- DEBUG: Systems being rendered -->
                <?php foreach ($systems as $system): ?>
                    <!-- System ID: <?= $system['id'] ?>, Name: <?= $system['name'] ?>, FullName: <?= $system['full_name'] ?> -->
                <?php endforeach; ?>
                
                <?php foreach ($systems as $system): ?>
                    <div class="system-item card p-3 mb-3" data-system-id="<?= $system['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center flex-grow-1">
                                <button class="btn btn-sm btn-outline-secondary me-2 toggle-system-roles">+</button>
                                <span class="editable-label flex-grow-1"
                                        data-type="system"
                                        data-id="<?= $system['id'] ?>"
                                        title="Click to edit">
                                    <?= htmlspecialchars($system['full_name'] ?? $system['name']) ?>
                                </span>
                            </div>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-secondary" data-action="duplicateSystem" data-system-id="<?= $system['id'] ?>" data-system-name="<?= htmlspecialchars($system['full_name'] ?? $system['name'], ENT_QUOTES) ?>">Duplicate</button>
                                <button class="btn btn-sm btn-danger" data-action="deleteSystem" data-system-id="<?= $system['id'] ?>">×</button>
                            </div>
                        </div>

                        <!-- Collapsible system roles -->
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
                                            <strong><?= htmlspecialchars($roleName) ?></strong>
                                            
                                            <!-- Collapsible modules for this role -->
                                            <div class="system-role-modules mt-2 ps-3" style="display:none;">
                                                <?php
                                                $modulesByName = [];
                                                foreach ($perms as $p) {
                                                    if (!isset($modulesByName[$p['module_id']])) {
                                                        $modulesByName[$p['module_id']] = [
                                                            'name' => $p['module_name'],
                                                            'actions' => []
                                                        ];
                                                    }
                                                    if ($p['action_name']) {
                                                        $modulesByName[$p['module_id']]['actions'][] = $p['action_name'];
                                                    }
                                                }
                                                
                                                foreach ($modulesByName as $modId => $modData):
                                                    ?>
                                                    <div class="system-module-item mb-2">
                                                        <strong><?= htmlspecialchars($modData['name']) ?></strong>
                                                        <div class="mt-1 ps-3">
                                                            <?php foreach ($modData['actions'] as $actName): ?>
                                                                <span class="badge bg-light text-dark me-2 mb-2">• <?= htmlspecialchars($actName) ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <?php
                                                endforeach;
                                                ?>
                                            </div>
                                        </div>
                                        <?php
                                    endforeach;
                                else:
                                    echo '<span class="text-muted">No roles assigned to this system</span>';
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- USER SETTINGS TAB (MERGED) -->
        <div class="tab-pane fade" id="userSettingsTab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-primary" data-action="addUser">Add User</button>
                <input type="text" class="form-control search-input" id="searchUsers" placeholder="Search users..." style="max-width: 300px;">
            </div>
            <div class="users-list">
                <?php foreach ($users as $user): 
                    if (!isset($user['id']) || !isset($user['employee_id'])) {
                        error_log("WARNING: User missing required fields: " . json_encode($user));
                        continue;
                    }
                    $userApprovals = $approverAssignments[$user['id']] ?? [];
                ?>
                    <div class="user-item card p-3 mb-2" data-user-id="<?= $user['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center">
                                    <?php if($user['reqhub_role'] === 'Approver'): ?>
                                        <button class="btn btn-sm btn-outline-secondary me-2 toggle-user-approvals">+</button>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= htmlspecialchars($user['user_name'] ?? $user['employee_id']) ?></strong> <small class="text-muted">(<?= htmlspecialchars($user['employee_id']) ?>)</small>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($user['reqhub_role']) ?></small>
                                    </div>
                                </div>
                                
                                <!-- Approver assignments (collapsible) -->
                                <?php if($user['reqhub_role'] === 'Approver' && !empty($userApprovals)): ?>
                                    <div class="user-approvals mt-2" style="display:none; margin-left: 30px;">
                                        <small class="text-muted d-block mb-1">Assigned to:</small>
                                        <div class="ps-2">
                                            <?php
                                            $systemDeptMap = [];
                                            foreach ($userApprovals as $approval) {
                                                $sysId = $approval['system_id'];
                                                $deptId = $approval['department_id'];
                                                
                                                if (!isset($systemDeptMap[$sysId])) {
                                                    $systemDeptMap[$sysId] = [];
                                                }
                                                $systemDeptMap[$sysId][] = $deptId;
                                            }
                                            
                                            foreach ($systemDeptMap as $sysId => $deptIds) {
                                                $sysName = '';
                                                $deptNames = [];
                                                
                                                foreach ($systems as $s) {
                                                    if ($s['id'] == $sysId) {
                                                        $sysName = $s['name'];
                                                        break;
                                                    }
                                                }
                                                
                                                foreach ($departments as $d) {
                                                    if (in_array($d['id'], $deptIds)) {
                                                        $deptNames[] = $d['name'];
                                                    }
                                                }
                                                
                                                echo '<small class="d-block mb-1">';
                                                echo '<strong>' . htmlspecialchars($sysName) . ':</strong> ';
                                                echo htmlspecialchars(implode(', ', $deptNames));
                                                echo '</small>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
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

    </div>
</div>

<!-- SINGLE MODAL -->
<div class="modal fade" id="accessTypeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="accessTypeForm" method="post" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="accessTypeModalLabel">Manage</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="overflow: hidden;">
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

            <div class="row" style="display: flex;">
                <div class="col-md-8" style="overflow-y: auto; max-height: 60vh;">
                    <div id="permissionsContainer"></div>
                </div>
                <div class="col-md-4 border-start" style="display:none; overflow: visible;" id="summaryColumn">
                    <h6>Selected</h6>
                    <div id="modalSummary" class="small">
                        <em>None selected</em>
                    </div>
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
/* ============================= */
/* ACTIONS TAB */
/* ============================= */
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

.action-item strong {
    word-break: break-word;
}

/* Hide delete buttons by default — shown only in Delete Mode */
.action-item .action-delete-btn {
    display: none;
}

/* ============================= */
/* MODULES TAB */
/* ============================= */
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

.module-item-header .title strong {
    max-width: none;
    display: block;
    overflow: visible;
    text-overflow: clip;
    white-space: normal;
    word-wrap: break-word;
}

.module-capsule-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
    width: 100%;
    box-sizing: border-box;
}

.capsule-item {
    background: #f1f3f5;
    border-radius: 1rem;
    padding: 0.4rem 0.6rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    width: 100%;
    box-sizing: border-box;
    font-size: 0.875rem;
    white-space: normal;
    word-break: break-word;
}

/* ============================= */
/* ROLES TAB */
/* ============================= */
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

/* ============================= */
/* SYSTEMS TAB */
/* ============================= */
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

.system-item strong {
    word-break: break-word;
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

/* ============================= */
/* USER SETTINGS */
/* ============================= */
.users-list {
    max-width: 800px;
    margin: 0 auto;
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

/* ============================= */
/* EDITABLE LABELS */
/* ============================= */
.editable-label {
    cursor: pointer;
    position: relative;
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
    padding: 0.25rem 0.5rem;
}

.editable-label-error {
    color: #dc3545;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
}

.editable-label-tooltip {
    position: absolute;
    bottom: -30px;
    left: 0;
    background-color: #dc3545;
    color: white;
    padding: 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    white-space: nowrap;
    z-index: 1000;
}

/* ============================= */
/* ROLE MODAL STYLING */
/* ============================= */
#permissionsContainer {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
    box-sizing: border-box;
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

.role-modal-module-title {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.6rem;
    word-break: break-word;
}

.role-modal-module-card .module-capsule-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
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
    box-sizing: border-box;
}

.role-modal-action-item input[type="checkbox"] {
    transform: scale(0.9);
    flex-shrink: 0;
}

.role-modal-action-item span {
    flex: 1;
    min-width: 0;
    word-break: break-word;
}

#accessTypeModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
    overflow-x: hidden;
}

/* ============================= */
/* MODULE MODAL GRID */
/* ============================= */
.module-modal-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px 16px;
}

.module-modal-grid .role-modal-action-item {
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

/* ============================= */
/* RESPONSIVE */
/* ============================= */
@media (max-width: 1200px) {
    .modules-grid, .roles-grid {
        grid-template-columns: 1fr;
    }
    .role-modal-module-card .module-capsule-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .role-modal-module-card .module-capsule-grid {
        grid-template-columns: 1fr;
    }
}

/* Explicit modal sizes since Bootstrap classes aren't applying */
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

/* Prevent modal body scrollbar */
#accessTypeModal .modal-body {
    overflow: visible !important;
    max-height: none !important;
}

/* Make permissions container scrollable, summary fixed */
#permissionsContainer {
    max-height: 60vh;
    overflow-y: auto;
    overflow-x: hidden;
}

#summaryColumn {
    max-height: calc(60vh - 40px);
    height: calc(60vh - 40px);
    overflow: hidden;
    overflow-y: auto;
    padding-right: 10px;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

#summaryColumn::-webkit-scrollbar {
    display: none;
}

#summaryColumn h6 {
    margin-bottom: 0.75rem;
    flex-shrink: 0;
}

#modalSummary {
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    max-height: calc(60vh - 80px);
    overflow-y: auto;
}
</style>
<?php include (__DIR__ . '/../includes/footer.php'); ?>

<script>
// Check if jQuery is loaded
if (typeof $ === 'undefined') {
    console.error('jQuery is NOT loaded! Buttons will not work.');
} else {
    console.log('jQuery is loaded successfully');
}

$(function(){

    const modal = new bootstrap.Modal(document.getElementById('accessTypeModal'));
    let roleAssignments = <?= json_encode($roleAssignments) ?>;
    let moduleAssignments = <?= json_encode($moduleAssignments ?? []) ?>;
    let approverAssignments = <?= json_encode($approverAssignments) ?>;
    let systemRoles = <?= json_encode($systemRoles) ?>;
    let defaultActionIds = <?= json_encode($defaultActionIds ?? []) ?>;

    let duplicateSystemCounter = {};

    // ==============================
    // HELPER FUNCTIONS
    // ==============================
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
        
        // Only show summary for Module, Role, and System modals
        if (!action.includes('Module') && !action.includes('Role') && !action.includes('System')) {
            $('#summaryColumn').hide();
            return;
        }

        $('#summaryColumn').show();

        let selected = [];
        let label = '';

        if (action.includes('Module')) {
            selected = $('.module-action-checkbox:checked').map(function() {
                return $(this).closest('label').find('span').text();
            }).get();
            label = 'Actions';
        } else if (action.includes('Role')) {
            selected = $('.role-permission-checkbox:checked').map(function() {
                return $(this).closest('label').find('span').text();
            }).get();
            label = 'Actions';
        } else if (action.includes('System')) {
            selected = $('.system-role-checkbox:checked').map(function() {
                return $(this).closest('label').find('span').text();
            }).get();
            label = 'Roles';
        }

        const summary = $('#modalSummary');

        if (selected.length === 0) {
            summary.html('<em>None selected</em>');
            return;
        }

        let html = '';

        if (action.includes('Module')) {
            html = '<strong>' + label + '</strong><br>';
            selected.forEach(action => {
                html += `<div style="margin-left: 10px;">• ${htmlEscape(action)}</div>`;
            });
        } else if (action.includes('Role')) {
            html = '<strong>' + label + '</strong><br>';
            selected.forEach(action => {
                html += `<div style="margin-left: 10px;">• ${htmlEscape(action)}</div>`;
            });
        } else if (action.includes('System')) {
            html = '<strong>' + label + '</strong><br>';
            selected.forEach(role => {
                html += `<div style="margin-left: 10px;">• ${htmlEscape(role)}</div>`;
            });
        }

        summary.html(html);
    }

    // ==============================
    // DELETE MODE TOGGLE - ACTIONS TAB
    // ==============================
    $('#toggleDeleteModeActions').on('click', function(){
        const isActive = $(this).hasClass('btn-danger');
        if (isActive) {
            $(this).removeClass('btn-danger').addClass('btn-outline-danger').text('Delete Mode');
            $('.action-item .action-delete-btn').hide();
        } else {
            $(this).removeClass('btn-outline-danger').addClass('btn-danger').text('Exit Delete Mode');
            $('.action-item .action-delete-btn').show();
        }
    });

    // ==============================
    // COLLAPSIBLE (Modules & Roles)
    // ==============================
    $(document).on('click', '.toggle-module', function(e){
        e.stopPropagation();
        const container = $(this).closest('.module-item').find('.module-actions');
        container.slideToggle(150);
        $(this).text($(this).text() === '+' ? '-' : '+');
    });

    $(document).on('click', '.toggle-role', function(e){
        e.stopPropagation();
        const container = $(this).closest('.role-item').find('.role-permissions');
        container.slideToggle(150);
        $(this).text($(this).text() === '+' ? '-' : '+');
    });

    $(document).on('click', '.toggle-system-roles', function(e){
        e.stopPropagation();
        const container = $(this).closest('.system-item').find('.system-roles-container');
        container.slideToggle(150);
        $(this).text($(this).text() === '+' ? '-' : '+');
    });

    $(document).on('click', '.toggle-system-role-modules', function(e){
        e.stopPropagation();
        const container = $(this).closest('.system-role-item').find('.system-role-modules');
        container.slideToggle(150);
        $(this).text($(this).text() === '+' ? '-' : '+');
    });

    $(document).on('click', '.toggle-user-approvals', function(e){
        e.stopPropagation();
        const container = $(this).closest('.user-item').find('.user-approvals');
        container.slideToggle(150);
        $(this).text($(this).text() === '+' ? '-' : '+');
    });

    // ==============================
    // CARD CLICK HANDLERS - OPEN MODAL
    // ==============================
    $(document).on('click', '.module-item', function(e){
        if ($(e.target).closest('button').length > 0) return;
        if ($(e.target).is('.toggle-module')) return;
        if ($(e.target).is('.editable-label')) return;
        
        const moduleId = $(this).find('.editable-label').data('id');
        const moduleName = $(this).find('.editable-label').text();
        if (moduleId) {
            $('#modalAction').val('editModule');
            $('#modalModule').val(moduleId);
            
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('Module Name');
            $('#modalInput').val(moduleName.trim()).attr('disabled', true);

            let html = '<div class="d-flex justify-content-between align-items-center mb-2"><strong class="d-block">Select Actions</strong><input type="text" class="form-control search-modal-input" id="searchModuleActionsEdit" placeholder="Search actions..." style="max-width: 250px;"></div><div class="module-modal-grid">';
            $('.action-item').each(function(){
                const aid = $(this).find('.editable-label').data('id');
                const aname = $(this).find('.editable-label').text().trim();
                html += `<label class="role-modal-action-item" data-action-name="${aname.toLowerCase()}">
                            <input type="checkbox" class="module-action-checkbox" value="${aid}">
                            <span>${htmlEscape(aname)}</span>
                        </label>`;
            });
            html += '</div>';
            $('#permissionsContainer').html(html);

            if(moduleId && moduleAssignments[moduleId]){
                moduleAssignments[moduleId].forEach(function(actionId){
                    $(`.module-action-checkbox[value="${actionId}"]`).prop('checked', true);
                });
            }

            setTimeout(() => {
                $(document).off('keyup', '#searchModuleActionsEdit').on('keyup', '#searchModuleActionsEdit', function(){
                    const query = $(this).val().toLowerCase();
                    $('.role-modal-action-item').each(function(){
                        const text = $(this).data('action-name');
                        $(this).toggle(text.includes(query));
                    });
                });
            }, 100);
            
            const modalDialog = $('#accessTypeModal .modal-dialog');
            modalDialog.removeClass('modal-sm modal-md modal-lg modal-xl').addClass('modal-xl');
            
            setTimeout(() => {
                $(document).off('change', '.module-action-checkbox').on('change', '.module-action-checkbox', updateModalSummary);
                updateModalSummary();
            }, 50);
            
            modal.show();
        }
    });

    $(document).on('click', '.role-item', function(e){
        if ($(e.target).closest('button').length > 0) return;
        if ($(e.target).is('.toggle-role')) return;
        if ($(e.target).is('.editable-label')) return;
        
        const roleId = $(this).find('.editable-label').data('id');
        const roleName = $(this).find('.editable-label').text();
        if (roleId) {
            const modalDialog = $('#accessTypeModal .modal-dialog');
            modalDialog.removeClass('modal-sm modal-md modal-lg modal-xl').addClass('modal-xl');
            
            $('#modalAction').val('editRole');
            $('#modalRole').val(roleId);
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('Role Name');
            $('#modalInput').val(roleName.trim()).attr('disabled', true);

            let html = '<div class="d-flex justify-content-between align-items-center mb-2"><strong class="d-block">Select Modules</strong><input type="text" class="form-control search-modal-input" id="searchRoleModulesEdit" placeholder="Search modules..." style="max-width: 250px;"></div>';

            $('.module-item').each(function(){
                const mid = $(this).find('.editable-label').data('id');
                const mname = $(this).find('.editable-label').text().trim();

                html += `
                <div class="role-modal-module-card">
                    <div class="role-modal-module-header">
                        <label>
                            <input type="checkbox"
                                class="module-master-checkbox"
                                data-module="${mid}">
                            <strong>${htmlEscape(mname)}</strong>
                        </label>
                    </div>
                    <div class="d-flex flex-wrap gap-2 ps-3">
                `;

                const moduleActions = moduleAssignments[mid] || [];

                moduleActions.forEach(function(actionId){
                    const actionItem = $(`.action-item`).find(`[data-id="${actionId}"]`).closest('.action-item');

                    if(actionItem.length){
                        const actionName = actionItem.find('.editable-label').text().trim();

                        html += `
                            <label class="badge bg-light text-dark">
                                <input type="checkbox"
                                    class="role-permission-checkbox"
                                    data-module="${mid}"
                                    value="${actionId}" class="me-1">
                                <span>${htmlEscape(actionName)}</span>
                            </label>
                        `;
                    }
                });

                html += `
                    </div>
                </div>
                `;
            });

            $('#permissionsContainer').html(html);

            if(roleId && roleAssignments[roleId]){
                const perms = roleAssignments[roleId];

                perms.forEach(function(p){
                    $(`.role-permission-checkbox[data-module="${p.module_id}"][value="${p.action_id}"]`)
                        .prop('checked', true);
                });

                $('.module-master-checkbox').each(function(){
                    const mid = $(this).data('module');
                    const checked = $(`.role-permission-checkbox[data-module="${mid}"]:checked`).length;
                    $(this).prop('checked', checked > 0);
                });
            }

            setTimeout(() => {
                $(document).off('keyup', '#searchRoleModulesEdit').on('keyup', '#searchRoleModulesEdit', function(){
                    const query = $(this).val().toLowerCase();
                    $('.role-modal-module-card').each(function(){
                        const text = $(this).find('strong').text().toLowerCase();
                        $(this).toggle(text.includes(query));
                    });
                });
                
                $(document).off('change', '.role-permission-checkbox').on('change', '.role-permission-checkbox', updateModalSummary);
                updateModalSummary();
            }, 100);
            
            modal.show();
        }
    });

    $(document).on('click', '.system-item', function(e){
        if ($(e.target).closest('button').length > 0) return;
        if ($(e.target).is('.toggle-system-roles')) return;
        if ($(e.target).is('.editable-label')) return;
        
        const systemId = $(this).find('.editable-label').data('id');
        const systemName = $(this).find('.editable-label').text();
        if (systemId) {
            const modalDialog = $('#accessTypeModal .modal-dialog');
            modalDialog.removeClass('modal-sm modal-md modal-lg modal-xl').addClass('modal-xl');
            
            $('#modalAction').val('editSystem');
            $('#modalSystem').val(systemId);
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('System Name');
            $('#modalInput').val(systemName.trim()).attr('disabled', true);

            let html = '<div class="d-flex justify-content-between align-items-center mb-2"><strong class="d-block">Assign Roles to This System</strong><input type="text" class="form-control search-modal-input" id="searchSystemRolesEdit" placeholder="Search roles..." style="max-width: 250px;"></div>';
            html += '<div class="module-modal-grid">';

            const assignedRoles = systemRoles[systemId] ? systemRoles[systemId].map(r => r.role_id) : [];

            $('.role-item').each(function(){
                const rid = $(this).find('.editable-label').data('id');
                const rname = $(this).find('.editable-label').text().trim();
                const isAssigned = assignedRoles.includes(parseInt(rid));

                html += `<label class="role-modal-action-item" data-role-name="${rname.toLowerCase()}">
                            <input type="checkbox" class="system-role-checkbox" value="${rid}" ${isAssigned ? 'checked' : ''}>
                            <span>${htmlEscape(rname)}</span>
                        </label>`;
            });

            html += '</div>';
            $('#permissionsContainer').html(html);

            setTimeout(() => {
                $(document).off('keyup', '#searchSystemRolesEdit').on('keyup', '#searchSystemRolesEdit', function(){
                    const query = $(this).val().toLowerCase();
                    $('.role-modal-action-item').each(function(){
                        const text = $(this).data('role-name');
                        $(this).toggle(text.includes(query));
                    });
                });
                
                $(document).off('change', '.system-role-checkbox').on('change', '.system-role-checkbox', updateModalSummary);
                updateModalSummary();
            }, 100);
            
            modal.show();
        }
    });

    // ==============================
    // INLINE EDITING
    // ==============================
    $(document).on('click', '.editable-label', function(e){
        e.stopPropagation();
        
        if ($(this).attr('contenteditable') === 'true') return;

        const originalText = $(this).text();
        const type = $(this).data('type');
        const id = $(this).data('id');
        let hasChanges = false;

        $(this).attr('contenteditable', 'true');
        $(this).focus();

        setTimeout(() => {
            const range = document.createRange();
            const elem = $(this)[0];
            if (elem && elem.childNodes && elem.childNodes.length > 0) {
                range.selectNodeContents(elem);
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            } else {
                document.execCommand('selectAll', false, null);
            }
        }, 10);

        const saveEdit = () => {
            const newText = $(this).text().trim();

            if (!newText) {
                $(this).text(originalText);
                $(this).attr('contenteditable', 'false');
                showTooltip($(this), 'Name cannot be empty');
                return;
            }

            if (newText === originalText) {
                $(this).attr('contenteditable', 'false');
                return;
            }

            hasChanges = true;
            $(this).attr('contenteditable', 'false');

            $.post('/zen/reqHub/actions/inline_edit_action.php', {
                action: 'editLabel',
                type: type,
                id: id,
                new_name: newText
            }, function(res){
                if (!res.success) {
                    $(this).text(originalText);
                    showTooltip($(this), res.message || 'Failed to update');
                } else {
                    $(this).text(res.new_name);
                }
            }, 'json').fail(function(){
                $(this).text(originalText);
                showTooltip($(this), 'Error updating label');
            });
        };

        $(this).on('blur', saveEdit);
        $(this).on('keydown', function(e){
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

    $(document).on('click', function(){
        $('.editable-label[contenteditable="true"]').each(function(){
            if ($(this).attr('contenteditable') === 'true') {
                $(this).trigger('blur');
            }
        });
    });

    function showTooltip(element, message) {
        const tooltip = $(`<div class="editable-label-tooltip">${message}</div>`);
        element.after(tooltip);
        setTimeout(() => tooltip.remove(), 3000);
    }

    // ==============================
    // DELETE HANDLER
    // ==============================
    $(document).on('click', '[data-action^="delete"]', function() {
        if (!confirm('Are you sure?')) return;

        const button = $(this);
        const action = button.data('action');
        let url = '';
        let data = { action };

        switch(action) {
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

            switch(action) {
                case 'deleteAction':
                    button.closest('.action-item').slideUp(200, function() { $(this).remove(); });
                    Object.keys(moduleAssignments).forEach(mid => {
                        moduleAssignments[mid] = moduleAssignments[mid].filter(aid => aid != data.id);
                    });
                    break;
                case 'deleteModule':
                    button.closest('.module-item').slideUp(200, function() { $(this).remove(); });
                    delete moduleAssignments[data.module_id];
                    break;
                case 'deleteRole':
                    button.closest('.role-item').slideUp(200, function() { $(this).remove(); });
                    delete roleAssignments[data.role_id];
                    break;
                case 'deleteSystem':
                    button.closest('.system-item').slideUp(200, function() { $(this).remove(); });
                    delete systemRoles[data.system_id];
                    break;
                case 'deleteUser':
                    button.closest('.user-item').slideUp(200, function() { $(this).remove(); });
                    break;
            }
        }, 'json');
    });

    // ==============================
    // OPEN MODAL (Add/Edit)
    // ==============================
    $(document).on('click', '[data-action]:not([data-action^="delete"]):not([data-action="duplicateSystem"])', function(){

        const action = $(this).data('action');
        console.log('Button clicked! Action:', action);
        const modalDialog = $('#accessTypeModal .modal-dialog');

        modalDialog.removeClass('modal-sm modal-md modal-lg modal-xl');
        void modalDialog[0].offsetWidth;
        
        if(action.includes('Action')) {
            modalDialog.addClass('modal-sm');
        }
        else if(action.includes('Role')) {
            modalDialog.addClass('modal-xl');
        }
        else if(action.includes('Module')) {
            modalDialog.addClass('modal-xl');
        }
        else if(action.includes('System')) {
            modalDialog.addClass('modal-xl');
        }
        else if(action.includes('User')) {
            modalDialog.addClass('modal-lg');
        }
        else {
            modalDialog.addClass('modal-md');
        }

        $('#modalAction').val(action);
        $('#modalRole').val($(this).data('role-id')||'');
        $('#modalModule').val($(this).data('module-id')||'');
        $('#modalUser').val($(this).data('user-id')||'');
        $('#modalSystem').val($(this).data('system-id')||'');
        $('#modalActionId').val($(this).data('id')||'');
        const name = ($(this).data('name') || '').toString();

        $('#modalWarning, #modalInputGroup').hide();

        // ==============================
        // ACTION MODAL
        // ==============================
        if(action.includes('Action')){
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('Action Name');
            $('#modalInput').val(name).attr('placeholder', 'Enter name').focus();
        }

        // ==============================
        // MODULE MODAL
        // ==============================
        if(action.includes('Module')){
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('Module Name');
            $('#modalInput').val(name.trim()).attr('placeholder', 'Enter name');
            if(action === 'editModule') {
                $('#modalInput').attr('disabled', true);
            } else {
                $('#modalInput').attr('disabled', false).focus();
            }

            let html = '<div class="d-flex justify-content-between align-items-center mb-2"><strong class="d-block">Select Actions</strong><input type="text" class="form-control search-modal-input" id="searchModuleActions" placeholder="Search actions..." style="max-width: 250px;"></div><div class="module-modal-grid">';
            $('.action-item').each(function(){
                const aid = $(this).find('.editable-label').data('id');
                const aname = $(this).find('.editable-label').text().trim();
                html += `<label class="role-modal-action-item" data-action-name="${aname.toLowerCase()}">
                            <input type="checkbox" class="module-action-checkbox" value="${aid}">
                            <span>${htmlEscape(aname)}</span>
                        </label>`;
            });
            html += '</div>';
            $('#permissionsContainer').html(html);

            const moduleId = $(this).data('module-id');
            if(moduleId && moduleAssignments[moduleId]){
                moduleAssignments[moduleId].forEach(function(actionId){
                    $(`.module-action-checkbox[value="${actionId}"]`).prop('checked', true);
                });
            }

            setTimeout(() => {
                $(document).off('keyup', '#searchModuleActions').on('keyup', '#searchModuleActions', function(){
                    const query = $(this).val().toLowerCase();
                    $('.role-modal-action-item').each(function(){
                        const text = $(this).data('action-name');
                        $(this).toggle(text.includes(query));
                    });
                });
                
                $('#permissionsContainer').off('change', '.module-action-checkbox').on('change', '.module-action-checkbox', function(){
                    updateModalSummary();
                });
                
                updateModalSummary();
            }, 100);
        }

        // ==============================
        // ROLE MODAL
        // ==============================
        if(action.includes('Role')){
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('Role Name');
            $('#modalInput').val(name.trim()).attr('placeholder', 'Enter name');
            if(action === 'editRole') {
                $('#modalInput').attr('disabled', true);
            } else {
                $('#modalInput').attr('disabled', false).focus();
            }

            let html = '<div class="d-flex justify-content-between align-items-center mb-2"><strong class="d-block">Select Modules</strong><input type="text" class="form-control search-modal-input" id="searchRoleModules" placeholder="Search modules..." style="max-width: 250px;"></div>';

            $('.module-item').each(function(){
                const mid = $(this).find('.editable-label').data('id');
                const mname = $(this).find('.editable-label').text().trim();

                html += `
                <div class="role-modal-module-card">
                    <div class="role-modal-module-header">
                        <label>
                            <input type="checkbox"
                                class="module-master-checkbox"
                                data-module="${mid}">
                            <strong>${htmlEscape(mname)}</strong>
                        </label>
                    </div>
                    <div class="d-flex flex-wrap gap-2 ps-3">
                `;

                const moduleActions = moduleAssignments[mid] || [];

                moduleActions.forEach(function(actionId){
                    const actionItem = $(`.action-item`).find(`[data-id="${actionId}"]`).closest('.action-item');

                    if(actionItem.length){
                        const actionName = actionItem.find('.editable-label').text().trim();

                        html += `
                            <label class="badge bg-light text-dark">
                                <input type="checkbox"
                                    class="role-permission-checkbox"
                                    data-module="${mid}"
                                    value="${actionId}" class="me-1">
                                <span>${htmlEscape(actionName)}</span>
                            </label>
                        `;
                    }
                });

                html += `
                    </div>
                </div>
                `;
            });

            $('#permissionsContainer').html(html);

            const roleId = $(this).data('role-id');

            if(roleId && roleAssignments[roleId]){
                const perms = roleAssignments[roleId];

                perms.forEach(function(p){
                    $(`.role-permission-checkbox[data-module="${p.module_id}"][value="${p.action_id}"]`)
                        .prop('checked', true);
                });

                $('.module-master-checkbox').each(function(){
                    const mid = $(this).data('module');
                    const checked = $(`.role-permission-checkbox[data-module="${mid}"]:checked`).length;
                    $(this).prop('checked', checked > 0);
                });
            }

            $('#permissionsContainer').off('change', '.role-permission-checkbox').on('change', '.role-permission-checkbox', function(){
                updateModalSummary();
            });

            setTimeout(() => {
                $(document).off('keyup', '#searchRoleModules').on('keyup', '#searchRoleModules', function(){
                    const query = $(this).val().toLowerCase();
                    $('.role-modal-module-card').each(function(){
                        const text = $(this).find('strong').text().toLowerCase();
                        $(this).toggle(text.includes(query));
                    });
                });
                
                updateModalSummary();
            }, 100);
        }

        // ==============================
        // SYSTEM MODAL
        // ==============================
        if(action.includes('System')){
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('System Name');
            $('#modalInput').val(name.trim()).attr('placeholder', 'Enter name');
            if(action === 'editSystem') {
                $('#modalInput').attr('disabled', true);
            } else {
                $('#modalInput').attr('disabled', false).focus();
            }

            let html = '<div class="d-flex justify-content-between align-items-center mb-2"><strong class="d-block">Assign Roles to This System</strong><input type="text" class="form-control search-modal-input" id="searchSystemRoles" placeholder="Search roles..." style="max-width: 250px;"></div>';
            html += '<div class="module-modal-grid">';

            const systemId = $(this).data('system-id');
            const assignedRoles = systemRoles[systemId] ? systemRoles[systemId].map(r => r.role_id) : [];

            $('.role-item').each(function(){
                const rid = $(this).find('.editable-label').data('id');
                const rname = $(this).find('.editable-label').text().trim();
                const isAssigned = assignedRoles.includes(parseInt(rid));

                html += `<label class="role-modal-action-item" data-role-name="${rname.toLowerCase()}">
                            <input type="checkbox" class="system-role-checkbox" value="${rid}" ${isAssigned ? 'checked' : ''}>
                            <span>${htmlEscape(rname)}</span>
                        </label>`;
            });

            html += '</div>';
            $('#permissionsContainer').html(html);

            setTimeout(() => {
                $(document).off('keyup', '#searchSystemRoles').on('keyup', '#searchSystemRoles', function(){
                    const query = $(this).val().toLowerCase();
                    $('.role-modal-action-item').each(function(){
                        const text = $(this).data('role-name');
                        $(this).toggle(text.includes(query));
                    });
                });
                
                $('#permissionsContainer').off('change', '.system-role-checkbox').on('change', '.system-role-checkbox', function(){
                    updateModalSummary();
                });
                
                updateModalSummary();
            }, 100);
        }

        // ==============================
        // USER MODAL
        // ==============================
        if(action.includes('User')){
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('Employee ID');
            $('#modalInput').val(name.trim()).attr('disabled', false).attr('placeholder', 'e.g., 045-2026-001');

            const userType = $(this).data('user-role')||'Requestor';
            const userId = $(this).data('user-id')||'';

            let html = `<div class="mt-3">
                            <label class="form-label">User Type</label>
                            <select name="user_type" class="form-select user-type-select" required>
                                <option value="Requestor" ${userType === 'Requestor' ? 'selected' : ''}>Requestor</option>
                                <option value="Approver" ${userType === 'Approver' ? 'selected' : ''}>Approver</option>
                                <option value="Admin" ${userType === 'Admin' ? 'selected' : ''}>Admin</option>
                                <option value="Reviewer" ${userType === 'Reviewer' ? 'selected' : ''}>Reviewer</option>
                            </select>
                        </div>`;

            const selectedSystems = [];
            const selectedDepts = [];
            if (userId && approverAssignments[userId]) {
                approverAssignments[userId].forEach(a => {
                    if (!selectedSystems.includes(a.system_id)) selectedSystems.push(a.system_id);
                    if (!selectedDepts.includes(a.department_id)) selectedDepts.push(a.department_id);
                });
            }

            html += `<div class="mt-3 system-department-selectors" style="display:none;">
                        <label class="form-label">System(s)</label>
                        <div class="border rounded p-2 bg-light" style="max-height: 200px; overflow-y: auto;">`;
            
            $('.system-item').each(function(){
                const sysId = $(this).data('system-id');
                const sysName = $(this).find('.editable-label').text().trim();
                const isSelected = selectedSystems.includes(parseInt(sysId));
                const checkId = `system-${sysId}`;
                html += `<div class="form-check">
                            <input type="checkbox" class="form-check-input system-checkbox" id="${checkId}" value="${sysId}" ${isSelected ? 'checked' : ''}>
                            <label class="form-check-label" for="${checkId}">${htmlEscape(sysName)}</label>
                        </div>`;
            });

            html += `</div></div>`;

            html += `<div class="mt-3 system-department-selectors" style="display:none;">
                        <label class="form-label">Department/Store(s)</label>
                        <div class="border rounded p-2 bg-light" style="max-height: 200px; overflow-y: auto;">`;
            
            const departmentsData = <?= json_encode($departments) ?>;
            departmentsData.forEach(dept => {
                const dId = dept.id;
                const dName = dept.name;
                const isDeptSelected = selectedDepts.includes(dId);
                const checkIdDept = `dept-${dId}`;
                html += `<div class="form-check">
                            <input type="checkbox" class="form-check-input department-checkbox" id="${checkIdDept}" value="${dId}" ${isDeptSelected ? 'checked' : ''}>
                            <label class="form-check-label" for="${checkIdDept}">${htmlEscape(dName)}</label>
                        </div>`;
            });

            html += `</div></div>`;

            $('#permissionsContainer').html(html);

            const updateSelectors = () => {
                const type = $('.user-type-select').val();
                const selectors = $('.system-department-selectors');
                if (type === 'Approver') {
                    selectors.removeAttr('style').show();
                } else {
                    selectors.hide();
                    $('.system-checkbox').prop('checked', false);
                    $('.department-checkbox').prop('checked', false);
                }
            };

            updateSelectors();
            $(document).off('change', '.user-type-select').on('change', '.user-type-select', updateSelectors);
        }

        modal.show();
        
        if($('#modalAction').val().includes('User')){
            setTimeout(() => {
                $('#modalInput').focus();
            }, 100);
        }
    });

    // ==============================
    // RE-APPLY SIZE WHEN MODAL SHOWS
    // ==============================
    $('#accessTypeModal').on('show.bs.modal', function() {
        const action = $('#modalAction').val();
        const modalDialog = $('#accessTypeModal .modal-dialog');
        
        modalDialog.removeClass('modal-sm modal-md modal-lg modal-xl');
        
        if(action.includes('Action')) {
            modalDialog.addClass('modal-sm');
        }
        else if(action.includes('Role')) {
            modalDialog.addClass('modal-xl');
        }
        else if(action.includes('Module')) {
            modalDialog.addClass('modal-xl');
        }
        else if(action.includes('System')) {
            modalDialog.addClass('modal-xl');
        }
        else if(action.includes('User')) {
            modalDialog.addClass('modal-lg');
        }
        else {
            modalDialog.addClass('modal-md');
        }
    });

    // ==============================
    // DUPLICATE SYSTEM
    // ==============================
    $(document).on('click', '[data-action="duplicateSystem"]', function(){
        const sourceSystemId = $(this).data('system-id');
        const sourceSystemName = $(this).data('system-name');

        if (!duplicateSystemCounter[sourceSystemId]) {
            duplicateSystemCounter[sourceSystemId] = 1;
        } else {
            duplicateSystemCounter[sourceSystemId]++;
        }

        const newSystemName = sourceSystemName + ' Copy ' + duplicateSystemCounter[sourceSystemId];

        $.post('/zen/reqHub/actions/system_action.php', {
            action: 'duplicateSystem',
            source_system_id: sourceSystemId,
            new_system_name: newSystemName
        }, function(res){
            if (!res.success) {
                alert(res.message);
                return;
            }

            const sourceRoles = systemRoles[sourceSystemId] || [];
            const rolesHtml = buildRolesHtml(sourceRoles);

            const html = buildSystemCardHtml(res.id, res.name || newSystemName, rolesHtml);
            $('.systems-list').append(html);
            systemRoles[res.id] = sourceRoles;
            
            alert('System duplicated successfully: ' + newSystemName);
        }, 'json');
    });

    // ==============================
    // CHECKBOX CHANGE LISTENERS FOR SUMMARY
    // ==============================
    $(document).on('change', '.module-action-checkbox', updateModalSummary);
    $(document).on('change', '.system-role-checkbox', updateModalSummary);

    // ==============================
    // MODULE MASTER CHECKBOX
    // ==============================
    $(document).on('change', '.module-master-checkbox', function(){
        const moduleId = $(this).data('module');
        const isChecked = $(this).is(':checked');
        $(`.role-permission-checkbox[data-module="${moduleId}"]`).prop('checked', isChecked);
        updateModalSummary();
    });

    $(document).on('change', '.role-permission-checkbox', function(){
        const moduleId = $(this).data('module');
        const total = $(`.role-permission-checkbox[data-module="${moduleId}"]`).length;
        const checked = $(`.role-permission-checkbox[data-module="${moduleId}"]:checked`).length;
        $(`.module-master-checkbox[data-module="${moduleId}"]`).prop('checked', checked > 0);
        updateModalSummary();
    });

    $(document).on('change', '.system-role-checkbox', function(){
        updateModalSummary();
    });

    // ==============================
    // SEARCH FUNCTIONALITY
    // ==============================
    $(document).on('keyup', '#searchActions', function(){
        const query = $(this).val().toLowerCase();
        $('.action-item').each(function(){
            const text = $(this).find('.editable-label').text().toLowerCase();
            $(this).toggle(text.includes(query));
        });
    });

    $(document).on('keyup', '#searchModules', function(){
        const query = $(this).val().toLowerCase();
        $('.module-item').each(function(){
            const text = $(this).find('.editable-label').text().toLowerCase();
            $(this).toggle(text.includes(query));
        });
    });

    $(document).on('keyup', '#searchRoles', function(){
        const query = $(this).val().toLowerCase();
        $('.role-item').each(function(){
            const text = $(this).find('.editable-label').text().toLowerCase();
            $(this).toggle(text.includes(query));
        });
    });

    $(document).on('keyup', '#searchSystems', function(){
        const query = $(this).val().toLowerCase();
        $('.system-item').each(function(){
            const text = $(this).find('.editable-label').text().toLowerCase();
            $(this).toggle(text.includes(query));
        });
    });

    $(document).on('keyup', '#searchUsers', function(){
        const query = $(this).val().toLowerCase();
        $('.user-item').each(function(){
            const text = $(this).find('strong').first().text().toLowerCase();
            $(this).toggle(text.includes(query));
        });
    });

    // ==============================
    // EXPLICIT MODAL CLOSE HANDLERS
    // ==============================
    $(document).on('click', '[data-bs-dismiss="modal"]', function(){
        modal.hide();
    });
    
    $(document).on('click', '.btn-close', function(){
        modal.hide();
    });

    // ==============================
    // RESET MODAL AFTER HIDE
    // ==============================
    $('#accessTypeModal').on('hidden.bs.modal', function () {
        const form = $('#accessTypeForm')[0];
        form.reset();
        $('#permissionsContainer').empty();
        $('#modalWarning, #modalInputGroup').hide();
        $('#modalSummary').html('<em>None selected</em>');
        $('#summaryColumn').hide();
        // FIX: reset placeholder so User modal's placeholder doesn't bleed into other modals
        $('#modalInput').attr('placeholder', 'Enter name');
        $(document).off('change', '.user-type-select');
    });

    // ==============================
    // SHARED HTML BUILDER FUNCTIONS
    // ==============================

    // FIX: buildRolesHtml uses p.module_name and p.action_name which are now
    // stored in roleAssignments (seeded by addRole/editRole with names captured from DOM)
    function buildRolesHtml(roles) {
        if (!roles || roles.length === 0) {
            return '<span class="text-muted">No roles assigned</span>';
        }
        let html = '<strong class="d-block mb-2">▼ Roles</strong><div class="ps-3">';
        roles.forEach(function(sysRole) {
            const roleId   = sysRole.role_id;
            const roleName = sysRole.role_name;
            const perms    = roleAssignments[roleId] || [];

            html += `<div class="system-role-item mb-3">
                        <button class="btn btn-sm btn-outline-secondary me-2 toggle-system-role-modules">+</button>
                        <strong>${htmlEscape(roleName)}</strong>
                        <div class="system-role-modules mt-2 ps-3" style="display:none;">`;

            const modulesByName = {};
            perms.forEach(p => {
                if (!modulesByName[p.module_id]) {
                    modulesByName[p.module_id] = { name: p.module_name || '', actions: [] };
                }
                if (p.action_name) {
                    modulesByName[p.module_id].actions.push(p.action_name);
                }
            });

            Object.keys(modulesByName).forEach(modId => {
                const modData = modulesByName[modId];
                html += `<div class="system-module-item mb-2">
                            <strong>${htmlEscape(modData.name)}</strong>
                            <div class="mt-1 ps-3">`;
                modData.actions.forEach(actName => {
                    html += `<span class="badge bg-light text-dark me-2 mb-2">• ${htmlEscape(actName)}</span>`;
                });
                html += `</div></div>`;
            });

            html += `</div></div>`;
        });
        html += '</div>';
        return html;
    }

    function buildSystemCardHtml(id, name, rolesHtml) {
        return `<div class="system-item card p-3 mb-3" data-system-id="${id}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center flex-grow-1">
                            <button class="btn btn-sm btn-outline-secondary me-2 toggle-system-roles">+</button>
                            <span class="editable-label flex-grow-1" data-type="system" data-id="${id}">${htmlEscape(name)}</span>
                        </div>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-secondary" data-action="duplicateSystem" data-system-id="${id}" data-system-name="${htmlEscape(name)}">Duplicate</button>
                            <button class="btn btn-sm btn-danger" data-action="deleteSystem" data-system-id="${id}">×</button>
                        </div>
                    </div>
                    <div class="system-roles-container mt-3" style="display:none;">
                        ${rolesHtml}
                    </div>
                </div>`;
    }

    // ==============================
    // FORM SUBMIT
    // ==============================
    $('#accessTypeForm').submit(function(e){
        e.preventDefault();

        const action = $('#modalAction').val();
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.text();
        
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        let url = '', data = { action };
        const nameVal = $('#modalInput').val().trim();

        if(['Role','Action','Module','System','User'].some(a=>action.includes(a)) && !nameVal){
            alert('Name cannot be empty');
            submitBtn.prop('disabled', false).text(originalText);
            return;
        }

        data.name = nameVal;

        if(action.includes('Role')){
            data.role_id = $('#modalRole').val();
            data.permissions = [];
            // FIX: capture module_name and action_name from the DOM while the modal
            // is still open, so roleAssignments can store them for system card rendering
            $('.role-permission-checkbox:checked').each(function(){
                const moduleId   = $(this).data('module');
                const actionId   = $(this).val();
                const moduleCard = $(`.role-modal-module-card`).has(`.module-master-checkbox[data-module="${moduleId}"]`);
                const moduleName = moduleCard.find('.role-modal-module-header strong').text().trim();
                const actionName = $(this).closest('label').find('span').text().trim();
                data.permissions.push({
                    module_id:   moduleId,
                    action_id:   actionId,
                    module_name: moduleName,
                    action_name: actionName
                });
            });
            url='/zen/reqHub/actions/role_action.php';
        }

        if(action.includes('Action')){
            url='/zen/reqHub/actions/action_action.php';
            data.id = $('#modalActionId').val();
        }

        if(action.includes('Module')){
            url='/zen/reqHub/actions/module_action.php';
            data.module_id = $('#modalModule').val();
            data.selected_actions = [];
            $('.module-action-checkbox:checked').each(function(){
                data.selected_actions.push($(this).val());
            });
        }

        if(action.includes('System')){
            url='/zen/reqHub/actions/system_action.php';
            data.system_id = $('#modalSystem').val();
            data.role_ids = [];
            $('.system-role-checkbox:checked').each(function(){
                data.role_ids.push($(this).val());
            });
        }

        if(action.includes('User')){
            url='/zen/reqHub/actions/user_action.php';
            data.user_id = $('#modalUser').val();
            data.user_type = $('#permissionsContainer select[name="user_type"]').val();
            data.system_ids = [];
            data.department_ids = [];

            $('#permissionsContainer .system-checkbox:checked').each(function(){
                data.system_ids.push($(this).val().toString());
            });

            $('#permissionsContainer .department-checkbox:checked').each(function(){
                data.department_ids.push($(this).val().toString());
            });
        }

        console.log('Posting to:', url);
        console.log('Data:', data);

        $.post(url, data, function(res){
            submitBtn.prop('disabled', false).text(originalText);
            console.log('Response:', res);

            if(!res.success){
                alert(res.message);
                return;
            }

            if(action === 'addAction'){
                const displayName = res.name || data.name;
                const html = `<div class="action-item card p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="editable-label" data-type="action" data-id="${res.id}">${htmlEscape(displayName)}</span>
                        <div>
                            <button class="btn btn-sm btn-danger action-delete-btn" data-action="deleteAction" data-id="${res.id}">×</button>
                        </div>
                    </div>
                </div>`;
                $('.actions-grid').append(html);
                if ($('#toggleDeleteModeActions').hasClass('btn-danger')) {
                    $('.actions-grid .action-item:last .action-delete-btn').show();
                }
            }

            if(action === 'editAction'){
                $(`.action-item .editable-label[data-id="${res.id}"]`).text(res.name || data.name);
            }

            if(action === 'addModule'){
                const displayName = res.name || data.name;
                let moduleActionsHtml = '';
                if(data.selected_actions && data.selected_actions.length > 0){
                    moduleActionsHtml = '<div class="d-flex flex-wrap gap-2">';
                    data.selected_actions.forEach(function(actionId){
                        const actionItem = $(`.action-item .editable-label[data-id="${actionId}"]`).closest('.action-item');
                        if(actionItem.length){
                            const actionName = actionItem.find('.editable-label').text().trim();
                            moduleActionsHtml += `<span class="badge bg-light text-dark"><input type="checkbox" checked disabled class="me-1">${htmlEscape(actionName)}</span>`;
                        }
                    });
                    moduleActionsHtml += '</div>';
                } else {
                    moduleActionsHtml = '<span class="text-muted">No actions assigned</span>';
                }

                const html = `<div class="module-item card p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2 module-item-header">
                        <div class="title d-flex align-items-center flex-shrink-1 overflow-hidden">
                            <button class="btn btn-sm btn-outline-secondary me-2 toggle-module flex-shrink-0">+</button>
                            <span class="editable-label flex-grow-1" data-type="module" data-id="${res.id}">${htmlEscape(displayName)}</span>
                        </div>
                        <div class="btn-group flex-shrink-0">
                            <button class="btn btn-sm btn-danger" data-action="deleteModule" data-module-id="${res.id}">×</button>
                        </div>
                    </div>
                    <div class="module-actions mt-2" style="display:none;">${moduleActionsHtml}</div>
                </div>`;

                $('.modules-grid').append(html);
                moduleAssignments[res.id] = data.selected_actions ? [...data.selected_actions] : [];
            }

            if(action === 'editModule'){
                const card = $(`.module-item .editable-label[data-id="${res.id}"]`).closest('.module-item');
                card.find('.editable-label').text(res.name || data.name);

                let moduleActionsHtml = '';
                if(data.selected_actions && data.selected_actions.length > 0){
                    moduleActionsHtml = '<div class="d-flex flex-wrap gap-2">';
                    data.selected_actions.forEach(function(actionId){
                        const actionItem = $(`.action-item .editable-label[data-id="${actionId}"]`).closest('.action-item');
                        if(actionItem.length){
                            const actionName = actionItem.find('.editable-label').text().trim();
                            moduleActionsHtml += `<span class="badge bg-light text-dark"><input type="checkbox" checked disabled class="me-1">${htmlEscape(actionName)}</span>`;
                        }
                    });
                    moduleActionsHtml += '</div>';
                } else {
                    moduleActionsHtml = '<span class="text-muted">No actions assigned</span>';
                }

                card.find('.module-actions').html(moduleActionsHtml);
                moduleAssignments[res.id] = data.selected_actions ? [...data.selected_actions] : [];
            }

            if(action === 'addRole'){
                const displayName = res.name || data.name;
                // FIX: store permissions WITH names so system card buildRolesHtml can render them
                roleAssignments[res.id] = (data.permissions || []).map(p => ({
                    module_id:   parseInt(p.module_id),
                    action_id:   parseInt(p.action_id),
                    module_name: p.module_name || '',
                    action_name: p.action_name || ''
                }));

                let rolePermissionsHtml = '';
                if(data.permissions && data.permissions.length > 0){
                    const byModule = {};
                    data.permissions.forEach(function(p){
                        if(!byModule[p.module_id]) byModule[p.module_id] = { name: p.module_name || '', actions: [] };
                        byModule[p.module_id].actions.push(p.action_name || '');
                    });

                    Object.keys(byModule).forEach(function(moduleId){
                        const mod = byModule[moduleId];
                        rolePermissionsHtml += `<strong class="d-block mt-2 ps-3">${htmlEscape(mod.name)}</strong><div class="d-flex flex-wrap gap-2 ps-3">`;
                        mod.actions.forEach(function(actName){
                            rolePermissionsHtml += `<span class="badge bg-light text-dark"><input type="checkbox" checked disabled class="me-1">${htmlEscape(actName)}</span>`;
                        });
                        rolePermissionsHtml += '</div>';
                    });
                } else {
                    rolePermissionsHtml = '<span class="text-muted">No permissions assigned</span>';
                }

                const html = `<div class="role-item card p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-secondary me-2 toggle-role">+</button>
                            <span class="editable-label" data-type="role" data-id="${res.id}">${htmlEscape(displayName)}</span>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-danger" data-action="deleteRole" data-role-id="${res.id}">×</button>
                        </div>
                    </div>
                    <div class="role-permissions mt-2" style="display:none;">${rolePermissionsHtml}</div>
                </div>`;

                $('.roles-grid').append(html);
            }

            if(action === 'editRole'){
                const card = $(`.role-item .editable-label[data-id="${res.id}"]`).closest('.role-item');
                card.find('.editable-label').text(res.name || data.name);

                // FIX: store permissions WITH names
                roleAssignments[res.id] = (data.permissions || []).map(p => ({
                    module_id:   parseInt(p.module_id),
                    action_id:   parseInt(p.action_id),
                    module_name: p.module_name || '',
                    action_name: p.action_name || ''
                }));

                let rolePermissionsHtml = '';
                if(data.permissions && data.permissions.length > 0){
                    const byModule = {};
                    data.permissions.forEach(function(p){
                        if(!byModule[p.module_id]) byModule[p.module_id] = { name: p.module_name || '', actions: [] };
                        byModule[p.module_id].actions.push(p.action_name || '');
                    });

                    Object.keys(byModule).forEach(function(moduleId){
                        const mod = byModule[moduleId];
                        rolePermissionsHtml += `<strong class="d-block mt-2 ps-3">${htmlEscape(mod.name)}</strong><div class="d-flex flex-wrap gap-2 ps-3">`;
                        mod.actions.forEach(function(actName){
                            rolePermissionsHtml += `<span class="badge bg-light text-dark"><input type="checkbox" checked disabled class="me-1">${htmlEscape(actName)}</span>`;
                        });
                        rolePermissionsHtml += '</div>';
                    });
                } else {
                    rolePermissionsHtml = '<span class="text-muted">No permissions assigned</span>';
                }

                card.find('.role-permissions').html(rolePermissionsHtml);
            }

            if(action === 'addSystem'){
                const displayName = res.name || data.name;
                const newRoles = res.roles || [];
                systemRoles[res.id] = newRoles;
                const rolesHtml = buildRolesHtml(newRoles);
                $('.systems-list').append(buildSystemCardHtml(res.id, displayName, rolesHtml));
            }

            if(action === 'editSystem'){
                const displayName = res.name || data.name;
                $(`.system-item .editable-label[data-id="${res.id}"]`).text(displayName);
                $(`.system-item [data-action="duplicateSystem"][data-system-id="${res.id}"]`).data('system-name', displayName);
                
                const newRoles = res.roles || [];
                systemRoles[res.id] = newRoles;
                const rolesHtml = newRoles.length > 0
                    ? buildRolesHtml(newRoles)
                    : '<span class="text-muted">No roles assigned</span>';
                $(`.system-item[data-system-id="${res.id}"] .system-roles-container`).html(rolesHtml);
            }

            if(action === 'addUser'){
                let toggleHtml = '';
                let approvalsHtml = '';
                
                if(res.user_type === 'Approver') {
                    toggleHtml = '<button class="btn btn-sm btn-outline-secondary me-2 toggle-user-approvals">+</button>';
                    
                    let approvalsContent = '';
                    if (res.assignments && res.assignments.length > 0) {
                        approvalsContent = '<div class="ps-2">';
                        
                        const systemDeptMap = {};
                        res.assignments.forEach(a => {
                            if (!systemDeptMap[a.system_id]) {
                                systemDeptMap[a.system_id] = [];
                            }
                            systemDeptMap[a.system_id].push(a.department_id);
                        });
                        
                        Object.keys(systemDeptMap).forEach(sysId => {
                            const deptIds = systemDeptMap[sysId];
                            
                            let sysName = '';
                            <?php foreach ($systems as $sys): ?>
                                if (<?= $sys['id'] ?> == sysId) {
                                    sysName = '<?= htmlspecialchars($sys['name']) ?>';
                                }
                            <?php endforeach; ?>
                            
                            const deptNames = [];
                            <?php foreach ($departments as $dept): ?>
                                if (deptIds.includes(<?= $dept['id'] ?>)) {
                                    deptNames.push('<?= htmlspecialchars($dept['name']) ?>');
                                }
                            <?php endforeach; ?>
                            
                            if (deptNames.length > 0) {
                                approvalsContent += `<small class="d-block mb-1"><strong>${sysName}:</strong> ${deptNames.join(', ')}</small>`;
                            }
                        });
                        
                        approvalsContent += '</div>';
                    } else {
                        approvalsContent = '<small class="text-muted">No systems assigned yet</small>';
                    }
                    
                    approvalsHtml = `<div class="user-approvals mt-2" style="display:none; margin-left: 30px;">
                                        <small class="text-muted d-block mb-1">Assigned to:</small>
                                        <div class="ps-2">
                                            ${approvalsContent}
                                        </div>
                                    </div>`;
                }
                
                const html = `<div class="user-item card p-3 mb-2" data-user-id="${res.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center">
                                ${toggleHtml}
                                <div>
                                    <strong>${htmlEscape(res.name || data.name)}</strong> <small class="text-muted">(${htmlEscape(res.employee_id || '')})</small>
                                    <br>
                                    <small class="text-muted">${htmlEscape(res.user_type || '')}</small>
                                </div>
                            </div>
                            ${approvalsHtml}
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

            if(action === 'editUser'){
                const item = $(`.user-item[data-user-id="${res.id}"]`);
                item.find('strong').first().text(res.name || data.name);
                item.find('small.text-muted').first().text(`(${res.employee_id || ''})`);
                item.find('[data-action="editUser"]').data('name', res.employee_id || '').data('user-role', res.user_type || '');
                
                approverAssignments[res.id] = res.assignments || [];
                
                if (res.user_type === 'Approver') {
                    if (item.find('.toggle-user-approvals').length === 0) {
                        item.find('.d-flex.align-items-center').prepend('<button class="btn btn-sm btn-outline-secondary me-2 toggle-user-approvals">+</button>');
                    }
                    
                    item.find('.user-approvals').remove();
                    
                    let approvalsContent = '';
                    if (res.assignments && res.assignments.length > 0) {
                        approvalsContent = '<small class="text-muted d-block mb-1">Assigned to:</small><div class="ps-2">';
                        
                        const systemDeptMap = {};
                        res.assignments.forEach(a => {
                            if (!systemDeptMap[a.system_id]) {
                                systemDeptMap[a.system_id] = [];
                            }
                            systemDeptMap[a.system_id].push(a.department_id);
                        });
                        
                        Object.keys(systemDeptMap).forEach(sysId => {
                            const deptIds = systemDeptMap[sysId];
                            
                            let sysName = '';
                            <?php foreach ($systems as $sys): ?>
                                if (<?= $sys['id'] ?> == sysId) {
                                    sysName = '<?= htmlspecialchars($sys['name']) ?>';
                                }
                            <?php endforeach; ?>
                            
                            const deptNames = [];
                            <?php foreach ($departments as $dept): ?>
                                if (deptIds.includes(<?= $dept['id'] ?>)) {
                                    deptNames.push('<?= htmlspecialchars($dept['name']) ?>');
                                }
                            <?php endforeach; ?>
                            
                            if (deptNames.length > 0) {
                                approvalsContent += `<small class="d-block mb-1"><strong>${sysName}:</strong> ${deptNames.join(', ')}</small>`;
                            }
                        });
                        
                        approvalsContent += '</div>';
                    } else {
                        approvalsContent = '<small class="text-muted">No systems assigned yet</small>';
                    }
                    
                    const approvalsDiv = `<div class="user-approvals mt-2" style="display:none; margin-left: 30px;">
                                            <small class="text-muted d-block mb-1">Assigned to:</small>
                                            <div class="ps-2">
                                                ${approvalsContent}
                                            </div>
                                        </div>`;
                    item.find('.d-flex.align-items-center').after(approvalsDiv);
                } else {
                    item.find('.toggle-user-approvals').remove();
                    item.find('.user-approvals').remove();
                }
            }

            modal.hide();
            $('#accessTypeForm')[0].reset();
            $('#permissionsContainer').html('');
            $('#modalInputGroup').hide();
        }, 'json').always(function() {
            submitBtn.prop('disabled', false).text(originalText);
        }).fail(function(xhr, status, error){
            console.error('AJAX Error:', status, error);
            console.error('Response:', xhr.responseText);
            alert('Error: ' + error + '\n\nCheck browser console for details');
            submitBtn.prop('disabled', false).text(originalText);
        });
    });

});
</script>