<?php
// Auth is checked by router
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['reqhub_user'])) {
    header('Location: /zen/login');
    exit;
}

require_once (__DIR__ . '/../database/db.php');

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
} catch (Exception $e) {
    die("Database connection error: " . htmlspecialchars($e->getMessage()));
}

$user        = $_SESSION['reqhub_user'];
$userId      = $user['emp_no'];
$debugUserId = $userId;
$role        = $user['reqhub_role'];
$status      = $_GET['status']      ?? 'pending';
$pending_tab = $_GET['pending_tab'] ?? 'all';

/* ================================================================
   BASE QUERY
================================================================ */
$sql = "
SELECT
    r.*,
    COALESCE(NULLIF(ts.sys_desc, ''), s.name) AS system_name,

    COALESCE(
        CONCAT(NULLIF(sub_bi.bi_empfname,''),' ',NULLIF(sub_bi.bi_emplname,'')),
        sub_hu.U_Name,
        sub_u.employee_id
    ) AS submitter_name,

    COALESCE(
        CONCAT(NULLIF(rf_bi.bi_empfname,''),' ',NULLIF(rf_bi.bi_emplname,'')),
        rf_hu.U_Name,
        rf_hu.Emp_No
    ) AS access_for_name,

    CASE
        WHEN r.remove_from REGEXP '^[0-9]+\$' THEN
            COALESCE(
                CONCAT(NULLIF(rm_bi.bi_empfname,''),' ',NULLIF(rm_bi.bi_emplname,'')),
                rm_hu.U_Name,
                r.remove_from
            )
        ELSE r.remove_from
    END AS remove_from_display,

    COALESCE(
        CONCAT(NULLIF(appr_bi.bi_empfname,''),' ',NULLIF(appr_bi.bi_emplname,'')),
        appr_u.employee_id
    ) AS approved_by_name,

    COALESCE(
        CONCAT(NULLIF(srv_bi.bi_empfname,''),' ',NULLIF(srv_bi.bi_emplname,'')),
        srv_u.employee_id
    ) AS served_by_name,

    COALESCE(
        CONCAT(NULLIF(den_bi.bi_empfname,''),' ',NULLIF(den_bi.bi_emplname,'')),
        den_u.employee_id
    ) AS denied_by_name,

    r.denied_at,
    r.approved_at,
    r.served_at,

    GROUP_CONCAT(
        CONCAT(at.role,'||',at.module,'||',at.actions)
        SEPARATOR '##'
    ) AS access_type,

0 AS human_chat_count

FROM requests r
LEFT JOIN systems s ON r.system_id = s.id
LEFT JOIN tngc_hrd2.tbl_systems ts ON LOWER(ts.system_id) = LOWER(s.name)

LEFT JOIN users sub_u ON sub_u.id = r.user_id
LEFT JOIN tngc_hrd2.tbl_user2 sub_hu ON sub_hu.Emp_No = sub_u.employee_id
LEFT JOIN tngc_hrd2.tbl201_basicinfo sub_bi ON sub_hu.Emp_No = sub_bi.bi_empno AND sub_bi.datastat = 'current'

LEFT JOIN tngc_hrd2.tbl_user2 rf_hu ON rf_hu.U_ID = r.request_for
LEFT JOIN tngc_hrd2.tbl201_basicinfo rf_bi ON rf_hu.Emp_No = rf_bi.bi_empno AND rf_bi.datastat = 'current'

LEFT JOIN users appr_u ON appr_u.id = r.approved_by
LEFT JOIN tngc_hrd2.tbl_user2 appr_hu ON appr_hu.Emp_No = appr_u.employee_id
LEFT JOIN tngc_hrd2.tbl201_basicinfo appr_bi ON appr_hu.Emp_No = appr_bi.bi_empno AND appr_bi.datastat = 'current'

