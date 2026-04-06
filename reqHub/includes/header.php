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
try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    $stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
    $stmt->execute([$empNo]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($userRow) $actualUserId = (int)$userRow['id'];
} catch (Exception $e) {
    error_log("Header: failed to get actualUserId - " . $e->getMessage());
}

// Fetch unread notifications for this user
$notifications  = [];
$unreadCount    = 0;

if ($actualUserId) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, type, request_id, message, is_read, created_at
            FROM notifications
            WHERE user_id = :uid AND is_read = 0
            ORDER BY created_at DESC
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

/* Notification bell badge */
/* .notif-bell {
    position: relative;
    display: inline-block;
} */
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

/* Notification dropdown items */
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

<!-- DEV ROLE SWITCHER -->
<div class="bg-secondary px-3 py-1 d-flex align-items-center gap-2" style="font-size: 0.8rem;">
    <span class="text-white">Dev Role:</span>
    <select onchange="switchRole(this.value)" class="form-select form-select-sm w-auto">
        <option value="Requestor" <?= $role === 'Requestor' ? 'selected' : '' ?>>Requestor</option>
        <option value="Approver"  <?= $role === 'Approver'  ? 'selected' : '' ?>>Approver</option>
        <option value="Admin"     <?= $role === 'Admin'     ? 'selected' : '' ?>>Admin</option>
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

        <div class="text-dark">
            Hello, <?= htmlspecialchars($user['name']) ?>
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

    // Mark single notification as read when clicked
    document.querySelectorAll('.notif-item').forEach(function(item) {
        item.addEventListener('click', function() {
            const notifId   = this.dataset.notifId;
            const requestId = this.dataset.requestId;
            const self      = this;

            fetch('/zen/reqHub/notification_action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(notifId)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    self.remove();

                    // Badge is now a Bootstrap badge span inside the button
                    const badge = document.querySelector('#notifBellBtn .badge');
                    if (badge) {
                        let count = parseInt(badge.textContent) || 0;
                        count--;
                        if (count <= 0) {
                            badge.remove();
                        } else {
                            badge.textContent = count > 9 ? '9+' : count;
                        }
                    }

                    const remaining = document.querySelectorAll('.notif-item');
                    if (remaining.length === 0) {
                        const menu = document.querySelector('.dropdown-menu');
                        const empty = document.createElement('div');
                        empty.className = 'text-center text-muted py-4';
                        empty.style.fontSize = '0.875rem';
                        empty.textContent = 'No new notifications';
                        menu.appendChild(empty);
                        const markAllBtn = document.getElementById('markAllReadBtn');
                        if (markAllBtn) markAllBtn.remove();
                    }
                }
            })
            .catch(err => console.error('Mark read error:', err));

            if (requestId) {
                window.location.href = '/zen/reqHub/dashboard';
            }
        });
    });

    // Mark all as read
    const markAllBtn = document.getElementById('markAllReadBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            fetch('/zen/reqHub/notification_action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: ''
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notif-item').forEach(el => el.remove());

                    const badge = document.querySelector('#notifBellBtn .badge');
                    if (badge) badge.remove();

                    markAllBtn.remove();

                    const menu = document.querySelector('.dropdown-menu');
                    const empty = document.createElement('div');
                    empty.className = 'text-center text-muted py-4';
                    empty.style.fontSize = '0.875rem';
                    empty.textContent = 'No new notifications';
                    menu.appendChild(empty);
                }
            })
            .catch(err => console.error('Mark all read error:', err));
        });
    }
});
</script>