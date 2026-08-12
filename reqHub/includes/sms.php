<?php
/**
 * SMS Helper
 * File: /zen/reqHub/includes/sms.php
 */

require_once (__DIR__ . '/sms_engine.php');

/**
 * SMS Reviewers assigned to a department.
 */
function smsReviewers(PDO $pdo, $requestId, $requestorName = '', $systemName = '')
{
    try {
        $deptId = null;

        if (!$requestorName || !$systemName) {
            $stmt = $pdo->prepare("SELECT user_id, system_id, department_id FROM requests WHERE id = ?");
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
                SELECT DISTINCT u.employee_id
                FROM users u
                INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
                WHERE u.reqhub_role = 'Reviewer'
                  AND u.is_active = 1
                  AND uaa.department_id = ?
            ");
            $stmt->execute([$deptId]);
        } else {
            $stmt = $pdo->prepare("SELECT employee_id FROM users WHERE reqhub_role = 'Reviewer' AND is_active = 1");
            $stmt->execute();
        }

        $reviewers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($reviewers as $empNo) {
            // SendSmsToEmpNo($empNo, $message, 'reqhub_review');
            SendSmsToEmpNo($empNo, $message, 'reqhub');
        }
    } catch (Exception $e) {
        error_log("smsReviewers error: " . $e->getMessage());
    }
}

/**
 * SMS Approvers assigned to a system.
 */
function smsApproversForSystem(PDO $pdo, $systemId, $requestId, $requestorName = '', $systemName = '', $message = '')
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

        if (!$message) {
            $message = "{$requestorName}'s [{$systemName}] request has been reviewed and is pending your approval.";
        }

        $stmt = $pdo->prepare("
            SELECT DISTINCT u.employee_id
            FROM users u
            INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
            WHERE uaa.system_id = :system_id
              AND u.reqhub_role = 'Approver'
              AND u.is_active = 1
        ");
        $stmt->execute([':system_id' => $systemId]);
        $approvers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($approvers as $empNo) {
            // SendSmsToEmpNo($empNo, $message, 'reqhub_approval');
            SendSmsToEmpNo($empNo, $message, 'reqhub');
        }
    } catch (Exception $e) {
        error_log("smsApproversForSystem error: " . $e->getMessage());
    }
}

/**
 * SMS Admins.
 */
function smsAdmins(PDO $pdo, $message)
{
    try {
        $stmt = $pdo->prepare("SELECT employee_id FROM users WHERE reqhub_role = 'Admin' AND is_active = 1");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($admins as $empNo) {
            // SendSmsToEmpNo($empNo, $message, 'reqhub_admin');
            SendSmsToEmpNo($empNo, $message, 'reqhub');
        }
    } catch (Exception $e) {
        error_log("smsAdmins error: " . $e->getMessage());
    }
}

/**
 * SMS a single user by their users.id.
 */
function smsUserById(PDO $pdo, $userId, $message)
{
    try {
        $stmt = $pdo->prepare("SELECT employee_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $empNo = $stmt->fetchColumn();
        if ($empNo) {
            // SendSmsToEmpNo($empNo, $message, 'reqhub_notify');
            SendSmsToEmpNo($empNo, $message, 'reqhub');
        }
    } catch (Exception $e) {
        error_log("smsUserById error: " . $e->getMessage());
    }
}

/**
 * SMS chat participants when a new message is sent.
 * Mirrors notifyChatParticipants() logic.
 */
function smsChatParticipants(PDO $pdo, $requestId, $senderUserId)
{
    try {
        $stmt = $pdo->prepare("SELECT user_id, system_id, department_id, status FROM requests WHERE id = ?");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$request) return;

        $senderName = resolveEmployeeNameByUserId($pdo, $senderUserId);
        $systemName = resolveSystemName($pdo, (int)$request['system_id']);
        $message    = "{$senderName} sent a message on a [{$systemName}] request.";
        $status     = $request['status'];

        $recipientIds = [];

        // Always include requestor (if not sender)
        if ((int)$request['user_id'] !== $senderUserId) {
            $recipientIds[] = (int)$request['user_id'];
        }

        if ($status === 'pending') {
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id
                FROM users u
                INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
                WHERE u.reqhub_role = 'Reviewer'
                  AND u.is_active = 1
                  AND uaa.department_id = ?
            ");
            $stmt->execute([$request['department_id']]);
            $reviewerIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmtApprover = $pdo->prepare("
                SELECT DISTINCT u.id
                FROM users u
                INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
                WHERE u.reqhub_role = 'Approver'
                  AND u.is_active = 1
                  AND uaa.system_id = ?
            ");
            $stmtApprover->execute([$request['system_id']]);
            $approverIds = $stmtApprover->fetchAll(PDO::FETCH_COLUMN);

            if (empty($reviewerIds) && empty($approverIds)) {
                // No Reviewer and no Approver — fall back to Admins
                $stmtAdmin = $pdo->prepare("SELECT id FROM users WHERE reqhub_role = 'Admin' AND is_active = 1");
                $stmtAdmin->execute();
                foreach ($stmtAdmin->fetchAll(PDO::FETCH_COLUMN) as $id) {
                    if ((int)$id !== $senderUserId) $recipientIds[] = (int)$id;
                }
            } else {
                foreach ($reviewerIds as $id) {
                    if ((int)$id !== $senderUserId) $recipientIds[] = (int)$id;
                }
            }

        } elseif (in_array($status, ['reviewed', 'needs_revision'])) {
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id
                FROM users u
                INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
                WHERE u.reqhub_role = 'Reviewer'
                  AND u.is_active = 1
                  AND uaa.department_id = ?
            ");
            $stmt->execute([$request['department_id']]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
                if ((int)$id !== $senderUserId) $recipientIds[] = (int)$id;
            }

            $stmt = $pdo->prepare("
                SELECT DISTINCT u.id
                FROM users u
                INNER JOIN user_approver_assignments uaa ON uaa.user_id = u.id
                WHERE u.reqhub_role = 'Approver'
                  AND u.is_active = 1
                  AND uaa.system_id = ?
            ");
            $stmt->execute([$request['system_id']]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
                if ((int)$id !== $senderUserId) $recipientIds[] = (int)$id;
            }

        } elseif (in_array($status, ['approved', 'denied'])) {
            // Requestor only — already added above
        }

        foreach (array_unique($recipientIds) as $recipientId) {
            // Skip if actively viewing (last_viewed_at within 30 seconds)
            $stmtView = $pdo->prepare("
                SELECT last_viewed_at FROM request_chat_views
                WHERE request_id = ? AND user_id = ?
            ");
            $stmtView->execute([$requestId, $recipientId]);
            $lastViewed = $stmtView->fetchColumn();
            if ($lastViewed && strtotime($lastViewed) >= (time() - 30)) continue;

            $stmtEmp = $pdo->prepare("SELECT employee_id FROM users WHERE id = ?");
            $stmtEmp->execute([$recipientId]);
            $empNo = $stmtEmp->fetchColumn();
            if ($empNo) {
                // SendSmsToEmpNo($empNo, $message, 'reqhub_chat');
                SendSmsToEmpNo($empNo, $message, 'reqhub');
            }
        }
    } catch (Exception $e) {
        error_log("smsChatParticipants error: " . $e->getMessage());
    }
}
?>