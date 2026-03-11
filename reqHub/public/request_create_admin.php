<?php
require_once ($reqhub_root . "/includes/auth.php");
require_once ($reqhub_root . "/database/db.php");

requireRole(['requestor', 'approver']);

// Fetch dropdown data
$systems = $pdo->query("SELECT id, name FROM systems ORDER BY name")->fetchAll();
$departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();

// Fetch access types grouped by Role → Module → Actions
$accessTypes = $pdo->query("
    SELECT id, role, module, name
    FROM access_types
    ORDER BY role, module, name
")->fetchAll(PDO::FETCH_ASSOC);

// Group them
$groupedAccess = [];
foreach ($accessTypes as $type) {
    $groupedAccess[$type['role']][$type['module']][] = $type;
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
    <h2>Create New Request</h2>

    <form action="../actions/request_create_action.php" method="POST">

        <!-- System -->
        <div class="mb-3">
            <label class="form-label">System</label>
            <select name="system_id" class="form-select" required>
                <option value="">Select System</option>
                <?php foreach ($systems as $system): ?>
                    <option value="<?= $system['id'] ?>"><?= htmlspecialchars($system['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Request For -->
        <div class="mb-3">
            <label class="form-label">Request For</label>
            <select name="request_for" class="form-select" required>
                <option value="">Select User</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Department -->
        <div class="mb-3">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select" required>
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
                
                <!-- COLUMN 1 — ROLES -->
                <div class="col-md-3 border-end">
                    <h6>Roles</h6>
                    <div class="list-group" id="roleList">
                        <?php $first = true; ?>
                        <?php foreach ($groupedAccess as $role => $modules): ?>
                            <button type="button"
                                    class="list-group-item list-group-item-action role-btn"
                                    data-role="<?= htmlspecialchars($role) ?>">
                                <?= htmlspecialchars($role) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- COLUMN 2 — MODULES + ACTIONS -->
                <div class="col-md-6">
                    <h6>Modules</h6>

                    <div id="modulePlaceholder" class="text-muted">
                        Select a role to view modules.
                    </div>

                    <?php foreach ($groupedAccess as $role => $modules): ?>
                        <div class="role-modules" data-role="<?= htmlspecialchars($role) ?>" style="display:none;">
                            
                            <!-- Select / Unselect All Buttons -->
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-primary select-all-btn">Select All</button>
                                <button type="button" class="btn btn-sm btn-secondary unselect-all-btn">Unselect All</button>
                            </div>

                            <div class="row g-3">
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
                                                            data-role="<?= htmlspecialchars($role) ?>"
                                                            data-module="<?= htmlspecialchars($module) ?>"
                                                            data-name="<?= htmlspecialchars($action['name']) ?>"
                                                            id="access<?= $action['id'] ?>">

                                                        <label class="form-check-label"
                                                            for="access<?= $action['id'] ?>">
                                                            <?= htmlspecialchars($action['name']) ?>
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

<script>
document.addEventListener("DOMContentLoaded", function() {

    const roleButtons = document.querySelectorAll(".role-btn");
    const roleModules = document.querySelectorAll(".role-modules");
    const checkboxes = document.querySelectorAll(".access-checkbox");
    const summary = document.getElementById("selectedSummary");

    // Role selection
    roleButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            roleButtons.forEach(b => b.classList.remove("active"));
            this.classList.add("active");

            showRole(this.dataset.role);
        });
    });

    function showRole(role) {
        document.getElementById("modulePlaceholder").style.display = "none";
        
        roleModules.forEach(section => {
            section.style.display = section.dataset.role === role ? "block" : "none";
        });
    }

    // Update summary whenever a checkbox changes
    function updateSummary() {
        const selected = document.querySelectorAll(".access-checkbox:checked");

        if (selected.length === 0) {
            summary.innerHTML = "<em>No access selected</em>";
            return;
        }

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
            html += `<strong>${role}</strong><br>`;

            for (let module in grouped[role]) {
                html += `&nbsp;&nbsp;<u>${module}</u><br>`;
                grouped[role][module].forEach(name => {
                    html += `&nbsp;&nbsp;&nbsp;&nbsp;• ${name}<br>`;
                });
            }

            html += "<br>";
        }

        summary.innerHTML = html;
    }

    // Attach change listeners to all checkboxes
    checkboxes.forEach(cb => {
        cb.addEventListener("change", updateSummary);
    });

    // Select All / Unselect All buttons
    roleModules.forEach(section => {
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
