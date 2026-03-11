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
	    	_13A.13a_id,
		    _13A.13a_memo_no,
		    _13A.13a_cc,
		    CONCAT(bi_from.bi_empfname,' ',bi_from.bi_emplname) AS issued_by_name,
		    13a_date AS 1_3ADate,
		    CONCAT(bi_to.bi_empfname,' ',bi_to.bi_emplname) AS to_name,
		    _13A.13a_regarding
		    
		FROM 
		    tbl_13a _13A
		LEFT JOIN 
		    tbl201_basicinfo bi_from ON _13A.13a_issuedby = bi_from.bi_empno
		LEFT JOIN 
    		tbl201_basicinfo bi_to ON _13A.13a_to = bi_to.bi_empno
		WHERE 
		    _13A.13a_stat = 'pending'
		    AND bi_from.datastat = 'current'
		    AND (
            _13A.13a_from = ? 
            OR _13A.13a_to= ? 
            OR _13A.13a_issuedby= ? 
            OR REPLACE(_13A.13a_notedby, ' ', '') LIKE CONCAT(?, ',%')
            -- OR REPLACE(_13A.ir_cc, ' ', '') LIKE CONCAT('%,', ?)
            OR REPLACE(_13A.13a_cc, ' ', '') LIKE CONCAT('%,', ?, ',%')
        )
		GROUP BY 
    	_13A.13a_memo_no, bi_from.bi_empno
	");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
$incident_report = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($incident_report)) {

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
      foreach ($incident_report as $ir) {
     	echo "<tr>";
     	echo "<td>";
     	echo isset($ir['1_3ADate']) && !empty($ir['1_3ADate']) ? htmlspecialchars((new DateTime($ir['1_3ADate']))->format('F j, Y')): 'Invalid date';	
     	echo "</td>";
     	echo "<td>" . htmlspecialchars($ir['issued_by_name']) . "</td>";
     	echo "<td>" . htmlspecialchars($ir['to_name']) . "</td>";
     	echo "<td>" . htmlspecialchars($ir['13a_regarding']) . "</td>";
     	echo "<td style='display:flex;margin: 0 -5px;'>
     		<a style='margin: 0 5px;' class='btn btn-primary btn-mini' class='btn btn-primary btn-mini' href='_13Aopen?_13aID=" . htmlspecialchars($ir['13a_id']) . "'><i class='icofont icofont-eye-alt' style='font-size:14px;'></i></a>
     		<a style='margin: 0 5px;' class='btn btn-success btn-mini' class='btn btn-primary btn-mini' href='#!'><i class='zmdi zmdi-edit' style='font-size:14px;'></i></i></a>
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
