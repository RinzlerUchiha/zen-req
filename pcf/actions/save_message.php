<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pcf_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
    $user_id = $_SESSION['user_id'];
    $disburNo = $_POST['disbur_no'];
    $comment = $_POST['comments'];

    $sql = "INSERT INTO tbl_comment (com_disb_no, com_sender, com_content, com_type) VALUES ('$disburNo', '$user_id', '$comment', 'reply')";
    if ($conn->query($sql) == TRUE) {
        echo "Comment Sent!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }


$conn->close();
?>