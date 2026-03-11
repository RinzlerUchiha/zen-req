<?php
require_once($pcf_root . "/db/db.php");


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pcf_db = Database::getConnection('pcf');

$disburNo = $_POST['disbur_no'];
$query = $pcf_db->prepare("SELECT file FROM tbl_attachment WHERE disbur_no = ?");
$query->execute([$disburNo]);
$results = $query->fetchAll(PDO::FETCH_ASSOC);

$files = [];
foreach ($results as $row) {
    $files = array_merge($files, explode(',', $row['file']));
}

echo json_encode($files);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
