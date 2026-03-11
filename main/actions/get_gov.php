<?php
require_once($main_root . "/db/db.php");

try {
    $port_db = Database::getConnection('hr');
    $yearMonth = date("Y-m-d");
    // Fetch ads from tbl_announcement
    $stmt = $port_db->prepare("SELECT a.ann_title AS title, a.ann_content AS image
                               FROM tbl_announcement a
                               LEFT JOIN tbl201_basicinfo b ON a.ann_approvedby = b.bi_empno
                               WHERE (a.ann_type = 'GOVERNMENT' AND DATE_FORMAT(a.ann_end, '%Y-%m-%d') >= ?)
                               OR (a.ann_type = 'GOVERNMENT' AND a.ann_end = '0000-00-00 00:00:00')
                               AND a.ann_status = 'Approved'
                               GROUP BY a.ann_id
                               ORDER BY a.ann_id DESC");
    $stmt->execute([$yearMonth]);
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($ads);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
