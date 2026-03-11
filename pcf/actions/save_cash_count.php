<?php
require_once($pcf_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

try {
    $pcf_db = Database::getConnection('pcf');

    $user_id = $_SESSION['user_id'];
    $unit = $_POST['unit'] ?? '';
    $outdept = $_POST['outdept'] ?? '';
    $datecount = $_POST['datecount'] ?? '';
    $end_pcf = str_replace(',', '', $_POST['end_pcf'] ?? '');
    $end_bal = str_replace(',', '', $_POST['end_bal'] ?? '');
    $_1000 = str_replace(',', '', $_POST['_1000'] ?? '');
    $_500  = str_replace(',', '', $_POST['_500'] ?? '');
    $_200 = str_replace(',', '', $_POST['_200'] ?? '');
    $_100 = str_replace(',', '', $_POST['_100'] ?? '');
    $_50 = str_replace(',', '', $_POST['_50'] ?? '');
    $_20 = str_replace(',', '', $_POST['_20'] ?? '');
    $_10 = str_replace(',', '', $_POST['_10'] ?? '');
    $_5 = str_replace(',', '', $_POST['_5'] ?? '');
    $_1 = str_replace(',', '', $_POST['_1'] ?? '');
    $loosecoin = str_replace(',', '', $_POST['loosecoin'] ?? '');

    // Insert new record
    $insertStmt = $pcf_db->prepare("INSERT INTO tbl_cash_count (
        cc_empno, cc_outdept, cc_unit, cc_cash_on_hand, cc_end_balance,
        cc_1000, cc_500, cc_200, cc_100, cc_50,
        cc_20, cc_10, cc_5, cc_1, cc_loose_coin, cc_date, cc_time
    ) VALUES (
        :cc_empno, :cc_outdept, :cc_unit, :cc_cash_on_hand, :cc_end_balance,
        :cc_1000, :cc_500, :cc_200, :cc_100, :cc_50,
        :cc_20, :cc_10, :cc_5, :cc_1, :cc_loose_coin, :cc_date, NOW()
    )"); // ← closed parenthesis and quote added here

    $insertStmt->execute([
        ':cc_empno' => $user_id,
        ':cc_outdept' => $outdept,
        ':cc_unit' => $unit,
        ':cc_cash_on_hand' => $end_pcf,
        ':cc_end_balance' => $end_bal,
        ':cc_1000' => $_1000,
        ':cc_500' => $_500,
        ':cc_200' => $_200,
        ':cc_100' => $_100,
        ':cc_50' => $_50,
        ':cc_20' => $_20,
        ':cc_10' => $_10,
        ':cc_5' => $_5,
        ':cc_1' => $_1,
        ':cc_loose_coin' => $loosecoin,
        ':cc_date' => $datecount
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Data inserted successfully']);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
