<?php
require_once($lv_root."/db/db_functions.php"); 
$trans = new Transactions;
$con1 = $trans->connect();
$load = 'break';
  if (isset($_SESSION['user_id'])) {
      $user_id = $_SESSION['user_id'];
  }
if(isset($_POST['get'])){

  // $user_id = $trans->getUser($_SESSION['DEMOHR_UID'], 'Emp_No');
  // $position = getjobinfo($user_id, "jrec_position");

  $user_assign_list = $trans->check_auth($user_id, 'DTR');
  $user_assign_list .= ($user_assign_list != "" ? "," : "").$user_id;
  $user_assign_arr = explode(",", $user_assign_list);

  $approver = $trans->get_assign('manualdtr','approve',$user_id) ? 1 : 0;

  switch ($_POST['get']) {
    case 'pending':
    case 'approved':
    case 'denied':
    case 'cancelled':

      if($_POST['get'] == 'approved'){

        $from = $_POST['from'];
        $to = $_POST['to'];

        $sql = $con1->prepare("SELECT a.*, TRIM(CONCAT(b.bi_emplname, ', ', b.bi_empfname, ' ', b.bi_empext)) AS empname 
          FROM tbl_break_validation a
          LEFT JOIN tbl201_basicinfo b ON b.bi_empno = a.brv_empno AND b.datastat = 'current'
          WHERE a.brv_stat = ? AND FIND_IN_SET(a.brv_empno, ?) > 0 AND a.brv_date BETWEEN ? AND ?");
        $sql->execute([ $_POST['get'], $user_assign_list, $from, $to ]);

        echo "<h5>".date("M d, Y", strtotime($from))." - ".date("M d, Y", strtotime($to))."</h5>";

      }else{

        $sql = $con1->prepare("SELECT a.*, TRIM(CONCAT(b.bi_emplname, ', ', b.bi_empfname, ' ', b.bi_empext)) AS empname 
          FROM tbl_break_validation a
          LEFT JOIN tbl201_basicinfo b ON b.bi_empno = a.brv_empno AND b.datastat = 'current'
          WHERE a.brv_stat = ? AND FIND_IN_SET(a.brv_empno, ?) > 0");
        $sql->execute([ $_POST['get'], $user_assign_list ]);

        echo "<h5>All Time</h5>";

      }

      echo "<button class='btn btn-outline-secondary btn-sm float-left' onclick=\"loadtab('" . $_POST['get'] . "')\"><i class='fas fa-sync-alt'></i> Reload</button>";
      echo "<div class='table-container'style='padding:10px'>";
      echo "<table id='pendingTable' style='width:100%'>";
      echo "<thead>";
      echo "<tr>";
      echo "<th>Name</th>";
      echo "<th>Date</th>";
      echo "<th>Break</th>";
      echo "<th>Reason</th>";
      // if(in_array($_POST['get'], ['pending', 'approved'])){
        echo "<th></th>";
      // }
      echo "</tr>";
      echo "</thead>";

      echo "<tbody>";
      foreach ($sql->fetchall(PDO::FETCH_ASSOC) as $v) {
        echo "<tr>";
        echo "<td>" . $v['empname'] . "</td>";
        echo "<td>" . $v['brv_date'] . "</td>";
        echo "<td>" . ($v['brv_break'] == '00:00' ? 'NONE' : $v['brv_break']) . "</td>";
        echo "<td>" . $v['brv_reason'] . "</td>";
        echo "<td>";
        if($_POST['get'] == 'pending'){

          if($v['brv_empno'] == $user_id){
            echo "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm m-1\" title='Add' data-toggle=\"modal\" data-reqact=\"edit\" data-reqid=\"".$v['brv_id']."\" data-reqemp=\"".$v['brv_empno']."\" data-reqdt=\"".$v['brv_date']."\" data-reqbreak=\"".$v['brv_break']."\" data-reqreason=\"".htmlspecialchars($v['brv_reason'])."\" data-target=\"#breakeditModal\"><i class='fa fa-edit'></i></button>";
            echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqid=\"".$v['brv_id']."\" data-reqemp=\"".$v['brv_empno']."\"><i class='fa fa-times-circle'></i></button>";
          }

          if($v['brv_empno'] != $user_id && in_array($v['brv_empno'], $user_assign_arr) && $approver == 1){
            echo "<button type=\"button\" class=\"reqapprove btn btn-outline-primary btn-sm m-1\" title=\"Approve\" data-reqid=\"".$v['brv_id']."\" data-reqemp=\"".$v['brv_empno']."\"><i class='fa fa-check'></i></button>";
            
            echo "<button type=\"button\" class=\"reqdeny btn btn-outline-danger btn-sm m-1\" title='Deny' data-reqid=\"".$v['brv_id']."\" data-reqemp=\"".$v['brv_empno']."\"><i class='fa fa-times'></i></button>";
          }

        }else if($_POST['get'] == 'approved' && $v['brv_empno'] != $user_id && in_array($v['brv_empno'], $user_assign_arr) && $approver == 1){
          echo "<button type=\"button\" class=\"reqcancel btn btn-outline-danger btn-sm m-1\" title='Cancel' data-reqid=\"".$v['brv_id']."\" data-reqemp=\"".$v['brv_empno']."\"><i class='fa fa-times-circle'></i></button>";
        }
        echo "</td>";
        echo "</tr>";
      }
      echo "</tbody>";
      echo "</table>";
      echo "</div>";
      break;

    case 'notification':
      


      break;
    
    default:
      // code...
      break;
  }
}
?>