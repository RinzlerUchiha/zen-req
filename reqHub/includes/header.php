<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['reqhub_user']) && !isset($_SESSION['user'])) {
    header("Location: /zen/login");
    exit;
}

$user = $_SESSION['reqhub_user'] ?? $_SESSION['user'];

if (!isset($user['id'])) $user['id'] = $user['user_id'] ?? null;
if (!isset($user['name'])) $user['name'] = $user['name'] ?? null;
if (!isset($user['role'])) $user['role'] = $user['reqhub_role'] ?? null;

$userId = $user['id'] ?? $user['user_id'] ?? null;
$role   = $user['role'] ?? $user['reqhub_role'] ?? null;
$empNo  = $user['emp_no'] ?? null;

require_once (__DIR__ . '/../database/db.php');

// Get the actual users.id from employee_id
$actualUserId = null;
$isAdminDev = false;
try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    $stmt = $pdo->prepare("SELECT id, is_admin_dev FROM users WHERE employee_id = ?");
    $stmt->execute([$empNo]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($userRow) {
        $actualUserId = (int)$userRow['id'];
        $isAdminDev = $userRow['is_admin_dev'] === 'yes';
    }
} catch (Exception $e) {
    error_log("Header: failed to get actualUserId - " . $e->getMessage());
}

// Fetch unread notifications for this user
$notifications = [];
$unreadCount   = 0;