LEFT JOIN users srv_u ON srv_u.id = r.served_by
LEFT JOIN tngc_hrd2.tbl_user2 srv_hu ON srv_hu.Emp_No = srv_u.employee_id
LEFT JOIN tngc_hrd2.tbl201_basicinfo srv_bi ON srv_hu.Emp_No = srv_bi.bi_empno AND srv_bi.datastat = 'current'

LEFT JOIN users den_u ON den_u.id = r.denied_by
LEFT JOIN tngc_hrd2.tbl_user2 den_hu ON den_hu.Emp_No = den_u.employee_id
LEFT JOIN tngc_hrd2.tbl201_basicinfo den_bi ON den_hu.Emp_No = den_bi.bi_empno AND den_bi.datastat = 'current'

LEFT JOIN tngc_hrd2.tbl_user2 rm_hu ON rm_hu.U_ID = (
    CASE WHEN r.remove_from REGEXP '^[0-9]+\$' THEN CAST(r.remove_from AS UNSIGNED) ELSE NULL END
)
LEFT JOIN tngc_hrd2.tbl201_basicinfo rm_bi ON rm_hu.Emp_No = rm_bi.bi_empno AND rm_bi.datastat = 'current'

LEFT JOIN request_access_types ra ON r.id = ra.request_id
LEFT JOIN access_types at ON ra.access_type_id = at.id
WHERE 1=1
";

/* ================================================================
   STATUS FILTER
================================================================ */
switch ($status) {
    case 'pending':
        if ($role === 'Reviewer') {
            if ($pending_tab === 'needs_revision') {
                $sql .= " AND r.status = 'needs_revision'";
            } else {
                $sql .= " AND r.status = 'pending'";
            }
        } elseif ($role === 'Approver') {
            if ($pending_tab === 'needs_revision') {
                $sql .= " AND r.status = 'needs_revision'";
            } else {
                $sql .= " AND r.status = 'reviewed'";
            }
        } else {
            if ($pending_tab === 'needs_revision') {
                $sql .= " AND r.status = 'needs_revision'";
            } else {
                $sql .= " AND r.status IN ('pending','reviewed')";
            }
        }
        break;

    case 'approved':
        $sql .= " AND r.status = 'approved' AND (r.admin_status = 'pending' OR r.admin_status IS NULL)";
        break;

    case 'denied':
        $sql .= " AND r.status = 'denied'";
        break;

    case 'served':
        $sql .= " AND r.admin_status = 'served'";
        break;

    default:
        $status = 'pending';
        if ($role === 'Approver') {
            $sql .= " AND r.status = 'reviewed'";
        } else {
            $sql .= " AND r.status IN ('pending','reviewed')";
        }
}

/* ================================================================
   ROLE FILTER
================================================================ */
$params = [];

$stmt_userLookup = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
$stmt_userLookup->execute([$userId]);
$userRecord = $stmt_userLookup->fetch(PDO::FETCH_ASSOC);

if ($userRecord) {
    $actual_user_id = $userRecord['id'];

    if ($role === 'Requestor') {
        $sql .= " AND r.user_id = ?";
        $params[] = $actual_user_id;
    }

    if ($role === 'Approver') {
        $stmt2 = $pdo->prepare("SELECT DISTINCT system_id FROM user_approver_assignments WHERE user_id = ?");
        $stmt2->execute([$actual_user_id]);
        $systemIds = $stmt2->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($systemIds)) {
            $placeholders = implode(',', array_fill(0, count($systemIds), '?'));
            $sql .= " AND r.system_id IN ($placeholders)";
            foreach ($systemIds as $sid) $params[] = $sid;
        } else {
            $sql .= " AND 1=0";
        }
    }

    if ($role === 'Reviewer') {
        // Reviewer sees ALL pending (pre-review) requests
    }
}

