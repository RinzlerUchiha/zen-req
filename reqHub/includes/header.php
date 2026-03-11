<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header("Location: public/login.php");
    exit;
}

require_once __DIR__ . '/db.php';

$user = $_SESSION['user'];
$userId = $user['id'];
$role = $user['role'];

$derivedNotifications = [];

/* =========================
   DERIVED NOTIFICATIONS
========================= */

if ($role === 'approver') {

    $stmt = $pdo->prepare("
        SELECT system_id, department_id
        FROM users
        WHERE id = :uid
    ");
    $stmt->execute([':uid' => $userId]);
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

if ($role === 'admin') {
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

$stmt = $pdo->prepare("
    SELECT message, updated_at 
    FROM notifications 
    WHERE user_id = :uid 
    ORDER BY updated_at DESC
");
$stmt->execute([':uid' => $userId]);
$eventNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalCount = count($derivedNotifications) + count($eventNotifications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Access Portal</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

</head>
<body>

<!-- QUICK ROLE SWITCH (optional testing tool) -->
<select onchange="window.location='login.php?user_id='+this.value;">
    <option value="1" <?= ($userId == 1) ? 'selected' : '' ?>>Requestor User</option>
    <option value="2" <?= ($userId == 2) ? 'selected' : '' ?>>Approver User</option>
    <option value="3" <?= ($userId == 3) ? 'selected' : '' ?>>Admin User</option>
</select>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="/zen/reqHub/public/dashboard.php">Access Portal</a>

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