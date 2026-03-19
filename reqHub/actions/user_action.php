<?php
/**
 * User Management
 * File: /zen/reqHub/actions/user_action.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once (__DIR__ . '/../includes/auth.php');
require_once (__DIR__ . '/../database/db.php');

header('Content-Type: application/json');

requireRole('Admin');

try {
    $pdo = ReqHubDatabase::getConnection('reqhub');
} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$action = $_POST['action'] ?? null;

try {
    if ($action === 'addUser') {
        $name = trim($_POST['name'] ?? '');
        $userType = $_POST['user_type'] ?? 'Requestor';
        
        if (!$name) {
            die(json_encode(['success' => false, 'message' => 'Name required']));
        }

        $stmt = $pdo->prepare("INSERT INTO users (name, user_type) VALUES (?, ?)");
        $stmt->execute([$name, $userType]);
        $userId = $pdo->lastInsertId();

        // Store assignments and fetch them for response
        $assignments = [];
        if ($userType === 'Approver') {
            $systemIds = $_POST['system_ids'] ?? [];
            $departmentIds = $_POST['department_ids'] ?? [];

            if (!empty($systemIds) && !empty($departmentIds)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_approver_assignments (user_id, system_id, department_id)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE id = id
                ");

                foreach ($systemIds as $sysId) {
                    foreach ($departmentIds as $deptId) {
                        $insertStmt->execute([$userId, $sysId, $deptId]);
                        $assignments[] = [
                            'system_id' => (int)$sysId,
                            'department_id' => (int)$deptId
                        ];
                    }
                }
            }
        }

        echo json_encode([
            'success' => true,
            'id' => $userId,
            'name' => $name,
            'user_type' => $userType,
            'assignments' => $assignments
        ]);
    }

    else if ($action === 'editUser') {
        $userId = $_POST['user_id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $userType = $_POST['user_type'] ?? 'Requestor';

        if (!$userId || !$name) {
            die(json_encode(['success' => false, 'message' => 'Missing required fields']));
        }

        $stmt = $pdo->prepare("UPDATE users SET name = ?, user_type = ? WHERE id = ?");
        $stmt->execute([$name, $userType, $userId]);

        // Handle approver assignments
        $assignments = [];
        if ($userType === 'Approver') {
            // Delete old assignments
            $delStmt = $pdo->prepare("DELETE FROM user_approver_assignments WHERE user_id = ?");
            $delStmt->execute([$userId]);

            // Add new assignments
            $systemIds = $_POST['system_ids'] ?? [];
            $departmentIds = $_POST['department_ids'] ?? [];

            if (!empty($systemIds) && !empty($departmentIds)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_approver_assignments (user_id, system_id, department_id)
                    VALUES (?, ?, ?)
                ");

                foreach ($systemIds as $sysId) {
                    foreach ($departmentIds as $deptId) {
                        $insertStmt->execute([$userId, $sysId, $deptId]);
                    }
                }
            }
            
            // Fetch the saved assignments
            $fetchStmt = $pdo->prepare("
                SELECT system_id, department_id 
                FROM user_approver_assignments 
                WHERE user_id = ?
            ");
            $fetchStmt->execute([$userId]);
            $assignments = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Clear approver assignments if not approver
            $delStmt = $pdo->prepare("DELETE FROM user_approver_assignments WHERE user_id = ?");
            $delStmt->execute([$userId]);
        }

        echo json_encode([
            'success' => true,
            'id' => $userId,
            'name' => $name,
            'user_type' => $userType,
            'assignments' => $assignments
        ]);
    }

    else if ($action === 'deleteUser') {
        $userId = $_POST['user_id'] ?? null;

        if (!$userId) {
            die(json_encode(['success' => false, 'message' => 'User ID required']));
        }

        // Cascade delete via foreign keys, but just in case:
        $delStmt = $pdo->prepare("DELETE FROM user_approver_assignments WHERE user_id = ?");
        $delStmt->execute([$userId]);

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        echo json_encode(['success' => true, 'id' => $userId]);
    }

    else {
        die(json_encode(['success' => false, 'message' => 'Unknown action']));
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>