$sql .= " GROUP BY r.id ORDER BY r.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($requests)) {
        $reqIds = array_column($requests, 'id');
        $ph = implode(',', array_fill(0, count($reqIds), '?'));
        // Get current user's actual DB id for view tracking
        $viewUserStmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
        $viewUserStmt->execute([$userId]);
        $viewUserRow = $viewUserStmt->fetch(PDO::FETCH_ASSOC);
        $currentDbUserId = $viewUserRow ? (int)$viewUserRow['id'] : 0;

        $chatStmt = $pdo->prepare("
            SELECT rc.request_id
            FROM request_chats rc
            JOIN users ru ON rc.sender_id = ru.id
            WHERE rc.request_id IN ($ph)
            AND rc.sender_id != 1
            AND ru.employee_id != ?
            AND rc.created_at > COALESCE(
                (
                    SELECT last_viewed_at
                    FROM request_chat_views
                    WHERE request_id = rc.request_id
                    AND user_id = ?
                ),
                '1970-01-01'
            )
            GROUP BY rc.request_id
        ");
        $chatParams = array_merge($reqIds, [$userId, $currentDbUserId]);
        $chatStmt->execute($chatParams);
        $activeIds = array_flip($chatStmt->fetchAll(PDO::FETCH_COLUMN));

        foreach ($requests as &$req) {
            $req['human_chat_count'] = isset($activeIds[$req['id']]) ? 1 : 0;
        }
        unset($req);
    }
} catch (PDOException $e) {
    error_log("Dashboard SQL error: " . $e->getMessage());
    die("<h1>Database Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">

<?php if (in_array($role, ['Requestor', 'Approver', 'Reviewer'])): ?>
<div class="d-flex justify-content-end mb-3">
    <a href="/zen/reqHub/request" class="btn btn-lg btn-success">+ Create Request</a>
</div>
<?php elseif ($role === 'Admin'): ?>
<div class="d-flex justify-content-end mb-3">
    <a href="/zen/reqHub/admin" class="btn btn-lg btn-primary">Admin Settings</a>
</div>
<?php endif; ?>

<!-- Main tabs -->
<ul class="nav nav-tabs">
<?php foreach (['pending', 'approved', 'denied', 'served'] as $tab): ?>
<li class="nav-item">
    <a class="nav-link <?= $status === $tab ? 'active' : '' ?>" href="?status=<?= $tab ?>">
        <?= ucfirst($tab) ?>
        <?php if ($tab === 'pending' && $role === 'Reviewer'): ?>
            <small class="text-muted">(to sign)</small>
        <?php endif; ?>
    </a>
</li>
<?php endforeach; ?>
</ul>

<!-- Sub-tabs for Pending -->
<?php if ($status === 'pending'): ?>
<ul class="nav nav-tabs mt-2 ms-3" style="border-bottom:2px solid #dee2e6;">
    <li class="nav-item">
        <a class="nav-link <?= $pending_tab === 'all' ? 'active' : '' ?>"
           href="?status=pending&pending_tab=all">
            <?php if ($role === 'Reviewer'): ?>Awaiting Signature<?php
            elseif ($role === 'Approver'): ?>All Pending<?php
            else: ?>All Pending<?php endif; ?>
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
<tr><td colspan="4" class="text-center text-muted">No requests found.</td></tr>
<?php endif; ?>

<?php foreach ($requests as $req): ?>
<tr class="request-row" style="cursor:pointer"
    data-id="<?= $req['id'] ?>"
    data-submitter="<?= htmlspecialchars($req['submitter_name'] ?? '') ?>"
    data-access-for="<?= htmlspecialchars($req['access_for_name'] ?? '') ?>"
    data-system="<?= htmlspecialchars($req['system_name'] ?? '') ?>"
    data-access="<?= htmlspecialchars($req['access_type'] ?? '') ?>"
    data-chosen-role="<?= htmlspecialchars($req['chosen_role'] ?? '') ?>"
    data-remove="<?= htmlspecialchars($req['remove_from_display'] ?? '') ?>"
    data-description="<?= htmlspecialchars($req['description'] ?? '') ?>"
    data-status="<?= $req['status'] ?>"
    data-admin-status="<?= htmlspecialchars($req['admin_status'] ?? '') ?>"
    data-approved-by="<?= htmlspecialchars($req['approved_by_name'] ?? '') ?>"
    data-approved-at="<?= htmlspecialchars($req['approved_at'] ?? '') ?>"
    data-served-by="<?= htmlspecialchars($req['served_by_name'] ?? '') ?>"
    data-served-at="<?= htmlspecialchars($req['served_at'] ?? '') ?>"
    data-has-chat="<?= ($req['human_chat_count'] ?? 0) > 0 ? '1' : '0' ?>"
    data-denied-by="<?= htmlspecialchars($req['denied_by_name'] ?? '') ?>"
    data-denied-at="<?= htmlspecialchars($req['denied_at'] ?? '') ?>"
>
    <td>
        <div class="d-flex align-items-center gap-2">
            <?php if (($req['human_chat_count'] ?? 0) > 0): ?>
                <span class="chat-dot" style="width:8px; height:8px; border-radius:50%; background-color:#0d6efd; flex-shrink:0; display:inline-block;" title="Has chat activity"></span>
            <?php else: ?>
                <span style="width:8px; height:8px; flex-shrink:0; display:inline-block;"></span>
            <?php endif; ?>
            <?= htmlspecialchars($req['system_name'] ?? '') ?>
        </div>
    </td>
    <td><?= htmlspecialchars($req['access_for_name'] ?? '') ?></td>
    <td><?= htmlspecialchars($req['chosen_role'] ?? '(Not specified)') ?></td>
    <td>
        <?php if ($req['status'] === 'needs_revision'): ?>
            <span class="badge bg-warning text-dark">Needs revision!</span>
        <?php elseif ($req['status'] === 'reviewed'): ?>
            <span class="badge bg-info text-dark">Reviewed</span>
        <?php elseif (($req['admin_status'] ?? '') === 'served'): ?>
            Served
        <?php else: ?>
            <?= ucfirst($req['status'] ?? '') ?>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- REQUEST DETAIL MODAL -->
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
    <p><strong>Requestor:</strong><br><span id="modalSubmitter"></span></p>
    <p><strong>Access For:</strong><br><span id="modalAccessFor"></span></p>
    <p><strong>System:</strong><br><span id="modalSystem"></span></p>
    <p><strong>Role:</strong><br><span id="modalRole" style="color:#333; font-weight:bold;"></span></p>
    <p><strong>Remove From:</strong><br><span id="modalRemove"></span></p>
    <p id="modalApprovedByRow" style="display:none;">
        <strong>Approved by:</strong><br><span id="modalApprovedBy"></span>
        <span id="modalApprovedAt" class="text-muted small ms-1"></span>
    </p>
    <p id="modalServedByRow" style="display:none;">
        <strong>Served by:</strong><br><span id="modalServedBy"></span>
        <span id="modalServedAt" class="text-muted small ms-1"></span>
    </p>
    <p id="modalDeniedByRow" style="display:none;">
        <strong>Denied by:</strong><br><span id="modalDeniedBy"></span>
        <span id="modalDeniedAt" class="text-muted small ms-1"></span>
    </p>
    <p><strong>Access Type:</strong></p>
    <div id="modalAccess" class="small" style="max-height:400px; overflow-y:auto; border:1px solid #ddd; border-radius:4px; padding:8px; background:#f8f9fa;"></div>
</div>

<div class="col-md-6 border-start">
    <h6>Chat</h6>
    <div id="chatBox" style="height:430px; overflow-y:auto;" class="border p-2 mb-2"></div>
    <form id="chatForm">
        <input type="hidden" name="request_id" id="chatRequestId">
        <div class="input-group">
            <input type="text" name="message" class="form-control" placeholder="Type message..." required id="chatInput" autocomplete="off">
            <button class="btn btn-primary" type="submit" id="chatSubmitBtn">Send</button>
        </div>
    </form>
    <p class="mt-2 mb-1 pt-2 border-top"><strong>Description:</strong></p>
    <div id="modalDescription" style="min-height:100px; max-height:250px; overflow-y:auto; white-space:pre-wrap; border:1px solid #ddd; padding:8px; border-radius:4px; background:#f8f9fa;"></div>
    <div id="modalActions" class="mt-3 pt-2 border-top mb-3"></div>
</div>
</div>
</div>
</div>
</div>
</div>

<!-- REVISE MODAL (Approver) -->
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
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
let modal, reviseModal;

function openReviseModal(requestId) {
    document.getElementById('reviseRequestId').value = requestId;
    document.getElementById('reviseForm').reset();
    reviseModal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    modal       = new bootstrap.Modal(document.getElementById('requestModal'));
    reviseModal = new bootstrap.Modal(document.getElementById('reviseModal'));
    let chatInterval = null;
    const openedRequests = new Set();

    // ── Row click handler ──────────────────────────────────────────
    function openRequestRow(row) {
        // Clear chat dot when request is opened
        const dot = row.querySelector('.chat-dot');
        if (dot) dot.style.display = 'none';
        openedRequests.add(row.dataset.id);

        if (row.dataset.status !== 'denied' && row.dataset.adminStatus !== 'served') {
            fetch('/zen/reqHub/chat_view', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'request_id=' + encodeURIComponent(row.dataset.id)
            }).catch(() => {});
        }

        document.getElementById('modalSubmitter').textContent   = row.dataset.submitter  || '—';
        document.getElementById('modalAccessFor').textContent   = row.dataset.accessFor  || '—';
        document.getElementById('modalSystem').textContent      = row.dataset.system;
        document.getElementById('modalRole').textContent        = row.dataset.chosenRole || '(Not specified)';
        document.getElementById('modalDescription').textContent = row.dataset.description;
        document.getElementById('chatRequestId').value          = row.dataset.id;
        document.getElementById('modalRemove').textContent      = row.dataset.remove || '—';

        const approvedByRow = document.getElementById('modalApprovedByRow');
        if (row.dataset.approvedBy) {
            document.getElementById('modalApprovedBy').textContent = row.dataset.approvedBy;
            document.getElementById('modalApprovedAt').textContent = row.dataset.approvedAt ? '(' + formatDateTime(row.dataset.approvedAt) + ')' : '';
            approvedByRow.style.display = '';
        } else {
            approvedByRow.style.display = 'none';
        }

        const servedByRow = document.getElementById('modalServedByRow');
        if (row.dataset.servedBy) {
            document.getElementById('modalServedBy').textContent = row.dataset.servedBy;
            document.getElementById('modalServedAt').textContent = row.dataset.servedAt ? '(' + formatDateTime(row.dataset.servedAt) + ')' : '';
            servedByRow.style.display = '';
        } else {
            servedByRow.style.display = 'none';
        }

        const deniedByRow = document.getElementById('modalDeniedByRow');
        if (row.dataset.deniedBy) {
            document.getElementById('modalDeniedBy').textContent = row.dataset.deniedBy;
            document.getElementById('modalDeniedAt').textContent = row.dataset.deniedAt ? '(' + formatDateTime(row.dataset.deniedAt) + ')' : '';
            deniedByRow.style.display = '';
        } else {
            deniedByRow.style.display = 'none';
        }

        renderAccessStructure(row.dataset.access, row.dataset.chosenRole);
        renderActions(row.dataset);
        loadChat(row.dataset.id);

        if (chatInterval) clearInterval(chatInterval);
        chatInterval = setInterval(() => loadChat(row.dataset.id), 5000);

        modal.show();
    }

    document.querySelectorAll('.request-row').forEach(row => {
        row.addEventListener('click', function () {
            openRequestRow(this);
        });
    });

    // ── Auto-open from URL param (notification redirect) ──────────
    const urlParams  = new URLSearchParams(window.location.search);
    const openId     = urlParams.get('open_request');
    if (openId) {
        const targetRow = document.querySelector(`.request-row[data-id="${openId}"]`);
        if (targetRow) {
            // Small delay to allow modal/bootstrap to be fully ready
            setTimeout(() => {
                targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                openRequestRow(targetRow);
            }, 150);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────
    function formatDateTime(dt) {
        if (!dt) return '';
        const d = new Date(dt);
        if (isNaN(d)) return dt;
        return d.toLocaleString('en-PH', { dateStyle: 'medium', timeStyle: 'short' });
    }

    function renderAccessStructure(raw, chosenRole) {
        const container = document.getElementById('modalAccess');
        container.innerHTML = '';
        if (!raw) { container.innerHTML = '<em>No access selected</em>'; return; }

        let grouped = {};
        raw.split('##').forEach(entry => {
            const parts = entry.split('||');
            if (parts.length !== 3) return;
            const [role, module, action] = parts;
            if (!grouped[module]) grouped[module] = {};
            if (!grouped[module][action]) grouped[module][action] = { role, isFromChosenRole: role === chosenRole };
        });

        let html = '<div class="accordion" id="accessAccordion">';
        let idx = 0;
        for (let module in grouped) {
            let hasManual = Object.values(grouped[module]).some(v => !v.isFromChosenRole);
            const mColor  = hasManual ? '#0d6efd' : '#000';
            const mWeight = hasManual ? 'bold' : 'normal';
            html += `<div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-accordion-toggle="${idx}"><strong style="color:${mColor}; font-weight:${mWeight};">${module}</strong></button></h2><div id="accordion${idx}" class="accordion-collapse collapse" style="display:none;"><div class="accordion-body p-2">`;
            for (let action in grouped[module]) {
                const { role, isFromChosenRole } = grouped[module][action];
                const color  = isFromChosenRole ? '#000' : '#0d6efd';
                const weight = isFromChosenRole ? 'normal' : 'bold';
                html += `<div class="ms-2 mb-2"><span style="color:${color}; font-weight:${weight};">• ${action}</span> <span style="font-size:0.75rem; color:#666;">(${role})</span></div>`;
            }
            html += '</div></div></div>';
            idx++;
        }
        html += '</div>';
        container.innerHTML = html;

        document.querySelectorAll('[data-accordion-toggle]').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId  = 'accordion' + this.getAttribute('data-accordion-toggle');
                const targetDiv = document.getElementById(targetId);
                const isHidden  = targetDiv.style.display === 'none';

                document.querySelectorAll('[data-accordion-toggle]').forEach(otherBtn => {
                    document.getElementById('accordion' + otherBtn.getAttribute('data-accordion-toggle')).style.display = 'none';
                    otherBtn.classList.add('collapsed');
                });

                if (isHidden) { targetDiv.style.display = 'block'; this.classList.remove('collapsed'); }
                else { targetDiv.style.display = 'none'; this.classList.add('collapsed'); }
            });
        });
    }

    function renderActions(data) {
        const container     = document.getElementById('modalActions');
        container.innerHTML = '';
        const role = "<?= $role ?>";

        const chatInput     = document.getElementById('chatInput');
        const chatSubmitBtn = document.getElementById('chatSubmitBtn');

        if (data.status === 'denied' || data.adminStatus === 'served') {
            chatInput.disabled = chatSubmitBtn.disabled = true;
            chatInput.placeholder = 'Chat disabled for this request';
        } else {
            chatInput.disabled = chatSubmitBtn.disabled = false;
            chatInput.placeholder = 'Type message...';
        }

        if (role === 'Reviewer' && (data.status === 'pending' || data.status === 'needs_revision')) {
            container.innerHTML = `
                <form id="reviewActionForm" class="d-inline">
                    <input type="hidden" name="id" value="${data.id}">
                    <button type="submit" class="btn btn-success btn-sm">✓ Sign & Send to Approver</button>
                </form>
                <form method="post" action="/zen/reqHub/deny" class="d-inline ms-2">
                    <input type="hidden" name="id" value="${data.id}">
                    <button type="submit" class="btn btn-danger btn-sm">Deny</button>
                </form>
                ${data.status === 'pending' ? `
                <button class="btn btn-warning btn-sm ms-2" onclick="openReviseModal('${data.id}')">Revise</button>` : ''}`;

            document.getElementById('reviewActionForm').addEventListener('submit', function(e) {
                e.preventDefault();
                fetch('/zen/reqHub/review', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + data.id
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        modal.hide();
                        alert('Request signed and sent to approver.');
                        location.reload();
                    } else {
                        alert('Error: ' + (res.message || 'Unknown error'));
                    }
                })
                .catch(() => alert('Network error'));
            });
        }

        if (role === 'Approver' && (data.status === 'reviewed' || data.status === 'needs_revision')) {
            container.innerHTML = `
                <form method="post" action="/zen/reqHub/approve" class="d-inline">
                    <input type="hidden" name="id" value="${data.id}">
                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                </form>
                <form method="post" action="/zen/reqHub/deny" class="d-inline ms-2">
                    <input type="hidden" name="id" value="${data.id}">
                    <button type="submit" class="btn btn-danger btn-sm">Deny</button>
                </form>
                ${data.status === 'reviewed' ? `<button class="btn btn-warning btn-sm ms-2" onclick="openReviseModal('${data.id}')">Revise</button>` : ''}`;
        }

        if (role === 'Requestor' && data.status === 'needs_revision') {
            container.innerHTML = `<a href="/zen/reqHub/request_revise?request_id=${data.id}" class="btn btn-primary btn-sm">Edit & Resubmit</a>`;
        }

        if (role === 'Admin' && data.status === 'approved' && data.adminStatus !== 'served') {
            container.innerHTML = `
                <form method="post" action="/zen/reqHub/served" class="d-inline">
                    <input type="hidden" name="id" value="${data.id}">
                    <button type="submit" class="btn btn-primary btn-sm">Mark as Served</button>
                </form>`;
        }
    }

    function loadChat(requestId) {
        fetch('/zen/reqHub/chat_fetch?request_id=' + requestId)
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.text();
        })
        .then(html => {
            const box = document.getElementById('chatBox');
            if (box) { box.innerHTML = html; box.scrollTop = box.scrollHeight; }
        })
        .catch(err => {
            const box = document.getElementById('chatBox');
            if (box) box.innerHTML = '<div class="alert alert-danger">Error loading chat: ' + err.message + '</div>';
        });
    }

    document.getElementById('chatForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('/zen/reqHub/chat_send', { method: 'POST', body: new FormData(this) })
        .then(() => { loadChat(document.getElementById('chatRequestId').value); this.reset(); });
    });

    document.getElementById('requestModal').addEventListener('hidden.bs.modal', function () {
        if (chatInterval) clearInterval(chatInterval);
    });

    const reviseForm = document.getElementById('reviseForm');
    if (reviseForm && !reviseForm.hasListener) {
        reviseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const requestId       = document.getElementById('reviseRequestId').value;
            const revisionMessage = document.querySelector('[name="revision_message"]').value;

            fetch('/zen/reqHub/revise_action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + requestId + '&revision_message=' + encodeURIComponent(revisionMessage)
            })
            .then(r => r.json())
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
            .catch(() => alert('Network error'));
        });
        reviseForm.hasListener = true;
    }

    // ── Chat activity dots polling ─────────────────────────────────
    const allRows = document.querySelectorAll('.request-row');
    const rowIds  = Array.from(allRows).map(r => r.dataset.id).filter(Boolean);

    if (rowIds.length > 0) {
        function pollChatActivity() {
            fetch('/zen/reqHub/chat_activity_fetch?ids=' + rowIds.join(','))
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    allRows.forEach(row => {
                        const dot = row.querySelector('.chat-dot');
                        if (!dot) return;
                        if (openedRequests.has(row.dataset.id)) return;
                        if (data.active.includes(parseInt(row.dataset.id))) {
                            dot.style.visibility = 'visible';
                        } else {
                            dot.style.visibility = 'hidden';
                        }
                    });
                })
                .catch(() => {});
        }

        pollChatActivity();
        setInterval(pollChatActivity, 15000);
    }
});
</script>