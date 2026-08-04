<?php
require_once(__DIR__ . "/db.php");

function SendSmsToEmpNo($empno, $msg, $tag)
{
	$hr_pdo = Database::getConnection('hr');
	$number = "";

	$sql_sms = $hr_pdo->prepare("SELECT 
			b1.bi_empno AS empno, 
			TRIM(CONCAT(b1.bi_emplname, ', ', b1.bi_empfname, '')) AS full_name,
			IF(LENGTH(p1.pi_mobileno) < 11 AND LEFT(p1.pi_mobileno, 1) = '9', CONCAT('0', p1.pi_mobileno), p1.pi_mobileno) AS personal, /*personal*/
			IF(LENGTH(p1.pi_cmobileno) < 11 AND LEFT(p1.pi_cmobileno, 1) = '9', CONCAT('0', p1.pi_cmobileno), p1.pi_cmobileno) AS company1, /*company*/
			IF(LENGTH(p2.acca_sim) < 11 AND LEFT(p2.acca_sim, 1) = '9', CONCAT('0', p2.acca_sim), p2.acca_sim) AS company2 /*company*/
		FROM tbl201_basicinfo b1
		LEFT JOIN tbl201_persinfo p1 ON p1.pi_empno = b1.bi_empno 
			AND p1.datastat = 'current' 
			AND (IFNULL(p1.pi_mobileno, '') != '' OR IFNULL(p1.pi_cmobileno, '') != '')
		LEFT JOIN tbl_account_agreement p2 ON p2.acca_empno = b1.bi_empno 
			AND NOT( p2.acca_dtissued IS NULL OR p2.acca_dtissued='' OR p2.acca_dtissued='0000-00-00' )
			AND ( p2.acca_dtreturned IS NULL OR p2.acca_dtreturned='' OR p2.acca_dtreturned='0000-00-00' )
		JOIN tbl201_jobinfo j1 ON j1.ji_empno = b1.bi_empno AND LOWER(j1.ji_remarks) = 'active'
		WHERE b1.datastat = 'current' AND FIND_IN_SET(b1.bi_empno, :empno) > 0");

	$sql_sms->execute([ ':empno' => $empno ]);

	foreach ($sql_sms->fetchall(PDO::FETCH_ASSOC) as $v) {

		if($v['company1'] != ''){
			$number = $v['company1'];
		}else if($v['company2'] != ''){
			$number = $v['company2'];
		}else if($v['personal'] != ''){
			$number = $v['personal'];
		}

		if($number != ""){
			break;
		}
	}
		
		if($number != ""){
		// Regular expression to match "+63" or "63" at the beginning
		$pattern = "/^(?:\+63|63)/";
		// Replace the matched prefix with "0"
		$number = preg_replace($pattern, "0", $number);

		$sql = $hr_pdo->prepare("INSERT INTO " . SMS_DB_DATABASE . ".messages (message, msg_created_at, msg_schedule, tag) VALUES(?, NOW(), '', ?)");
		if($sql->execute([ $msg, $tag ])){
			$msg_id = $hr_pdo->lastInsertId();

			$sql1 = $hr_pdo->prepare("INSERT INTO " . SMS_DB_DATABASE . ".recipients (msg, recipient, status, r_created_at) VALUES(?, ?, 'pending', NOW())");
			$sql1->execute([ $msg_id, $number ]);
		}
	}
}


function convertAndCompressImage($tmpPath, $originalName, $destinationDir, $quality = 80, $maxWidth = 1000) {
    $info = getimagesize($tmpPath);
    if ($info === false) {
        return false;
    }

    $mime = $info['mime'];
    // $originalExt = image_type_to_extension($info[2], false); // e.g. "jpeg", "png", "gif"

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($tmpPath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($tmpPath);
            break;
        default:
            return false; // unsupported format
    }

    $width = imagesx($image);
    $height = imagesy($image);

    // Resize if necessary
    if ($width > $maxWidth) {
        $ratio = $maxWidth / $width;
        $newWidth = $maxWidth;
        $newHeight = (int)($height * $ratio);

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNGs (even though we're converting to JPEG)
        if ($mime === 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }

    // Set destination filename
    // $filenameWithoutExt = pathinfo($source, PATHINFO_FILENAME);

    // Prepare filename (same name, new extension if needed)
    $filename = pathinfo($originalName, PATHINFO_FILENAME);
    $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename); // sanitize name

    $finalPath = rtrim($destinationDir, "/") . '/' . $safeFilename . '.jpg';
    imagejpeg($image, $finalPath, $quality);

    imagedestroy($image);
    return $finalPath;
}