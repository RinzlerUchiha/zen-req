<?php

/**
 * Get all user IDs by role
 */
function getUsersByRole(PDO $pdo, string $role): array
{
    $stmt = $pdo->prepare("
        SELECT id 
        FROM users 
        WHERE role = :role
    ");

    $stmt->execute([':role' => $role]);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}


/**
 * Refresh a user's notification based on their role
 * This recalculates what the notification SHOULD be.
 *
 * - If there is something actionable → update/insert notification
 * - If nothing actionable → delete notification row
 */
function refreshNotification(PDO $pdo, int $userId): void
{
    // Get user role and system
    $stmt = $pdo->prepare("
        SELECT reqhub_role, system_assigned 
        FROM users 
        WHERE id = :id
    ");

    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return;
    }

    $role = strtolower($user['reqhub_role'] ?? '');
    $system = $user['system_assigned'] ?? null;

    $message = null;

    /*
    |--------------------------------------------------------------------------
    | APPROVER LOGIC
    |--------------------------------------------------------------------------
    */
    if ($role === 'approver') {

    // Get approver's system_id and department_id
    $stmt = $pdo->prepare("
        SELECT system_id, department_id
        FROM users
        WHERE id = :id
    ");
    $stmt->execute([':id' => $userId]);
    $approver = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($approver) {

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM requests
            WHERE status = 'pending'
              AND system_id = :system_id
              AND department_id = :department_id
        ");

        $stmt->execute([
            ':system_id' => $approver['system_id'],
            ':department_id' => $approver['department_id']
        ]);

        $count = (int)$stmt->fetchColumn();

        if ($count > 0) {
            $message = "You have {$count} pending request(s) that need approval.";
        }
    }
}


    /*
    |--------------------------------------------------------------------------
    | ADMIN LOGIC
    |--------------------------------------------------------------------------
    */
    elseif ($role === 'admin') {

        $stmt = $pdo->query("
            SELECT COUNT(*)
            FROM requests
            WHERE status = 'approved'
              AND admin_status = 'pending'
        ");

        $count = (int) $stmt->fetchColumn();

        if ($count > 0) {
            $message = "You have {$count} approved request(s) waiting to be served.";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REQUESTOR LOGIC
    |--------------------------------------------------------------------------
    */
    elseif ($role === 'requestor') {

        // Check served first (highest priority)
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM requests
            WHERE user_id = :uid
              AND admin_status = 'served'
        ");

        $stmt->execute([':uid' => $userId]);
        $served = (int) $stmt->fetchColumn();

        if ($served > 0) {
            $message = "Your request has been approved and served.";
        } else {

            // Check approved but not yet served
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM requests
                WHERE user_id = :uid
                  AND status = 'approved'
                  AND admin_status = 'pending'
            ");

            $stmt->execute([':uid' => $userId]);
            $approved = (int) $stmt->fetchColumn();

            if ($approved > 0) {
                $message = "Your request has been approved.";
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STORE OR DELETE NOTIFICATION
    |--------------------------------------------------------------------------
    */

    if ($message) {

        // UPSERT (since user_id is PRIMARY KEY)
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, message, is_read)
            VALUES (:uid, :msg, 0)
            ON DUPLICATE KEY UPDATE
                message = VALUES(message),
                is_read = 0
        ");

        $stmt->execute([
            ':uid' => $userId,
            ':msg' => $message
        ]);

    } else {

        // Nothing actionable → remove notification
        $stmt = $pdo->prepare("
            DELETE FROM notifications
            WHERE user_id = :uid
        ");

        $stmt->execute([':uid' => $userId]);
    }
}
?>