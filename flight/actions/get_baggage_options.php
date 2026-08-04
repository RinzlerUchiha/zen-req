<?php
require_once($_SERVER['DOCUMENT_ROOT']."/zen/config/db.php");

$airline   = $_GET['airline'] ?? '';
$existing  = (int)($_GET['existing'] ?? 0);
try {
    $fb_db = Database::getConnection('fb');
/**
 * 1. Get MAX kg per airline
 */
$maxStmt = $fb_db->prepare("
    SELECT MAX(bag_kg) 
    FROM tbl_baggage
    WHERE bag_airlines = ?
    AND bag_status = '1'
");
$maxStmt->execute([$airline]);
$maxKg = (int)$maxStmt->fetchColumn();

/**
 * 2. Remaining allowable kg
 */
$remaining = max(0, $maxKg - $existing);

/**
 * 3. Get only baggage options that fit
 */
$stmt = $fb_db->prepare("
    SELECT bag_kg
    FROM tbl_baggage
    WHERE bag_airlines = ?
      AND bag_kg <= ?
      AND bag_status = '1'
    ORDER BY bag_kg ASC
");
$stmt->execute([$airline, $remaining]);

$options = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    'max'       => $maxKg,
    'existing'  => $existing,
    'remaining'=> $remaining,
    'options'  => $options
]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}