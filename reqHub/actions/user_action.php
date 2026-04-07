<?php
/**
 * User Management for ReqHub Admin Settings
 * File: /zen/reqHub/actions/user_action.php
 * 
 * Manages users in reqhub.users table with proper employee_id, reqhub_role, system_id, department_id
 */

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Access denied']));
}

$currentUser = getCurrentUser();

if ($currentUser['reqhub_role'] !== 'Admin') {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Access denied: Admin only']));
}

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
    $hrPdo = ReqHubDatabase::getConnection('hr');
} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$action = $_POST['action'] ?? null;

try {
    if ($action === 'addUser') {
        $employeeId = trim($_POST['name'] ?? ''); // This is actually employee_id
        $userType = $_POST['user_type'] ?? 'Requestor';
        $systemId = !empty($_POST['system_ids']) ? $_POST['system_ids'][0] : null;
        $departmentId = !empty($_POST['department_ids']) ? $_POST['department_ids'][0] : null;
        
        if (!$employeeId) {
            die(json_encode(['success' => false, 'message' => 'Employee ID required']));
        }

        // Map user_type to reqhub_role
        $reqhubRole = $userType; // Requestor, Approver, Admin, Reviewer

        // Check if employee already exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
        $checkStmt->execute([$employeeId]);
        if ($checkStmt->fetch()) {
            die(json_encode(['success' => false, 'message' => 'Employee ID already exists']));
        }

        // Get employee name from HR database for display
        $employeeName = $employeeId;
        try {
            $hrStmt = $hrPdo->prepare("
                SELECT CONCAT(COALESCE(bi_empfname, ''), ' ', COALESCE(bi_emplname, '')) as full_name
                FROM tbl201_basicinfo
                WHERE bi_empno = ? AND datastat = 'current'
                LIMIT 1
            ");
            $hrStmt->execute([$employeeId]);
            $hrResult = $hrStmt->fetch(PDO::FETCH_ASSOC);
            if ($hrResult && !empty(trim($hrResult['full_name']))) {
                $employeeName = trim($hrResult['full_name']);
            }
        } catch (Exception $e) {
            // If HR lookup fails, use employee_id as name
            error_log("HR lookup failed for $employeeId: " . $e->getMessage());
        }

        // Insert user with all required fields
        $stmt = $pdo->prepare("
            INSERT INTO users (employee_id, reqhub_role, system_id, department_id, is_active)
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([$employeeId, $reqhubRole, $systemId, $departmentId]);
        $userId = $pdo->lastInsertId();

        // If Approver, store all system-department combinations
        $assignments = [];
        if ($userType === 'Approver') {
            $systemIds = $_POST['system_ids'] ?? [];
            $departmentIds = $_POST['department_ids'] ?? [];

            if (!empty($systemIds)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_approver_assignments (user_id, system_id, department_id)
                    VALUES (?, ?, NULL)
                    ON DUPLICATE KEY UPDATE id = id
                ");

                foreach ($systemIds as $sysId) {
                    $insertStmt->execute([$userId, $sysId]);
                    $assignments[] = ['system_id' => (int)$sysId];
                }
            }

            // if (!empty($systemIds) && !empty($departmentIds)) {
            //     $insertStmt = $pdo->prepare("
            //         INSERT INTO user_approver_assignments (user_id, system_id, department_id)
            //         VALUES (?, ?, ?)
            //         ON DUPLICATE KEY UPDATE id = id
            //     ");

            //     foreach ($systemIds as $sysId) {
            //         foreach ($departmentIds as $deptId) {
            //             $insertStmt->execute([$userId, $sysId, $deptId]);
            //             $assignments[] = [
            //                 'system_id' => (int)$sysId,
            //                 'department_id' => (int)$deptId
            //             ];
            //         }
            //     }
            // }
        }

        echo json_encode([
            'success' => true,
            'id' => $userId,
            'name' => $employeeName,
            'employee_id' => $employeeId,
            'user_type' => $userType,
            'assignments' => $assignments
        ]);
    }

    else if ($action === 'editUser') {
        $userId = $_POST['user_id'] ?? null;
        $employeeId = trim($_POST['name'] ?? ''); // This is actually employee_id from inline edit
        $userType = $_POST['user_type'] ?? 'Requestor';
        $systemId = !empty($_POST['system_ids']) ? $_POST['system_ids'][0] : null;
        $departmentId = !empty($_POST['department_ids']) ? $_POST['department_ids'][0] : null;

        if (!$userId || !$employeeId) {
            die(json_encode(['success' => false, 'message' => 'Missing required fields']));
        }

        // Get current employee_id to check if it changed
        $currentStmt = $pdo->prepare("SELECT employee_id FROM users WHERE id = ?");
        $currentStmt->execute([$userId]);
        $currentRow = $currentStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$currentRow) {
            die(json_encode(['success' => false, 'message' => 'User not found']));
        }

        // If employee_id changed, check uniqueness
        if ($currentRow['employee_id'] !== $employeeId) {
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
            $checkStmt->execute([$employeeId]);
            if ($checkStmt->fetch()) {
                die(json_encode(['success' => false, 'message' => 'Employee ID already exists']));
            }
        }

        // Map user_type to reqhub_role
        $reqhubRole = $userType;

        // Get employee name from HR database
        $employeeName = $employeeId;
        try {
            $hrStmt = $hrPdo->prepare("
                SELECT CONCAT(COALESCE(bi_empfname, ''), ' ', COALESCE(bi_emplname, '')) as full_name
                FROM tbl201_basicinfo
                WHERE bi_empno = ? AND datastat = 'current'
                LIMIT 1
            ");
            $hrStmt->execute([$employeeId]);
            $hrResult = $hrStmt->fetch(PDO::FETCH_ASSOC);
            if ($hrResult && !empty(trim($hrResult['full_name']))) {
                $employeeName = trim($hrResult['full_name']);
            }
        } catch (Exception $e) {
            error_log("HR lookup failed for $employeeId: " . $e->getMessage());
        }

        // Update user with all fields
        $stmt = $pdo->prepare("
            UPDATE users 
            SET employee_id = ?, reqhub_role = ?, system_id = ?, department_id = ?
            WHERE id = ?
        ");
        $stmt->execute([$employeeId, $reqhubRole, $systemId, $departmentId, $userId]);

        // Handle approver assignments if type is Approver
        $assignments = [];
        if ($userType === 'Approver') {
            // Delete old assignments
            $delStmt = $pdo->prepare("DELETE FROM user_approver_assignments WHERE user_id = ?");
            $delStmt->execute([$userId]);

            // Add new assignments
            $systemIds = $_POST['system_ids'] ?? [];
            $departmentIds = $_POST['department_ids'] ?? [];

            if (!empty($systemIds)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_approver_assignments (user_id, system_id, department_id)
                    VALUES (?, ?, NULL)
                ");

                foreach ($systemIds as $sysId) {
                    $insertStmt->execute([$userId, $sysId]);
                    $assignments[] = ['system_id' => (int)$sysId];
                }
            }

            // if (!empty($systemIds) && !empty($departmentIds)) {
            //     $insertStmt = $pdo->prepare("
            //         INSERT INTO user_approver_assignments (user_id, system_id, department_id)
            //         VALUES (?, ?, ?)
            //     ");

            //     foreach ($systemIds as $sysId) {
            //         foreach ($departmentIds as $deptId) {
            //             $insertStmt->execute([$userId, $sysId, $deptId]);
            //             $assignments[] = [
            //                 'system_id' => (int)$sysId,
            //                 'department_id' => (int)$deptId
            //             ];
            //         }
            //     }
            // }
        } else {
            // Clear approver assignments if not approver
            $delStmt = $pdo->prepare("DELETE FROM user_approver_assignments WHERE user_id = ?");
            $delStmt->execute([$userId]);

            // Clear any pending_approval notifications since user is no longer an Approver
            $delNotifStmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND type = 'pending_approval'");
            $delNotifStmt->execute([$userId]);
        }

        echo json_encode([
            'success' => true,
            'id' => $userId,
            'name' => $employeeName,
            'employee_id' => $employeeId,
            'user_type' => $userType,
            'assignments' => $assignments
        ]);
    }

    else if ($action === 'deleteUser') {
        $userId = $_POST['user_id'] ?? null;

        if (!$userId) {
            die(json_encode(['success' => false, 'message' => 'User ID required']));
        }

        // Delete approver assignments
        $delStmt = $pdo->prepare("DELETE FROM user_approver_assignments WHERE user_id = ?");
        $delStmt->execute([$userId]);

        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        echo json_encode(['success' => true, 'id' => $userId]);
    }

    else {
        die(json_encode(['success' => false, 'message' => 'Unknown action']));
    }

} catch (Exception $e) {
    error_log("user_action.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>