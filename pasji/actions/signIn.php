<!-- <?php
if(session_status() === PHP_SESSION_NONE) session_start(); // Start the session

require_once($main_root."/db/db.php");

try {
    $hr_db = Database::getConnection('hr');

} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $hr_db->prepare('SELECT * FROM tbl_user2 WHERE U_Name = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && $password === $user['U_Password']) {
        $_SESSION['user_id'] = $user['Emp_No'];
        echo json_encode(['success' => '1', 'message' => 'Login success']);
    } else {
        echo json_encode(['danger' => '0', 'message' => 'Incorrect username/password']);
    }
}
?> -->

<?php
if (session_status() === PHP_SESSION_NONE) session_start(); // Start session

require_once($main_root . "/db/db.php");

try {
    $hr_db = Database::getConnection('hr');
} catch (\PDOException $e) {
    die(json_encode(['danger' => '1', 'message' => 'Database connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        die(json_encode(['danger' => '1', 'message' => 'Username and password are required']));
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $hr_db->prepare('SELECT * FROM tbl_user2 WHERE U_Name = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['U_Password'])) {
            session_regenerate_id(true); // Secure session

            $_SESSION['user_id'] = $user['Emp_No'];
            $_SESSION['csrf_token1'] = bin2hex(random_bytes(32)); 

            echo json_encode(['success' => '1', 'message' => 'Login success']);
        } else {
            echo json_encode(['danger' => '0', 'message' => 'Incorrect username/password']);
        }
    } else {
        echo json_encode(['danger' => '0', 'message' => 'Incorrect username/password']);
    }
}
?>
