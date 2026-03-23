<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

if (!userHasRoleIn('Requestor', 'Approver')) {
    http_response_code(403);
    die('Access denied: Only Requestors and Approvers can create requests');
}

$pdo = ReqHubDatabase::getConnection('reqhub');

try {
    $systems = $pdo->query("SELECT id, name FROM systems ORDER BY name")->fetchAll();
    
    $departments = $pdo->query("
        SELECT DISTINCT d.id, d.name, d.code
        FROM tngc_hrd2.tbl201_jobrec jr
        INNER JOIN departments d ON d.code = jr.jrec_department
        WHERE jr.jrec_status = 'primary' AND jr.jrec_department IS NOT NULL AND jr.jrec_department != ''
        ORDER BY d.name ASC
    ")->fetchAll();
    
    $users = $pdo->query("
        SELECT hu.U_ID as id, hu.Emp_No as employee_id,
            COALESCE(CONCAT(NULLIF(bi.bi_empfname, ''), ' ', NULLIF(bi.bi_emplname, '')), hu.U_Name, hu.Emp_No) as name
        FROM tngc_hrd2.tbl_user2 hu
        LEFT JOIN tngc_hrd2.tbl201_basicinfo bi ON hu.Emp_No = bi.bi_empno AND bi.datastat = 'current'
        LEFT JOIN tngc_hrd2.tbl201_jobrec jr ON hu.Emp_No = jr.jrec_empno AND jr.jrec_status = 'primary'
        WHERE hu.U_stat = 1
        GROUP BY hu.U_ID, hu.Emp_No, hu.U_Name, bi.bi_empfname, bi.bi_emplname
        ORDER BY COALESCE(CONCAT(NULLIF(bi.bi_empfname, ''), ' ', NULLIF(bi.bi_emplname, '')), hu.U_Name, hu.Emp_No) ASC
    ")->fetchAll();
    
    $accessTypes = $pdo->query("SELECT id, system, role, module, actions FROM access_types ORDER BY role, module, actions")->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Loaded " . count($systems) . " systems, " . count($departments) . " departments, " . count($users) . " users, " . count($accessTypes) . " access types");
} catch (PDOException $e) {
    error_log("Error fetching data: " . $e->getMessage());
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// Group by System → Role → Module → Actions
$groupedBySystem = [];
foreach ($accessTypes as $type) {
    $system = $type['system'];
    $role = $type['role'];
    $module = $type['module'];
    
    if (!isset($groupedBySystem[$system])) $groupedBySystem[$system] = [];
    if (!isset($groupedBySystem[$system][$role])) $groupedBySystem[$system][$role] = [];
    if (!isset($groupedBySystem[$system][$role][$module])) $groupedBySystem[$system][$role][$module] = [];
    
    $groupedBySystem[$system][$role][$module][] = $type;
}
?>

<?php include ($reqhub_root . "/includes/header.php"); ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<style>
    .choices__list--dropdown { z-index: 1000 !important; }
    .choices[data-type*="select-one"] .choices__button { z-index: 999 !important; }
</style>

<div class="container-fluid mt-4 px-3 px-lg-5">
    <h2>Create New Request</h2>

    <form action="/zen/reqHub/request_create_action" method="POST" id="requestForm">

        <input type="hidden" name="chosen_role" id="chosenRoleInput" value="">

        <div class="mb-3">
            <label class="form-label">System</label>
            <select name="system_id" id="systemSelect" class="form-select" required>
                <option value="">Select System</option>
                <?php foreach ($systems as $system): ?>
                    <option value="<?= $system['id'] ?>"><?= htmlspecialchars($system['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
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

        <div class="mb-3">
            <label class="form-label">Department</label>
            <select name="department_id" id="departmentSelect" class="form-select" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3" id="storeContainer" style="display: none;">
            <label class="form-label">Store</label>
            <input type="text" name="store" id="storeInput" class="form-control" placeholder="Enter store name">
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
            <label class="form-label">Remove From (leave blank if new request)</label>
            <input type="text" name="remove_from" class="form-control">
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

    const requestForm = document.getElementById('requestForm');
    requestForm.addEventListener('submit', function(e) {
        const disabledSelects = this.querySelectorAll('select:disabled');
        disabledSelects.forEach(select => select.disabled = false);
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
    
    let systemNameMap = {};
    document.querySelectorAll("#systemSelect option").forEach(opt => {
        if (opt.value) systemNameMap[opt.value] = opt.textContent;
    });

    let currentSearch = "";

    // Render modules based on search
    function renderModules() {
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
            // Use action name as key to deduplicate - we only need one entry per action per module
            if (!grouped[type.module][type.actions]) {
                grouped[type.module][type.actions] = type;
            }
        });

        // Display grouped modules in cards like the photo
        Object.entries(grouped).forEach(([moduleName, actionsMap]) => {
            const actions = Object.values(actionsMap); // Convert back to array
            
            const moduleCard = document.createElement("div");
            moduleCard.className = "module-card";
            moduleCard.style.border = "1px solid #555";
            moduleCard.style.borderRadius = "4px";
            moduleCard.style.padding = "10px";
            moduleCard.style.backgroundColor = "#2a2a2a";
            moduleCard.style.minHeight = "250px";
            moduleCard.style.display = "flex";
            moduleCard.style.flexDirection = "column";

            // Module header with checkbox and name and action count
            const headerDiv = document.createElement("div");
            headerDiv.style.display = "flex";
            headerDiv.style.justifyContent = "space-between";
            headerDiv.style.alignItems = "flex-start";
            headerDiv.style.marginBottom = "10px";
            headerDiv.style.paddingBottom = "8px";
            headerDiv.style.borderBottom = "1px solid #444";
            headerDiv.style.gap = "8px";

            // Module checkbox
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
                
                actionCheckboxes.push(checkbox);

                checkbox.addEventListener("change", function() {
                    // Remove from auto-selected if user manually toggles
                    if (this.checked && !autoSelectedItems.has(this.value)) {
                        // Manually checked - not auto
                    } else if (!this.checked && autoSelectedItems.has(this.value)) {
                        // Manually unchecked - remove from auto
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
                    // If manually unchecking, remove from auto-selected
                    if (!this.checked && autoSelectedItems.has(cb.value)) {
                        autoSelectedItems.delete(cb.value);
                    }
                    // If manually checking, don't add to auto-selected
                });
                updateSummary();
            });

            moduleCard.appendChild(actionsGrid);
            modulesDisplay.appendChild(moduleCard);
        });
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
        console.log('Role selected:', role);
        
        // IMPORTANT: Store the chosen role in the hidden input
        document.getElementById('chosenRoleInput').value = role;
        console.log('Stored chosen_role:', role);
        
        if (!role) {
            // Clear selections if no role selected
            document.querySelectorAll('.access-checkbox').forEach(cb => cb.checked = false);
            updateSummary();
            return;
        }
        
        const systemName = systemNameMap[systemSelect.value];
        selectAllModulesForRole(role, systemName);
    });

    function selectAllModulesForRole(role, systemName) {
        // Uncheck all first
        document.querySelectorAll('.access-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.module-checkbox').forEach(cb => cb.checked = false);
        
        // Clear auto-selected tracking
        autoSelectedItems.clear();
        
        // Check only items for this role and system
        const roleCbs = document.querySelectorAll(`.access-checkbox[data-role="${role}"][data-system="${systemName}"]`);
        roleCbs.forEach(cb => {
            cb.checked = true;
            autoSelectedItems.add(cb.value); // Mark as auto-selected
        });
        
        // Also check the module checkboxes for selected modules
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

    let autoSelectedItems = new Set(); // Track items auto-selected by role

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
            
            // Check if this item was auto-selected and hasn't been manually toggled
            const isStillAuto = autoSelectedItems.has(itemId);
            
            if (isStillAuto) {
                grouped[module].default.push(action);
            } else {
                grouped[module].added.push(action);
            }
        });

        summaryContainer.innerHTML = "";
        Object.entries(grouped).forEach(([moduleName, items]) => {
            // Determine if this module has any manually selected items
            const hasAddedItems = items.added.length > 0;
            
            const card = document.createElement("div");
            card.className = "border rounded p-3";
            card.style.backgroundColor = "#f5f5f5";
            card.style.borderColor = "#ddd";

            const title = document.createElement("strong");
            title.style.fontSize = "0.95rem";
            title.style.color = hasAddedItems ? "#0d6efd" : "#fff";
            title.textContent = moduleName;

            const actionsList = document.createElement("div");
            actionsList.style.marginTop = "8px";
            actionsList.style.fontSize = "0.85rem";

            // Default items (white text if no added items, blue if any added)
            items.default.forEach(action => {
                const item = document.createElement("div");
                item.style.color = hasAddedItems ? "#0d6efd" : "#fff";
                item.style.fontWeight = hasAddedItems ? "bold" : "normal";
                item.style.marginBottom = "4px";
                item.textContent = "• " + action;
                actionsList.appendChild(item);
            });

            // Added items (blue text)
            items.added.forEach(action => {
                const item = document.createElement("div");
                item.style.color = "#0d6efd";
                item.style.fontWeight = "bold";
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
});
</script>

<?php include ($reqhub_root . "/includes/footer.php"); ?>