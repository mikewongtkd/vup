<?php
$storeFolder = '/usr/local/vaztic/videos';
 
if (!empty($_FILES)) {
    $tempFile = $_FILES[ 'file' ][ 'tmp_name' ];
    $targetPath = "/uploads";
    $targetFile =  $targetPath. $_FILES[ 'file' ][ 'name' ];
    move_uploaded_file( $tempFile, $targetFile );
}
?>     
