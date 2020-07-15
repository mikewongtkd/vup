<?php

/* ========================================
  VARIABLES
======================================== */

// chunk variables
$fileId     = $_FILES[ 'file' ][ 'name' ];
$fileId     = preg_replace( '/\.\w*$/', '', $fileId );
$chunkIndex = $_POST[ 'dzchunkindex' ] + 1;
$chunkTotal = $_POST[ 'dztotalchunkcount' ];

// file path variables
$targetPath = "/usr/local/vaztic/videos";
$fileType   = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
$fileSize   = $_FILES["file"]["size"];
$filename   = null;
if( $chunkTotal == 0 ) {
	$filename   = "{$fileId}.{$fileType}";
} else {
	$filename   = "{$fileId}-{$chunkIndex}.{$fileType}";
}
$targetFile = "{$targetPath}/{$filename}";

// change directory permissions
// chmod( $targetPath, 0777 ) or die( "Could not modify directory permissions $targetPath." );

/* ========================================
  DEPENDENCY FUNCTIONS
======================================== */

function respond($info = null, $filelink = null, $status = "error") {
	if( $status == "error" ) { header( "HTTP/1.0 500 Internal Server Error" ); }
	die(json_encode([
		"status"    => $status,
		"info"      => $info,
		"file_link" => $filelink
	]));
};

/* ========================================
  CHUNK UPLOAD
======================================== */

move_uploaded_file( $_FILES['file']['tmp_name'], $targetFile );

// Be sure that the file has been uploaded
if ( ! file_exists( $targetFile )) respond( "An error occurred and we couldn't upload the requested file." );
chmod( $targetFile, 0777) or respond( "Could not reset permissions on uploaded chunk." );

respond( null, null, "success" );
