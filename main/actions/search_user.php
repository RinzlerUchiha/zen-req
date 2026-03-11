<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=portal_db", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

header('Content-Type: application/json');

if (isset($_POST['query'])) {
    $search = '%' . trim($_POST['query']) . '%';

    $port_db = Database::getConnection('port');
    $stmt = $port_db->prepare("SELECT CONCAT(bi_empfname, ' ', bi_emplname) AS fullname FROM tbl201_basicinfo WHERE bi_emplname LIKE ?");
    $stmt->execute([$search]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
}
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
