<?php
// Database connection
require_once($main_root."/db/db.php");
try {
    $hr_db = Database::getConnection('hr');

} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
// Get the category ID from the request
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Fetch subcategories based on the category ID
$stmt = $pdo->prepare("SELECT id, name FROM subcategories WHERE category_id = ?");
$stmt->execute([$category_id]);
$subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the subcategories as JSON
echo json_encode($subcategories);
?>
