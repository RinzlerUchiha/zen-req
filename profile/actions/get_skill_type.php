<?php
require_once($sr_root . "/db/db.php");

header('Content-Type: application/json');
header('Content-Type: text/html; charset=UTF-8');

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    $sc_id = $_GET['sc_id'] ?? '';
    
    if ($sc_id) {
        $stmt = $port_db->prepare("SELECT id, skill_name FROM tbl_skill_type WHERE skil_categID = :category");
        $stmt->execute(['category' => $sc_id]);
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($types as $type) {
            echo '<option value="' . htmlspecialchars($type['id']) . '">' . htmlspecialchars($type['skill_name']) . '</option>';
        }
    }

} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
