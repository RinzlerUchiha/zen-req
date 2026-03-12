<?php
require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../../database/SampleDatabase.php');


$pdo = ReqHubDatabase::getConnection('reqhub');

$user = $_SESSION['user'];
$userId = $user['id'];
$role = $user['role'];
$status = $_GET['status'] ?? 'pending';

/* ================================
   FETCH REQUESTS
================================ */

$sql = "
SELECT 
    r.*,
    s.name AS system_name,
    req_user.name AS requestor_name,
    u1.name AS approved_by_name,
    u2.name AS denied_by_name,
    u3.name AS served_by_name,
    GROUP_CONCAT(
        CONCAT(at.role, '||', at.module, '||', at.actions)
        SEPARATOR '##'
    ) AS access_type
FROM requests r
LEFT JOIN systems s ON r.system_id = s.id
LEFT JOIN users req_user ON r.user_id = req_user.id
LEFT JOIN users u1 ON r.approved_by = u1.id
LEFT JOIN users u2 ON r.denied_by = u2.id
LEFT JOIN users u3 ON r.served_by = u3.id
LEFT JOIN request_access_types ra ON r.id = ra.request_id
LEFT JOIN access_types at ON ra.access_type_id = at.id
WHERE 1=1
";

/* STATUS FILTER */
switch ($status) {
    case 'pending':
        $sql .= " AND r.status = 'pending'";
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
}

/* ROLE FILTER */
$params = [];

if ($role === 'requestor') {
    $sql .= " AND r.user_id = :uid";
    $params[':uid'] = $userId;
}

if ($role === 'approver') {
    $stmt2 = $pdo->prepare("
        SELECT system_id, department_id
        FROM users
        WHERE id = :id
    ");
    $stmt2->execute([':id' => $userId]);
    $approverData = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!empty($approverData['system_id']) && !empty($approverData['department_id'])) {
        $sql .= " AND r.system_id = :system_id 
                  AND r.department_id = :department_id";
        $params[':system_id'] = $approverData['system_id'];
        $params[':department_id'] = $approverData['department_id'];
    }
}

$sql .= " GROUP BY r.id ORDER BY r.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">

<?php if (in_array($role, ['requestor','approver'])): ?>
<div class="d-flex justify-content-end mb-3">
    <a href="request_create.php" class="btn btn-lg btn-success">
        + Create Request
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

<table class="table table-hover mt-3">
<thead>
<tr>
    <th>System</th>
    <th>Name</th>
    <th>Access Type</th>
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

<?php
$roles = [];

if (!empty($req['access_type'])) {
    $entries = explode('##', $req['access_type']);
    foreach ($entries as $entry) {
        $parts = explode('||', $entry);
        if (count($parts) === 3) {
            $roles[$parts[0]] = true;
        }
    }
}
?>

<tr class="request-row" style="cursor:pointer"
    data-id="<?= $req['id'] ?>"
    data-name="<?= htmlspecialchars($req['requestor_name'] ?? '') ?>"
    data-system="<?= htmlspecialchars($req['system_name'] ?? '') ?>"
    data-access="<?= htmlspecialchars($req['access_type'] ?? '') ?>"
    data-remove="<?= htmlspecialchars($req['remove_from'] ?: 'New Request') ?>"
    data-description="<?= htmlspecialchars($req['description'] ?? '') ?>"
    data-status="<?= $req['status'] ?>"
    data-admin-status="<?= htmlspecialchars($req['admin_status'] ?? '') ?>"
>
    <td><?= htmlspecialchars($req['system_name'] ?? '') ?></td>
    <td><?= htmlspecialchars($req['requestor_name'] ?? '') ?></td>
    <td><?= htmlspecialchars(implode(', ', array_keys($roles))) ?></td>
    <td>
        <?= ucfirst(
            ($req['admin_status'] ?? '') === 'served'
                ? 'Served'
                : ($req['status'] ?? '')
        ) ?>
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
<h5 class="modal-title">Request Details</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<div class="row">
<div class="col-md-6 d-flex flex-column">

