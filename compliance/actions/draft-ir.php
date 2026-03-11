<?php
require_once($com_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

   
$stmt = $port_db->prepare("
	    SELECT 
	    ir.ir_id,
	    ir.ir_subject,
	    ir.ir_date,
	    CONCAT(bi_from.bi_empfname,' ',bi_from.bi_emplname) AS ir_from_fullname,
	    CONCAT(bi_to.bi_empfname,' ',bi_to.bi_emplname) AS ir_to_fullname
	FROM 
	    tbl_ir ir
	LEFT JOIN 
	    tbl201_basicinfo bi_from ON ir.ir_from = bi_from.bi_empno
	LEFT JOIN 
	    tbl201_basicinfo bi_to ON ir.ir_to = bi_to.bi_empno
	WHERE ir.ir_stat = 'draft'
	AND bi_from.datastat = 'current'
	AND ir_from = ?
	GROUP BY ir.ir_id, bi_from.bi_empno
	");
$stmt->execute([$user_id]);
$draft_ir = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($draft_ir)) {

	echo "<table class='sticky-table'>";
	echo "<thead>";
	echo "<tr>";
	echo "<th>Date</th>";
	echo "<th>From</th>";
	echo "<th>To</th>";
	echo "<th>Subject</th>";
	echo "<th></th>";
	echo "</tr>";
	echo "</thead>";
	echo "<tbody>";
      foreach ($draft_ir as $dir) {
     	echo "<tr>";
     	echo "<td>";
     	echo isset($dir['ir_date']) && !empty($dir['ir_date']) ? htmlspecialchars((new DateTime($dir['ir_date']))->format('F j, Y')): 'Invalid date';	
     	echo "</td>";
     	echo "<td>" . htmlspecialchars($dir['ir_from_fullname']) . "</td>";
     	echo "<td>" . htmlspecialchars($dir['ir_to_fullname']) . "</td>";
     	echo "<td>" . htmlspecialchars($dir['ir_subject']) . "</td>";
     	echo "<td style='display:flex;margin: 0 -5px;'>
     		<a style='margin: 0 5px;' class='btn btn-primary btn-mini' href='IRopen?irID=" . htmlspecialchars($dir['ir_id']) . "'><i class='icofont icofont-eye-alt' style='font-size:14px;'></i></a>
     		<a style='margin: 0 5px;' class='btn btn-success btn-mini' href='#!'><i class='icofont icofont-ui-edit' style='font-size:14px;'></i></a>
     		<a style='margin: 0 5px;' class='btn btn-danger btn-mini' href='#!'><i class='icofont icofont-trash' style='font-size:14px;'></i></a>
     	</td>";
     	echo "</tr>";
      } 
    echo "<tbody>"; 
	echo "</table>";
}   

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
