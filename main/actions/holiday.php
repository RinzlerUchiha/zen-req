<?php
require_once($sr_root . "/db/db.php");

try {
    $hr = Database::getConnection('hr');

$statement = $hr->prepare("SELECT * FROM tbl_holiday");
$statement->execute();
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

$events = [];

foreach ($results as $row) {
	$end_date = date('Y-m-d', strtotime($row['date'] . ' +1 day'));
    $events[] = [
        'title' => $row['holiday'],
        'start' => $row['date'],
        'end' => $end_date
    ];
}

echo json_encode($events);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
