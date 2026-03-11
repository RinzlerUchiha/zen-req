<?php
require_once ($reqhub_root . "/includes/auth.php");
requireLogin();

if ($_SESSION['user']['role'] !== 'admin') die("Access denied");

require_once '../includes/db.php';

// --- Fetch data ---
$users = $pdo->query("SELECT * FROM users ORDER BY name")->fetchAll();
$systems = $pdo->query("SELECT * FROM systems ORDER BY name")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
$actions = $pdo->query("SELECT * FROM actions ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$modules = $pdo->query("SELECT * FROM modules ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$roles   = $pdo->query("SELECT * FROM roles ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch module_actions for easier role assignment ---
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

// --- Build module -> action_id map for JS prechecking ---
$moduleAssignments = [];

foreach ($moduleActions as $moduleId => $actionsList) {
    foreach ($actionsList as $a) {
        $moduleAssignments[$moduleId][] = $a['action_id'];
    }
}

// --- Fetch role_permissions for displaying assigned modules + actions ---
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
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h4 class="mb-4">Admin Settings</h4>
    <div class="row">
        <!-- LEFT: Categories -->
        <div class="col-md-3">
            <div class="list-group mb-4" id="settingCategories">
                <button type="button" class="list-group-item list-group-item-action active" data-target="approverSettings">Approver Setter</button>
                <button type="button" class="list-group-item list-group-item-action" data-target="systemAccessSettings">System & Access Types</button>
            </div>
        </div>

        <!-- RIGHT: Contents -->
        <div class="col-md-9">

            <!-- APPROVER SETTER -->
            <div id="approverSettings" class="setting-content">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Approver Setter</h5>
                        <form method="post" action="../actions/set_approver_action.php" autocomplete="off">
                            <label class="form-label">Select User</label>
                            <select name="user_id" class="form-select searchable" required>
                                <option value="">Select User</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label class="form-label mt-3">System</label>
                            <select name="system_id" class="form-select" required>
                                <option value="">Select System</option>
                                <?php foreach ($systems as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label class="form-label mt-3">Department / Store</label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <button type="submit" class="btn btn-primary w-100 mt-4">Set Approver</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- SYSTEM & ACCESS TYPES -->
            <div id="systemAccessSettings" class="setting-content" style="display:none;">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">System & Access Types</h5>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3" id="systemAccessTabs" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#actionsTab" type="button">Actions</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#modulesTab" type="button">Modules</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rolesTab" type="button">Roles</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#usersTab" type="button">Users</button></li>
                        </ul>

                        <div class="tab-content">
                            <!-- Actions -->
                            <div class="tab-pane fade show active" id="actionsTab">
    <button class="btn btn-primary mb-2" data-action="addAction">Add Action</button>

    <div class="actions-grid">
        <?php foreach ($actions as $action): ?>
            <div class="action-item card p-2 mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong><?= htmlspecialchars($action['name']) ?></strong>
                    <div>
                        <button class="btn btn-sm btn-success me-1"
                                data-action="editAction"
                                data-id="<?= $action['id'] ?>"
                                data-name="<?= htmlspecialchars($action['name'], ENT_QUOTES) ?>">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger" data-action="deleteAction" data-id="<?= $action['id'] ?>">Delete</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

                            <!-- Modules -->
                            <div class="tab-pane fade" id="modulesTab">
    <button class="btn btn-primary mb-2" data-action="addModule">Add Module</button>

    <div class="modules-grid">
        <?php foreach ($modules as $module): ?>
            <div class="module-item card p-2 mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2 module-item-header">
                    <div class="title d-flex align-items-center flex-shrink-1 overflow-hidden">
                        <button class="btn btn-sm btn-outline-secondary me-2 toggle-module flex-shrink-0">+</button>
                        <strong class="text-truncate flex-grow-1" title="<?= htmlspecialchars($module['name']) ?>">
                            <?= htmlspecialchars($module['name']) ?>
                        </strong>
                    </div>
                    <div class="btn-group flex-shrink-0">
                        <button class="btn btn-sm btn-success me-1" data-action="editModule" data-module-id="<?= $module['id'] ?>" data-name="<?= htmlspecialchars($module['name']) ?>">Edit</button>
                        <button class="btn btn-sm btn-danger" data-action="deleteModule" data-module-id="<?= $module['id'] ?>">Delete</button>
                    </div>
                </div>

                <!-- Collapsible actions list -->
                <div class="module-actions mt-2" style="display:none;">
                    <?php if(!empty($moduleActions[$module['id']])): ?>
                        <div class="module-capsule-grid">
                            <?php foreach($moduleActions[$module['id']] as $action): ?>
                                <div class="capsule-item">
                                    <input type="checkbox" checked disabled>
                                    <span><?= htmlspecialchars($action['name']) ?></span>
                                </div>
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

                            <!-- Roles Tab -->
<div class="tab-pane fade" id="rolesTab">
    <button class="btn btn-primary mb-2" data-action="addRole">Add Role</button>

    <div class="roles-grid">
        <?php foreach ($roles as $role): ?>
            <div class="role-item card p-2 mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary me-2 toggle-role">+</button>
                        <strong><?= htmlspecialchars($role['name']) ?></strong>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success me-1" data-action="editRole" data-role-id="<?= $role['id'] ?>" data-name="<?= htmlspecialchars($role['name']) ?>">Edit</button>
                        <button class="btn btn-sm btn-danger" data-action="deleteRole" data-role-id="<?= $role['id'] ?>">Delete</button>
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
                                if ($currentModule !== '') echo '</div>'; // close previous module capsule grid
                                $currentModule = $item['module_name'];
                                echo '<strong>' . htmlspecialchars($currentModule) . '</strong>';
                                echo '<div class="module-capsule-grid mt-1 mb-2">';
                            endif;
                            if ($item['action_name']):
                                echo '<div class="capsule-item">';
                                echo '<input type="checkbox" checked disabled> ';
                                echo '<span>' . htmlspecialchars($item['action_name']) . '</span>';
                                echo '</div>';
                            endif;
                        endforeach;
                        if ($currentModule !== '') echo '</div>'; // close last module capsule grid
                    else
                        echo '<span class="text-muted">No permissions assigned</span>';
                    endif;
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

                            <!-- Users -->
                            <div class="tab-pane fade" id="usersTab">
                                <button class="btn btn-primary mb-2" data-action="addUser">Add User</button>
                                <ul class="list-group mb-3">
                                    <?php foreach ($users as $user): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?= htmlspecialchars($user['name']) ?>
                                            <div>
                                                <button class="btn btn-sm btn-success me-1" data-action="editUser" data-user-id="<?= $user['id'] ?>" data-name="<?= htmlspecialchars($user['name']) ?>" data-role-id="<?= $user['role_id'] ?>">Edit</button>
                                                <button class="btn btn-sm btn-danger" data-action="deleteUser" data-user-id="<?= $user['id'] ?>">Delete</button>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- SINGLE MODAL -->
<div class="modal fade" id="accessTypeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="accessTypeForm" method="post" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="accessTypeModalLabel">Manage</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="modalAction" name="action">
            <input type="hidden" id="modalRole" name="role_id">
            <input type="hidden" id="modalModule" name="module_id">
            <input type="hidden" id="modalUser" name="user_id">
            <input type="hidden" id="modalActionId" name="id">

            <div id="modalInputGroup" class="mb-3" style="display:none;">
                <label for="modalInput" class="form-label" id="modalInputLabel"></label>
                <input type="text" class="form-control" name="name" id="modalInput" placeholder="Enter name" autocomplete="off">
            </div>

            <div id="permissionsContainer"></div>
            <p id="modalWarning" class="text-danger" style="display:none;"></p>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Confirm</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<style>
/* ============================= */
/* ACCESS TYPE MODAL CHECKBOXES */
/* ============================= */

#accessTypeModal .modal-body ul.list-unstyled li,
#accessTypeModal .modal-body label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

#accessTypeModal .modal-body input[type="checkbox"] {
    flex-shrink: 0;
}

/* ============================= */
/* MODULES TAB */
/* ============================= */

.modules-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    align-items: start;
}

.module-item {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 0.5rem;
    background: #fff;
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

.module-item-header .title strong {
    max-width: 200px;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.module-item-header .btn-group {
    flex-shrink: 0;
    display: flex;
    gap: 0.25rem;
}

/* ============================= */
/* ACTIONS TAB */
/* ============================= */

.actions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.action-item {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 0.5rem;
    background: #fff;
}

/* ============================= */
/* ROLES TAB */
/* ============================= */

.roles-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    align-items:start;
}

.role-item {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 0.5rem;
    background: #fff;
}

/* ============================= */
/* ROLE MODAL — MODULES CAPSULE LAYOUT */
/* ============================= */

/* Container for role modal modules */
#permissionsContainer {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 columns for modules */
    gap: 1rem;
    width: 100%;
    box-sizing: border-box;
}

/* Module card */
.role-modal-module-card {
    border: 1px solid #dee2e6;
    border-radius: 0.75rem;
    padding: 0.75rem;
    background: #fff;
    display: flex;
    flex-direction: column;
    min-width: 0; /* prevent overflow */
}

/* Module title */
.role-modal-module-title {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.6rem;
    word-break: break-word;
    white-space: normal;
}

/* Capsules inside each module */
.role-modal-module-card .module-capsule-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 columns for capsules */
    gap: 0.5rem;
}

