<?php
// Auth is checked by router - session is already populated
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify session is set
if (empty($_SESSION['reqhub_user'])) {
    error_log("ERROR: dashboard.php - reqhub_user not in session");
    header('Location: /zen/login');
    exit;
}

require_once (__DIR__ . '/../database/db.php');

error_log("=== DASHBOARD.PHP LOADED ===");

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    error_log("Database connection successful");
} catch (Exception $e) {
    error_log("ERROR: Database connection failed - " . $e->getMessage());
    die("Database connection error: " . htmlspecialchars($e->getMessage()));
}

// Get user data from session
$user   = $_SESSION['reqhub_user'];
$userId = $user['emp_no'];
$role   = $user['reqhub_role'];
$status = $_GET['status'] ?? 'pending';
$pending_tab = $_GET['pending_tab'] ?? 'all';  // 'all' or 'needs_revision'

error_log("User: $userId, Role: $role, Status: $status, Pending Tab: $pending_tab");

/* ================================
   FETCH REQUESTS
================================ */

$sql = "
SELECT 
    r.*,
    s.name AS system_name,
    COALESCE(
        CONCAT(NULLIF(bi.bi_empfname, ''), ' ', NULLIF(bi.bi_emplname, '')),
        hu.U_Name,
        hu.Emp_No
    ) AS requestor_name,
    u1.U_Name AS approved_by_name,
    u2.U_Name AS denied_by_name,
    u3.U_Name AS served_by_name,
    GROUP_CONCAT(
        CONCAT(at.role, '||', at.module, '||', at.actions)
        SEPARATOR '##'
    ) AS access_type
FROM requests r
LEFT JOIN systems s ON r.system_id = s.id
LEFT JOIN tngc_hrd2.tbl_user2 hu ON hu.U_ID = r.request_for
LEFT JOIN tngc_hrd2.tbl201_basicinfo bi ON hu.Emp_No = bi.bi_empno AND bi.datastat = 'current'
LEFT JOIN tngc_hrd2.tbl_user2 u1 ON u1.Emp_No = r.approved_by
LEFT JOIN tngc_hrd2.tbl_user2 u2 ON u2.Emp_No = r.denied_by
LEFT JOIN tngc_hrd2.tbl_user2 u3 ON u3.Emp_No = r.served_by
LEFT JOIN request_access_types ra ON r.id = ra.request_id
LEFT JOIN access_types at ON ra.access_type_id = at.id
WHERE 1=1
";

/* STATUS FILTER */
switch ($status) {
    case 'pending':
        // Handle both 'all' and 'needs_revision' sub-tabs
        if ($pending_tab === 'needs_revision') {
            $sql .= " AND r.status = 'needs_revision'";
        } else {
            // 'all' tab - show only original pending
            $sql .= " AND r.status = 'pending'";
        }
        break;
    case 'approved':
        $sql .= " AND r.status = 'approved' 
                  AND (r.admin_status = 'pending' OR r.admin_status IS NULL)";
        break;
    case 'denied':
        $sql .= " AND r.status = 'denied'";
        break;
    case 'served':
        $sql .= " AND r.admin_status = 'served'";
        break;
    default:
        error_log("WARNING: Invalid status filter: $status");
        $status = 'pending';
        $sql .= " AND r.status = 'pending'";
}

/* ROLE FILTER */
$params = [];

// For all roles, we need to get the actual users.id from employee_id
$stmt_userLookup = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt_userLookup->execute([$userId]);
$userRecord = $stmt_userLookup->fetch(PDO::FETCH_ASSOC);

