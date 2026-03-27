<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Check for reqhub_user (set by auth.php) OR user (legacy)
if (!isset($_SESSION['reqhub_user']) && !isset($_SESSION['user'])) {
    header("Location: /zen/login");
    exit;
}

// Use reqhub_user if available, otherwise use user
$user = $_SESSION['reqhub_user'] ?? $_SESSION['user'];

// Build user array for compatibility
if (!isset($user['id'])) {
    $user['id'] = $user['user_id'] ?? null;
}
if (!isset($user['name'])) {
    $user['name'] = $user['name'] ?? null;
}
if (!isset($user['role'])) {
    $user['role'] = $user['reqhub_role'] ?? null;
}

$userId = $user['id'] ?? $user['user_id'] ?? null;
$role = $user['role'] ?? $user['reqhub_role'] ?? null;

require_once (__DIR__ . '/../database/db.php');

$derivedNotifications = [];

/* =========================
   DERIVED NOTIFICATIONS
========================= */

if ($role === 'Approver' || $role === 'approver') {

    $stmt = $pdo->prepare("
        SELECT system_id, department_id
        FROM users
        WHERE employee_id = :uid OR id = :uid
    ");
    $stmt->execute([':uid' => $user['emp_no'] ?? $userId]);
    $approverData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($approverData['system_id']) && !empty($approverData['department_id'])) {

        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM requests 
            WHERE status = 'pending'
            AND system_id = :system_id
            AND department_id = :department_id
        ");

        $stmt->execute([
            ':system_id' => $approverData['system_id'],
            ':department_id' => $approverData['department_id']
        ]);

        $pendingCount = (int)$stmt->fetchColumn();

        if ($pendingCount > 0) {
            $derivedNotifications[] = [
                'message' => "You have {$pendingCount} pending requests to approve.",
                'link' => 'dashboard.php?status=pending'
            ];
        }
    }
}

if ($role === 'Admin' || $role === 'admin') {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM requests 
        WHERE status = 'approved' 
        AND admin_status = 'pending'
    ");
    $stmt->execute();
    $serveCount = (int)$stmt->fetchColumn();

    if ($serveCount > 0) {
        $derivedNotifications[] = [
            'message' => "You have {$serveCount} requests to serve.",
            'link' => 'dashboard.php?status=approved'
        ];
    }
}

/* =========================
   EVENT NOTIFICATIONS
========================= */

// Try to fetch notifications - table might not exist yet
try {
    $stmt = $pdo->prepare("
        SELECT message, updated_at 
        FROM notifications 
        WHERE user_id = :uid
        ORDER BY updated_at DESC
    ");
    $stmt->execute([':uid' => $userId]);
    $eventNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Notifications table doesn't exist or has different schema
    $eventNotifications = [];
}

$totalCount = count($derivedNotifications) + count($eventNotifications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Access Portal</title>

<!-- jQuery (MUST be loaded first!) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JS (requires jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS (requires jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

</head>
<body>

<!-- DEV ROLE SWITCHER -->
<div class="bg-secondary px-3 py-1 d-flex align-items-center gap-2" style="font-size: 0.8rem;">
    <span class="text-white">Dev Role:</span>
    <select onchange="switchRole(this.value)" class="form-select form-select-sm w-auto">
        <option value="Requestor" <?= $role === 'Requestor' ? 'selected' : '' ?>>Requestor</option>
        <option value="Approver" <?= $role === 'Approver' ? 'selected' : '' ?>>Approver</option>
        <option value="Admin" <?= $role === 'Admin' ? 'selected' : '' ?>>Admin</option>
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

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="/zen/reqHub/dashboard">Access Portal</a>
    <a class="btn btn-outline-light btn-sm" href="/zen/dashboard">Return to ZenHub</a>

    <div class="ms-auto dropdown">
        <button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
            🔔 <?= $totalCount ?>
        </button>

        <ul class="dropdown-menu dropdown-menu-end" style="width: 350px;">

            <?php if ($totalCount === 0): ?>
                <li>
                    <span class="dropdown-item text-muted">
                        No new notifications
                    </span>
                </li>
            <?php endif; ?>

            <?php foreach ($derivedNotifications as $notif): ?>
                <li>
                    <a href="<?= htmlspecialchars($notif['link']) ?>" 
                       class="dropdown-item fw-semibold">
                        <?= htmlspecialchars($notif['message']) ?>
                    </a>
                </li>
            <?php endforeach; ?>

            <?php foreach ($eventNotifications as $notif): ?>
                <li>
                    <span class="dropdown-item">
                        <?= htmlspecialchars($notif['message']) ?>
                        <div class="text-muted small">
                            <?= date('M d, Y H:i', strtotime($notif['updated_at'])) ?>
                        </div>
                    </span>
                </li>
            <?php endforeach; ?>

            <?php if (!empty($eventNotifications)): ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button id="markAllReadBtn" 
                            class="dropdown-item text-center text-primary">
                        Mark all as read
                    </button>
                </li>
            <?php endif; ?>

        </ul>
    </div>

    <div class="ms-3 text-light">
        Hello, <?= htmlspecialchars($user['name']) ?>
    </div>
</nav>