/* Capsule items */
.role-modal-module-card .capsule-item {
    background: #f1f3f5;
    border-radius: 1rem;
    padding: 0.4rem 0.6rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.875rem;
    white-space: normal;
    word-break: break-word;
    width: 100%;
    box-sizing: border-box;
}

/* Actions grid inside module */
.role-modal-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* 2 columns for checkboxes */
    gap: 0.4rem;
}

/* Checkbox items */
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

/* Prevent modal overflow */
#accessTypeModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}


/* ============================= */
/* SHARED TEXT BEHAVIOR */
/* ============================= */

.role-modal-action-item span,
.capsule-item span {
    flex: 1;
    min-width: 0;
    word-break: break-word;
}

/* ===============================
   MODULE MODAL - 4 COLUMN GRID
================================ */
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
    #permissionsContainer {
        grid-template-columns: repeat(2, 1fr);
    }
    .role-modal-module-card .module-capsule-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    #permissionsContainer {
        grid-template-columns: 1fr;
    }
    .role-modal-module-card .module-capsule-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>

<script>
$(function(){

    const modal = new bootstrap.Modal(document.getElementById('accessTypeModal'));
    let roleAssignments = <?= json_encode($roleAssignments) ?>;
    let moduleAssignments = <?= json_encode($moduleAssignments ?? []) ?>;

    // ==============================
    // CATEGORY SWITCH
    // ==============================
    $(document).on('click', '#settingCategories button', function(){
        $('#settingCategories button').removeClass('active');
        $(this).addClass('active');

        const target = $(this).data('target');
        $('.setting-content').hide();
        $('#' + target).show();
    });

    // ==============================
    // COLLAPSIBLE (Modules & Roles)
    // ==============================
    $(document).on('click', '.toggle-module', function(){
        const container = $(this).closest('.module-item').find('.module-actions');
        container.slideToggle(150);
        $(this).text($(this).text() === '+' ? '-' : '+');
    });

    $(document).on('click', '.toggle-role', function(){
        const container = $(this).closest('.role-item').find('.role-permissions');
        container.slideToggle(150);
        $(this).text($(this).text() === '+' ? '-' : '+');
    });

    // ==============================
    // DELETE HANDLER - DYNAMIC DOM UPDATE
    // ==============================
    $(document).on('click', '[data-action^="delete"]', function() {
        if (!confirm('Are you sure?')) return;

        const button = $(this);
        const action = button.data('action');
        let url = '';
        let data = { action };

        switch(action) {
            case 'deleteAction':
                url = '../actions/action_action.php';
                data.id = button.data('id');
                break;
            case 'deleteModule':
                url = '../actions/module_action.php';
                data.module_id = button.data('module-id');
                break;
            case 'deleteRole':
                url = '../actions/role_action.php';
                data.role_id = button.data('role-id');
                break;
            case 'deleteUser':
                url = '../actions/user_action.php';
                data.user_id = button.data('user-id');
                break;
            default:
                console.error('Unknown delete action:', action);
                return;
        }

        $.post(url, data, function(res) {
            if (!res.success) {
                alert(res.message || 'Failed to delete item.');
                return;
            }

            // Remove from DOM dynamically
            switch(action) {
                case 'deleteAction':
                    button.closest('.action-item').slideUp(200, function() { $(this).remove(); });
                    // Remove from moduleAssignments
                    Object.keys(moduleAssignments).forEach(mid => {
                        moduleAssignments[mid] = moduleAssignments[mid].filter(aid => aid != data.id);
                    });
                    break;
                case 'deleteModule':
                    button.closest('.module-item').slideUp(200, function() { $(this).remove(); });
                    delete moduleAssignments[data.module_id];
                    // Remove module cards from role modals
                    $('.role-modal-module-card').filter(function(){
                        return $(this).find('.module-master-checkbox').data('module') == data.module_id;
                    }).remove();
                    break;
                case 'deleteRole':
                    button.closest('.role-item').slideUp(200, function() { $(this).remove(); });
                    delete roleAssignments[data.role_id];
                    break;
                case 'deleteUser':
                    button.closest('li').slideUp(200, function() { $(this).remove(); });
                    break;
            }
        }, 'json');
    });

    // ==============================
    // OPEN MODAL (Add/Edit)
    // ==============================
    $(document).on('click', '[data-action]:not([data-action^="delete"])', function(){

        const action = $(this).data('action');
        const modalDialog = $('#accessTypeModal .modal-dialog');

        modalDialog.removeClass('modal-sm modal-md modal-lg modal-xl');
        if(action.includes('Action')) modalDialog.addClass('modal-sm');
        else if(action.includes('Role')) modalDialog.addClass('modal-xl');
        else if(action.includes('Module')) modalDialog.addClass('modal-lg');
        else modalDialog.addClass('modal-md');

        // SET HIDDEN FIELDS
        $('#modalAction').val(action);
        $('#modalRole').val($(this).data('role-id')||'');
        $('#modalModule').val($(this).data('module-id')||'');
        $('#modalUser').val($(this).data('user-id')||'');
        $('#modalActionId').val($(this).data('id')||'');
        const name = $(this).data('name')||'';

        $('#modalWarning, #modalInputGroup').hide();

        // ==============================
        // ACTION MODAL
        // ==============================
        if(action.includes('Action')){
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('Action Name');
            $('#modalInput').val(name).focus();
        }

        // ==============================
        // MODULE MODAL
        // ==============================
        if(action.includes('Module')){
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('Module Name');
            $('#modalInput').val(name).focus();

            let html = '<strong>Select Actions</strong><div class="module-modal-grid mt-2">';
            $('.actions-grid .action-item').each(function(){
                const aid = $(this).find('button[data-action="editAction"]').data('id');
                const aname = $(this).find('strong').text();
                html += `<label class="role-modal-action-item">
                            <input type="checkbox" class="module-action-checkbox" value="${aid}">
                            <span>${aname}</span>
                        </label>`;
            });
            html += '</div>';
            $('#permissionsContainer').html(html);

            const moduleId = $(this).data('module-id');
            if(moduleId && moduleAssignments[moduleId]){
                const savedActions = moduleAssignments[moduleId];
                savedActions.forEach(function(actionId){
                    $(`.module-action-checkbox[value="${actionId}"]`).prop('checked', true);
                });
            }
        }

        // ==============================
        // ROLE MODAL (FIXED)
        // ==============================
        if(action.includes('Role')){
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('Role Name');
            $('#modalInput').val(name).focus();

            let html = '';

            $('.modules-grid .module-item').each(function(){

                const mid = $(this).find('button[data-action="editModule"]').data('module-id');
                const mname = $(this).find('strong').text();

                html += `
                <div class="role-modal-module-card">
                    <div class="role-modal-module-header">
                        <label>
                            <input type="checkbox"
                                class="module-master-checkbox"
                                data-module="${mid}">
                            <strong>${mname}</strong>
                        </label>
                    </div>
                    <div class="module-capsule-grid">
                `;

                // 🔥 ONLY GET ACTIONS ASSIGNED TO THIS MODULE
                const moduleActions = moduleAssignments[mid] || [];

                moduleActions.forEach(function(actionId){

                    // find action name from actions tab
                    const actionItem = $(`.action-item button[data-id="${actionId}"]`)
                                        .closest('.action-item');

                    if(actionItem.length){
                        const actionName = actionItem.find('strong').text();

                        html += `
                            <div class="capsule-item">
                                <label class="role-modal-action-item">
                                    <input type="checkbox"
                                        class="role-permission-checkbox"
                                        data-module="${mid}"
                                        value="${actionId}">
                                    <span>${actionName}</span>
                                </label>
                            </div>
                        `;
                    }
                });

                html += `
                    </div>
                </div>
                `;
            });

            $('#permissionsContainer').html(html);

            // ============================
            // PRECHECK SAVED PERMISSIONS
            // ============================
            const roleId = $(this).data('role-id');

            if(roleId && roleAssignments[roleId]){

                const perms = roleAssignments[roleId];

                perms.forEach(function(p){
                    $(`.role-permission-checkbox[data-module="${p.module_id}"][value="${p.action_id}"]`)
                        .prop('checked', true);
                });

                // update master checkbox
                $('.module-master-checkbox').each(function(){
                    const mid = $(this).data('module');
                    const checked = $(`.role-permission-checkbox[data-module="${mid}"]:checked`).length;
                    $(this).prop('checked', checked > 0);
                });
            }
        }

        // ==============================
        // USER MODAL
        // ==============================
        if(action.includes('User')){
            $('#modalInputGroup').show();
            $('#modalInputLabel').text('User Name');
            $('#modalInput').val(name).focus();

            let html = `<div class="mt-3">
                            <label class="form-label">Assign Role</label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Select Role</option>`;
            $('.roles-grid .role-item').each(function(){
                const rid = $(this).find('button[data-action="editRole"]').data('role-id');
                const rname = $(this).find('strong').text();
                html += `<option value="${rid}">${rname}</option>`;
            });
            html += '</select></div>';
            $('#permissionsContainer').html(html);
            $('#permissionsContainer select[name="role_id"]').val($(this).data('role-id')||'');
        }

        modal.show();
    });

    // ==============================
    // FORM SUBMIT
    // ==============================
    $('#accessTypeForm').submit(function(e){
        e.preventDefault();

        const action = $('#modalAction').val();
        let url = '', data = { action };
        const nameVal = $('#modalInput').val().trim();

        if(['Role','Action','Module','User'].some(a=>action.includes(a)) && !nameVal){
            alert('Name cannot be empty');
            return;
        }

        data.name = nameVal;

        if(action.includes('Role')){
            data.role_id = $('#modalRole').val();
            data.permissions = [];
            $('.role-permission-checkbox:checked').each(function(){
                data.permissions.push({
                    module_id: $(this).data('module'),
                    action_id: $(this).val()
                });
            });
            url='../actions/role_action.php';
        }

        if(action.includes('Action')){
            url='../actions/action_action.php';
            data.id = $('#modalActionId').val();
        }

        if(action.includes('Module')){
            url='../actions/module_action.php';
            data.module_id = $('#modalModule').val();
            data.selected_actions = [];
            $('.module-action-checkbox:checked').each(function(){
                data.selected_actions.push($(this).val());
            });
        }

        if(action.includes('User')){
            url='../actions/user_action.php';
            data.user_id = $('#modalUser').val();
            data.role_id = $('#permissionsContainer select[name="role_id"]').val();
        }

        $.post(url, data, function(res){
            if(!res.success){
                alert(res.message);
                return;
            }

            // -------------------------
            // DYNAMIC ADD/UPDATE
            // -------------------------
            if(action === 'addAction'){
                const html = `<div class="action-item card p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>${res.name}</strong>
                        <div>
                            <button class="btn btn-sm btn-success me-1"
                                    data-action="editAction"
                                    data-id="${res.id}"
                                    data-name="${res.name}">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-danger" data-action="deleteAction" data-id="${res.id}">Delete</button>
                        </div>
                    </div>
                </div>`;
                $('.actions-grid').append(html);

                // Update module modals immediately
                $('.module-modal-grid').append(`<label class="role-modal-action-item">
                            <input type="checkbox" class="module-action-checkbox" value="${res.id}">
                            <span>${res.name}</span>
                        </label>`);
            }

            if(action === 'editAction'){
                const card = $(`.action-item button[data-id="${res.id}"]`).closest('.action-item');
                card.find('strong').text(res.name);
                card.find('button[data-action="editAction"]').data('name', res.name);
                // Update module modals labels
                $('.module-action-checkbox').filter(function(){ return $(this).val() == res.id; }).next('span').text(res.name);
            }

            // ==========================
            // ADD MODULE
            // ==========================
            if(action === 'addModule'){

                let moduleActionsHtml = '';
                if(data.selected_actions && data.selected_actions.length > 0){
                    moduleActionsHtml = '<div class="module-capsule-grid">';
                    data.selected_actions.forEach(function(actionId){
                        const actionItem = $(`.action-item button[data-id="${actionId}"]`).closest('.action-item');
                        if(actionItem.length){
                            const actionName = actionItem.find('strong').text();
                            moduleActionsHtml += `
                                <div class="capsule-item">
                                    <input type="checkbox" checked disabled>
                                    <span>${actionName}</span>
                                </div>
                            `;
                        }
                    });
                    moduleActionsHtml += '</div>';
                } else {
                    moduleActionsHtml = '<span class="text-muted">No actions assigned</span>';
                }

                const html = `<div class="module-item card p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2 module-item-header">
                        <div class="title d-flex align-items-center flex-shrink-1 overflow-hidden">
                            <button class="btn btn-sm btn-outline-secondary me-2 toggle-module flex-shrink-0">+</button>
                            <strong class="text-truncate flex-grow-1">${res.name}</strong>
                        </div>
                        <div class="btn-group flex-shrink-0">
                            <button class="btn btn-sm btn-success me-1"
                                    data-action="editModule"
                                    data-module-id="${res.id}"
                                    data-name="${res.name}">Edit</button>
                            <button class="btn btn-sm btn-danger"
                                    data-action="deleteModule"
                                    data-module-id="${res.id}">Delete</button>
                        </div>
                    </div>
                    <div class="module-actions mt-2" style="display:none;">
                        ${moduleActionsHtml}
                    </div>
                </div>`;

                $('.modules-grid').append(html);

                moduleAssignments[res.id] = data.selected_actions ? [...data.selected_actions] : [];
            }

            // ==========================
            // EDIT MODULE
            // ==========================
            if(action === 'editModule'){

                const card = $(`.module-item button[data-module-id="${res.id}"]`)
                                .closest('.module-item');

                card.find('strong').text(res.name);
                card.find('button[data-action="editModule"]').data('name', res.name);

                let moduleActionsHtml = '';
                if(data.selected_actions && data.selected_actions.length > 0){
                    moduleActionsHtml = '<div class="module-capsule-grid">';
                    data.selected_actions.forEach(function(actionId){
                        const actionItem = $(`.action-item button[data-id="${actionId}"]`).closest('.action-item');
                        if(actionItem.length){
                            const actionName = actionItem.find('strong').text();
                            moduleActionsHtml += `
                                <div class="capsule-item">
                                    <input type="checkbox" checked disabled>
                                    <span>${actionName}</span>
                                </div>
                            `;
                        }
                    });
                    moduleActionsHtml += '</div>';
                } else {
                    moduleActionsHtml = '<span class="text-muted">No actions assigned</span>';
                }

                card.find('.module-actions').html(moduleActionsHtml);

                moduleAssignments[res.id] = data.selected_actions ? [...data.selected_actions] : [];
            }

            // ==========================
            // ADD ROLE
            // ==========================
            if(action === 'addRole'){

                let rolePermissionsHtml = '';
                if(data.permissions && data.permissions.length > 0){
                    const byModule = {};
                    data.permissions.forEach(function(p){
                        if(!byModule[p.module_id]) byModule[p.module_id] = [];
                        byModule[p.module_id].push(p.action_id);
                    });

                    Object.keys(byModule).forEach(function(moduleId){
                        const moduleCard = $(`.module-item button[data-module-id="${moduleId}"]`).closest('.module-item');
                        if(moduleCard.length){
                            const moduleName = moduleCard.find('strong').text();
                            rolePermissionsHtml += `<strong>${moduleName}</strong><div class="module-capsule-grid mt-1 mb-2">`;
                            
                            byModule[moduleId].forEach(function(actionId){
                                const actionCard = $(`.action-item button[data-id="${actionId}"]`).closest('.action-item');
                                if(actionCard.length){
                                    const actionName = actionCard.find('strong').text();
                                    rolePermissionsHtml += `
                                        <div class="capsule-item">
                                            <input type="checkbox" checked disabled>
                                            <span>${actionName}</span>
                                        </div>
                                    `;
                                }
                            });
                            
                            rolePermissionsHtml += '</div>';
                        }
                    });
                } else {
                    rolePermissionsHtml = '<span class="text-muted">No permissions assigned</span>';
                }

                const html = `<div class="role-item card p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-secondary me-2 toggle-role">+</button>
                            <strong>${res.name}</strong>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-success me-1"
                                    data-action="editRole"
                                    data-role-id="${res.id}"
                                    data-name="${res.name}">Edit</button>
                            <button class="btn btn-sm btn-danger"
                                    data-action="deleteRole"
                                    data-role-id="${res.id}">Delete</button>
                        </div>
                    </div>
                    <div class="role-permissions mt-2" style="display:none;">
                        ${rolePermissionsHtml}
                    </div>
                </div>`;

                $('.roles-grid').append(html);

                roleAssignments[res.id] = (data.permissions || []).map(p => ({
                    module_id: parseInt(p.module_id),
                    action_id: parseInt(p.action_id)
                }));
            }

            // ==========================
            // EDIT ROLE
            // ==========================
            if(action === 'editRole'){

                const card = $(`.role-item button[data-role-id="${res.id}"]`)
                                .closest('.role-item');

                card.find('strong').text(res.name);
                card.find('button[data-action="editRole"]').data('name', res.name);

                let rolePermissionsHtml = '';
                if(data.permissions && data.permissions.length > 0){
                    const byModule = {};
                    data.permissions.forEach(function(p){
                        if(!byModule[p.module_id]) byModule[p.module_id] = [];
                        byModule[p.module_id].push(p.action_id);
                    });

                    Object.keys(byModule).forEach(function(moduleId){
                        const moduleCard = $(`.module-item button[data-module-id="${moduleId}"]`).closest('.module-item');
                        if(moduleCard.length){
                            const moduleName = moduleCard.find('strong').text();
                            rolePermissionsHtml += `<strong>${moduleName}</strong><div class="module-capsule-grid mt-1 mb-2">`;
                            
                            byModule[moduleId].forEach(function(actionId){
                                const actionCard = $(`.action-item button[data-id="${actionId}"]`).closest('.action-item');
                                if(actionCard.length){
                                    const actionName = actionCard.find('strong').text();
                                    rolePermissionsHtml += `
                                        <div class="capsule-item">
                                            <input type="checkbox" checked disabled>
                                            <span>${actionName}</span>
                                        </div>
                                    `;
                                }
                            });
                            
                            rolePermissionsHtml += '</div>';
                        }
                    });
                } else {
                    rolePermissionsHtml = '<span class="text-muted">No permissions assigned</span>';
                }

                card.find('.role-permissions').html(rolePermissionsHtml);

                roleAssignments[res.id] = data.permissions ? [...data.permissions] : [];
            }

            modal.hide();
        }, 'json');
    });

    // =======================================
    // MODULE MASTER → Check / Uncheck All
    // =======================================
    $(document).on('change', '.module-master-checkbox', function(){
        const moduleId = $(this).data('module');
        const isChecked = $(this).is(':checked');
        $(`.role-permission-checkbox[data-module="${moduleId}"]`).prop('checked', isChecked);
    });

    // =======================================
    // ACTION CHECKBOX → Update Module Master
    // =======================================
    $(document).on('change', '.role-permission-checkbox', function(){
        const moduleId = $(this).data('module');
        const total = $(`.role-permission-checkbox[data-module="${moduleId}"]`).length;
        const checked = $(`.role-permission-checkbox[data-module="${moduleId}"]:checked`).length;
        $(`.module-master-checkbox[data-module="${moduleId}"]`).prop('checked', checked > 0);
    });

    // ==============================
    // RESET MODAL AFTER HIDE
    // ==============================
    $('#accessTypeModal').on('hidden.bs.modal', function () {
        const form = $('#accessTypeForm')[0];
        form.reset();
        $('#permissionsContainer').empty();
        $('#modalWarning, #modalInputGroup').hide();
    });

});
</script>