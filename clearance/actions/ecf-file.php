<?php
	include '../db/database.php';
	require"../db/core.php";
	include('../db/mysqlhelper.php'); 
	$hr_pdo = HRDatabase::connect();

	date_default_timezone_set('Asia/Manila');

	$act=$_POST['ecfup_act'];

	switch ($act) {

		case 'addfile':
				
				$ecfid=$_POST['ecfup_ecf'];
				$ecfup_desc=$_POST['ecfup_desc'];

				$fileName=$_FILES["ecfup_file"]["name"];
				$fileSize=$_FILES["ecfup_file"]["size"];
				$fileType=$_FILES["ecfup_file"]["type"];
				$fileTmpName=$_FILES["ecfup_file"]["tmp_name"];

				$getFileType = pathinfo(basename($fileName),PATHINFO_EXTENSION);
				$fileName=basename($fileName,".".$getFileType);
				$files = "../ecf-uploads/".$ecfid."/".$fileName.".".$getFileType;
				$i=1;
				if(!file_exists("../ecf-uploads/".$ecfid)){
					mkdir("../ecf-uploads/".$ecfid);
				}
				while(file_exists($files)){
					$fileName=$fileName."(".$i.")";
					$files="../ecf-uploads/".$ecfid."/".$fileName.".".$getFileType;
					$i++;
				}
				$allowedExts=array("application/pdf","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application/vnd.ms-excel",".csv","application/msword","application/vnd.ms-powerpoint","application/vnd.openxmlformats-officedocument.wordprocessingml.document","image/png","image/jpeg");
				if(in_array($fileType, $allowedExts)){
					//File upload path
					$uploadPath="../ecf-uploads/".$ecfid."/".$fileName.".".$getFileType;
					//function for upload file
					if(move_uploaded_file($fileTmpName,$uploadPath)){
						$sql=$hr_pdo->prepare("INSERT INTO db_ecf2.tbl_uploads(up_ecfid, up_desc, up_file, up_timestamp) VALUES(?,?,?,?)");
						if($sql->execute(array($ecfid, $ecfup_desc, $fileName.".".$getFileType, date("Y-m-d H:i:s")))){
							echo "1";
							_log("Uploaded ECF File to ECF ID:$ecfid. ID: ".$hr_pdo->lastInsertId());
						}
					}
					
				}else{
					echo "Invalid File Type";
				}

			break;

		case 'editfile':
				
				$ecfup_id=$_POST['ecfup_id'];
				$ecfid=$_POST['ecfup_ecf'];
				$ecfup_desc=$_POST['ecfup_desc'];

				$fileName=$_FILES["ecfup_file"]["name"];
				$fileSize=$_FILES["ecfup_file"]["size"];
				$fileType=$_FILES["ecfup_file"]["type"];
				$fileTmpName=$_FILES["ecfup_file"]["tmp_name"];

				$getFileType = pathinfo(basename($fileName),PATHINFO_EXTENSION);
				$fileName=basename($fileName,".".$getFileType);
				$files = "../ecf-uploads/".$ecfid."/".$fileName.".".$getFileType;
				$i=1;
				if(!file_exists("../ecf-uploads/".$ecfid)){
					mkdir("../ecf-uploads/".$ecfid);
				}
				$sql_fnd="SELECT up_file FROM db_ecf2.tbl_uploads WHERE up_ecfid='$ecfid' AND up_id='$ecfup_id' LIMIT 1";
				foreach ($hr_pdo->query($sql_fnd) as $fnd) {
					unlink("../ecf-uploads/".$ecfid."/".$fnd['up_file']);
				}
				while(file_exists($files)){
					$fileName=$fileName."(".$i.")";
					$files="../ecf-uploads/".$ecfid."/".$fileName.".".$getFileType;
					$i++;
				}
				if($ecfup_id!=''){
					if($_FILES["ecfup_file"]["name"]!=''){
						$allowedExts=array("application/pdf","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application/vnd.ms-excel",".csv","application/msword","application/vnd.ms-powerpoint","application/vnd.openxmlformats-officedocument.wordprocessingml.document","image/png","image/jpeg");
						if(in_array($fileType, $allowedExts)){
							//File upload path
							$uploadPath="../ecf-uploads/".$ecfid."/".$fileName.".".$getFileType;
							//function for upload file
							if(move_uploaded_file($fileTmpName,$uploadPath)){
								$sql=$hr_pdo->prepare("UPDATE db_ecf2.tbl_uploads SET up_desc=?, up_file=?, up_timestamp=? WHERE up_ecfid=? and up_id=?");
								if($sql->execute(array($ecfup_desc, $fileName.".".$getFileType, date("Y-m-d H:i:s"), $ecfid, $ecfup_id))){
									echo "1";
									_log("Update ECF File of ECF ID:$ecfid. ID: $ecfid");
								}
							}
							
						}else{
							echo "Invalid File Type";
						}
					}else{
						$sql=$hr_pdo->prepare("UPDATE db_ecf2.tbl_uploads SET up_desc=?, up_timestamp=? WHERE up_ecfid=? and up_id=?");
						if($sql->execute(array($ecfup_desc, date("Y-m-d H:i:s"), $ecfid, $ecfup_id))){
							echo "1";
							_log("Update ECF File of ECF ID:$ecfid. ID: $ecfid");
						}
					}
				}

			break;

		case 'delfile':
				
				$ecfup_id=$_POST['ecfup_id'];
				$ecfid=$_POST['ecfup_ecf'];

				$sql_fnd="SELECT up_file FROM db_ecf2.tbl_uploads WHERE up_ecfid='$ecfid' AND up_id='$ecfup_id' LIMIT 1";
				foreach ($hr_pdo->query($sql_fnd) as $fnd) {
					unlink("../ecf-uploads/".$ecfid."/".$fnd['up_file']);
				}

				$sql=$hr_pdo->prepare("DELETE FROM db_ecf2.tbl_uploads WHERE up_ecfid=? and up_id=?"); 
				if($sql->execute(array($ecfid, $ecfup_id))){
					echo "1";
					_log("Remove ECF File of ECF ID:$ecfid. ID: $ecfid");
				}

			break;
	}
?>