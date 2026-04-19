<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

if (!userHasRoleIn('Requestor', 'Approver', 'Reviewer')) {
    http_response_code(403);
    die('Access denied: Only Requestors, Approvers, and Reviewers can create requests');
}

$pdo         = ReqHubDatabase::getConnection('reqhub');
$currentUser = getCurrentUser();
$emp_no      = $currentUser['emp_no'];
$userRole    = $currentUser['reqhub_role'];

try {
    // ── Systems: Requestors see only their assigned systems; Approvers see all ──
    if ($userRole === 'Requestor' || $userRole === 'Reviewer') {
        // Get the users.id
        $stmtUid = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
        $stmtUid->execute([$emp_no]);
        $userRow = $stmtUid->fetch(PDO::FETCH_ASSOC);

        if ($userRow) {
        $stmtSys = $pdo->prepare("
            SELECT DISTINCT s.id, s.name, COALESCE(NULLIF(ts.sys_desc,''), s.name) AS full_name
            FROM user_approver_assignments uaa
            JOIN systems s ON uaa.system_id = s.id
            LEFT JOIN tngc_hrd2.tbl_systems ts ON LOWER(ts.system_id) = LOWER(s.name)
            WHERE uaa.user_id = ?
            ORDER BY s.name ASC
        ");
        $stmtSys->execute([$userRow['id']]);
        $systems = $stmtSys->fetchAll(PDO::FETCH_ASSOC);

        // Fallback: if no valid system assignments found, show all systems
        if (empty($systems)) {
            $systems = $pdo->query("SELECT id, name FROM systems ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        $systems = $pdo->query("SELECT id, name FROM systems ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    }
    } else {
        // Approver sees all systems
        $systems = $pdo->query("
        SELECT s.id, s.name, COALESCE(NULLIF(ts.sys_desc,''), s.name) AS full_name
        FROM systems s
        LEFT JOIN tngc_hrd2.tbl_systems ts ON LOWER(ts.system_id) = LOWER(s.name)
        ORDER BY s.name
    ")->fetchAll(PDO::FETCH_ASSOC);
        }

    $departments = $pdo->query("
        SELECT DISTINCT d.id, d.name, d.code
        FROM tngc_hrd2.tbl201_jobrec jr
        INNER JOIN departments d ON d.code = jr.jrec_department
        WHERE jr.jrec_status = 'primary' AND jr.jrec_department IS NOT NULL AND jr.jrec_department != ''
        ORDER BY d.name ASC
    ")->fetchAll();

    $users = $pdo->query("
        SELECT hu.U_ID as id, hu.Emp_No as employee_id,
            COALESCE(CONCAT(NULLIF(bi.bi_empfname,''),' ',NULLIF(bi.bi_emplname,'')), hu.U_Name, hu.Emp_No) as name
        FROM tngc_hrd2.tbl_user2 hu
        LEFT JOIN tngc_hrd2.tbl201_basicinfo bi ON hu.Emp_No = bi.bi_empno AND bi.datastat = 'current'
        LEFT JOIN tngc_hrd2.tbl201_jobrec jr ON hu.Emp_No = jr.jrec_empno AND jr.jrec_status = 'primary'
        WHERE hu.U_remarks = 'Active'
        GROUP BY hu.U_ID, hu.Emp_No, hu.U_Name, bi.bi_empfname, bi.bi_emplname
        ORDER BY COALESCE(CONCAT(NULLIF(bi.bi_empfname,''),' ',NULLIF(bi.bi_emplname,'')), hu.U_Name, hu.Emp_No) ASC
    ")->fetchAll();

    $accessTypes = $pdo->query("SELECT id, system, role, module, actions FROM access_types ORDER BY role, module, actions")->fetchAll(PDO::FETCH_ASSOC);

    $allUsers = $pdo->query("
        SELECT 
            hu.U_ID as id,
            hu.Emp_No as employee_id,
            COALESCE(
                CONCAT(NULLIF(bi.bi_empfname, ''), ' ', NULLIF(bi.bi_emplname, '')),
                hu.U_Name,
                hu.Emp_No
            ) as name
        FROM tngc_hrd2.tbl_user2 hu
        LEFT JOIN tngc_hrd2.tbl201_basicinfo bi ON hu.Emp_No = bi.bi_empno AND bi.datastat = 'current'
        LEFT JOIN tngc_hrd2.tbl201_jobrec jr ON hu.Emp_No = jr.jrec_empno AND jr.jrec_status = 'primary'
        GROUP BY hu.U_ID, hu.Emp_No, hu.U_Name, bi.bi_empfname, bi.bi_emplname
        ORDER BY COALESCE(
            CONCAT(NULLIF(bi.bi_empfname, ''), ' ', NULLIF(bi.bi_emplname, '')),
            hu.U_Name,
            hu.Emp_No
        ) ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    error_log("Loaded " . count($systems) . " systems (role=$userRole), " . count($departments) . " departments, " . count($users) . " users, " . count($accessTypes) . " access types");
} catch (PDOException $e) {
    error_log("Error fetching data: " . $e->getMessage());
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>

<?php include ($reqhub_root . "/includes/header.php"); ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<style>
    .choices__list--dropdown { z-index: 1000 !important; }
    .choices[data-type*="select-one"] .choices__button { z-index: 999 !important; }
    .choices.is-disabled .choices__item--selectable,
    .choices.is-disabled .choices__inner {
        color: #212529 !important;
        opacity: 1 !important;
        background-color: #e9ecef !important;
        cursor: not-allowed;
    }
    .choices.is-disabled { opacity: 1 !important; }
    .choices__input {
        color: #212529 !important;
        padding: 0 !important;
        margin: 0 !important;
        line-height: normal !important;
        vertical-align: middle !important;
    }
    .choices__inner {
        display: flex !important;
        align-items: center !important;
    }
</style>

<div class="container-fluid mt-4 px-3 px-lg-5">
    <h2>Create New Request</h2>

    <?php if (empty($systems)): ?>
    <div class="alert alert-warning">
        You have no systems assigned. Please contact an administrator to have systems assigned to your account.
    </div>
    <?php endif; ?>

    <form action="/zen/reqHub/request_create_action" method="POST" id="requestForm">

        <input type="hidden" name="chosen_role" id="chosenRoleInput" value="">

        <div class="row mb-3">
            <div class="col">
                <label class="form-label">System</label>
                <select name="system_id" id="systemSelect" class="form-select" required>
                    <option value="">Select System</option>
                    <?php foreach ($systems as $system): ?>
                    <option value="<?= $system['id'] ?>" data-acronym="<?= htmlspecialchars($system['name']) ?>"><?= htmlspecialchars($system['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <label class="form-label">Request For</label>
                <select name="request_for" id="requestForSelect" class="form-select" required>
                    <option value="">Select User</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" data-employee-id="<?= htmlspecialchars($u['employee_id']) ?>">
                        <?= htmlspecialchars($u['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <label class="form-label">Department</label>
                <select name="department_id" id="departmentSelect" class="form-select" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" data-code="<?= htmlspecialchars($dept['code']) ?>">
                        <?= htmlspecialchars($dept['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Remove From <span class="text-muted" style="font-weight:normal;">(leave blank if new request)</span></label>
            <select name="remove_from" id="removeFromSelect" class="form-select">
                <option value="">-- Leave blank if new request --</option>
                <?php foreach ($allUsers as $u): ?>
                <option value="<?= htmlspecialchars($u['name']) ?>">
                    <?= htmlspecialchars($u['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3" id="storeContainer" style="display:none;">
            <label class="form-label">Store</label>
            <input type="text" name="store" id="storeInput" class="form-control" placeholder="Enter store name">
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">Access Types</label>

            <div class="mb-3">
                <label class="form-label">Select Role (Auto-selects all its modules)</label>
                <select id="roleSelect" class="form-select">
                    <option value="">-- Choose a role --</option>
                </select>
            </div>

            <div class="row g-3">
                <!-- Modules & Actions (62%) -->
                <div style="flex:0 0 62.333333%; max-width:62.333333%;">
                    <h6>Modules & Actions</h6>
                    <div class="mb-2">
                        <input type="text" id="searchModules" class="form-control form-control-sm" placeholder="Search modules..." style="font-size:0.9rem;">
                    </div>
                    <div id="modulesContainer" class="border rounded p-2" style="max-height:600px; overflow-y:auto; background-color:#fafafa; min-height:200px;">
                        <div id="modulesDisplay" style="font-size:0.85rem; display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:12px;"></div>
                    </div>
                </div>

                <!-- Summary (38%) -->
                <div style="flex:0 0 37.666667%; max-width:37.666667%;">
                    <h6>Selected Access</h6>
                    <div id="summaryContainer" class="border rounded p-3" style="max-height:639px; overflow-y:auto; background-color:#fafafa; display:grid; grid-template-columns:repeat(2,1fr); gap:12px; padding:12px;">
                        <div class="text-muted small" style="grid-column:1/-1;">No access selected</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Description / Purpose</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // ── All users data for remove_from rebuilding ──
    const allUsersData = <?= json_encode(array_map(fn($u) => [
        'id'          => $u['id'],
        'name'        => $u['name'],
        'employee_id' => $u['employee_id'],
    ], $allUsers)) ?>;

    // ── Form submit ──
    const requestForm = document.getElementById('requestForm');
    requestForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const selectedAccessTypes = Array.from(document.querySelectorAll('.access-checkbox:checked'));
        if (selectedAccessTypes.length === 0) {
            alert('Please select at least one access type');
            return;
        }

        const disabledSelects = this.querySelectorAll('select:disabled');
        disabledSelects.forEach(s => s.disabled = false);

        const formData = new FormData(this);
        const departmentSelect = document.getElementById('departmentSelect');
        if (departmentSelect.disabled && departmentSelect.value) {
            formData.set('department_id', departmentSelect.value);
        }

        fetch('/zen/reqHub/request_create_action', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json().then(data => { throw new Error(data.message || 'Unknown error'); });
            }
            return response.text().then(() => { window.location.href = '/zen/reqHub/dashboard?status=pending'; });
        })
        .catch(error => { alert('Error: ' + error.message); });
    });

    // ── Choices.js init ──
    new Choices('#systemSelect',    { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });
    const requestForChoices  = new Choices('#requestForSelect', { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });
    const departmentChoices  = new Choices('#departmentSelect', { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });
    let removeFromChoices    = new Choices('#removeFromSelect', { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });

    const systemSelect      = document.getElementById("systemSelect");
    const roleSelect        = document.getElementById("roleSelect");
    const modulesDisplay    = document.getElementById("modulesDisplay");
    const summaryContainer  = document.getElementById("summaryContainer");
    const searchModules     = document.getElementById("searchModules");

    const allAccessTypesList = <?= json_encode($accessTypes) ?>;

    let systemNameMap = {};
    let systemAcronymMap = {};
    document.querySelectorAll("#systemSelect option").forEach(opt => {
        if (opt.value) {
            systemNameMap[opt.value] = opt.textContent.trim();
            systemAcronymMap[opt.value] = opt.dataset.acronym || opt.textContent.trim();
        }
    });

    let currentSearch        = "";
    let autoSelectedItems    = new Set();
    let autoSelectedModules  = new Set();

    // ── Rebuild Remove From dropdown ──
    function rebuildRemoveFrom(filteredUsers) {
        // Destroy old instance, recreate
        removeFromChoices.destroy();
        const sel = document.getElementById('removeFromSelect');
        // Clear options
        while (sel.options.length > 1) sel.remove(1);
        // Repopulate
        filteredUsers.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.name;
            opt.textContent = u.name;
            sel.appendChild(opt);
        });
        removeFromChoices = new Choices('#removeFromSelect', { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });
    }

    // ── Render modules ──
    function renderModules(prioritizeAutoSelected = false) {
        modulesDisplay.innerHTML = "";

        const selectedSystemId   = systemSelect.value;
        const selectedSystemName = selectedSystemId ? systemAcronymMap[selectedSystemId] : null;
        let toDisplay = selectedSystemName
            ? allAccessTypesList.filter(type => type.system === selectedSystemName)
            : [];

        if (currentSearch) {
            toDisplay = toDisplay.filter(type =>
                type.module.toLowerCase().includes(currentSearch.toLowerCase()) ||
                type.actions.toLowerCase().includes(currentSearch.toLowerCase())
            );
        }

        const grouped = {};
        toDisplay.forEach(type => {
            if (!grouped[type.module]) grouped[type.module] = {};
            if (!grouped[type.module][type.actions]) grouped[type.module][type.actions] = [];
            grouped[type.module][type.actions].push(type);
        });

        let modulesToDisplay = Object.entries(grouped);

        if (prioritizeAutoSelected) {
            const prioritized = [], others = [];
            modulesToDisplay.forEach(([mn, am]) => {
                if (autoSelectedModules.has(mn)) prioritized.push([mn, am]);
                else others.push([mn, am]);
            });
            modulesToDisplay = [...prioritized, ...others];
        }

        modulesToDisplay.forEach(([moduleName, actionsMap]) => {
            const actions = Object.values(actionsMap);

            const moduleCard = document.createElement("div");
            moduleCard.className = "module-card";
            moduleCard.style.cssText = "border:1px solid #555; border-radius:4px; padding:10px; background-color:#fffcfc; display:flex; flex-direction:column;";

            // Header
            const headerDiv = document.createElement("div");
            headerDiv.style.cssText = "display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid #444; gap:8px;";

            const moduleCheckbox = document.createElement("input");
            moduleCheckbox.type = "checkbox";
            moduleCheckbox.className = "module-checkbox";
            moduleCheckbox.style.cssText = "width:16px; height:16px; margin-top:2px; cursor:pointer; flex-shrink:0;";

            const moduleTitle = document.createElement("label");
            moduleTitle.style.cssText = "font-weight:bold; font-size:0.9rem; color:#000; word-break:break-word; flex:1; cursor:pointer; margin-bottom:0;";
            moduleTitle.textContent = moduleName;
            moduleTitle.addEventListener("click", function() { moduleCheckbox.click(); });

            const badge = document.createElement("span");
            badge.style.cssText = "background-color:#555; color:#fff; padding:3px 6px; border-radius:3px; font-size:0.7rem; font-weight:bold; white-space:nowrap; flex-shrink:0;";
            badge.textContent = actions.length + " action" + (actions.length !== 1 ? "s" : "");

            headerDiv.appendChild(moduleCheckbox);
            headerDiv.appendChild(moduleTitle);
            headerDiv.appendChild(badge);
            moduleCard.appendChild(headerDiv);

            // Actions — INLINE (flex-wrap row instead of 2-col grid)
            const actionsGrid = document.createElement("div");
            // actionsGrid
            actionsGrid.style.cssText = "display:grid; grid-template-columns:repeat(2, 1fr); gap:6px; align-content:flex-start;";

            const actionCheckboxes = [];

            actions.forEach(types => {
                const matchedType = types.find(t => autoSelectedItems.has(t.id.toString())) || types[0];

                const actionDiv = document.createElement("div");
                // Inline capsule style
                // actionDiv
                actionDiv.style.cssText = "display:flex; align-items:center; gap:4px; background:#f0f0f0; border-radius:20px; padding:3px 10px; cursor:pointer;";

                const checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.className = "access-checkbox";
                checkbox.name = "access_types[]";
                checkbox.value = matchedType.id;
                checkbox.id = `access_${matchedType.id}`;
                checkbox.dataset.system = matchedType.system;
                checkbox.dataset.role   = matchedType.role;
                checkbox.dataset.module = matchedType.module;
                checkbox.dataset.name   = matchedType.actions;
                checkbox.style.cssText  = "width:14px; height:14px; flex-shrink:0; cursor:pointer; margin:0;";

                if (autoSelectedItems.has(matchedType.id.toString())) {
                    checkbox.checked = true;
                }

                actionCheckboxes.push(checkbox);

                checkbox.addEventListener("change", function() {
                    if (!this.checked) {
                        autoSelectedItems.delete(this.value);
                    } else {
                        const role       = roleSelect.value;
                        const systemName = systemAcronymMap[systemSelect.value];
                        const type       = allAccessTypesList.find(t => t.id.toString() === this.value);
                        if (type && type.role === role && type.system === systemName) autoSelectedItems.add(this.value);
                    }
                    const allChecked = actionCheckboxes.every(cb => cb.checked);
                    const anyChecked = actionCheckboxes.some(cb => cb.checked);
                    moduleCheckbox.checked       = allChecked;
                    moduleCheckbox.indeterminate  = anyChecked && !allChecked;
                    updateSummary();
                });

                const label = document.createElement("label");
                label.htmlFor = `access_${matchedType.id}`;
                // label
                label.style.cssText = "margin-bottom:0; cursor:pointer; font-size:0.78rem; user-select:none; white-space:normal; color:#333; line-height:1.3; word-break:break-word;";
                label.textContent = matchedType.actions;

                // Clicking the capsule div also toggles
                actionDiv.addEventListener("click", function(e) {
                    if (e.target !== checkbox && e.target !== label) checkbox.click();
                });

                actionDiv.appendChild(checkbox);
                actionDiv.appendChild(label);
                actionsGrid.appendChild(actionDiv);
            });

            // Module checkbox handler
            moduleCheckbox.addEventListener("change", function() {
                const role       = roleSelect.value;
                const systemName = systemAcronymMap[systemSelect.value];
                actionCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                    if (!this.checked) autoSelectedItems.delete(cb.value);
                    else {
                        const type = allAccessTypesList.find(t => t.id.toString() === cb.value);
                        if (type && type.role === role && type.system === systemName) autoSelectedItems.add(cb.value);
                    }
                });
                updateSummary();
            });

            const allChecked = actionCheckboxes.every(cb => cb.checked);
            const anyChecked = actionCheckboxes.some(cb => cb.checked);
            moduleCheckbox.checked      = allChecked;
            moduleCheckbox.indeterminate = anyChecked && !allChecked;

            moduleCard.appendChild(actionsGrid);
            modulesDisplay.appendChild(moduleCard);
        });
    }

    searchModules.addEventListener("input", function() {
        currentSearch = this.value;
        renderModules(true);
    });

    renderModules(false);

    // ── System change ──
    systemSelect.addEventListener("change", function() {
        const systemId = this.value;
        autoSelectedItems.clear();
        autoSelectedModules.clear();
        updateSummary();
        roleSelect.value = '';
        document.getElementById('chosenRoleInput').value = '';

        if (!systemId) {
            roleSelect.innerHTML = '<option value="">-- Choose a role --</option>';
            renderModules(false);
            updateSummary();
            return;
        }

        fetch(`/zen/reqHub/system_roles_action?system_id=${systemId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.roles.length > 0) {
                    updateRoleUI(data.roles, data.system_name);
                } else {
                    roleSelect.innerHTML = '<option value="">No roles found</option>';
                }
                renderModules(false);
                updateSummary();
            })
            .catch(() => { roleSelect.innerHTML = '<option value="">Error loading roles</option>'; });
    });

    function updateRoleUI(roles, systemName) {
        roleSelect.innerHTML = '<option value="">-- Choose a role --</option>';
        roles.forEach(role => {
            const option = document.createElement("option");
            option.value = role;
            option.dataset.system = systemName;
            option.textContent = role;
            roleSelect.appendChild(option);
        });
    }

    roleSelect.addEventListener("change", function() {
        const role = this.value;
        document.getElementById('chosenRoleInput').value = role;
        if (!role) {
            autoSelectedItems.clear();
            autoSelectedModules.clear();
            renderModules(false);
            updateSummary();
            return;
        }
        selectAllModulesForRole(role, systemAcronymMap[systemSelect.value]);
    });

    function selectAllModulesForRole(role, systemName) {
        autoSelectedItems.clear();
        autoSelectedModules.clear();
        allAccessTypesList.forEach(type => {
            if (type.role === role && type.system === systemName) {
                autoSelectedItems.add(type.id.toString());
                autoSelectedModules.add(type.module);
            }
        });
        renderModules(true);
        updateSummary();
    }

    function updateSummary() {
        const selected = document.querySelectorAll(".access-checkbox:checked");
        if (selected.length === 0) {
            summaryContainer.innerHTML = '<div class="text-muted small" style="grid-column:1/-1;">No access selected</div>';
            return;
        }

        const grouped = {};
        selected.forEach(cb => {
            const module = cb.dataset.module;
            const action = cb.dataset.name;
            const itemId = cb.value;
            if (!grouped[module]) grouped[module] = { default: [], added: [] };
            if (autoSelectedItems.has(itemId)) grouped[module].default.push(action);
            else grouped[module].added.push(action);
        });

        summaryContainer.innerHTML = "";
        Object.entries(grouped).forEach(([moduleName, items]) => {
            const isAutoModule   = autoSelectedModules.has(moduleName);
            const moduleTitleColor = isAutoModule ? "#333" : "#0d6efd";

            const card = document.createElement("div");
            card.className = "border rounded p-3";
            card.style.cssText = "background-color:#f5f5f5; border-color:#ddd;";

            const title = document.createElement("strong");
            title.style.cssText = "font-size:0.95rem; color:" + moduleTitleColor + ";";
            title.textContent = moduleName;

            const actionsList = document.createElement("div");
            actionsList.style.cssText = "margin-top:8px; font-size:0.85rem;";

            items.default.forEach(action => {
                const item = document.createElement("div");
                item.style.cssText = "color:#666; margin-bottom:4px;";
                item.textContent = "• " + action;
                actionsList.appendChild(item);
            });
            items.added.forEach(action => {
                const item = document.createElement("div");
                item.style.cssText = "color:#0d6efd; margin-bottom:4px;";
                item.textContent = "• " + action;
                actionsList.appendChild(item);
            });

            card.appendChild(title);
            card.appendChild(actionsList);
            summaryContainer.appendChild(card);
        });
    }

    // ── Department & Remove From logic ──
    const requestForSelect = document.getElementById('requestForSelect');
    const departmentSelect = document.getElementById('departmentSelect');
    const storeContainer   = document.getElementById('storeContainer');

    // When "Request For" changes: auto-fill department + filter Remove From by company
    requestForSelect.addEventListener('change', async function() {
        const employeeId = this.options[this.selectedIndex]?.getAttribute('data-employee-id');
        if (!employeeId) {
            departmentSelect.disabled = false;
            if (departmentChoices) departmentChoices.enable();
            storeContainer.style.display = 'none';
            rebuildRemoveFrom(allUsersData);
            return;
        }

        try {
            const response = await fetch('/zen/reqHub/getempdept?emp_no=' + encodeURIComponent(employeeId));
            if (!response.ok) return;
            const data = await response.json();

            // Auto-fill department
            if (data.department) {
                for (let option of departmentSelect.options) {
                    if (option.textContent.trim() === data.department || option.value === data.department) {
                        departmentSelect.value = option.value;
                        if (departmentChoices) departmentChoices.setChoiceByValue(option.value);
                        departmentSelect.disabled = true;
                        if (departmentChoices) departmentChoices.disable();
                        break;
                    }
                }

                // Fetch company-filtered users for Remove From
                const deptCode = departmentSelect.options[departmentSelect.selectedIndex]?.getAttribute('data-code') || data.dept_code || '';
                if (deptCode) {
                    try {
                        const companyRes = await fetch('/zen/reqHub/company_fetch?dept_code=' + encodeURIComponent(deptCode));
                        if (companyRes.ok) {
                            const companyData = await companyRes.json();
                            if (companyData.success && companyData.users) {
                                rebuildRemoveFrom(companyData.users);
                            }
                        }
                    } catch (err) {
                        console.error('Company fetch error:', err);
                        rebuildRemoveFrom(allUsersData);
                    }
                } else {
                    rebuildRemoveFrom(allUsersData);
                }
            } else {
                rebuildRemoveFrom(allUsersData);
            }

            storeContainer.style.display = data.requires_store ? 'block' : 'none';
        } catch (error) {
            console.error('Error:', error);
        }
    });
});
</script>

<?php include ($reqhub_root . "/includes/footer.php"); ?>