if ($userRecord) {
    $actual_user_id = $userRecord['id'];
    error_log("Mapped emp_no=$userId to users.id=$actual_user_id");
    
    if ($role === 'Requestor') {
    $sql .= " AND r.user_id = ?";
    $params[] = $actual_user_id;
    }
    
    if ($role === 'Approver') {
    error_log("Filter: Approver - fetching assigned systems from user_approver_assignments");
    try {
        $stmt2 = $pdo->prepare("
            SELECT DISTINCT system_id
            FROM user_approver_assignments
            WHERE user_id = :id
        ");
        $stmt2->execute([':id' => $actual_user_id]);
        $systemIds = $stmt2->fetchAll(PDO::FETCH_COLUMN);

        error_log("Approver system assignments: " . json_encode($systemIds));

        if (!empty($systemIds)) {
            $placeholders = implode(',', array_fill(0, count($systemIds), '?'));
            $sql .= " AND r.system_id IN ($placeholders)";
            foreach ($systemIds as $sid) $params[] = $sid;
        } else {
            $sql .= " AND 1=0";
            error_log("WARNING: No approver assignments found for user id=$actual_user_id");
        }
    } catch (Exception $e) {
        error_log("ERROR: Failed to fetch approver assignments - " . $e->getMessage());
    }
    }
}

$sql .= " GROUP BY r.id ORDER BY r.id DESC";

error_log("Final SQL: " . $sql);
error_log("SQL Params: " . json_encode($params));

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Query successful - found " . count($requests) . " requests");
} catch (PDOException $e) {
    error_log("ERROR: SQL query failed - " . $e->getMessage());
    die("<h1>Database Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}

error_log("=== DASHBOARD.PHP DATA LOADED ===");
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">

<?php if (in_array($role, ['Requestor','Approver','Reviewer'])): ?>
<div class="d-flex justify-content-end mb-3">
    <a href="/zen/reqHub/request" class="btn btn-lg btn-success">
        + Create Request
    </a>
</div>
<?php elseif ($role === 'Admin'): ?>
<div class="d-flex justify-content-end mb-3">
    <a href="/zen/reqHub/admin" class="btn btn-lg btn-primary">
        Admin Settings
    </a>
</div>
<?php endif; ?>

<ul class="nav nav-tabs">
<?php foreach (['pending','approved','denied','served'] as $tab): ?>
<li class="nav-item">
    <a class="nav-link <?= $status === $tab ? 'active' : '' ?>" 
       href="?status=<?= $tab ?>">
        <?= ucfirst($tab) ?>
    </a>
</li>
<?php endforeach; ?>
</ul>

<!-- Sub-tabs for Pending -->
<?php if ($status === 'pending'): ?>
<ul class="nav nav-tabs mt-2 ms-3" style="border-bottom: 2px solid #dee2e6;">
    <li class="nav-item">
        <a class="nav-link <?= $pending_tab === 'all' ? 'active' : '' ?>" 
           href="?status=pending&pending_tab=all">
            All Pending
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $pending_tab === 'needs_revision' ? 'active' : '' ?>" 
           href="?status=pending&pending_tab=needs_revision">
            Needs Revision
        </a>
    </li>
</ul>
<?php endif; ?>

<table class="table table-hover mt-3">
<thead>
<tr>
    <th>System</th>
    <th>Access For</th>
    <th>Role</th>
    <th>Status</th>
</tr>
</thead>
<tbody>

<?php if (empty($requests)): ?>
<tr>
    <td colspan="4" class="text-center text-muted">
        No requests found.
    </td>
</tr>
<?php endif; ?>

<?php foreach ($requests as $req): ?>

<tr class="request-row" style="cursor:pointer"
    data-id="<?= $req['id'] ?>"
    data-name="<?= htmlspecialchars($req['requestor_name'] ?? '') ?>"
    data-system="<?= htmlspecialchars($req['system_name'] ?? '') ?>"
    data-access="<?= htmlspecialchars($req['access_type'] ?? '') ?>"
    data-chosen-role="<?= htmlspecialchars($req['chosen_role'] ?? '') ?>"
    data-remove="<?= htmlspecialchars($req['remove_from'] ?: 'New Request') ?>"
    data-description="<?= htmlspecialchars($req['description'] ?? '') ?>"
    data-status="<?= $req['status'] ?>"
    data-admin-status="<?= htmlspecialchars($req['admin_status'] ?? '') ?>"
>
    <td><?= htmlspecialchars($req['system_name'] ?? '') ?></td>
    <td><?= htmlspecialchars($req['requestor_name'] ?? '') ?></td>
    <td><?= htmlspecialchars($req['chosen_role'] ?? '(Not specified)') ?></td>
    <td>
        <?php if ($req['status'] === 'needs_revision'): ?>
            <span class="badge bg-warning text-dark">Needs revision!</span>
        <?php else: ?>
            <?= ucfirst(
                ($req['admin_status'] ?? '') === 'served'
                    ? 'Served'
                    : ($req['status'] ?? '')
            ) ?>
        <?php endif; ?>
    </td>
</tr>

<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- MODAL -->
<div class="modal fade" id="requestModal" tabindex="-1" data-bs-backdrop="static">
<div class="modal-dialog modal-xl">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="modalTitle">Request Details</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">

<div class="row">
<div class="col-md-6 d-flex flex-column">

<p><strong>Requestor:</strong><br><span id="modalName"></span></p>
<p><strong>System:</strong><br><span id="modalSystem"></span></p>

<p><strong>Role:</strong><br><span id="modalRole" style="color: #333; font-weight: bold;"></span></p>

<p><strong>Remove From:</strong><br><span id="modalRemove"></span></p>

<p><strong>Access Type:</strong><br>
<div id="modalAccess" class="small" style="
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px;
    background-color: #f8f9fa;
"></div>
</p>

</div>

<div class="col-md-6 border-start">
<h6>Chat</h6>
<div id="chatBox" style="height:430px; overflow-y:auto;"
     class="border p-2 mb-2"></div>

<form id="chatForm">
<input type="hidden" name="request_id" id="chatRequestId">
<div class="input-group">
<input type="text" name="message" class="form-control"
       placeholder="Type message..." required id="chatInput" autocomplete="off">
<button class="btn btn-primary" type="submit" id="chatSubmitBtn">Send</button>
</div>
</form>

<p class="mt-2 mb-1 pt-2 border-top mb-3"><strong>Description:</strong></p>
<div id="modalDescription"
     style="flex:1;min-height:100px;max-height:250px;overflow-y:auto;
            white-space:pre-wrap;border:1px solid #ddd;
            padding:8px;border-radius:4px;background:#f8f9fa;">
</div>

<div id="modalActions" class="mt-3 pt-2 border-top mb-3"></div>

</div>
</div>
</div>

</div>
</div>
</div>

<!-- REVISE MODAL (for Approver) -->
<div class="modal fade" id="reviseModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Request Revision</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<form id="reviseForm">
<input type="hidden" name="request_id" id="reviseRequestId">
<div class="mb-3">
<label class="form-label">Reason for Revision:</label>
<textarea name="revision_message" class="form-control" rows="6" required placeholder="Explain what needs to be revised..."></textarea>
</div>
<div class="d-flex gap-2">
<button type="submit" class="btn btn-primary">Send to Revise</button>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</div>
</form>
</div>
</div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
// Define global variables for modals
let modal;
let reviseModal;

// Define helper functions FIRST, before they're referenced
function openReviseModal(requestId) {
    document.getElementById('reviseRequestId').value = requestId;
    document.getElementById('reviseForm').reset();
    reviseModal.show();
}

// NOW run the main code
document.addEventListener('DOMContentLoaded', function () {

    // Initialize modals as global variables
    modal = new bootstrap.Modal(document.getElementById('requestModal'));
    reviseModal = new bootstrap.Modal(document.getElementById('reviseModal'));
    let chatInterval = null;

    document.querySelectorAll('.request-row').forEach(row => {
        row.addEventListener('click', function() {

            document.getElementById('modalName').textContent = this.dataset.name;
            document.getElementById('modalSystem').textContent = this.dataset.system;
            document.getElementById('modalRole').textContent = this.dataset.chosenRole || '(Not specified)';
            document.getElementById('modalRemove').textContent = this.dataset.remove;
            document.getElementById('modalDescription').textContent = this.dataset.description;
            document.getElementById('chatRequestId').value = this.dataset.id;

            renderAccessStructure(this.dataset.access, this.dataset.chosenRole);
            renderActions(this.dataset);

            loadChat(this.dataset.id);

            if (chatInterval) clearInterval(chatInterval);
            chatInterval = setInterval(() => {
                loadChat(this.dataset.id);
            }, 5000);

            modal.show();
        });
    });

    function renderAccessStructure(raw, chosenRole) {
        const container = document.getElementById('modalAccess');
        container.innerHTML = '';
        if (!raw) {
            container.innerHTML = '<em>No access selected</em>';
            return;
        }

        let grouped = {};
        const entries = raw.split('##');

        entries.forEach(entry => {
            const parts = entry.split('||');
            if (parts.length !== 3) return;

            const role = parts[0];
            const module = parts[1];
            const action = parts[2];

            if (!grouped[module]) grouped[module] = {};
            if (!grouped[module][action]) {
                grouped[module][action] = {
                    role: role,
                    isFromChosenRole: role === chosenRole
                };
            }
        });

        // Create accordion HTML with collapsible sections by Module
        let html = '<div class="accordion" id="accessAccordion">';
        let accordionIndex = 0;
        
        for (let module in grouped) {
            // Check if ANY action in this module is manually added
            let hasManuallyAdded = false;
            for (let action in grouped[module]) {
                if (!grouped[module][action].isFromChosenRole) {
                    hasManuallyAdded = true;
                    break;
                }
            }
            
            const moduleColor = hasManuallyAdded ? '#0d6efd' : '#000';
            const moduleFontWeight = hasManuallyAdded ? 'bold' : 'normal';
            
            html += `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-accordion-toggle="${accordionIndex}">
                        <strong style="color: ${moduleColor}; font-weight: ${moduleFontWeight};">${module}</strong>
                    </button>
                </h2>
                <div id="accordion${accordionIndex}" class="accordion-collapse collapse" style="display:none;">
                    <div class="accordion-body p-2">
            `;
            
            for (let action in grouped[module]) {
                const isFromChosenRole = grouped[module][action].isFromChosenRole;
                const role = grouped[module][action].role;
                const color = isFromChosenRole ? '#000' : '#0d6efd';
                const fontWeight = isFromChosenRole ? 'normal' : 'bold';
                
                html += `
                <div class="ms-2 mb-2">
                    <span style="color: ${color}; font-weight: ${fontWeight};">• ${action}</span>
                    <span style="font-size: 0.75rem; color: #666;">(${role})</span>
                </div>
                `;
            }
            
            html += `
                    </div>
                </div>
            </div>
            `;
            accordionIndex++;
        }
        
        html += '</div>';
        container.innerHTML = html;
        
        // Add manual click handlers for collapse functionality
        document.querySelectorAll('[data-accordion-toggle]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = 'accordion' + this.getAttribute('data-accordion-toggle');
                const targetDiv = document.getElementById(targetId);
                const isHidden = targetDiv.style.display === 'none';
                
                // Close all other accordions
                document.querySelectorAll('[data-accordion-toggle]').forEach(otherBtn => {
                    const otherId = 'accordion' + otherBtn.getAttribute('data-accordion-toggle');
                    const otherDiv = document.getElementById(otherId);
                    otherDiv.style.display = 'none';
                    otherBtn.classList.add('collapsed');
                });
                
                // Toggle current accordion
                if (isHidden) {
                    targetDiv.style.display = 'block';
                    this.classList.remove('collapsed');
                } else {
                    targetDiv.style.display = 'none';
                    this.classList.add('collapsed');
                }
            });
        });
    }

    function renderActions(data) {
        const container = document.getElementById('modalActions');
        container.innerHTML = '';
        const role = "<?= $role ?>";
        
        // Disable chat if request is denied or served
        const chatInput = document.getElementById('chatInput');
        const chatSubmitBtn = document.getElementById('chatSubmitBtn');
        
        if (data.status === 'denied' || data.adminStatus === 'served') {
            chatInput.disabled = true;
            chatSubmitBtn.disabled = true;
            chatInput.placeholder = 'Chat disabled for this request';
        } else {
            chatInput.disabled = false;
            chatSubmitBtn.disabled = false;
            chatInput.placeholder = 'Type message...';
        }
        
        console.log('renderActions called with data:', data);
        console.log('role:', role);
        console.log('data.status:', data.status);
        console.log('data.adminStatus:', data.adminStatus);

        if (role === 'Approver' && data.status === 'pending') {
            console.log('Showing Approver actions for pending');
            container.innerHTML = `
                <form method="post" action="/zen/reqHub/approve" class="d-inline">
                    <input type="hidden" name="id" value="${data.id}">
                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                </form>
                <form method="post" action="/zen/reqHub/deny" class="d-inline ms-2">
                    <input type="hidden" name="id" value="${data.id}">
                    <button type="submit" class="btn btn-danger btn-sm">Deny</button>
                </form>
                <button class="btn btn-warning btn-sm ms-2" onclick="openReviseModal('${data.id}')">Revise</button>`;
        }

        if (role === 'Approver' && data.status === 'needs_revision') {
            console.log('Showing Approver actions for needs_revision');
            container.innerHTML = `
                <form method="post" action="/zen/reqHub/approve" class="d-inline">
                    <input type="hidden" name="id" value="${data.id}">
                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                </form>
                <form method="post" action="/zen/reqHub/deny" class="d-inline ms-2">
                    <input type="hidden" name="id" value="${data.id}">
                    <button type="submit" class="btn btn-danger btn-sm">Deny</button>
                </form>`;
        }

        if (role === 'Requestor' && data.status === 'needs_revision') {
            console.log('Showing Requestor actions for needs_revision');
            container.innerHTML = `
                <a href="/zen/reqHub/request_revise?request_id=${data.id}" class="btn btn-primary btn-sm">Edit & Resubmit</a>`;
        }

        if (role === 'Admin' && data.status === 'approved' && data.adminStatus !== 'served') {
            console.log('Showing Admin actions');
            container.innerHTML = `
                <form method="post" action="/zen/reqHub/served" class="d-inline">
                    <input type="hidden" name="id" value="${data.id}">
                    <button type="submit" class="btn btn-primary btn-sm">Mark as Served</button>
                </form>`;
        }
        
        if (container.innerHTML === '') {
            console.log('No actions available for this user/status combination');
        }
    }

    function openReviseModal(requestId) {
        document.getElementById('reviseRequestId').value = requestId;
        document.getElementById('reviseForm').reset();
        reviseModal.show();
    }

    function loadChat(requestId){
        fetch('/zen/reqHub/chat_fetch?request_id='+requestId)
        .then(res=> {
            console.log('chat_fetch response:', res.status, res.statusText);
            if (!res.ok) {
                console.error('Chat fetch error:', res.status, res.statusText);
                document.getElementById('chatBox').innerHTML = '<div class="alert alert-warning">Unable to load chat (' + res.status + ')</div>';
                throw new Error('Failed to fetch chat: ' + res.status);
            }
            return res.text();
        })
        .then(html=>{
            console.log('Chat HTML received:', html.substring(0, 100));
            const chatBox = document.getElementById('chatBox');
            if (chatBox) {
                chatBox.innerHTML = html;
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        })
        .catch(err => {
            console.error('Chat error:', err);
            const chatBox = document.getElementById('chatBox');
            if (chatBox) {
                chatBox.innerHTML = '<div class="alert alert-danger">Error loading chat: ' + err.message + '</div>';
            }
        });
    }

    document.getElementById('chatForm').addEventListener('submit', function(e){
        e.preventDefault();
        fetch('/zen/reqHub/chat_send',{
            method:'POST',
            body:new FormData(this)
        }).then(()=>{
            loadChat(document.getElementById('chatRequestId').value);
            this.reset();
        });
    });

    document.getElementById('requestModal').addEventListener('hidden.bs.modal', function () {
        if (chatInterval) clearInterval(chatInterval);
    });

    // Handle revise form submission (for approver)
    const reviseForm = document.getElementById('reviseForm');
    if (reviseForm && !reviseForm.hasListener) {
        reviseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const requestId = document.getElementById('reviseRequestId').value;
            const revisionMessage = document.querySelector('[name="revision_message"]').value;
            
            fetch('/zen/reqHub/revise_action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + requestId + '&revision_message=' + encodeURIComponent(revisionMessage)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    reviseModal.hide();
                    modal.hide();
                    alert('Request sent to revision.');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending revision request');
            });
        });
        reviseForm.hasListener = true;
    }

});
</script>