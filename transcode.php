<?php
include_once( 'config.php' );

function transcodepath( $uuid, $formid ) {
	global $webroot;
	$transcodepath = "$webroot/transcode/$uuid";
	if( ! file_exists( $transcodepath )) { mkdir( $transcodepath ); }
	return "$transcodepath/{$formid}.mp4";
}

function vidpath( $vid ) {
	global $vidroot;
	$vidpath = "$vidroot/videos/{$vid}";
	return $vidpath;
}

function respond_invalid_input() {
	header( 'HTTP/1.1 404 Not Found' );
	echo( 'Invalid input' );
	exit();
}

function respond( $vidpath, $result ) {
	header( 'HTTP/1.1 200 OK' );
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

$vid            = "{$uuid}/{$formid}.{$ext}";
$vidpath        = vidpath( $vid );
$transcodepath  = transcodepath( $uuid, $formid );

if( ! $vidpath ) {
	respond( null, '{"status":"fail","description":"Invalid Video ID/Path"}' );
	exit();
}

try {
	$respose   = `/usr/local/bin/ffmpeg -i $vidpath -c:v libx264 -c:a aac -vf format=yuv420p -s 1920x1080 -movflags +faststart $transcodepath`;

} catch ( Exception $e ) {
	respond( null, $e->getMessage());
}