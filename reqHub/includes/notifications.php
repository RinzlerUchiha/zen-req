<?php
/**
 * Notification Helper
 * File: /zen/reqHub/includes/notifications.php
 */

function createNotification(PDO $pdo, int $userId, string $type, ?int $requestId, string $message): void
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, request_id, message, is_read, created_at)
            VALUES (:user_id, :type, :request_id, :message, 0, NOW())
        ");
        $stmt->execute([
            ':user_id'    => $userId,
            ':type'       => $type,
            ':request_id' => $requestId,
            ':message'    => $message
        ]);
    } catch (Exception $e) {
        error_log("createNotification error: " . $e->getMessage());
    }
}

function notifyApproversForSystem(PDO $pdo, int $systemId, int $requestId): void
{
    // Get all approvers assigned to this system
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id
        FROM users u
        INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
        WHERE uaa.system_id = :system_id
        AND u.reqhub_role = 'Approver'
    ");
    $stmt->execute([':system_id' => $systemId]);
    $approvers = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($approvers as $approverId) {
        createNotification(
            $pdo,
            (int)$approverId,
            'pending_approval',
            $requestId,
            "A new request has been submitted and needs your approval."
        );
    }
}

function notifyAdmins(PDO $pdo, int $requestId, string $message): void
{
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reqhub_role = 'Admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($admins as $adminId) {
        createNotification($pdo, (int)$adminId, 'serve_reminder', $requestId, $message);
    }
}

function notifyChatParticipants(PDO $pdo, int $requestId, int $senderUserId): void
{
    // Get request owner
    $stmt = $pdo->prepare("SELECT user_id, system_id FROM requests WHERE id = :id");
    $stmt->execute([':id' => $requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) return;

    $recipients = [];

    // Add requestor if not the sender
    if ((int)$request['user_id'] !== $senderUserId) {
        $recipients[] = (int)$request['user_id'];
    }

    // Add approvers of the system if not the sender
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id
        FROM users u
        INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
        WHERE uaa.system_id = :system_id
        AND u.reqhub_role = 'Approver'
    ");
    $stmt->execute([':system_id' => $request['system_id']]);
    $approvers = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($approvers as $approverId) {
        if ((int)$approverId !== $senderUserId) {
            $recipients[] = (int)$approverId;
        }
    }

    // Deduplicate and notify
    foreach (array_unique($recipients) as $recipientId) {
        createNotification(
            $pdo,
            $recipientId,
            'chat',
            $requestId,
            "There is a new message on a request."
        );
    }
}
?>