<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $atd_db = Database::getConnection('atd');
    $stmt = $atd_db->prepare("SELECT 
        ai_name AS item,
        ac_name AS category, 
        atd_type AS atdType,
        ai_term AS term,
        ai_take_home AS takehome 
        FROM tbl_atd_item a
        LEFT JOIN tbl_atd_category b
        ON b.`ac_id` = a.`ai_category_id`
        LEFT JOIN tbl_atd_type c
        ON c.`id` = b.`ac_type_id`");
    $stmt->execute();
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<table id="saving-reorder" class="table table-striped table-bordered nowrap">';
    echo '<thead><tr>
              <th>ATD Type</th>
              <th>ATD Category</th>
              <th>ATD Item</th>
              <th># of Payroll</th>
              <th>Take-Home Pay (%)</th>
              <th>Action</th>
          </tr></thead><tbody>';

    if (!empty($list)) {
        foreach ($list as $l) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($l['atdType']) . '</td>';
            echo '<td>' . htmlspecialchars($l['category']) . '</td>';
            echo '<td>' . htmlspecialchars($l['item']) . '</td>';
            echo '<td id="num">' . htmlspecialchars($l['term']) . '</td>';
            echo '<td id="num">' . htmlspecialchars($l['takehome']) . '%</td>';
            echo '<td>
                      <img src="assets/img/atd_icons/edit.png" width="30" height="30">
                      <img src="assets/img/atd_icons/deactive.png" width="30" height="30">
                  </td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6">No data available.</td></tr>';
    }
    echo '</tbody></table>';

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
