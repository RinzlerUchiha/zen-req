<?php
// require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

requireRole(['requestor', 'approver']);

// Fetch dropdown data
$systems = $pdo->query("SELECT id, name FROM systems ORDER BY name")->fetchAll();
$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();

// Fetch access types grouped by Role → Module → Actions
$accessTypes = $pdo->query("
    SELECT id, system, role, module, actions
    FROM access_types
    ORDER BY role, module, actions
")->fetchAll(PDO::FETCH_ASSOC);

// Group them: System → Role → Module → Actions
$groupedBySystem = [];
foreach ($accessTypes as $type) {
    $system = $type['system'];
    $role = $type['role'];
    $module = $type['module'];
    
    if (!isset($groupedBySystem[$system])) {
        $groupedBySystem[$system] = [];
    }
    if (!isset($groupedBySystem[$system][$role])) {
        $groupedBySystem[$system][$role] = [];
    }
    if (!isset($groupedBySystem[$system][$role][$module])) {
        $groupedBySystem[$system][$role][$module] = [];
    }
    
    $groupedBySystem[$system][$role][$module][] = $type;
}
?>

<?php include "../includes/header.php"; ?>

<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<div class="container mt-4">
    <h2>Create New Request</h2>

    <form action="../actions/request_create_action.php" method="POST" id="requestForm">

        <!-- System (Single Selection Only) -->
        <div class="mb-3">
            <label class="form-label">System</label>
            <select name="system_id" id="systemSelect" class="form-select" required>
                <option value="">Select System</option>
                <?php foreach ($systems as $system): ?>
                    <option value="<?= $system['id'] ?>"><?= htmlspecialchars($system['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">Changing system will clear your selections</small>
        </div>

        <!-- Request For -->
        <div class="mb-3">
            <label class="form-label">Request For</label>
            <select name="request_for" id="requestForSelect" class="form-select" required>
                <option value="">Select User</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Department -->
        <div class="mb-3">
            <label class="form-label">Department</label>
            <select name="department_id" id="departmentSelect" class="form-select" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Access Types -->
        <div class="mb-4">
            <label class="form-label fw-bold">Access Types</label>

            <div class="row">
                
                <!-- COLUMN 1 — ROLES (System-Specific) -->
                <div class="col-md-3 border-end">
                    <h6>Roles</h6>
                    <div class="list-group" id="roleList">
                        <div class="text-muted small p-2">Select a system first</div>
                    </div>
                </div>

                <!-- COLUMN 2 — MODULES + ACTIONS (System & Role Specific) -->
                <div class="col-md-6">
                    <h6>Modules & Actions</h6>

                    <div id="modulePlaceholder" class="text-muted small p-2">
                        Select a role to view modules.
                    </div>

                    <!-- Generate containers for each system + role combination -->
                    <?php foreach ($groupedBySystem as $system => $roles): ?>
                        <?php foreach ($roles as $role => $modules): ?>
                            <div class="role-modules-system" 
                                 data-system="<?= htmlspecialchars($system) ?>" 
                                 data-role="<?= htmlspecialchars($role) ?>" 
                                 style="display:none;">
                                
                                <!-- Select / Unselect All Buttons -->
                                <div class="mb-2">
                                    <button type="button" class="btn btn-sm btn-primary select-all-btn">Select All</button>
                                    <button type="button" class="btn btn-sm btn-secondary unselect-all-btn">Unselect All</button>
                                </div>

                                <div class="row g-3 mb-4">
                                    <?php foreach ($modules as $module => $actions): ?>
                                        <div class="col-md-6">
                                            <div class="border rounded p-2 h-100">
                                                <strong><?= htmlspecialchars($module) ?></strong>

                                                <div class="mt-2">
                                                    <?php foreach ($actions as $action): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input access-checkbox"
                                                                type="checkbox"
                                                                name="access_types[]"
                                                                value="<?= $action['id'] ?>"
                                                                data-system="<?= htmlspecialchars($action['system']) ?>"
                                                                data-role="<?= htmlspecialchars($action['role']) ?>"
                                                                data-module="<?= htmlspecialchars($action['module']) ?>"
                                                                data-name="<?= htmlspecialchars($action['actions']) ?>"
                                                                id="access<?= $action['id'] ?>">

                                                            <label class="form-check-label"
                                                                for="access<?= $action['id'] ?>">
                                                                <?= htmlspecialchars($action['actions']) ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>

                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>

                <!-- COLUMN 3 — SUMMARY -->
                <div class="col-md-3 border-start">
                    <h6>Selected Access</h6>
                    <div id="selectedSummary" class="small">
                        <em>No access selected</em>
                    </div>
                </div>

            </div>
        </div>

        <!-- Remove From -->
        <div class="mb-3">
            <label class="form-label">Remove From (leave blank if new request)</label>
            <input type="text" name="remove_from" class="form-control">
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label class="form-label">Description / Purpose</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</div>

<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<!-- Choices.js JS -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // Initialize Choices.js on the three dropdowns
    const systemChoices = new Choices('#systemSelect', {
        searchEnabled: true,
        itemSelectText: 'Press to select',
        removeItemButton: true
    });

    const requestForChoices = new Choices('#requestForSelect', {
        searchEnabled: true,
        itemSelectText: 'Press to select',
        removeItemButton: true
    });

    const departmentChoices = new Choices('#departmentSelect', {
        searchEnabled: true,
        itemSelectText: 'Press to select',
        removeItemButton: true
    });

    const systemSelect = document.getElementById("systemSelect");
    const roleList = document.getElementById("roleList");
    const roleModulesSystems = document.querySelectorAll(".role-modules-system");
    const checkboxes = document.querySelectorAll(".access-checkbox");
    const summary = document.getElementById("selectedSummary");

    // Build system name map from options
    let systemNameMap = {};
    document.querySelectorAll("#systemSelect option").forEach(opt => {
        if (opt.value) {
            systemNameMap[opt.value] = opt.textContent;
        }
    });

    // When system is selected
    systemSelect.addEventListener("change", function() {
        const systemId = this.value;
        
        if (!systemId) {
            roleList.innerHTML = '<div class="text-muted small p-2">Select a system first</div>';
            roleModulesSystems.forEach(rm => rm.style.display = "none");
            document.getElementById("modulePlaceholder").style.display = "block";
            return;
        }

        // IMPORTANT: Clear all selections when system changes
        checkboxes.forEach(cb => cb.checked = false);
        updateSummary();

        // Get system name
        const systemName = systemNameMap[systemId];

        // Fetch roles for this system
        fetch(`../actions/system_roles_action.php?system_id=${systemId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.roles.length > 0) {
                    updateRoleUI(data.roles, data.system_name);
                } else {
                    roleList.innerHTML = '<div class="text-muted small p-2">No roles found for this system</div>';
                    roleModulesSystems.forEach(rm => rm.style.display = "none");
                    document.getElementById("modulePlaceholder").style.display = "block";
                }
            })
            .catch(error => {
                console.error("Error fetching roles:", error);
                roleList.innerHTML = '<div class="alert alert-danger small p-2">Error loading roles</div>';
            });
    });

    // Update role UI with fetched roles
    function updateRoleUI(roles, systemName) {
        roleList.innerHTML = ""; // Clear
        
        roles.forEach(role => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "list-group-item list-group-item-action role-btn";
            btn.dataset.role = role;
            btn.dataset.system = systemName;
            btn.textContent = role;
            
            btn.addEventListener("click", function() {
                document.querySelectorAll(".role-btn").forEach(b => b.classList.remove("active"));
                this.classList.add("active");
                showRole(role, systemName);
            });
            
            roleList.appendChild(btn);
        });
    }

    // Show modules/actions for selected system + role combination
    function showRole(role, systemName) {
        document.getElementById("modulePlaceholder").style.display = "none";
        
        roleModulesSystems.forEach(section => {
            // Only show if BOTH system AND role match
            const isVisible = section.dataset.system === systemName && section.dataset.role === role;
            section.style.display = isVisible ? "block" : "none";
        });
    }

    // Update summary whenever a checkbox changes
    function updateSummary() {
        const selected = document.querySelectorAll(".access-checkbox:checked");

        if (selected.length === 0) {
            summary.innerHTML = "<em>No access selected</em>";
            return;
        }

        // Group by Role → Module → Action (all from same system)
        let grouped = {};

        selected.forEach(cb => {
            const role = cb.dataset.role;
            const module = cb.dataset.module;
            const name = cb.dataset.name;

            if (!grouped[role]) grouped[role] = {};
            if (!grouped[role][module]) grouped[role][module] = [];

            grouped[role][module].push(name);
        });

        let html = "";

        for (let role in grouped) {
            html += `<strong>${htmlEscape(role)}</strong><br>`;

            for (let module in grouped[role]) {
                html += `<div style="margin-left: 15px;">`;
                html += `<u>${htmlEscape(module)}</u><br>`;
                
                grouped[role][module].forEach(name => {
                    html += `<div style="margin-left: 15px;">• ${htmlEscape(name)}</div>`;
                });

                html += `</div>`;
            }

            html += `<br>`;
        }

        summary.innerHTML = html;
    }

    // Helper function to escape HTML
    function htmlEscape(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Attach change listeners to all checkboxes
    checkboxes.forEach(cb => {
        cb.addEventListener("change", updateSummary);
    });

    // Select All / Unselect All buttons
    roleModulesSystems.forEach(section => {
        const selectAllBtn = section.querySelector(".select-all-btn");
        const unselectAllBtn = section.querySelector(".unselect-all-btn");

        selectAllBtn.addEventListener("click", () => {
            const roleCheckboxes = section.querySelectorAll(".access-checkbox");
            roleCheckboxes.forEach(cb => cb.checked = true);
            updateSummary();
        });

        unselectAllBtn.addEventListener("click", () => {
            const roleCheckboxes = section.querySelectorAll(".access-checkbox");
            roleCheckboxes.forEach(cb => cb.checked = false);
            updateSummary();
        });
    });

});
</script>

<?php include "../includes/footer.php"; ?>