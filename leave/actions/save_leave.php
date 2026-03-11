<?php
require_once($lv_root . "/db/dbcon.php"); 
$db = new Dbcon;
$con1 = $db->connect();

$laID        = $_POST['laID'] ?? '';
$empno       = $_POST['emp'] ?? '';
$leave_type  = $_POST['leave_type'] ?? '';
$mtype       = $_POST['mtype'] ?? '';
$la_days     = $_POST['la_days'] ?? 0;
$max_days    = $_POST['max_days'] ?? 0;
$start       = $_POST['start'] ?? '';
$end         = $_POST['end'] ?? '';
$return      = $_POST['return'] ?? '';
$reasons     = $_POST['reasons'] ?? '';
$status      = $_POST['status'] ?? '';
$dates_csv   = $_POST['dates'] ?? '';

if (!$empno || !$leave_type || !$start || !$return || !$dates_csv) {
  echo "Invalid data.";
  exit;
}

$current_timestamp = date('Y-m-d H:i:s');
$dates_array = explode(',', $dates_csv);

if (empty($laID)) {
    $check_sql = "
        SELECT la_dates FROM tbl201_leave 
        WHERE la_empno = ? AND la_status != 'cancelled'
    ";

    $stmt = $con1->prepare($check_sql);
    $stmt->execute([$empno]);

    $existing_dates = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existing = explode(',', $row['la_dates']);
        $existing_dates = array_merge($existing_dates, $existing);
    }

    $duplicates = array_intersect($existing_dates, $dates_array);

    if (!empty($duplicates)) {
        echo "Duplicate dates found: " . implode(', ', $duplicates);
        exit;
    }
}

try {
    if (!empty($laID)) {
        $update = $con1->prepare("
            UPDATE tbl201_leave 
            SET 
                la_type = ?, 
                la_start = ?, 
                la_end = ?, 
                la_bal = ?, 
                la_days = ?, 
                la_return = ?, 
                la_reason = ?, 
                la_dates = ?, 
                la_status = ?, 
                la_timestamp = ?, 
                la_mtype = ?
            WHERE la_id = ?
        ");

        $update->execute([
            $leave_type,
            $start,
            $end,
            $max_days,
            $la_days,
            $return,
            $reasons,
            implode(',', $dates_array),
            $status,
            $current_timestamp,
            $leave_type === 'Maternity Leave' ? $mtype : null,
            $laID
        ]);

        echo "Updated successfully.";
    } else {
        $insert = $con1->prepare("
            INSERT INTO tbl201_leave (
                la_empno, la_type, la_start, la_end, la_bal, la_days, la_return, 
                la_reason, la_dates, la_status, la_timestamp, la_mtype
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $insert->execute([
            $empno,
            $leave_type,
            $start,
            $end,
            $max_days,
            $la_days,
            $return,
            $reasons,
            implode(',', $dates_array),
            $status,
            $current_timestamp,
            $leave_type === 'Maternity Leave' ? $mtype : null
        ]);

        echo "Saved successfully.";
    }
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>
