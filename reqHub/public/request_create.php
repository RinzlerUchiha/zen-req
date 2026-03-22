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

            <div class="row g-3">
                <!-- COLUMN 1: Roles (Radio Buttons) -->
                <div class="col-md-2">
                    <h6>Roles</h6>
                    <div id="roleList" class="border rounded p-3" style="background-color: #f9f9f9; min-height: 200px;">
                        <div class="text-muted small">Select a system first</div>
                    </div>
                </div>

                <!-- COLUMN 2: Modules & Actions (Compact) -->
                <div class="col-md-5">
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

                <!-- COLUMN 3: Summary (Horizontal scrollable cards) -->
                <div class="col-md-5">
                    <h6>Selected Access</h6>
                    <div id="summaryContainer" class="border rounded p-3" style="max-height: 600px; overflow-x: auto; overflow-y: auto; background-color: #fafafa; display: flex; gap: 12px; flex-wrap: nowrap; padding: 12px;">
                        <div class="text-muted small flex-shrink-0">No access selected</div>
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
    const roleList = document.getElementById("roleList");
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
            if (!grouped[type.module]) grouped[type.module] = [];
            grouped[type.module].push(type);
        });

        // Display grouped modules in cards
        Object.entries(grouped).forEach(([moduleName, actions]) => {
            const moduleCard = document.createElement("div");
            moduleCard.style.border = "1px solid #ddd";
            moduleCard.style.borderRadius = "4px";
            moduleCard.style.padding = "10px";
            moduleCard.style.backgroundColor = "#f8f9fa";

            const moduleTitle = document.createElement("div");
            moduleTitle.style.fontWeight = "bold";
            moduleTitle.style.fontSize = "0.85rem";
            moduleTitle.style.marginBottom = "8px";
            moduleTitle.style.wordBreak = "break-word";
            moduleTitle.textContent = moduleName;

            moduleCard.appendChild(moduleTitle);

            actions.forEach(type => {
                const actionDiv = document.createElement("div");
                actionDiv.style.marginBottom = "6px";
                actionDiv.style.display = "flex";
                actionDiv.style.alignItems = "flex-start";
                actionDiv.style.gap = "4px";

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
                checkbox.style.width = "14px";
                checkbox.style.height = "14px";
                checkbox.style.marginRight = "0";
                checkbox.style.marginTop = "2px";
                checkbox.style.flexShrink = "0";
                checkbox.addEventListener("change", updateSummary);

                const label = document.createElement("label");
                label.htmlFor = `access_${type.id}`;
                label.style.marginBottom = "0";
                label.style.cursor = "pointer";
                label.style.fontSize = "0.75rem";
                label.style.userSelect = "none";
                label.style.wordBreak = "break-word";
                label.style.lineHeight = "1.2";
                label.textContent = "• " + type.actions;

                actionDiv.appendChild(checkbox);
                actionDiv.appendChild(label);
                moduleCard.appendChild(actionDiv);
            });

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
        if (!systemId) {
            roleList.innerHTML = '<div class="text-muted small">Select a system first</div>';
            return;
        }

        const systemName = systemNameMap[systemId];
        fetch(`/zen/reqHub/system_roles_action?system_id=${systemId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.roles.length > 0) {
                    updateRoleUI(data.roles, data.system_name);
                } else {
                    roleList.innerHTML = '<div class="text-muted small">No roles found</div>';
                }
            })
            .catch(error => {
                console.error("Error:", error);
                roleList.innerHTML = '<div class="alert alert-danger small p-2">Error loading roles</div>';
            });
    });

    function updateRoleUI(roles, systemName) {
        roleList.innerHTML = "";
        roles.forEach(role => {
            const radioDiv = document.createElement("div");
            radioDiv.className = "form-check mb-2";
            
            const radio = document.createElement("input");
            radio.type = "radio";
            radio.className = "form-check-input";
            radio.name = "selected_role";
            radio.value = role;
            radio.id = `role_${role}`;
            
            const label = document.createElement("label");
            label.className = "form-check-label";
            label.htmlFor = `role_${role}`;
            label.textContent = role;
            
            radio.addEventListener("change", () => {
                if (radio.checked) {
                    selectAllModulesForRole(role, systemName);
                }
            });
            
            radioDiv.appendChild(radio);
            radioDiv.appendChild(label);
            roleList.appendChild(radioDiv);
        });
    }

    function selectAllModulesForRole(role, systemName) {
        // Uncheck all first
        document.querySelectorAll('.access-checkbox').forEach(cb => cb.checked = false);
        
        // Check only items for this role and system
        document.querySelectorAll(`.access-checkbox[data-role="${role}"][data-system="${systemName}"]`).forEach(cb => cb.checked = true);
        
        updateSummary();
    }

    function updateSummary() {
        const selected = document.querySelectorAll(".access-checkbox:checked");

        if (selected.length === 0) {
            summaryContainer.innerHTML = '<div class="text-muted small flex-shrink-0">No access selected</div>';
            return;
        }

        const grouped = {};
        selected.forEach(cb => {
            const module = cb.dataset.module;
            const action = cb.dataset.name;
            if (!grouped[module]) grouped[module] = [];
            grouped[module].push(action);
        });

        summaryContainer.innerHTML = "";
        Object.entries(grouped).forEach(([moduleName, actions]) => {
            const card = document.createElement("div");
            card.className = "border rounded p-3 flex-shrink-0";
            card.style.backgroundColor = "#f5f5f5";
            card.style.minWidth = "200px";
            card.style.maxWidth = "250px";

            const title = document.createElement("strong");
            title.style.fontSize = "0.95rem";
            title.textContent = moduleName;

            const actionsList = document.createElement("div");
            actionsList.style.marginTop = "8px";
            actionsList.style.fontSize = "0.85rem";

            actions.forEach(action => {
                const item = document.createElement("div");
                item.textContent = "• " + action;
                item.style.marginBottom = "4px";
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