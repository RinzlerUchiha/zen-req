<?php
if(isset($_FILES['upload'])){
   // ------ Process your file upload code -------
        $filen = $_FILES['upload']['tmp_name']; 
        $con_images = "../announcement/".$_FILES['upload']['name'];

        // Set a maximum height and width
        $width = 2000;
        $height = 2000;

        // Get new dimensions
        // list($width_orig, $height_orig) = getimagesize($_FILES['upload']['tmp_name']);

        // $ratio_orig = $width_orig/$height_orig;

        // if ($width/$height > $ratio_orig) {
        //    $width = $height*$ratio_orig;
        // } else {
        //    $height = $width/$ratio_orig;
        // }

        // Resample
        // $image_p = imagecreatetruecolor($width, $height);

        $imageFileType = pathinfo(basename($_FILES["upload"]["name"]),PATHINFO_EXTENSION);
        // if($imageFileType=="jpg" || $imageFileType=="jpeg" || $imageFileType=="JPG" || $imageFileType=="JPEG"){
        //     $image = imagecreatefromjpeg($_FILES["upload"]["tmp_name"]);
        //     imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
        //     imagejpeg($image_p, $con_images);
        // }else if($imageFileType=="png" || $imageFileType=="PNG"){
        //     $image = imagecreatefrompng($_FILES["upload"]["tmp_name"]);
        //     imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
        //     imagepng($image_p, $con_images);
        // }else if($imageFileType=="gif" || $imageFileType=="GIF"){
        //     move_uploaded_file($filen, $con_images );
        // }

        if($imageFileType=="jpg" || $imageFileType=="jpeg" || $imageFileType=="JPG" || $imageFileType=="JPEG" || $imageFileType=="png" || $imageFileType=="PNG" || $imageFileType=="gif" || $imageFileType=="GIF"){
            move_uploaded_file($filen, $con_images );
        }

       $url = $con_images;

   $funcNum = $_GET['CKEditorFuncNum'] ;
   // Optional: instance name (might be used to load a specific configuration file or anything else).
   $CKEditor = $_GET['CKEditor'] ;
   // Optional: might be used to provide localized messages.
   $langCode = $_GET['langCode'] ;
    
   // Usually you will only assign something here if the file could not be uploaded.
   $message = '';
   echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
}