<?php
/**
 * Notification Helper
 * File: /zen/reqHub/includes/notifications.php
 */

function createNotification(PDO $pdo, $userId, $type, $requestId, $message)
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

/**
 * Resolve a display name from tbl201_basicinfo using employee_id (emp_no).
 * Falls back to the raw employee_id string if not found.
 */
function resolveEmployeeName(PDO $pdo, string $empNo): string
{
    try {
        $stmt = $pdo->prepare("
            SELECT CONCAT(COALESCE(NULLIF(bi_empfname,''),''), ' ', COALESCE(NULLIF(bi_emplname,''),''))
            FROM tngc_hrd2.tbl201_basicinfo
            WHERE bi_empno = ? AND datastat = 'current'
            LIMIT 1
        ");
        $stmt->execute([$empNo]);
        $name = trim($stmt->fetchColumn());
        return $name ?: $empNo;
    } catch (Exception $e) {
        error_log("resolveEmployeeName error: " . $e->getMessage());
        return $empNo;
    }
}

/**
 * Resolve a display name from users.id -> employee_id -> tbl201_basicinfo.
 */
function resolveEmployeeNameByUserId(PDO $pdo, int $userId): string
{
    try {
        $stmt = $pdo->prepare("SELECT employee_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $empNo = $stmt->fetchColumn();
        if (!$empNo) return "Unknown";
        return resolveEmployeeName($pdo, $empNo);
    } catch (Exception $e) {
        error_log("resolveEmployeeNameByUserId error: " . $e->getMessage());
        return "Unknown";
    }
}

/**
 * Resolve a system name from systems.id.
 */
function resolveSystemName(PDO $pdo, int $systemId): string
{
    try {
        $stmt = $pdo->prepare("
            SELECT COALESCE(NULLIF(ts.sys_desc, ''), s.name) AS display_name
            FROM systems s
            LEFT JOIN tngc_hrd2.tbl_systems ts ON LOWER(ts.system_id) = LOWER(s.name)
            WHERE s.id = ?
        ");
        $stmt->execute([$systemId]);
        return $stmt->fetchColumn() ?: "Unknown System";
    } catch (Exception $e) {
        error_log("resolveSystemName error: " . $e->getMessage());
        return "Unknown System";
    }
}

/**
 * Notify Reviewers when a new request is created.
 * Message: "[Requestor Name] submitted a new request for [System]."
 */
function notifyReviewers(PDO $pdo, $requestId, $requestorName = '', $systemName = '')
{
    try {
        $deptId = null;

        if (!$requestorName || !$systemName) {
            $stmt = $pdo->prepare("SELECT r.user_id, r.system_id, r.department_id FROM requests r WHERE r.id = ?");
            $stmt->execute([$requestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                if (!$requestorName) $requestorName = resolveEmployeeNameByUserId($pdo, (int)$row['user_id']);
                if (!$systemName)    $systemName    = resolveSystemName($pdo, (int)$row['system_id']);
                $deptId = $row['department_id'] ?? null;
            }
        } else {
            $stmt = $pdo->prepare("SELECT department_id FROM requests WHERE id = ?");
            $stmt->execute([$requestId]);
            $deptId = $stmt->fetchColumn();
        }

        $message = "{$requestorName} submitted a new [{$systemName}] request pending your review.";

        if ($deptId !== null) {
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id
                FROM users u
                INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
                WHERE u.reqhub_role = 'Reviewer'
                  AND u.is_active = 1
                  AND uaa.department_id = ?
            ");
            $stmt->execute([$deptId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE reqhub_role = 'Reviewer' AND is_active = 1");
            $stmt->execute();
        }

        $reviewers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($reviewers as $reviewerId) {
            createNotification($pdo, (int)$reviewerId, 'pending_review', $requestId, $message);
        }
    } catch (Exception $e) {
        error_log("notifyReviewers error: " . $e->getMessage());
    }
}

/**
 * Notify Approvers assigned to a system once the request is reviewed/signed.
 * Message: "[Requestor Name]'s [System] request has been reviewed and is pending your approval."
 */
function notifyApproversForSystem(PDO $pdo, $systemId, $requestId, $requestorName = '', $systemName = '')
{
    try {
        if (!$requestorName || !$systemName) {
            $stmt = $pdo->prepare("SELECT user_id, system_id FROM requests WHERE id = ?");
            $stmt->execute([$requestId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                if (!$requestorName) $requestorName = resolveEmployeeNameByUserId($pdo, (int)$row['user_id']);
                if (!$systemName)    $systemName    = resolveSystemName($pdo, (int)($row['system_id'] ?? $systemId));
            }
        }

        $message = "{$requestorName}'s [{$systemName}] request has been reviewed and is pending your approval.";

        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id
            FROM users u
            INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
            WHERE uaa.system_id = :system_id
            AND u.reqhub_role = 'Approver'
            AND u.is_active = 1
        ");
        $stmt->execute([':system_id' => $systemId]);
        $approvers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($approvers as $approverId) {
            createNotification($pdo, (int)$approverId, 'pending_approval', $requestId, $message);
        }
    } catch (Exception $e) {
        error_log("notifyApproversForSystem error: " . $e->getMessage());
    }
}

/**
 * Notify Admins.
 * Callers should pass a fully-formed message string.
 */
function notifyAdmins(PDO $pdo, $requestId, $message)
{
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reqhub_role = 'Admin' AND is_active = 1");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($admins as $adminId) {
            createNotification($pdo, (int)$adminId, 'serve_reminder', $requestId, $message);
        }
    } catch (Exception $e) {
        error_log("notifyAdmins error: " . $e->getMessage());
    }
}

/**
 * Notify chat participants when a new message is sent.
 * Message: "[Sender Name] sent a message on a [System] request."
 */
function notifyChatParticipants(PDO $pdo, $requestId, $senderUserId)
{
    try {
        $stmt = $pdo->prepare("SELECT user_id, system_id FROM requests WHERE id = :id");
        $stmt->execute([':id' => $requestId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$request) return;

        $senderName = resolveEmployeeNameByUserId($pdo, $senderUserId);
        $systemName = resolveSystemName($pdo, (int)$request['system_id']);
        $message    = "{$senderName} sent a message on a [{$systemName}] request.";

        $recipients = [];

        if ((int)$request['user_id'] !== $senderUserId) {
            $recipients[] = (int)$request['user_id'];
        }

        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id
            FROM users u
            INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
            WHERE uaa.system_id = :system_id AND u.reqhub_role = 'Approver'
        ");
        $stmt->execute([':system_id' => $request['system_id']]);
        $approvers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($approvers as $approverId) {
            if ((int)$approverId !== $senderUserId) $recipients[] = (int)$approverId;
        }

        // After the approvers block, add:
        $stmt = $pdo->prepare("
            SELECT id FROM users WHERE reqhub_role = 'Reviewer' AND is_active = 1
        ");
        $stmt->execute();
        $reviewers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($reviewers as $reviewerId) {
            if ((int)$reviewerId !== $senderUserId) $recipients[] = (int)$reviewerId;
        }

        foreach (array_unique($recipients) as $recipientId) {
            $stmt = $pdo->prepare("
                SELECT id FROM notifications
                WHERE user_id = ? AND type = 'chat' AND request_id = ? AND is_read = 0
                LIMIT 1
            ");
            $stmt->execute([$recipientId, $requestId]);
            if (!$stmt->fetch()) {
                createNotification($pdo, $recipientId, 'chat', $requestId, $message);
            }
        }
    } catch (Exception $e) {
        error_log("notifyChatParticipants error: " . $e->getMessage());
    }
}
?>