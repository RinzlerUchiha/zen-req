<?php
if(session_status() === PHP_SESSION_NONE) session_start(); // Start the session

require_once($main_root."/db/db.php");

try {
    $hr_db = Database::getConnection('hr');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $hr_db->prepare('SELECT * FROM tbl_user2 WHERE U_Name = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && $password === $user['U_Password']) {
        $_SESSION['user_id'] = $user['Emp_No'];
        $_SESSION['HR_UID'] = $user['U_ID'];
        session_write_close();
        echo json_encode(['success' => '1', 'message' => 'Login success']);
    } else {
        echo json_encode(['danger' => '0', 'message' => 'Incorrect username/password']);
    }
}

} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>