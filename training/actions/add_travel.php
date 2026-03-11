<?php
require_once($lv_root . "/db/db_functions.php");
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'travel';

header('Content-Type: application/json');

// Get current user ID
$user_id = $_SESSION['user_id'] ?? null;

$response = ['status' => 'error', 'message' => 'Unknown request'];

// Proceed only for POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = strtoupper($_POST['action'] ?? '');
  $empno = $_POST['empno'] ?? '';
  $change = $_POST['change'] ?? '';
  $reqtype = $_POST['reqtype'] ?? '';
  $id = $_POST['reqid'] ?? '';
  $timestamp = date("Y-m-d H:i:00");
  switch ($action) {
    case 'ADD':
   

            // If editing an existing record
    if (!empty($id)) {
      $dtwork = $_POST['dtwork'] ?? '';
      $day_type = ucwords($_POST['reqtype'] ?? '');
      $reason = $_POST['reason'] ?? '';
      $totaltime = $_POST['totaltime'] ?? '00:00';

                // Normalize time
      $totaltime_parts = explode(":", $totaltime);
      $totaltime = str_pad($totaltime_parts[0], 2, "0", STR_PAD_LEFT) . ":" . 
      (isset($totaltime_parts[1]) ? str_pad($totaltime_parts[1], 2, "0", STR_PAD_LEFT) : "00");

                // Conflict check
      $sql = $con1->prepare("SELECT COUNT(*) FROM tbl_edtr_hours 
       WHERE date_dtr = ? AND day_type = ? AND emp_no = ? 
       AND LOWER(dtr_stat) IN ('pending','approved','confirmed') AND id != ?");
      $sql->execute([$dtwork, $day_type, $empno, $id]);
      if ($sql->fetchColumn() > 0) {
        $response['message'] = "Invalid Input! Date already exists.";
        echo json_encode($response);
        exit;
      }

                // Update
      $update = $con1->prepare("UPDATE tbl_edtr_hours 
        SET date_dtr = ?, day_type = ?, reason = ?, total_hours = ?, dtr_stat = ?, date_added = ?, bf_change = ? 
        WHERE id = ?");
      $update->execute([$dtwork, $day_type, $reason, $totaltime, 'PENDING', $timestamp, $change, $id]);

                // Log
      $log_action = "Updated $day_type record on $dtwork for Emp No: $empno";
      $log = $con1->prepare("INSERT INTO tbl_edtr_logs (emp_no, activity, date_time) VALUES (?, ?, ?)");
      $log->execute([$user_id, $log_action, $timestamp]);

      echo json_encode(['status' => 'success', 'message' => 'Updated successfully']);
      exit;
    }

            // Otherwise, insert new records
    $arrset = $_POST['arrset'] ?? [];

    if (empty($arrset) || !is_array($arrset)) {
      echo json_encode(['status' => 'error', 'message' => 'Invalid or empty data set']);
      exit;
    }
    
    foreach ($arrset as $i => $arr) {
      if (!is_array($arr) || count($arr) < 5 || in_array(null, $arr, true) || in_array('', $arr, true)) {
        echo json_encode(['status' => 'error', 'message' => "Incomplete data at row " . ($i + 1)]);
        exit;
      }
    
      list($dtwork_from, $day_type, $reason, $totaltime_input, $dtwork_to) = $arr;


    // Normalize
      $day_type = ucwords(trim($day_type));
      $totaltime_parts = explode(":", $totaltime_input);
      $totaltime = str_pad($totaltime_parts[0], 2, "0", STR_PAD_LEFT) . ":" . 
      (isset($totaltime_parts[1]) ? str_pad($totaltime_parts[1], 2, "0", STR_PAD_LEFT) : "00");

      if ($dtwork_from > $dtwork_to) {
        echo json_encode(['status' => 'error', 'message' => "$dtwork_from cannot be after $dtwork_to."]);
        exit;
      }

    // Conflict check
      $check = $con1->prepare("SELECT COUNT(*) FROM tbl_edtr_hours 
       WHERE emp_no = ? AND date_dtr BETWEEN ? AND ? 
       AND LOWER(dtr_stat) IN ('pending','approved','confirmed')");
      $check->execute([$empno, $dtwork_from, $dtwork_to]);
      if ($check->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => "Date range ($dtwork_from to $dtwork_to) already exists."]);
        exit;
      }

    // Insert one row per date in range
      $begin = new DateTime($dtwork_from);
      $end = new DateTime($dtwork_to);

      while ($begin <= $end) {
        $current_date = $begin->format("Y-m-d");

        $insert = $con1->prepare("INSERT INTO tbl_edtr_hours 
          (emp_no, date_dtr, total_hours, day_type, reason, dtr_stat, date_added)
          VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([$empno, $current_date, $totaltime, $day_type, $reason, 'PENDING', $timestamp]);

        $log_action = "Added $day_type record on $current_date for Emp No: $empno";
        $log = $con1->prepare("INSERT INTO tbl_edtr_logs (emp_no, activity, date_time) VALUES (?, ?, ?)");
        $log->execute([$user_id, $log_action, $timestamp]);

        $begin->modify('+1 day');
      }
    }


    $response = ['status' => 'success', 'message' => 'Saved successfully'];
    echo json_encode($response);
    break;

    default:
    $response['message'] = 'Invalid action.';
    echo json_encode($response);
    break;
  }
}
?>
