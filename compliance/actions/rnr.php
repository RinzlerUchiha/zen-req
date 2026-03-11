<?php
	require_once($com_root."/db/database.php"); 
	require_once($com_root."/db/core.php"); 
	require_once($com_root."/db/mysqlhelper.php");
	// $pdo = Database::connect();
	$hr_pdo = HRDatabase::connect();

	date_default_timezone_set('Asia/Manila');
	$_t = isset($_POST['_t']) ? $_POST['_t'] : '';

	// if($_SESSION['csrf_token1']==$_t){

		// foreach ($_POST as $input=>$val) {
		// 	$_POST[$input]=cleanjavascript($val);
		// }
		
		// $action=$_POST['action'];
		$action = isset($_POST['action']) ? $_POST['action'] : '';

		$user_empno=fn_get_user_info('bi_empno');
		
		switch ($action) {
			case 'add':

				$article=$_POST["article"];
				$articlename=$_POST["articlename"];

				try {
					$sql=$hr_pdo->prepare("INSERT INTO tbl_rnr_article (rnrart_articlecode, rnrart_articlename) VALUES (?, ?)");
					if($sql->execute(array($article, $articlename))){
						echo "1";
					}
				} catch (Exception $e) {
					echo "Something went wrong. Please try again";
				}

				break;
			
			case 'edit':
					$id=$_POST["id"];
					$article=$_POST["article"];
					$articlename=$_POST["articlename"];

					try {
						$sql=$hr_pdo->prepare("UPDATE tbl_rnr_article SET rnrart_articlecode=?, rnrart_articlename=? WHERE rnrart_id=?");
						if($sql->execute(array($article, $articlename, $id))){
							echo "1";
						}
					} catch (Exception $e) {
						echo "Something went wrong. Please try again";
					}
					
				break;

			case 'del':
				$id1=$_POST['id'];
				$sql=$hr_pdo->prepare("DELETE FROM tbl_rnr_article WHERE rnrart_id=?");
				if($sql->execute(array($id1))){
					$sql1=$hr_pdo->prepare("DELETE FROM tbl_rnr_sec WHERE rnrsec_articleid=?");
					$sql1->execute(array($id1));
					echo "1";
				}
				break;

			case 'add2':

				$article=$_POST["article"];
				$section=$_POST["section"];
				$sectionname=$_POST["sectionname"];
				$content=$_POST["content"];

				try {
					$sql=$hr_pdo->prepare("INSERT INTO tbl_rnr_sec (rnrsec_articleid, rnrsec_section, rnrsec_sectionname, rnrsec_content) VALUES (?, ?, ?, ?)");
					if($sql->execute(array($article, $section, $sectionname, $content))){
						echo "1";
					}
				} catch (Exception $e) {
					echo "Something went wrong. Please try again";
				}

				break;
			
			case 'edit2':
					$id=$_POST["id"];
					$article=$_POST["article"];
					$section=$_POST["section"];
					$sectionname=$_POST["sectionname"];
					$content=$_POST["content"];

					try {
						$sql=$hr_pdo->prepare("UPDATE tbl_rnr_sec SET rnrsec_articleid=?, rnrsec_section=?, rnrsec_sectionname=?, rnrsec_content=? WHERE rnrsec_id=?");
						if($sql->execute(array($article, $section, $sectionname, $content, $id))){
							echo "1";
						}
					} catch (Exception $e) {
						echo "Something went wrong. Please try again";
					}
					
				break;

			case 'del2':
				$id1=$_POST['id'];
				$sql=$hr_pdo->prepare("DELETE FROM tbl_rnr_sec WHERE rnrsec_id=?");
				if($sql->execute(array($id1))){
					echo "1";
				}
				break;
		}

	// }else{
	// 	echo "Error. Refresh this page.";
	// }
?>