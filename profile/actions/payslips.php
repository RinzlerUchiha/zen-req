<?php
require_once($sr_root . "/db/db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $port_db = Database::getConnection('port');
    $hr_db = Database::getConnection('hr');

    $stmt = $hr_db->prepare("
        SELECT psl_from, 
        psl_to, 
        psl_paydate, 
        ps_empno,
        ps_monthlypay, 
        CONCAT(bi_empfname,' ',bi_emplname) AS FULLNAME,
        Dept_Name 
        FROM tbl_payroll 
        LEFT JOIN tbl_payroll_list ON psl_id = ps_listid 
        LEFT JOIN tbl201_basicinfo ON bi_empno = ps_empno
        LEFT JOIN tbl201_jobrec ON jrec_empno = ps_empno
        LEFT JOIN tbl_department ON Dept_Code = jrec_department
        WHERE psl_status = 'approved' 
        AND ps_empno = ? 
        GROUP BY psl_paydate
        ORDER BY psl_from DESC, psl_to DESC
    ");
    $stmt->execute([$user_id]);
    $payslip = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($payslip)) {
        echo '<div class="card-block" id="prof-card">'; // prof-card start
        echo '<div id="specialSkills" class="contact" style="margin-bottom:10px;">';
        foreach ($payslip as $ps) {
            echo '<div class="skill" onclick="get_payslip(' . htmlspecialchars($ps['psl_from']) . ', ' . htmlspecialchars($ps['psl_to']) . ', ' . htmlspecialchars($ps['ps_empno']) . ')">';

            // echo '<div class="image-skill" style="background-image: url(\'\');"></div>';
            echo '<div class="desc-skill">';
            // echo '
            //     <div class="skill-title">
            //         <p id="title">Payslip</p>
            //     </div>
            // ';
            echo '<div class="skill-types">';
            echo '<p id="type"><i class="icofont icofont-calendar"></i> ';
            echo isset($ps['psl_paydate']) && !empty($ps['psl_paydate']) ? htmlspecialchars((new DateTime($ps['psl_paydate']))->format('F j, Y')) : 'Invalid date';
            echo '</p>';
            echo '<p id="type"><i class="icofont icofont-man-in-glasses"></i> Sungold Technologies Inc.</p>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p>No payslips found.</p>';
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