if ($actualUserId) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                n.id,
                n.type,
                n.request_id,
                n.message,
                n.is_read,
                n.created_at,
                r.status        AS request_status,
                r.admin_status  AS admin_status
            FROM notifications n
            LEFT JOIN requests r ON n.request_id = r.id
            WHERE n.user_id = :uid AND n.is_read = 0
            ORDER BY n.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([':uid' => $actualUserId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $unreadCount   = count($notifications);
    } catch (Exception $e) {
        error_log("Header: failed to fetch notifications - " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Access Portal</title>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<style>
body,
.container,
.table,
.table th,
.table td,
.nav-tabs,
.nav-tabs .nav-link,
.modal-content {
    background-color: #e9ecf3 !important;
}
.navbar {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.navbar-brand {
    color: #5d2502 !important;
}
.nav-tabs {
    border-bottom: 1px solid #6c757d !important;
}
.nav-tabs .nav-link {
    border: 1px solid #6c757d !important;
    color: #0d6efd;
    transition: all 0.2s ease;
}
.nav-tabs .nav-link.active {
    background-color: #cfd4da !important;
    border-color: #6c757d #6c757d #cfd4da !important;
    color: #000 !important;
    font-weight: 600;
}
.nav-tabs .nav-link:hover {
    background-color: #dee2e6 !important;
    border-color: #6c757d !important;
    color: #000 !important;
}

.notif-badge {
    position: absolute;
    top: -6px;
    right: -8px;
    background: #dc3545;
    color: #fff;
    border-radius: 50%;
    font-size: 0.7rem;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.notif-item {
    border-bottom: 1px solid #f0f0f0;
    padding: 10px 14px;
    cursor: pointer;
    transition: background 0.15s;
}
.notif-item:hover {
    background-color: #f8f9fa;
}
.notif-item .notif-message {
    font-size: 0.875rem;
    color: #212529;
    margin-bottom: 2px;
}
.notif-item .notif-time {
    font-size: 0.75rem;
    color: #6c757d;
}
.notif-item.unread {
    background-color: #eef4ff;
    border-left: 3px solid #0d6efd;
}
.notif-item.unread:hover {
    background-color: #dceeff;
}
</style>
</head>
<body>

<?php if ($isAdminDev): ?>
    <!-- DEV ROLE SWITCHER -->
    <div class="bg-secondary px-3 py-1 d-flex align-items-center gap-2" style="font-size: 0.8rem;">
        <span class="text-white">Dev Role:</span>
        <select onchange="switchRole(this.value)" class="form-select form-select-sm w-auto">
            <option value="Requestor" <?= $role === 'Requestor' ? 'selected' : '' ?>>Requestor</option>
            <option value="Approver"  <?= $role === 'Approver'  ? 'selected' : '' ?>>Approver</option>
            <option value="Admin"     <?= $role === 'Admin'     ? 'selected' : '' ?>>Admin</option>
            <option value="Reviewer"  <?= $role === 'Reviewer'  ? 'selected' : '' ?>>Reviewer</option>
        </select>
        <span class="text-white-50">Current: <strong class="text-white"><?= htmlspecialchars($role) ?></strong></span>
    </div>

    <script>
    function switchRole(newRole) {
        fetch('/zen/reqHub/role_switch', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'role=' + encodeURIComponent(newRole)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) window.location.reload();
            else alert('Failed: ' + (data.message || 'Unknown error'));
        });
    }
    </script>
<?php endif; ?>

<nav class="navbar navbar-expand-lg navbar-light bg-white px-3">
    <a class="navbar-brand" href="/zen/reqHub/dashboard">Access Portal</a>
    <a class="btn btn-outline-dark btn-sm" href="/zen/dashboard">Return to ZenHub</a>

    <div class="ms-auto d-flex align-items-center gap-3">

        <!-- BELL DROPDOWN -->
        <div class="dropdown">
            <button class="btn btn-outline-dark position-relative"
                    id="notifBellBtn"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="outside"
                    aria-expanded="false">
                🔔
                <?php if ($unreadCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                        <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                    </span>
                <?php endif; ?>
            </button>

            <div class="dropdown-menu dropdown-menu-end p-0"
                style="width: 360px; max-height: 480px; overflow-y: auto;">

                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <strong style="font-size: 0.95rem;">Notifications</strong>
                    <?php if ($unreadCount > 0): ?>
                        <button class="btn btn-link btn-sm p-0 text-primary" id="markAllReadBtn">
                            Mark all as read
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (empty($notifications)): ?>
                    <div class="text-center text-muted py-4" style="font-size: 0.875rem;">
                        No new notifications
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notif-item unread"
                            data-notif-id="<?= $notif['id'] ?>"
                            data-request-id="<?= $notif['request_id'] ?? '' ?>"
                            data-request-status="<?= htmlspecialchars($notif['request_status'] ?? '') ?>"
                            data-admin-status="<?= htmlspecialchars($notif['admin_status'] ?? '') ?>"
                            data-type="<?= htmlspecialchars($notif['type']) ?>">
                            <div class="notif-message"><?= htmlspecialchars($notif['message']) ?></div>
                            <div class="notif-time">
                                <?= date('M d, Y H:i', strtotime($notif['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>

        <div class="text-dark d-flex flex-column align-items-end" style="font-size: 18px; line-height: 1.2;">
            <span>Hello, <?= htmlspecialchars($user['name']) ?></span>
            <span class="text-muted" style="font-size: 1rem;"><?= htmlspecialchars($role) ?></span>
            <?php if (in_array($role, ['Approver', 'Reviewer']) && $actualUserId): ?>
                <?php
                try {
                    $stmtSys = $pdo->prepare("
                        SELECT COALESCE(NULLIF(ts.sys_desc,''), s.name) AS name
                        FROM user_approver_assignments uaa
                        JOIN systems s ON uaa.system_id = s.id
                        LEFT JOIN tngc_hrd2.tbl_systems ts ON LOWER(ts.system_id) = LOWER(s.name)
                        WHERE uaa.user_id = ? AND uaa.system_id IS NOT NULL
                    ");
                    $stmtSys->execute([$actualUserId]);
                    $assignedSystems = $stmtSys->fetchAll(PDO::FETCH_COLUMN);

                    $stmtDept = $pdo->prepare("
                        SELECT d.name
                        FROM user_approver_assignments uaa
                        JOIN departments d ON uaa.department_id = d.id
                        WHERE uaa.user_id = ? AND uaa.department_id IS NOT NULL
                    ");
                    $stmtDept->execute([$actualUserId]);
                    $assignedDepartments = $stmtDept->fetchAll(PDO::FETCH_COLUMN);
                } catch (Exception $e) {
                    $assignedSystems = [];
                }
                ?>

                <?php if (!empty($assignedSystems) || !empty($assignedDepartments)): ?>
                    <div class="mt-1 position-relative d-inline-block">

                        <button
                            id="systemsToggleBtn"
                            class="btn btn-sm btn-outline-secondary py-0 px-2"
                            type="button"
                            style="font-size: 0.75rem;">
                            View Assignments
                        </button>

                        <div
                            id="systemsCard"
                            class="position-absolute bg-white shadow rounded border mt-2"
                            style="display: none; min-width: 220px; z-index: 1050; right: 0;">

                            <?php if (!empty($assignedSystems)): ?>
                            <div class="p-2 border-bottom fw-semibold">
                                Assigned Systems
                            </div>
                            <div class="p-2">
                                <?php foreach ($assignedSystems as $sys): ?>
                                    <div class="py-1 border-bottom small">
                                        <?= htmlspecialchars($sys) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($assignedDepartments)): ?>
                            <div class="p-2 border-bottom fw-semibold">
                                Assigned Departments
                            </div>
                            <div class="p-2">
                                <?php foreach ($assignedDepartments as $dept): ?>
                                    <div class="py-1 border-bottom small">
                                        <?= htmlspecialchars($dept) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                        </div>

                    </div>

                    <script>
                        const btn = document.getElementById('systemsToggleBtn');
                        const card = document.getElementById('systemsCard');

                        btn.addEventListener('click', function (e) {
                            e.stopPropagation();
                            card.style.display = (card.style.display === 'block') ? 'none' : 'block';
                        });

                        document.addEventListener('click', function () {
                            card.style.display = 'none';
                        });
                    </script>

                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bellBtn = document.getElementById('notifBellBtn');
    if (bellBtn) {
        const dropdown = new bootstrap.Dropdown(bellBtn);
        bellBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.toggle();
        });
    }

    function getTabForRequest(requestStatus, adminStatus) {
        if (adminStatus === 'served') return 'served';
        if (requestStatus === 'approved') return 'approved';
        if (requestStatus === 'denied') return 'denied';
        if (requestStatus === 'needs_revision') return 'pending';
        return 'pending';
    }

    function getPendingTabForRequest(requestStatus) {
        if (requestStatus === 'needs_revision') return 'needs_revision';
        return 'all';
    }

    function buildRedirectUrl(requestId, requestStatus, adminStatus) {
        const tab        = getTabForRequest(requestStatus, adminStatus);
        const pendingTab = getPendingTabForRequest(requestStatus);
        let url = '/zen/reqHub/dashboard?status=' + tab + '&open_request=' + requestId;
        if (tab === 'pending') url += '&pending_tab=' + pendingTab;
        return url;
    }

    function markAndRedirect(notifId, requestId, requestStatus, adminStatus) {
        fetch('/zen/reqHub/notification_action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(notifId)
        }).catch(() => {});

        if (requestId) {
            window.location.href = buildRedirectUrl(requestId, requestStatus, adminStatus);
        }
    }

    function attachNotifClickHandler(item) {
        item.addEventListener('click', function () {
            const notifId       = this.dataset.notifId;
            const requestId     = this.dataset.requestId;
            const requestStatus = this.dataset.requestStatus || '';
            const adminStatus   = this.dataset.adminStatus   || '';

            // If we already have status data on the element, redirect immediately
            if (requestStatus || adminStatus) {
                markAndRedirect(notifId, requestId, requestStatus, adminStatus);
                return;
            }

            // Fallback: fetch current status then redirect
            fetch('/zen/reqHub/notification_fetch')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        window.location.href = '/zen/reqHub/dashboard';
                        return;
                    }
                    const match = data.notifications.find(n => n.request_id == requestId);
                    if (match) {
                        markAndRedirect(notifId, requestId, match.request_status || '', match.admin_status || '');
                    } else {
                        // Already read or not found — still redirect to dashboard
                        fetch('/zen/reqHub/notification_action', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'id=' + encodeURIComponent(notifId)
                        }).catch(() => {});
                        window.location.href = '/zen/reqHub/dashboard';
                    }
                })
                .catch(() => { window.location.href = '/zen/reqHub/dashboard'; });
        });
    }

    function attachMarkAllListener(btn) {
        btn.addEventListener('click', function () {
            fetch('/zen/reqHub/notification_action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: ''
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) fetchNotifications();
            })
            .catch(() => {});
        });
    }

    function fetchNotifications() {
        fetch('/zen/reqHub/notification_fetch')
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                const count = data.count;
                const items = data.notifications;

                // Update badge
                let badge = bellBtn ? bellBtn.querySelector('.badge') : null;
                if (count > 0) {
                    if (badge) {
                        badge.textContent = count > 9 ? '9+' : count;
                    } else if (bellBtn) {
                        badge = document.createElement('span');
                        badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                        badge.style.fontSize = '0.65rem';
                        badge.textContent = count > 9 ? '9+' : count;
                        bellBtn.appendChild(badge);
                    }
                } else {
                    if (badge) badge.remove();
                }

                if (!bellBtn) return;
                const menu   = bellBtn.closest('.dropdown').querySelector('.dropdown-menu');
                const header = menu.querySelector('.d-flex.justify-content-between');

                // Remove old notification items and empty state
                menu.querySelectorAll('.notif-item, .text-center.text-muted').forEach(el => el.remove());

                // Mark all button
                let markAllBtn = document.getElementById('markAllReadBtn');
                if (count > 0) {
                    if (!markAllBtn && header) {
                        markAllBtn = document.createElement('button');
                        markAllBtn.className = 'btn btn-link btn-sm p-0 text-primary';
                        markAllBtn.id = 'markAllReadBtn';
                        markAllBtn.textContent = 'Mark all as read';
                        header.appendChild(markAllBtn);
                        attachMarkAllListener(markAllBtn);
                    }
                } else {
                    if (markAllBtn) markAllBtn.remove();
                }

                if (items.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'text-center text-muted py-4';
                    empty.style.fontSize = '0.875rem';
                    empty.textContent = 'No new notifications';
                    menu.appendChild(empty);
                } else {
                    items.forEach(notif => {
                        const div = document.createElement('div');
                        div.className = 'notif-item unread';
                        div.dataset.notifId       = notif.id;
                        div.dataset.requestId     = notif.request_id     || '';
                        div.dataset.requestStatus = notif.request_status || '';
                        div.dataset.adminStatus   = notif.admin_status   || '';
                        div.dataset.type          = notif.type;
                        div.innerHTML = `
                            <div class="notif-message">${notif.message}</div>
                            <div class="notif-time">${notif.created_at_formatted}</div>
                        `;
                        attachNotifClickHandler(div);
                        menu.appendChild(div);
                    });
                }
            })
            .catch(() => {});
    }

    // Attach handlers to server-rendered notifications on initial load
    // (these already have data-request-status and data-admin-status from PHP)
    document.querySelectorAll('.notif-item').forEach(function(item) {
        attachNotifClickHandler(item);
    });

    // Mark all as read (server-rendered button)
    const markAllBtn = document.getElementById('markAllReadBtn');
    if (markAllBtn) attachMarkAllListener(markAllBtn);

    // Poll every 20 seconds
    if (bellBtn) setInterval(fetchNotifications, 20000);
});
</script>