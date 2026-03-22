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

// Group access types
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

// Fetch current access types for this request
try {
    $stmt = $pdo->prepare("
        SELECT access_type_id FROM request_access_types WHERE request_id = ?
    ");
    $stmt->execute([$request_id]);
    $currentAccessTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Error fetching current access types: " . $e->getMessage());
    $currentAccessTypes = [];
}
?>

<?php include ($reqhub_root . "/includes/header.php"); ?>

<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<style>
    .choices__list--dropdown {
        z-index: 1000 !important;
    }
    
    .choices[data-type*="select-one"] .choices__button {
        z-index: 999 !important;
    }
</style>

<div class="container-fluid mt-4 px-3 px-lg-5">
    <h2>Edit & Resubmit Request</h2>
    <div class="alert alert-warning">
        <strong>This request needs revision.</strong> Please review the comments in the request and make the necessary changes below.
    </div>

    <form id="requestForm">
        <input type="hidden" name="request_id" value="<?= $request_id ?>">

        <!-- System (Single Selection Only) -->
        <div class="mb-3">
            <label class="form-label">System</label>
            <select name="system_id" id="systemSelect" class="form-select" required>
                <option value="">Select System</option>
                <?php foreach ($systems as $system): ?>
                    <option value="<?= $system['id'] ?>" 
                            <?= $request['system_id'] == $system['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($system['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Request For -->
        <div class="mb-3">
            <label class="form-label">Request For</label>
            <select name="request_for" id="requestForSelect" class="form-select" required>
                <option value="">Select User</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" 
                            data-employee-id="<?= htmlspecialchars($u['employee_id']) ?>"
                            <?= $request['request_for'] == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Department (Auto-filled) -->
        <div class="mb-3">
            <label class="form-label">Department</label>
            <select name="department_id" id="departmentSelect" class="form-select" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>"
                            <?= $request['department_id'] == $dept['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Store (Conditional) -->
        <div class="mb-3" id="storeContainer" style="display: none;">
            <label class="form-label">Store</label>
            <input type="text" name="store" id="storeInput" class="form-control" 
                   placeholder="Enter store name" value="<?= htmlspecialchars($request['store'] ?? '') ?>">
        </div>

        <!-- Access Types -->
        <div class="mb-4">
            <label class="form-label fw-bold">Access Types</label>

            <div class="row">
                
                <!-- COLUMN 1 — ROLES (System-Specific) -->
                <div class="border-end" style="flex: 0 0 15%; max-width: 15%;">
                    <h6>Roles</h6>
                    <div class="list-group" id="roleList">
                        <div class="text-muted small p-2">Select a system first</div>
                    </div>
                </div>

                <!-- COLUMN 2 — MODULES + ACTIONS (System & Role Specific) -->
                <div style="flex: 0 0 85%; max-width: 85%;">
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

                                <!-- Modules & Actions in 2-column CSS Grid -->
                                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                                    <?php foreach ($modules as $module => $actions): ?>
                                        <div class="border rounded p-2" style="background-color: #f8f9fa;">
                                            <!-- Module Header -->
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                                <strong style="font-size: 0.9rem;"><?= htmlspecialchars($module) ?></strong>
                                                <span class="badge bg-secondary" style="font-size: 0.7rem;">
                                                    <?= count($actions) ?> action<?= count($actions) !== 1 ? 's' : '' ?>
                                                </span>
                                            </div>

                                            <!-- Actions in 3 columns grid -->
                                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                                                <?php foreach ($actions as $action): ?>
                                                    <div class="form-check" style="margin-bottom: 0;">
                                                        <input class="form-check-input access-checkbox"
                                                            type="checkbox"
                                                            name="access_types[]"
                                                            value="<?= $action['id'] ?>"
                                                            data-system="<?= htmlspecialchars($action['system']) ?>"
                                                            data-role="<?= htmlspecialchars($action['role']) ?>"
                                                            data-module="<?= htmlspecialchars($action['module']) ?>"
                                                            data-name="<?= htmlspecialchars($action['actions']) ?>"
                                                            id="access<?= $action['id'] ?>"
                                                            <?= in_array($action['id'], $currentAccessTypes) ? 'checked' : '' ?>
                                                            style="margin-top: 2px;">

                                                        <label class="form-check-label"
                                                            for="access<?= $action['id'] ?>"
                                                            style="font-size: 0.8rem; margin-bottom: 0; word-break: break-word;">
                                                            <?= htmlspecialchars($action['actions']) ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- COLUMN 3 — SUMMARY -->
        <div class="mt-3">
            <h6>Selected Access</h6>
            <div id="selectedSummary" class="small" style="
                max-height: 400px;
                overflow-y: auto;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 10px;
                background-color: #f8f9fa;
            ">
                <em>No access selected</em>
            </div>
        </div>

        <!-- Remove From -->
        <div class="mb-3">
            <label class="form-label">Remove From (leave blank if not applicable)</label>
            <input type="text" name="remove_from" class="form-control" 
                   value="<?= htmlspecialchars($request['remove_from'] ?? '') ?>">
        </div>

        <!-- Description -->
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

<!-- Choices.js JS -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // Declare all variables at the top
    const systemSelect = document.getElementById("systemSelect");
    const roleList = document.getElementById("roleList");
    const roleModulesSystems = document.querySelectorAll(".role-modules-system");
    const checkboxes = document.querySelectorAll(".access-checkbox");
    const summary = document.getElementById("selectedSummary");
    const requestForSelect = document.getElementById('requestForSelect');
    const departmentSelect = document.getElementById('departmentSelect');
    const storeContainer = document.getElementById('storeContainer');
    const storeInput = document.getElementById('storeInput');
    const requestForm = document.getElementById('requestForm');

    // Initialize Choices.js
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

    // Set Choices.js values from pre-selected options
    if (systemSelect.value) {
        systemChoices.setChoiceByValue(systemSelect.value);
        console.log('Set system value to:', systemSelect.value);
    }
    
    if (requestForSelect.value) {
        requestForChoices.setChoiceByValue(requestForSelect.value);
        console.log('Set request_for value to:', requestForSelect.value);
    }
    
    if (departmentSelect.value) {
        departmentChoices.setChoiceByValue(departmentSelect.value);
        console.log('Set department value to:', departmentSelect.value);
    }

    let systemNameMap = {};
    document.querySelectorAll("#systemSelect option").forEach(opt => {
        if (opt.value) {
            systemNameMap[opt.value] = opt.textContent;
        }
    });

    // Update summary on load
    updateSummary();

    // When system is selected
    systemSelect.addEventListener("change", function() {
        const systemId = this.value;
        
        if (!systemId) {
            roleList.innerHTML = '<div class="text-muted small p-2">Select a system first</div>';
            roleModulesSystems.forEach(rm => rm.style.display = "none");
            document.getElementById("modulePlaceholder").style.display = "block";
            return;
        }

        const systemName = systemNameMap[systemId];

        fetch(`/zen/reqHub/system_roles_action?system_id=${systemId}`)
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

    function updateRoleUI(roles, systemName) {
        roleList.innerHTML = "";
        
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

    function showRole(role, systemName) {
        document.getElementById("modulePlaceholder").style.display = "none";
        
        roleModulesSystems.forEach(section => {
            const isVisible = section.dataset.system === systemName && section.dataset.role === role;
            section.style.display = isVisible ? "block" : "none";
        });
    }

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

    checkboxes.forEach(cb => {
        cb.addEventListener("change", updateSummary);
    });

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

    // REQUEST FOR AUTO-DEPARTMENT LOGIC

    requestForSelect.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        const employeeId = selectedOption.getAttribute('data-employee-id');
        
        if (!employeeId) {
            departmentSelect.value = '';
            departmentSelect.disabled = false;
            if (departmentChoices) departmentChoices.enable();
            storeContainer.style.display = 'none';
            storeInput.value = '';
            return;
        }

        try {
            const response = await fetch('/zen/reqHub/getempdept?emp_no=' + encodeURIComponent(employeeId));
            
            if (!response.ok) {
                console.error('Failed to fetch employee department:', response.status);
                return;
            }
            
            const data = await response.json();
            
            if (data.department) {
                let found = false;
                for (let option of departmentSelect.options) {
                    const optionText = option.textContent.trim();
                    
                    if (optionText === data.department || option.value === data.department) {
                        departmentSelect.value = option.value;
                        
                        if (departmentChoices) {
                            departmentChoices.setChoiceByValue(option.value);
                        }
                        
                        found = true;
                        break;
                    }
                }
            }
            
            if (data.requires_store) {
                storeContainer.style.display = 'block';
                storeInput.required = true;
            } else {
                storeContainer.style.display = 'none';
                storeInput.required = false;
                storeInput.value = '';
            }
        } catch (error) {
            console.error('Error fetching employee department:', error);
        }
    });

    // REQUEST FOR AUTO-DEPARTMENT LOGIC
    requestForSelect.addEventListener('change', async function() {
        const selectedOption = this.options[this.selectedIndex];
        const employeeId = selectedOption.getAttribute('data-employee-id');
        
        console.log('Request For changed:', employeeId);
        
        if (!employeeId) {
            // Reset if nothing selected
            departmentSelect.value = '';
            departmentSelect.disabled = false;
            if (departmentChoices) departmentChoices.enable();
            console.log('Department dropdown re-enabled');
            
            storeContainer.style.display = 'none';
            storeInput.value = '';
            return;
        }

        try {
            // Fetch department and store requirement from tbl201_jobrec
            const response = await fetch('/zen/reqHub/getempdept?emp_no=' + encodeURIComponent(employeeId));
            
            if (!response.ok) {
                console.error('Failed to fetch employee department:', response.status);
                return;
            }
            
            const data = await response.json();
            console.log('Employee data:', data);
            
            // Set department if found
            if (data.department) {
                console.log('Looking for department:', data.department);
                
                // Find the department option by name/value and set it
                let found = false;
                for (let option of departmentSelect.options) {
                    const optionText = option.textContent.trim();
                    console.log('Comparing with option:', optionText, 'Value:', option.value);
                    
                    if (optionText === data.department || option.value === data.department) {
                        console.log('Match found! Setting value to:', option.value);
                        departmentSelect.value = option.value;
                        
                        // Update Choices.js if it's active
                        if (departmentChoices) {
                            departmentChoices.setChoiceByValue(option.value);
                            console.log('Updated Choices.js');
                        }
                        
                        // Disable the department dropdown - user cannot change it
                        departmentSelect.disabled = true;
                        departmentSelect.setAttribute('readonly', 'readonly');
                        if (departmentChoices) {
                            departmentChoices.disable();
                        }
                        console.log('Department dropdown disabled');
                        
                        found = true;
                        break;
                    }
                }
                
                if (!found) {
                    console.warn('Department not found in dropdown:', data.department);
                }
            }
            
            // Show/hide store input based on requirement
            if (data.requires_store) {
                storeContainer.style.display = 'block';
                storeInput.required = true;
            } else {
                storeContainer.style.display = 'none';
                storeInput.required = false;
                storeInput.value = '';
            }
        } catch (error) {
            console.error('Error fetching employee department:', error);
        }
    });

    // Update summary on load
    updateSummary();

    // Trigger on page load to set initial department
    if (systemSelect.value) {
        systemSelect.dispatchEvent(new Event('change'));
        
        // After system change, auto-click the first role button
        setTimeout(() => {
            const firstRoleBtn = document.querySelector('.role-btn');
            if (firstRoleBtn) {
                firstRoleBtn.click();
            }
        }, 100);
    }
    
    // Trigger request for change to set initial department
    if (requestForSelect.value) {
        requestForSelect.dispatchEvent(new Event('change'));
    }

    // Handle form submission
    if (requestForm) {
        requestForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate that at least one access type is selected
            const selectedAccessTypes = Array.from(document.querySelectorAll('.access-checkbox:checked'));
            
            if (selectedAccessTypes.length === 0) {
                alert('Please select at least one access type');
                return;
            }
            
            const formData = new FormData(this);
            
            // IMPORTANT: If department is disabled, manually add it
            // because disabled fields don't get included in FormData
            if (departmentSelect.disabled && departmentSelect.value) {
                console.log('Department is disabled, manually adding:', departmentSelect.value);
                formData.set('department_id', departmentSelect.value);
            }
            
            // DEBUG: Log all form data
            console.log('Form data being sent:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }
            console.log('Request ID:', formData.get('request_id'));
            console.log('System ID:', formData.get('system_id'));
            console.log('Department ID:', formData.get('department_id'));
            console.log('Request For:', formData.get('request_for'));
            console.log('Access Types (via getAll):', formData.getAll('access_types[]'));
            
            fetch('/zen/reqHub/revise_submit', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Request revisions submitted successfully!');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = '/zen/reqHub/dashboard?status=pending&pending_tab=all';
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
    }

});
</script>

<?php include ($reqhub_root . "/includes/footer.php"); ?>