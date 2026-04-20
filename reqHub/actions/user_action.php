<?php
/**
 * User Management for ReqHub Admin Settings
 * File: /zen/reqHub/actions/user_action.php
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

// Roles that can have system assignments
$rolesWithSystemAssignment = ['Approver', 'Requestor'];

// Roles that can have department assignments
$rolesWithDepartmentAssignment = ['Reviewer'];

/**
 * Look up department name from HR DB given a Dept_Code.
 */
function getDeptName($pdo, $deptId) {
    try {
        $stmt = $pdo->prepare("SELECT name FROM departments WHERE id = ? LIMIT 1");
        $stmt->execute([$deptId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? trim($row['name']) : $deptId;
    } catch (Exception $e) {
        return $deptId;
    }
}

try {
    if ($action === 'addUser') {
        $employeeId = trim($_POST['name'] ?? '');
        $userType   = $_POST['user_type'] ?? 'Requestor';
        $systemId   = !empty($_POST['system_ids']) ? $_POST['system_ids'][0] : null;

        if (!$employeeId) {
            die(json_encode(['success' => false, 'message' => 'Employee ID required']));
        }

        $reqhubRole = $userType;

        // Check duplicate
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
        $checkStmt->execute([$employeeId]);
        if ($checkStmt->fetch()) {
            die(json_encode(['success' => false, 'message' => 'Employee ID already exists']));
        }

        // HR name lookup
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

        $stmt = $pdo->prepare("
            INSERT INTO users (employee_id, reqhub_role, is_active)
            VALUES (?, ?, 1)
        ");
        $stmt->execute([$employeeId, $reqhubRole]);
        $userId = $pdo->lastInsertId();

        $assignments = [];

        if (in_array($userType, $rolesWithSystemAssignment)) {
            // System assignments for Approver / Requestor
            $systemIds = $_POST['system_ids'] ?? [];
            if (!empty($systemIds)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_approver_assignments (user_id, system_id, department_id)
                    VALUES (?, ?, NULL)
                    ON DUPLICATE KEY UPDATE id = id
                ");
                foreach ($systemIds as $sysId) {
                    $insertStmt->execute([$userId, $sysId]);
                    $assignments[] = ['system_id' => (int)$sysId, 'department_id' => null];
                }
            }
        } elseif (in_array($userType, $rolesWithDepartmentAssignment)) {
            // System assignments for Reviewer
            $systemIds = $_POST['system_ids'] ?? [];
            if (!empty($systemIds)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_approver_assignments (user_id, system_id, department_id)
                    VALUES (?, ?, NULL)
                    ON DUPLICATE KEY UPDATE id = id
                ");
                foreach ($systemIds as $sysId) {
                    $insertStmt->execute([$userId, $sysId]);
                    $assignments[] = ['system_id' => (int)$sysId, 'department_id' => null];
                }
            }
            // Department assignments for Reviewer
            $deptCodes = $_POST['department_codes'] ?? [];
            if (!empty($deptCodes)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_approver_assignments (user_id, system_id, department_id)
                    VALUES (?, NULL, ?)
                    ON DUPLICATE KEY UPDATE id = id
                ");
                foreach ($deptCodes as $deptCode) {
                    // Resolve dept code to numeric id in reqhub departments table
                    $deptLookup = $pdo->prepare("SELECT id FROM departments WHERE code = ? LIMIT 1");
                    $deptLookup->execute([$deptCode]);
                    $deptRow = $deptLookup->fetch(PDO::FETCH_ASSOC);
                    if (!$deptRow) continue; // skip if not found
                    $deptId = $deptRow['id'];

                    $insertStmt->execute([$userId, $deptId]);
                    $assignments[] = [
                        'system_id'     => null,
                        'department_id' => $deptId,
                        'dept_name'     => getDeptName($pdo, $deptId),
                    ];
                }
            }
        }

        echo json_encode([
            'success'     => true,
            'id'          => $userId,
            'name'        => $employeeName,
            'employee_id' => $employeeId,
            'user_type'   => $userType,
            'assignments' => $assignments
        ]);
    }

    elseif ($action === 'editUser') {
        $userId     = $_POST['user_id'] ?? null;
        $employeeId = trim($_POST['name'] ?? '');
        $userType   = $_POST['user_type'] ?? 'Requestor';
        $systemId   = !empty($_POST['system_ids']) ? $_POST['system_ids'][0] : null;

        if (!$userId || !$employeeId) {
            die(json_encode(['success' => false, 'message' => 'Missing required fields']));
        }

        $currentStmt = $pdo->prepare("SELECT employee_id FROM users WHERE id = ?");
        $currentStmt->execute([$userId]);
        $currentRow = $currentStmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentRow) {
            die(json_encode(['success' => false, 'message' => 'User not found']));
        }

        if ($currentRow['employee_id'] !== $employeeId) {
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
            $checkStmt->execute([$employeeId]);
            if ($checkStmt->fetch()) {
                die(json_encode(['success' => false, 'message' => 'Employee ID already exists']));
            }
        }

        $reqhubRole = $userType;

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

        $stmt = $pdo->prepare("
            UPDATE users
            SET employee_id = ?, reqhub_role = ?
            WHERE id = ?
        ");
        $stmt->execute([$employeeId, $reqhubRole, $userId]);

        // Clear all existing assignments first
        $delStmt = $pdo->prepare("DELETE FROM user_approver_assignments WHERE user_id = ?");
        $delStmt->execute([$userId]);

        $assignments = [];

        if (in_array($userType, $rolesWithSystemAssignment)) {
            // System assignments for Approver / Requestor
            $systemIds = $_POST['system_ids'] ?? [];
            if (!empty($systemIds)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_approver_assignments (user_id, system_id, department_id)
                    VALUES (?, ?, NULL)
                ");
                foreach ($systemIds as $sysId) {
                    $insertStmt->execute([$userId, $sysId]);
                    $assignments[] = ['system_id' => (int)$sysId, 'department_id' => null];
                }
            }
        } elseif (in_array($userType, $rolesWithDepartmentAssignment)) {
            // System assignments for Reviewer
            $systemIds = $_POST['system_ids'] ?? [];
            if (!empty($systemIds)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_approver_assignments (user_id, system_id, department_id)
                    VALUES (?, ?, NULL)
                ");
                foreach ($systemIds as $sysId) {
                    $insertStmt->execute([$userId, $sysId]);
                    $assignments[] = ['system_id' => (int)$sysId, 'department_id' => null];
                }
            }
            // Department assignments for Reviewer
            $deptCodes = $_POST['department_codes'] ?? [];
            if (!empty($deptCodes)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_approver_assignments (user_id, system_id, department_id)
                    VALUES (?, NULL, ?)
                ");
                foreach ($deptCodes as $deptCode) {
                    // Resolve dept code to numeric id in reqhub departments table
                    $deptLookup = $pdo->prepare("SELECT id FROM departments WHERE code = ? LIMIT 1");
                    $deptLookup->execute([$deptCode]);
                    $deptRow = $deptLookup->fetch(PDO::FETCH_ASSOC);
                    if (!$deptRow) continue; // skip if not found
                    $deptId = $deptRow['id'];

                    $insertStmt->execute([$userId, $deptId]);
                    $assignments[] = [
                        'system_id'     => null,
                        'department_id' => $deptId,
                        'dept_name'     => getDeptName($pdo, $deptId),
                    ];
                }
            }
        } else {
            // No assignable role — also clean up pending_approval notifications
            $delNotifStmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND type = 'pending_approval'");
            $delNotifStmt->execute([$userId]);
        }

        echo json_encode([
            'success'     => true,
            'id'          => $userId,
            'name'        => $employeeName,
            'employee_id' => $employeeId,
            'user_type'   => $userType,
            'assignments' => $assignments
        ]);
    }

    elseif ($action === 'deleteUser') {
        $userId = $_POST['user_id'] ?? null;

        if (!$userId) {
            die(json_encode(['success' => false, 'message' => 'User ID required']));
        }

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
    error_log("user_action.php error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>