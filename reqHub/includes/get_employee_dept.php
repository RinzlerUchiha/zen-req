<?php
error_log("=== get_employee_dept.php START ===");

// $reqhub_root should be set by the router
if (!isset($reqhub_root)) {
    error_log("ERROR: reqhub_root is not set!");
    http_response_code(500);
    die(json_encode(['error' => 'Server configuration error']));
}

try {
    require_once ($reqhub_root . '/includes/auth.php');
    require_once ($reqhub_root . '/database/db.php');
} catch (Exception $e) {
    error_log("ERROR including files: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Server error']));
}

if (!isAuthenticated()) {
    error_log("get_employee_dept: User not authenticated");
    http_response_code(403);
    die(json_encode(['error' => 'Not authenticated']));
}

$emp_no = $_GET['emp_no'] ?? null;
error_log("get_employee_dept: emp_no = " . ($emp_no ?? 'NULL'));

if (!$emp_no) {
    http_response_code(400);
    die(json_encode(['error' => 'Missing employee number']));
}

try {
    // Connect to ZenHub database to get job record data
    $pdo = ReqHubDatabase::getConnection('reqhub');
    
    error_log("get_employee_dept: Querying tbl201_jobrec for emp_no: $emp_no");
    
    $stmt = $pdo->prepare("
        SELECT 
            jrec_department,
            jrec_empno
        FROM tngc_hrd2.tbl201_jobrec
        WHERE jrec_empno = :emp_no
        AND jrec_status = 'primary'
        LIMIT 1
    ");
    
    $stmt->execute([':emp_no' => $emp_no]);
    $jobRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("get_employee_dept: Query result: " . json_encode($jobRecord));
    
    $response = [
        'employee_id' => $emp_no,
        'department' => null,
        'dept_code' => null,       // ← add this
        'requires_store' => false
    ];

    if ($jobRecord) {
        $department = $jobRecord['jrec_department'];
        $response['department'] = $department;
        $response['dept_code'] = $department;   // ← add this
        
        error_log("get_employee_dept: Found department: $department");
        
        // Check if this department requires a store
        // You can customize this logic based on your business rules
        // For example, certain departments might require store info
        $departmentsRequiringStore = ['Sales', 'Retail', 'Store Operations']; // Customize as needed
        
        if (in_array($department, $departmentsRequiringStore)) {
            $response['requires_store'] = true;
            error_log("get_employee_dept: Department requires store");
        }
    } else {
        error_log("get_employee_dept: No job record found for emp_no: $emp_no");
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    error_log("get_employee_dept: SUCCESS");
    
} catch (PDOException $e) {
    error_log("get_employee_dept: PDOException - " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("get_employee_dept: Exception - " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}

error_log("=== get_employee_dept.php END ===");
?>