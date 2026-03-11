<?php
if(session_status() === PHP_SESSION_NONE) session_start(); // Start the session
require_once($pi_root."/db/db.php");

try {
   $scms_db = Database::getConnection('scms');
    $pi_db = Database::getConnection('pi');
    $hr_db = Database::getConnection('hr');

} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    $stmt = $hr_db->prepare('SELECT * FROM tbl201_basicinfo WHERE bi_empno = :id');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo json_encode(['username' => $user['bi_empfname']]);
    } else {
        echo json_encode(['username' => null]);
    }
} else {
    echo json_encode(['username' => null]);
}
?>
