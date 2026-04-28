<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

// Only Requestors can revise their own requests
if (!userHasRoleIn('Requestor', 'Reviewer')) {
    http_response_code(403);
    die('Access denied: Only requestors and reviewers can revise requests');
}

$pdo = ReqHubDatabase::getConnection('reqhub');
$currentUser = getCurrentUser();
$emp_no = $currentUser['emp_no'];

error_log("request_revise.php: emp_no = $emp_no");

// Get request_id from URL
$request_id = $_GET['request_id'] ?? null;

if (!$request_id) {
    die('Request ID is required');
}

// Get the users.id for the current employee
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
    $stmt->execute([$emp_no]);
    $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userRecord) {
        error_log("request_revise.php: User not found for emp_no=$emp_no");
        die('User not found in database');
    }
    
    $actual_user_id = $userRecord['id'];
    error_log("request_revise.php: Mapped emp_no=$emp_no to users.id=$actual_user_id");
} catch (PDOException $e) {
    error_log("Error looking up user: " . $e->getMessage());
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// Fetch the request to revise
try {
    error_log("request_revise.php: Looking for request_id=$request_id, user_id=$actual_user_id, status=needs_revision");
    
    $stmt = $pdo->prepare("
        SELECT r.*, s.name AS system_name
        FROM requests r
        LEFT JOIN systems s ON r.system_id = s.id
        WHERE r.id = ? AND r.user_id = ? AND r.status = 'needs_revision'
    ");
    $stmt->execute([$request_id, $actual_user_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("request_revise.php: Query returned: " . ($request ? 'FOUND' : 'NOT FOUND'));
    
    if (!$request) {
        $stmt = $pdo->prepare("SELECT id, user_id, status FROM requests WHERE id = ?");
        $stmt->execute([$request_id]);
        $debugRequest = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("request_revise.php: Debug - actual request: " . json_encode($debugRequest));
        
        die('Request not found or you do not have permission to revise it');
    }
} catch (PDOException $e) {
    error_log("Error fetching request: " . $e->getMessage());
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// Fetch dropdown data
try {
    $systems = $pdo->query("
        SELECT s.id, s.name,
               COALESCE(NULLIF(ts.sys_desc, ''), s.name) AS full_name
        FROM systems s
        LEFT JOIN tngc_hrd2.tbl_systems ts ON LOWER(ts.system_id) = LOWER(s.name)
        ORDER BY s.name
    ")->fetchAll();
    
    $departments = $pdo->query("
        SELECT DISTINCT 
            d.id,
            d.name,
            d.code
        FROM tngc_hrd2.tbl201_jobrec jr
        INNER JOIN departments d ON d.code = jr.jrec_department
        WHERE jr.jrec_status = 'primary'
        AND jr.jrec_department IS NOT NULL 
        AND jr.jrec_department != ''
        ORDER BY d.name ASC
    ")->fetchAll();
    
    $users = $pdo->query("
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
        WHERE hu.U_remarks = 'Active'
        GROUP BY hu.U_ID, hu.Emp_No, hu.U_Name, bi.bi_empfname, bi.bi_emplname
        ORDER BY COALESCE(
            CONCAT(NULLIF(bi.bi_empfname, ''), ' ', NULLIF(bi.bi_emplname, '')),
            hu.U_Name,
            hu.Emp_No
        ) ASC
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

    error_log("Loaded " . count($systems) . " systems, " . count($departments) . " departments, " . count($users) . " users, " . count($accessTypes) . " access types");
} catch (PDOException $e) {
    error_log("Error fetching dropdown data: " . $e->getMessage());
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// Fetch current access types for this request
try {
    $stmt = $pdo->prepare("
        SELECT rat.access_type_id, at.role, at.system 
        FROM request_access_types rat
        JOIN access_types at ON rat.access_type_id = at.id
        WHERE rat.request_id = ?
    ");
    $stmt->execute([$request_id]);
    $currentAccessTypesWithRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $currentAccessTypes = array_column($currentAccessTypesWithRoles, 'access_type_id');
    
    $originalRole = $request['chosen_role'] ?? null;
    $originalSystem = $request['system_name'] ?? null;
    
    error_log("Stored chosen_role: $originalRole");
    
    $roleAccessTypeIds = [];
    if (!empty($originalRole) && !empty($originalSystem)) {
        $stmt = $pdo->prepare("
            SELECT id
            FROM access_types
            WHERE role = ? AND system = ?
        ");
        $stmt->execute([$originalRole, $originalSystem]);
        $roleAccessTypeIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        error_log("Role access type IDs: " . json_encode($roleAccessTypeIds));
    }
    
    $manuallyAddedAccessTypeIds = array_diff($currentAccessTypes, $roleAccessTypeIds);
    error_log("Manually added access type IDs: " . json_encode($manuallyAddedAccessTypeIds));
    
} catch (PDOException $e) {
    error_log("Error fetching current access types: " . $e->getMessage());
    $currentAccessTypes = [];
    $originalRole = null;
    $roleAccessTypeIds = [];
    $manuallyAddedAccessTypeIds = [];
}

// Fetch approver's revision comment
$revisionComment = '';
try {
    $stmt = $pdo->prepare("
        SELECT message FROM request_chats 
        WHERE request_id = ? AND message LIKE '[REVISION REQUESTED%'
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$request_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $revisionComment = trim(preg_replace('/^\[REVISION REQUESTED\]:\s*/s', '', $row['message']));
    }
} catch (PDOException $e) {
    error_log("Error fetching revision comment: " . $e->getMessage());
}
?>

<?php include ($reqhub_root . "/includes/header.php"); ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<style>
    .choices__list--dropdown { z-index: 1000 !important; }
    .choices[data-type*="select-one"] .choices__button { z-index: 999 !important; }

    /* Keep disabled department readable */
    .choices.is-disabled .choices__item--selectable,
    .choices.is-disabled .choices__inner {
        color: #212529 !important;
        opacity: 1 !important;
        background-color: #e9ecef !important;
        cursor: not-allowed;
    }
    .choices.is-disabled {
        opacity: 1 !important;
    }
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
    <h2>Edit & Resubmit Request</h2>
    <div class="alert alert-warning">
        <strong>This request needs revision.</strong> Please review the comments in the request and make the necessary changes below.
    </div>

    <form action="/zen/reqHub/revise_submit" method="POST" id="requestForm">
        <input type="hidden" name="request_id" value="<?= $request_id ?>">
        <input type="hidden" name="chosen_role" id="chosenRoleInput" value="<?= htmlspecialchars($originalRole ?? '') ?>">

        <!-- Top section: form fields on left, approver comments on right -->
        <div class="row g-4 mb-3">

            <!-- LEFT: form fields -->
            <div class="col-md-9">

                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">System</label>
                        <select name="system_id" id="systemSelect" class="form-select" required>
                            <option value="">Select System</option>
                            <?php foreach ($systems as $system): ?>
                                <option value="<?= $system['id'] ?>" data-name="<?= htmlspecialchars($system['name']) ?>" <?= $request['system_id'] == $system['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($system['full_name'] ?? $system['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label">Request For</label>
                        <select name="request_for" id="requestForSelect" class="form-select" required>
                            <option value="">Select User</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" data-employee-id="<?= htmlspecialchars($u['employee_id']) ?>"
                                        <?= $request['request_for'] == $u['id'] ? 'selected' : '' ?>>
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
                                <option value="<?= $dept['id'] ?>" <?= $request['department_id'] == $dept['id'] ? 'selected' : '' ?>>
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
                            <option value="<?= htmlspecialchars($u['name']) ?>" <?= $request['remove_from'] == $u['name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3" id="storeContainer" style="display: none;">
                    <label class="form-label">Store</label>
                    <input type="text" name="store" id="storeInput" class="form-control" placeholder="Enter store name"
                           value="<?= htmlspecialchars($request['store'] ?? '') ?>">
                </div>

            </div>

            <!-- RIGHT: approver's comments -->
            <div class="col-md-3">
                <label class="form-label fw-bold" style="color: #842029;">Reviewer/Approver's Comments</label>
                <div class="border rounded p-3" style="
                    background-color: #fff8f8;
                    border-color: #f5c6cb !important;
                    min-height: 180px;
                    white-space: pre-wrap;
                    font-size: 0.875rem;
                    color: #842029;
                    line-height: 1.5;
                "><?php if ($revisionComment): ?><?= htmlspecialchars($revisionComment) ?><?php else: ?><em style="color: #aaa;">No comments from approver.</em><?php endif; ?></div>
            </div>

        </div><!-- end top row -->

        <div class="mb-4">
            <label class="form-label fw-bold">Access Types</label>

            <div class="mb-3">
                <label class="form-label">Select Role (Auto-selects all its modules)</label>
                <select id="roleSelect" class="form-select">
                    <option value="">-- Choose a role --</option>
                </select>
            </div>

            <div class="row g-3">
                <div style="flex: 0 0 62.333333%; max-width: 62.333333%;">
                    <h6>Modules & Actions</h6>
                    <div class="mb-2">
                        <input type="text" id="searchModules" class="form-control form-control-sm" placeholder="Search modules..." style="font-size: 0.9rem;">
                    </div>
                    <div id="modulesContainer" class="border rounded p-2" style="max-height: 600px; overflow-y: auto; background-color: #fafafa; min-height: 200px;">
                        <div id="modulesDisplay" style="font-size: 0.85rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px;"></div>
                    </div>
                </div>

                <div style="flex: 0 0 37.666667%; max-width: 37.666667%;">
                    <h6>Selected Access</h6>
                    <div id="summaryContainer" class="border rounded p-3" style="max-height: 639px; overflow-y: auto; background-color: #fafafa; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; padding: 12px; align-content: start;">
                        <div class="text-muted small" style="grid-column: 1 / -1;">No access selected</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Description / Purpose</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($request['description'] ?? '') ?></textarea>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Resubmit Request</button>
            <a href="/zen/reqHub/dashboard?status=pending&pending_tab=needs_revision" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // ADD after: document.addEventListener("DOMContentLoaded", function() {
    const allUsersData = <?= json_encode(array_map(function($u) { return [
        'id'          => $u['id'],
        'name'        => $u['name'],
        'employee_id' => $u['employee_id'],
    ]; }, $allUsers)) ?>;

    const requestForm = document.getElementById('requestForm');
    requestForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const selectedAccessTypes = Array.from(document.querySelectorAll('.access-checkbox:checked'));
        if (selectedAccessTypes.length === 0) {
            alert('Please select one access type');
            return;
        }

        const formData = new FormData(this);

        const disabledSelects = this.querySelectorAll('select:disabled');
        disabledSelects.forEach(select => {
            select.disabled = false;
            formData.set(select.name, select.value);
        });

        const departmentSelect = document.getElementById('departmentSelect');
        if (departmentSelect.disabled && departmentSelect.value) {
            formData.set('department_id', departmentSelect.value);
        }

        fetch('/zen/reqHub/revise_submit', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json().then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Unknown error');
                    }
                    return data;
                });
            }
            return { success: true, redirect: '/zen/reqHub/dashboard?status=pending' };
        })
        .then(data => {
            window.location.href = data.redirect || '/zen/reqHub/dashboard?status=pending';
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    });

    new Choices('#systemSelect', { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });
    const requestForChoices = new Choices('#requestForSelect', { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });
    const departmentChoices = new Choices('#departmentSelect', { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });
    const removeFromChoices = new Choices('#removeFromSelect', { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });
    const savedRemoveFrom = <?= json_encode($request['remove_from'] ?? '') ?>;
    if (savedRemoveFrom) {
        removeFromChoices.setChoiceByValue(savedRemoveFrom.toString());
    }

    const systemSelect = document.getElementById("systemSelect");
    const roleSelect = document.getElementById("roleSelect");
    const modulesDisplay = document.getElementById("modulesDisplay");
    const summaryContainer = document.getElementById("summaryContainer");
    const searchModules = document.getElementById("searchModules");

    const allAccessTypesList = <?= json_encode($accessTypes) ?>;
    const currentAccessTypeIds = <?= json_encode($currentAccessTypes ?? []) ?>;
    const roleAccessTypeIds = <?= json_encode(array_values(array_map('intval', $roleAccessTypeIds ?? []))) ?>;
    const manuallyAddedAccessTypeIds = <?= json_encode(array_values(array_map('intval', $manuallyAddedAccessTypeIds ?? []))) ?>;
    const originalRole = <?= json_encode($originalRole ?? null) ?>;
    const originalSystem = <?= json_encode($request['system_name'] ?? null) ?>;

    const currentAccessTypeIdsSet = new Set(currentAccessTypeIds.map(id => parseInt(id)));

    let systemNameMap = {};
    document.querySelectorAll("#systemSelect option").forEach(opt => {
        if (opt.value) systemNameMap[opt.value] = opt.dataset.name || opt.textContent.trim();
    });

    let currentSearch = "";
    let autoSelectedItems = new Set();
    let autoSelectedModules = new Set();

    function clearAllCheckboxes() {
        document.querySelectorAll('.access-checkbox').forEach(cb => {
            cb.checked = false;
        });
        document.querySelectorAll('.module-checkbox').forEach(cb => {
            cb.checked = false;
            cb.indeterminate = false;
        });
    }

    // ADD before renderModules():
    function rebuildRemoveFrom(filteredUsers) {
        const sel = document.getElementById('removeFromSelect');
        const currentVal = sel.value;
        while (sel.options.length > 1) sel.remove(1);
        filteredUsers.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.name;
            opt.textContent = u.name;
            sel.appendChild(opt);
        });
        sel.value = currentVal || '';
    }

    function renderModules(prioritizeAutoSelected = false) {
        modulesDisplay.innerHTML = "";

        const selectedSystemId = systemSelect.value;
        const selectedSystemName = selectedSystemId ? systemNameMap[selectedSystemId] : null;
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
            if (!grouped[type.module][type.actions]) {
                grouped[type.module][type.actions] = [];
            }
            grouped[type.module][type.actions].push(type);
        });

        let modulesToDisplay = Object.entries(grouped);

        if (prioritizeAutoSelected) {
            const prioritized = [];
            const others = [];
            modulesToDisplay.forEach(([moduleName, actionsMap]) => {
                if (autoSelectedModules.has(moduleName)) {
                    prioritized.push([moduleName, actionsMap]);
                } else {
                    others.push([moduleName, actionsMap]);
                }
            });
            modulesToDisplay = [...prioritized, ...others];
        }

        modulesToDisplay.forEach(([moduleName, actionsMap]) => {
            const actions = Object.values(actionsMap);

            const moduleCard = document.createElement("div");
            moduleCard.className = "module-card";
            moduleCard.style.cssText = "border:1px solid #555; border-radius:4px; padding:10px; background-color:#fffcfc; display:flex; flex-direction:column;";

            const headerDiv = document.createElement("div");
            headerDiv.style.display = "flex";
            headerDiv.style.justifyContent = "space-between";
            headerDiv.style.alignItems = "flex-start";
            headerDiv.style.marginBottom = "10px";
            headerDiv.style.paddingBottom = "8px";
            headerDiv.style.borderBottom = "1px solid #444";
            headerDiv.style.gap = "8px";

            const moduleCheckbox = document.createElement("input");
            moduleCheckbox.type = "checkbox";
            moduleCheckbox.className = "module-checkbox";
            moduleCheckbox.style.width = "16px";
            moduleCheckbox.style.height = "16px";
            moduleCheckbox.style.marginTop = "2px";
            moduleCheckbox.style.cursor = "pointer";
            moduleCheckbox.style.flexShrink = "0";

            const moduleTitle = document.createElement("label");
            moduleTitle.style.fontWeight = "bold";
            moduleTitle.style.fontSize = "0.9rem";
            moduleTitle.style.color = "#000000";
            moduleTitle.style.wordBreak = "break-word";
            moduleTitle.style.flex = "1";
            moduleTitle.style.cursor = "pointer";
            moduleTitle.style.marginBottom = "0";
            moduleTitle.textContent = moduleName;
            moduleTitle.addEventListener("click", function() { moduleCheckbox.click(); });

            const badge = document.createElement("span");
            badge.style.backgroundColor = "#555";
            badge.style.color = "#fff";
            badge.style.padding = "3px 6px";
            badge.style.borderRadius = "3px";
            badge.style.fontSize = "0.7rem";
            badge.style.fontWeight = "bold";
            badge.style.whiteSpace = "nowrap";
            badge.style.flexShrink = "0";
            badge.textContent = actions.length + " action" + (actions.length !== 1 ? "s" : "");

            headerDiv.appendChild(moduleCheckbox);
            headerDiv.appendChild(moduleTitle);
            headerDiv.appendChild(badge);
            moduleCard.appendChild(headerDiv);

            const actionsGrid = document.createElement("div");
            actionsGrid.style.cssText = "display:grid; grid-template-columns:repeat(2, 1fr); gap:6px; align-content:flex-start; flex:1;";

            const actionCheckboxes = [];

            actions.forEach(types => {
                const matchedType =
                    types.find(t => autoSelectedItems.has(t.id.toString())) ||
                    types.find(t => currentAccessTypeIdsSet.has(t.id)) ||
                    types[0];

                const actionDiv = document.createElement("div");
                actionDiv.style.cssText = "display:flex; align-items:center; gap:4px; background:#f0f0f0; border-radius:20px; padding:3px 10px; cursor:pointer;";

                const checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.className = "access-checkbox";
                checkbox.name = "access_types[]";
                checkbox.value = matchedType.id;
                checkbox.id = `access_${matchedType.id}`;
                checkbox.dataset.system = matchedType.system;
                checkbox.dataset.role = matchedType.role;
                checkbox.dataset.module = matchedType.module;
                checkbox.dataset.name = matchedType.actions;
                checkbox.style.width = "16px";
                checkbox.style.height = "16px";
                checkbox.style.marginTop = "1px";
                checkbox.style.flexShrink = "0";
                checkbox.style.cursor = "pointer";

                if (autoSelectedItems.has(matchedType.id.toString()) || currentAccessTypeIdsSet.has(matchedType.id)) {
                    checkbox.checked = true;
                }

                actionCheckboxes.push(checkbox);

                checkbox.addEventListener("change", function() {
                    if (!this.checked) {
                        autoSelectedItems.delete(this.value);
                    } else {
                        const role = roleSelect.value;
                        const systemName = systemNameMap[systemSelect.value];
                        const type = allAccessTypesList.find(t => t.id.toString() === this.value);
                        if (type && type.role === role && type.system === systemName) {
                            autoSelectedItems.add(this.value);
                        }
                    }

                    const allChecked = actionCheckboxes.every(cb => cb.checked);
                    const anyChecked = actionCheckboxes.some(cb => cb.checked);
                    moduleCheckbox.checked = allChecked;
                    moduleCheckbox.indeterminate = anyChecked && !allChecked;

                    updateSummary();
                });

                const label = document.createElement("label");
                label.htmlFor = `access_${matchedType.id}`;
                label.style.cssText = "margin-bottom:0; cursor:pointer; font-size:0.78rem; user-select:none; white-space:normal; color:#333; line-height:1.3; word-break:break-word;";
                label.textContent = matchedType.actions;

                actionDiv.addEventListener("click", function(e) {
                    if (e.target !== checkbox) checkbox.click();
                });

                actionDiv.appendChild(checkbox);
                actionDiv.appendChild(label);
                actionsGrid.appendChild(actionDiv);
            });

            moduleCheckbox.addEventListener("change", function() {
                const role = roleSelect.value;
                const systemName = systemNameMap[systemSelect.value];
                actionCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                    if (!this.checked) {
                        autoSelectedItems.delete(cb.value);
                    } else {
                        const type = allAccessTypesList.find(t => t.id.toString() === cb.value);
                        if (type && type.role === role && type.system === systemName) {
                            autoSelectedItems.add(cb.value);
                        }
                    }
                });
                updateSummary();
            });

            const allChecked = actionCheckboxes.every(cb => cb.checked);
            const anyChecked = actionCheckboxes.some(cb => cb.checked);
            moduleCheckbox.checked = allChecked;
            moduleCheckbox.indeterminate = anyChecked && !allChecked;

            moduleCard.appendChild(actionsGrid);
            modulesDisplay.appendChild(moduleCard);
        });
    }

    searchModules.addEventListener("input", function() {
        currentSearch = this.value;
        renderModules(true);
    });

    const requestForSelect = document.getElementById('requestForSelect');
    const departmentSelect = document.getElementById('departmentSelect');
    const storeContainer = document.getElementById('storeContainer');
    const storeInput = document.getElementById('storeInput');

        function initFromDatabase() {
        autoSelectedItems.clear();
        autoSelectedModules.clear();

        allAccessTypesList.forEach(type => {
            if (currentAccessTypeIds.includes(type.id)) {
                autoSelectedItems.add(type.id.toString());
                autoSelectedModules.add(type.module);
            }
        });

        if (autoSelectedItems.size === 0 && originalRole && originalSystem) {
            allAccessTypesList.forEach(type => {
                if (type.role === originalRole && type.system === originalSystem) {
                    autoSelectedItems.add(type.id.toString());
                    autoSelectedModules.add(type.module);
                }
            });
        }
    }

    initFromDatabase();

    if (systemSelect.value) {
        fetch(`/zen/reqHub/system_roles_action?system_id=${systemSelect.value}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.roles.length > 0) {
                    updateRoleUI(data.roles, data.system_name);
                    if (originalRole) {
                        roleSelect.value = originalRole;
                        document.getElementById('chosenRoleInput').value = originalRole;
                    }
                }
                renderModules(true);
                updateSummary();
            })
            .catch(() => {
                renderModules(true);
                updateSummary();
            });
    } else {
        renderModules(false);
        updateSummary();
    }

    if (requestForSelect.value) {
        const employeeId = requestForSelect.options[requestForSelect.selectedIndex]?.getAttribute('data-employee-id');
        if (employeeId) {
            fetch('/zen/reqHub/getempdept?emp_no=' + encodeURIComponent(employeeId))
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (!data) return;
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
                    }
                    storeContainer.style.display = data.requires_store ? 'block' : 'none';
                })
                .catch(() => {});
        }
    }

    systemSelect.addEventListener("change", function() {
        const systemId = this.value;

        currentAccessTypeIdsSet.clear();
        autoSelectedItems.clear();
        autoSelectedModules.clear();

        clearAllCheckboxes();
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
            .then(response => response.json())
            .then(data => {
                if (data.success && data.roles.length > 0) {
                    updateRoleUI(data.roles, data.system_name);
                } else {
                    roleSelect.innerHTML = '<option value="">No roles found</option>';
                }
                renderModules(false);
                updateSummary();
            })
            .catch(error => {
                console.error("Error:", error);
                roleSelect.innerHTML = '<option value="">Error loading roles</option>';
                renderModules(false);
                updateSummary();
            });
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

        const systemName = systemNameMap[systemSelect.value];
        selectAllModulesForRole(role, systemName);
    });

    function selectAllModulesForRole(role, systemName) {
        autoSelectedItems.clear();
        autoSelectedModules.clear();

        currentAccessTypeIdsSet.clear();
        clearAllCheckboxes();

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
            summaryContainer.innerHTML = '<div class="text-muted small" style="grid-column: 1 / -1;">No access selected</div>';
            return;
        }

        const grouped = {};

        selected.forEach(cb => {
            const module = cb.dataset.module;
            const action = cb.dataset.name;
            const itemId = cb.value;

            if (!grouped[module]) {
                grouped[module] = { default: [], added: [] };
            }

            const isStillAuto = autoSelectedItems.has(itemId);

            if (isStillAuto) {
                grouped[module].default.push(action);
            } else {
                grouped[module].added.push(action);
            }
        });

        summaryContainer.innerHTML = "";
        Object.entries(grouped).forEach(([moduleName, items]) => {
            const isAutoSelectedModule = autoSelectedModules.has(moduleName);
            const moduleTitleColor = isAutoSelectedModule ? "#333" : "#0d6efd";

            const card = document.createElement("div");
            card.className = "border rounded p-3";
            card.style.backgroundColor = "#f5f5f5";
            card.style.borderColor = "#ddd";

            const title = document.createElement("strong");
            title.style.fontSize = "0.95rem";
            title.style.color = moduleTitleColor;
            title.textContent = moduleName;

            const actionsList = document.createElement("div");
            actionsList.style.marginTop = "8px";
            actionsList.style.fontSize = "0.85rem";

            items.default.forEach(action => {
                const item = document.createElement("div");
                item.style.color = "#666";
                item.style.fontWeight = "normal";
                item.style.marginBottom = "4px";
                item.textContent = "• " + action;
                actionsList.appendChild(item);
            });

            items.added.forEach(action => {
                const item = document.createElement("div");
                item.style.color = "#0d6efd";
                item.style.fontWeight = "normal";
                item.style.marginBottom = "4px";
                item.textContent = "• " + action;
                actionsList.appendChild(item);
            });

            card.appendChild(title);
            card.appendChild(actionsList);
            summaryContainer.appendChild(card);
        });
    }

    requestForSelect.addEventListener('change', async function() {
        const employeeId = this.options[this.selectedIndex]?.getAttribute('data-employee-id');
        if (!employeeId) {
            departmentSelect.value = '';
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