<p><strong>Name:</strong><br><span id="modalName"></span></p>
<p><strong>System:</strong><br><span id="modalSystem"></span></p>

<p><strong>Access Type:</strong><br>
<div id="modalAccess" class="small"></div>
</p>

<p><strong>Remove From:</strong><br><span id="modalRemove"></span></p>

<p><strong>Description:</strong></p>
<div id="modalDescription"
     style="flex:1;min-height:150px;max-height:250px;overflow-y:auto;
            white-space:pre-wrap;border:1px solid #ddd;
            padding:8px;border-radius:4px;background:#f8f9fa;">
</div>

<div id="modalActions" class="mt-3 pt-2 border-top"></div>

</div>

<div class="col-md-6 border-start">
<h6>Chat</h6>
<div id="chatBox" style="height:350px; overflow-y:auto;"
     class="border p-2 mb-2"></div>

<form id="chatForm">
<input type="hidden" name="request_id" id="chatRequestId">
<div class="input-group">
<input type="text" name="message" class="form-control"
       placeholder="Type message..." required>
<button class="btn btn-primary">Send</button>
</div>
</form>
</div>
</div>
</div>
</div>
</div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = new bootstrap.Modal(document.getElementById('requestModal'));
    let chatInterval = null;

    document.querySelectorAll('.request-row').forEach(row => {
        row.addEventListener('click', function() {

            document.getElementById('modalName').textContent = this.dataset.name;
            document.getElementById('modalSystem').textContent = this.dataset.system;
            document.getElementById('modalRemove').textContent = this.dataset.remove;
            document.getElementById('modalDescription').textContent = this.dataset.description;
            document.getElementById('chatRequestId').value = this.dataset.id;

            renderAccessStructure(this.dataset.access);
            renderActions(this.dataset);

            loadChat(this.dataset.id);

            if (chatInterval) clearInterval(chatInterval);
            chatInterval = setInterval(() => {
                loadChat(this.dataset.id);
            }, 5000);

            modal.show();
        });
    });

    function renderAccessStructure(raw) {
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

            if (!grouped[role]) grouped[role] = {};
            if (!grouped[role][module]) grouped[role][module] = [];
            grouped[role][module].push(action);
        });

        let html = '';
        for (let role in grouped) {
            html += `<strong>Role: ${role}</strong><br>`;
            for (let module in grouped[role]) {
                html += `&nbsp;&nbsp;<u>Module: ${module}</u><br>`;
                html += `&nbsp;&nbsp;&nbsp;&nbsp;Actions: ${grouped[role][module].join(', ')}<br><br>`;
            }
        }
        container.innerHTML = html;
    }

    function renderActions(data) {
        const container = document.getElementById('modalActions');
        container.innerHTML = '';
        const role = "<?= $role ?>";

        if (role==='approver' && data.status==='pending') {
            container.innerHTML = `
                <form method="post" action="../actions/request_approve_action.php" class="d-inline">
                    <input type="hidden" name="id" value="${data.id}">
                    <button class="btn btn-success btn-sm">Approve</button>
                </form>
                <form method="post" action="../actions/request_deny_action.php" class="d-inline ms-2">
                    <input type="hidden" name="id" value="${data.id}">
                    <button class="btn btn-danger btn-sm">Deny</button>
                </form>`;
        }

        if (role==='admin' && data.status==='approved' && data.adminStatus!=='served') {
            container.innerHTML = `
                <form method="post" action="../actions/request_serve_action.php">
                    <input type="hidden" name="id" value="${data.id}">
                    <button class="btn btn-primary btn-sm">Mark as Served</button>
                </form>`;
        }
    }

    function loadChat(requestId){
        fetch('../includes/chat_fetch.php?request_id='+requestId)
        .then(res=>res.text())
        .then(html=>{
            const chatBox = document.getElementById('chatBox');
            chatBox.innerHTML = html;
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    }

    document.getElementById('chatForm').addEventListener('submit', function(e){
        e.preventDefault();
        fetch('../actions/chat_send.php',{
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

});
</script>