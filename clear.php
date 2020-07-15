<?php

include_once( 'config.php' );

function vidpath( $vid ) {
	global $vidroot;
	$vidpath = "$vidroot/videos/{$vid}";
	return $vidpath;
}

function respond_invalid_input() {
	header( 'HTTP/1.0 404 Not Found' );
	echo( 'Invalid input' );
	exit();
}

function respond( $vidpath, $result ) {
	header( 'HTTP/1.0 200 OK' );
	echo( $result );
	if( $vidpath ) {
		$json = preg_replace( '/\.\w+$/', '.json', $vidpath );
		$fp = fopen( $json, 'w' );
		fwrite( $fp, $result );
		fclose( $fp );
	}
	exit();
}

$formid = $_POST[ 'formid' ];
$uuid   = $_POST[ 'uuid' ];
$ext    = $_POST[ 'ext' ];

if( ! preg_match( '/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/', $uuid )) { respond_invalid_input(); }
if( ! preg_match( '/^(?:prelim|semfin|finals)\-\d+$/', $formid )) { respond_invalid_input(); }
if( ! preg_match( '/^\w+$/', $ext )) { respond_invalid_input(); }

$vid      = "{$uuid}/{$formid}.{$ext}"; 
$vidpath  = vidpath( $vid );
$json     = "{$uuid}/{$formid}.json";
$jsonpath = vidpath( $json );

if( file_exists( $jsonpath )) {
	$text = file_get_contents( $jsonpath );
	$data = json_decode( $text, true );
	if( array_key_exists( 'file', $data ) && file_exists( $data[ 'file' ]) && preg_match( "|^$vidroot/videos/$uuid|", $data[ 'file' ])) {
		$archive = date( 'Y-m-d-H-i-s' );
		`mv {$data[ 'file' ]} {$data[ 'file' ]}.{$archive}`;
		unlink( $jsonpath );
	}
}

echo( '{"status":"success"}' );
exit();

?>
