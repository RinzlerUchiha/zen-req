<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

// Only Requestors can revise their own requests
if (!userHasRoleIn('Requestor')) {
    http_response_code(403);
    die('Access denied: Only requestors can revise requests');
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
        // Debug: check what we actually got
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

// Fetch dropdown data (same as request_create.php)
try {
    // Systems
    $systems = $pdo->query("SELECT id, name FROM systems ORDER BY name")->fetchAll();
    
    // Departments
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
    
    // Users
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
        WHERE hu.U_stat = 1
        GROUP BY hu.U_ID, hu.Emp_No, hu.U_Name, bi.bi_empfname, bi.bi_emplname
        ORDER BY COALESCE(
            CONCAT(NULLIF(bi.bi_empfname, ''), ' ', NULLIF(bi.bi_emplname, '')),
            hu.U_Name,
            hu.Emp_No
        ) ASC
    ")->fetchAll();
    
    error_log("Loaded " . count($systems) . " systems, " . count($departments) . " departments, " . count($users) . " users");
} catch (PDOException $e) {
    error_log("Error fetching dropdown data: " . $e->getMessage());
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// Fetch access types
try {
    $accessTypes = $pdo->query("
        SELECT id, system, role, module, actions
        FROM access_types
        ORDER BY role, module, actions
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Fetched " . count($accessTypes) . " access types");
} catch (PDOException $e) {
    error_log("Error fetching access types: " . $e->getMessage());
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
    
    // Use the stored chosen_role from the request
    $originalRole = $request['chosen_role'] ?? null;
    $originalSystem = $request['system_name'] ?? null;
    
    error_log("Stored chosen_role: $originalRole");
    
    // Get all access_type IDs that belong to the chosen role (auto-selected items)
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
    
    // Determine which saved access_types are manually added
    // Manually added = in currentAccessTypes but NOT in roleAccessTypeIds
    $manuallyAddedAccessTypeIds = array_diff($currentAccessTypes, $roleAccessTypeIds);
    error_log("Manually added access type IDs: " . json_encode($manuallyAddedAccessTypeIds));
    
} catch (PDOException $e) {
    error_log("Error fetching current access types: " . $e->getMessage());
    $currentAccessTypes = [];
    $originalRole = null;
    $roleAccessTypeIds = [];
    $manuallyAddedAccessTypeIds = [];
}
?>

<?php include ($reqhub_root . "/includes/header.php"); ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<style>
    .choices__list--dropdown { z-index: 1000 !important; }
    .choices[data-type*="select-one"] .choices__button { z-index: 999 !important; }
</style>

<div class="container-fluid mt-4 px-3 px-lg-5">
    <h2>Edit & Resubmit Request</h2>
    <div class="alert alert-warning">
        <strong>This request needs revision.</strong> Please review the comments in the request and make the necessary changes below.
    </div>

    <form action="/zen/reqHub/revise_submit" method="POST" id="requestForm">
        <input type="hidden" name="request_id" value="<?= $request_id ?>">

        <div class="mb-3">
            <label class="form-label">System</label>
            <select name="system_id" id="systemSelect" class="form-select" required>
                <option value="">Select System</option>
                <?php foreach ($systems as $system): ?>
                    <option value="<?= $system['id'] ?>" <?= $request['system_id'] == $system['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($system['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
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

        <div class="mb-3">
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

        <div class="mb-3" id="storeContainer" style="display: none;">
            <label class="form-label">Store</label>
            <input type="text" name="store" id="storeInput" class="form-control" placeholder="Enter store name"
                   value="<?= htmlspecialchars($request['store'] ?? '') ?>">
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">Access Types</label>

            <!-- Roles Dropdown -->
            <div class="mb-3">
                <label class="form-label">Select Role (Auto-selects all its modules)</label>
                <select id="roleSelect" class="form-select">
                    <option value="">-- Choose a role --</option>
                </select>
            </div>

            <div class="row g-3">
                <!-- COLUMN 2: Modules & Actions (62% width) -->
                <div style="flex: 0 0 62.333333%; max-width: 62.333333%;">
                    <h6>Modules & Actions</h6>
                    
                    <!-- Search Bar -->
                    <div class="mb-2">
                        <input type="text" id="searchModules" class="form-control form-control-sm" placeholder="Search modules..." style="font-size: 0.9rem;">
                    </div>
                    
                    <!-- Modules Container -->
                    <div id="modulesContainer" class="border rounded p-2" style="max-height: 600px; overflow-y: auto; background-color: #fafafa; min-height: 200px;">
                        <div id="modulesDisplay" style="font-size: 0.85rem; display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;"></div>
                    </div>
                </div>

                <!-- COLUMN 3: Summary (38% width, 2-column grid) -->
                <div style="flex: 0 0 37.666667%; max-width: 37.666667%;">
                    <h6>Selected Access</h6>
                    <div id="summaryContainer" class="border rounded p-3" style="max-height: 600px; overflow-y: auto; background-color: #fafafa; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; padding: 12px;">
                        <div class="text-muted small" style="grid-column: 1 / -1;">No access selected</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Remove From (leave blank if not applicable)</label>
            <input type="text" name="remove_from" class="form-control" value="<?= htmlspecialchars($request['remove_from'] ?? '') ?>">
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

    const requestForm = document.getElementById('requestForm');
    requestForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate that at least one access type is selected
        const selectedAccessTypes = Array.from(document.querySelectorAll('.access-checkbox:checked'));
        if (selectedAccessTypes.length === 0) {
            alert('Please select at least one access type');
            return;
        }
        
        // Re-enable disabled selects so they get submitted
        const disabledSelects = this.querySelectorAll('select:disabled');
        disabledSelects.forEach(select => select.disabled = false);
        
        // Collect form data
        const formData = new FormData(this);
        
        // If department is disabled, make sure it's in the data
        const departmentSelect = document.getElementById('departmentSelect');
        if (departmentSelect.disabled && departmentSelect.value) {
            formData.set('department_id', departmentSelect.value);
        }
        
        console.log('Submitting form data...');
        
        // Submit via AJAX
        fetch('/zen/reqHub/revise_submit', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response:', data);
            if (data.success) {
                alert(data.message || 'Request revisions submitted successfully!');
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = '/zen/reqHub/dashboard?status=pending';
                }
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error submitting revisions: ' + error.message);
        });
    });

    new Choices('#systemSelect', { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });
    const requestForChoices = new Choices('#requestForSelect', { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });
    const departmentChoices = new Choices('#departmentSelect', { searchEnabled: true, itemSelectText: 'Press to select', removeItemButton: true });

    const systemSelect = document.getElementById("systemSelect");
    const roleSelect = document.getElementById("roleSelect");
    const modulesDisplay = document.getElementById("modulesDisplay");
    const summaryContainer = document.getElementById("summaryContainer");
    const searchModules = document.getElementById("searchModules");

    const allAccessTypesList = <?= json_encode($accessTypes) ?>;
    const currentAccessTypeIds = <?= json_encode($currentAccessTypes ?? []) ?>;
    const manuallyAddedAccessTypeIds = <?= json_encode(array_values(array_map('intval', $manuallyAddedAccessTypeIds ?? []))) ?>;
    const originalRole = <?= json_encode($originalRole ?? null) ?>;
    const originalSystemRaw = <?= json_encode($request['system_name'] ?? null) ?>;
    const originalSystem = originalSystemRaw ? originalSystemRaw.trim() : null;
    
    // Convert to Sets for faster lookup
    const currentAccessTypeIdsSet = new Set(currentAccessTypeIds.map(id => parseInt(id)));
    const manuallyAddedAccessTypeIdsSet = new Set((manuallyAddedAccessTypeIds || []).map(id => parseInt(id)));
    
    console.log('=== Variables loaded from PHP ===');
    console.log('allAccessTypesList:', allAccessTypesList);
    console.log('currentAccessTypeIds:', currentAccessTypeIds);
    console.log('manuallyAddedAccessTypeIds:', manuallyAddedAccessTypeIds);
    console.log('originalRole:', originalRole);
    console.log('originalSystem:', originalSystem);

    // Build systemNameMap FIRST before any event listeners
    let systemNameMap = {};
    document.querySelectorAll("#systemSelect option").forEach(opt => {
        if (opt.value) systemNameMap[opt.value] = opt.textContent.trim();
    });
    console.log('systemNameMap:', systemNameMap);
    
    // Build a map of system ID → system name from access_types data
    let systemIdToNameMap = {};
    allAccessTypesList.forEach(at => {
        for (let [sysId, sysName] of Object.entries(systemNameMap)) {
            const cleanAtSystem = (at.system || '').trim();
            const cleanSysName = (sysName || '').trim();
            
            if (cleanSysName === cleanAtSystem) {
                systemIdToNameMap[sysId] = cleanAtSystem;
                break;
            }
        }
    });
    console.log('systemIdToNameMap:', systemIdToNameMap);

    let currentSearch = "";
    let autoSelectedItems = new Set(); // Track items auto-selected by role

    // Render modules based on search
    function renderModules() {
        console.log('=== renderModules() called ===');
        
        modulesDisplay.innerHTML = "";
        let toDisplay = allAccessTypesList;

        // Apply search filter
        if (currentSearch) {
            toDisplay = toDisplay.filter(type =>
                type.module.toLowerCase().includes(currentSearch.toLowerCase()) ||
                type.actions.toLowerCase().includes(currentSearch.toLowerCase())
            );
        }

        // Group by module
        const grouped = {};
        toDisplay.forEach(type => {
            if (!grouped[type.module]) grouped[type.module] = {};
            if (!grouped[type.module][type.actions]) {
                grouped[type.module][type.actions] = type;
            }
        });

        // Display grouped modules in cards
        Object.entries(grouped).forEach(([moduleName, actionsMap]) => {
            const actions = Object.values(actionsMap);
            
            const moduleCard = document.createElement("div");
            moduleCard.className = "module-card";
            moduleCard.style.border = "1px solid #555";
            moduleCard.style.borderRadius = "4px";
            moduleCard.style.padding = "10px";
            moduleCard.style.backgroundColor = "#2a2a2a";
            moduleCard.style.minHeight = "250px";
            moduleCard.style.display = "flex";
            moduleCard.style.flexDirection = "column";

            // Module header
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
            moduleTitle.style.color = "#fff";
            moduleTitle.style.wordBreak = "break-word";
            moduleTitle.style.flex = "1";
            moduleTitle.style.cursor = "pointer";
            moduleTitle.style.marginBottom = "0";
            moduleTitle.textContent = moduleName;

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

            // Actions in 2-column grid
            const actionsGrid = document.createElement("div");
            actionsGrid.style.display = "grid";
            actionsGrid.style.gridTemplateColumns = "1fr 1fr";
            actionsGrid.style.gap = "8px";
            actionsGrid.style.flex = "1";

            const actionCheckboxes = [];

            actions.forEach(type => {
                const actionDiv = document.createElement("div");
                actionDiv.style.display = "flex";
                actionDiv.style.alignItems = "flex-start";
                actionDiv.style.gap = "6px";

                const checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.className = "access-checkbox";
                checkbox.name = "access_types[]";
                checkbox.value = type.id;
                checkbox.id = `access_${type.id}`;
                checkbox.dataset.system = type.system;
                checkbox.dataset.role = type.role;
                checkbox.dataset.module = type.module;
                checkbox.dataset.name = type.actions;
                checkbox.style.width = "16px";
                checkbox.style.height = "16px";
                checkbox.style.marginTop = "1px";
                checkbox.style.flexShrink = "0";
                checkbox.style.cursor = "pointer";
                
                // Pre-check if this access type is in the current request
                const shouldBeChecked = currentAccessTypeIdsSet.has(type.id);
                if (shouldBeChecked) {
                    checkbox.checked = true;
                    console.log('✓ Pre-checked:', type.id, 'action:', type.actions);
                }
                
                actionCheckboxes.push(checkbox);

                checkbox.addEventListener("change", function() {
                    // Remove from auto-selected if user manually toggles
                    if (this.checked && !autoSelectedItems.has(this.value)) {
                        // Manually checked
                    } else if (!this.checked && autoSelectedItems.has(this.value)) {
                        autoSelectedItems.delete(this.value);
                    }
                    
                    // Update module checkbox state
                    const allChecked = actionCheckboxes.every(cb => cb.checked);
                    const anyChecked = actionCheckboxes.some(cb => cb.checked);
                    moduleCheckbox.checked = allChecked;
                    moduleCheckbox.indeterminate = anyChecked && !allChecked;
                    
                    updateSummary();
                });

                const label = document.createElement("label");
                label.htmlFor = `access_${type.id}`;
                label.style.marginBottom = "0";
                label.style.cursor = "pointer";
                label.style.fontSize = "0.8rem";
                label.style.userSelect = "none";
                label.style.wordBreak = "break-word";
                label.style.lineHeight = "1.4";
                label.style.color = "#ccc";
                label.textContent = type.actions;

                actionDiv.appendChild(checkbox);
                actionDiv.appendChild(label);
                actionsGrid.appendChild(actionDiv);
            });

            // Handle module checkbox click
            moduleCheckbox.addEventListener("change", function() {
                actionCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                    if (!this.checked && autoSelectedItems.has(cb.value)) {
                        autoSelectedItems.delete(cb.value);
                    }
                });
                updateSummary();
            });

            moduleCard.appendChild(actionsGrid);
            modulesDisplay.appendChild(moduleCard);
        });
        
        // After rendering, update module checkbox states
        document.querySelectorAll('.module-checkbox').forEach(moduleCheckbox => {
            const parentCard = moduleCheckbox.closest('.module-card');
            if (parentCard) {
                const actionCheckboxes = parentCard.querySelectorAll('.access-checkbox');
                const allChecked = Array.from(actionCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(actionCheckboxes).some(cb => cb.checked);
                moduleCheckbox.checked = allChecked;
                moduleCheckbox.indeterminate = anyChecked && !allChecked;
            }
        });
        
        console.log('=== renderModules() complete ===');
    }

    // Search functionality
    searchModules.addEventListener("input", function() {
        currentSearch = this.value;
        renderModules();
    });

    // Initialize modules
    renderModules();

    systemSelect.addEventListener("change", function() {
        const systemId = this.value;
        
        // Unselect all modules and actions when system changes
        document.querySelectorAll('.access-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.module-checkbox').forEach(cb => cb.checked = false);
        updateSummary();
        
        // Reset role dropdown
        roleSelect.value = '';
        
        if (!systemId) {
            roleSelect.innerHTML = '<option value="">-- Choose a role --</option>';
            return;
        }

        const systemName = systemNameMap[systemId];
        fetch(`/zen/reqHub/system_roles_action?system_id=${systemId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.roles.length > 0) {
                    updateRoleUI(data.roles, data.system_name);
                    
                    // After roles are populated, set the original role if available
                    if (originalRole && originalSystem) {
                        console.log('Setting roleSelect.value to:', originalRole);
                        roleSelect.value = originalRole;
                        
                        // Mark all pre-saved items as auto-selected
                        document.querySelectorAll('.access-checkbox').forEach(cb => {
                            const checkboxId = parseInt(cb.value);
                            if (currentAccessTypeIdsSet.has(checkboxId)) {
                                autoSelectedItems.add(cb.value);
                            }
                        });
                        
                        // Re-check the items
                        document.querySelectorAll('.access-checkbox').forEach(cb => {
                            const checkboxId = parseInt(cb.value);
                            if (currentAccessTypeIdsSet.has(checkboxId)) {
                                cb.checked = true;
                            }
                        });
                        
                        // Update module checkboxes and summary
                        document.querySelectorAll('.module-checkbox').forEach(moduleCheckbox => {
                            const parentCard = moduleCheckbox.closest('.module-card');
                            if (parentCard) {
                                const actionCheckboxes = parentCard.querySelectorAll('.access-checkbox');
                                const allChecked = Array.from(actionCheckboxes).every(cb => cb.checked);
                                const anyChecked = Array.from(actionCheckboxes).some(cb => cb.checked);
                                moduleCheckbox.checked = allChecked;
                                moduleCheckbox.indeterminate = anyChecked && !allChecked;
                            }
                        });
                        
                        updateSummary();
                    }
                } else {
                    roleSelect.innerHTML = '<option value="">No roles found</option>';
                }
            })
            .catch(error => {
                console.error("Error:", error);
                roleSelect.innerHTML = '<option value="">Error loading roles</option>';
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

    // Handle role selection from dropdown
    roleSelect.addEventListener("change", function() {
        const role = this.value;
        console.log('=== Role dropdown changed ===');
        
        if (!role) {
            console.log('No role selected, clearing');
            document.querySelectorAll('.access-checkbox').forEach(cb => cb.checked = false);
            updateSummary();
            return;
        }
        
        const systemId = systemSelect.value;
        const systemName = systemIdToNameMap[systemId] || '';
        console.log('systemId:', systemId, 'systemName:', systemName);
        selectAllModulesForRole(role, systemName);
    });

    function selectAllModulesForRole(role, systemName) {
        console.log('=== selectAllModulesForRole called ===');
        console.log('role:', role, 'systemName:', systemName);
        
        systemName = (systemName || '').trim();
        
        // Uncheck all first
        document.querySelectorAll('.access-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.module-checkbox').forEach(cb => cb.checked = false);
        
        // Clear auto-selected tracking
        autoSelectedItems.clear();
        
        // Check only items for this role and system
        const selector = `.access-checkbox[data-role="${role}"][data-system="${systemName}"]`;
        const roleCbs = document.querySelectorAll(selector);
        
        console.log('Found checkboxes for this role:', roleCbs.length);
        
        roleCbs.forEach(cb => {
            cb.checked = true;
            autoSelectedItems.add(cb.value);
        });
        
        // Update module checkboxes
        const selectedModules = new Set();
        roleCbs.forEach(cb => selectedModules.add(cb.dataset.module));
        
        document.querySelectorAll('.module-checkbox').forEach(moduleCheckbox => {
            const parentCard = moduleCheckbox.closest('.module-card');
            if (parentCard) {
                const moduleTitle = parentCard.querySelector('label');
                const moduleName = moduleTitle.textContent.trim();
                if (selectedModules.has(moduleName)) {
                    moduleCheckbox.checked = true;
                }
            }
        });
        
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
            const itemId = parseInt(cb.value);
            const role = cb.dataset.role;
            
            if (!grouped[module]) {
                grouped[module] = { default: [], added: [] };
            }
            
            // Check if this item belongs to the currently selected role
            const currentRole = roleSelect.value;
            const isFromCurrentRole = (role === currentRole);
            
            if (isFromCurrentRole) {
                grouped[module].default.push(action);
            } else {
                grouped[module].added.push(action);
            }
        });

        summaryContainer.innerHTML = "";
        Object.entries(grouped).forEach(([moduleName, items]) => {
            // Determine if this module has any manually added items
            const hasAddedItems = items.added.length > 0;
            
            const card = document.createElement("div");
            card.className = "border rounded p-3";
            card.style.backgroundColor = "#f5f5f5";
            card.style.borderColor = "#ddd";

            const title = document.createElement("strong");
            title.style.fontSize = "0.95rem";
            title.style.color = hasAddedItems ? "#0d6efd" : "#333";
            title.textContent = moduleName;

            const actionsList = document.createElement("div");
            actionsList.style.marginTop = "8px";
            actionsList.style.fontSize = "0.85rem";

            // Default items (white text if no added items, blue if any added)
            items.default.forEach(action => {
                const item = document.createElement("div");
                item.style.color = hasAddedItems ? "#0d6efd" : "#666";
                // item.style.fontWeight = hasAddedItems ? "bold" : "normal";
                item.style.marginBottom = "4px";
                item.textContent = "• " + action;
                actionsList.appendChild(item);
            });

            // Added items (blue text)
            items.added.forEach(action => {
                const item = document.createElement("div");
                item.style.color = "#0d6efd";
                // item.style.fontWeight = "bold";
                item.style.marginBottom = "4px";
                item.textContent = "• " + action;
                actionsList.appendChild(item);
            });

            card.appendChild(title);
            card.appendChild(actionsList);
            summaryContainer.appendChild(card);
        });
    }

    // Department auto-fill logic
    const requestForSelect = document.getElementById('requestForSelect');
    const departmentSelect = document.getElementById('departmentSelect');
    const storeContainer = document.getElementById('storeContainer');
    const storeInput = document.getElementById('storeInput');

    requestForSelect.addEventListener('change', async function() {
        const employeeId = this.options[this.selectedIndex].getAttribute('data-employee-id');
        if (!employeeId) {
            departmentSelect.value = '';
            departmentSelect.disabled = false;
            if (departmentChoices) departmentChoices.enable();
            storeContainer.style.display = 'none';
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
            }
            
            storeContainer.style.display = data.requires_store ? 'block' : 'none';
        } catch (error) {
            console.error('Error:', error);
        }
    });

    // On page load, initialize with pre-selected values
    console.log('=== Page Load Initialization ===');
    console.log('originalRole:', originalRole);
    console.log('currentAccessTypeIds:', currentAccessTypeIds);
    
    if (systemSelect.value) {
        console.log('System already selected:', systemSelect.value);
        systemSelect.dispatchEvent(new Event('change'));
    } else {
        console.log('No system selected');
    }
    
    if (requestForSelect.value) {
        console.log('Request For already selected:', requestForSelect.value);
        requestForSelect.dispatchEvent(new Event('change'));
    }

    // Update summary on initial load
    setTimeout(() => {
        console.log('Calling updateSummary() after 300ms');
        updateSummary();
    }, 300);
});
</script>

<?php include ($reqhub_root . "/includes/footer.php"); ?>