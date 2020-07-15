<?php
include_once( 'config.php' );

$require = [
	'width'  => 1280,
	'height' => 720,
	'fps'    => 60
];

function pngpath( $uuid, $formid ) {
	global $webroot;
	$pngpath = "$webroot/thumbs/$uuid";
	if( ! file_exists( $pngpath )) { mkdir( $pngpath ); }
	return "$pngpath/{$formid}.png";
}

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

$vid     = "{$uuid}/{$formid}.{$ext}"; 
$vidpath = vidpath( $vid );
$pngpath = pngpath( $uuid, $formid );

if( ! $vidpath ) {
	respond( null, '{"status":"fail","description":"Invalid Video ID/Path"}' );
	exit();
}

try {
	$response   = `/usr/local/bin/ffprobe $vidpath 2>&1 | grep fps`;
	preg_match( '/,\s*(\d+)x(\d+)\s*/', $response, $matches );
	$width      = $matches[ 1 ];
	$height     = $matches[ 2 ];
	preg_match( '/,\s*(\d+(?:.\d+)?)\s*fps\b/', $response, $matches );
	$fps        = ceil( floatval( $matches[ 1 ]));
	$check      = [];
	$check[ 'width' ]       = $width >= $require[ 'width' ];
	$check[ 'height' ]      = $height >= $require[ 'height' ];
	$check[ 'resolution' ]  = $check[ 'width' ] && $check[ 'height' ];
	$check[ 'orientation' ] = $width > $height;
	$check[ 'framerate' ]   = $fps >= $require[ 'fps' ];
	$check[ 'all' ]         = $check[ 'resolution' ] && $check[ 'orientation' ] && $check[ 'framerate' ];

	$found[ 'resolution' ]  = [ 'width' => $width, 'height' => $height ];
	$found[ 'orientation' ] = $check[ 'resolution' ] ? 'landscape' : 'portrait';
	$found[ 'framerate' ]   = $fps;

	// ===== CREATE THUMBNAIL
	`/usr/local/bin/ffmpeg -i $vidpath -ss 5 -vframes 1 $pngpath 2>&1`;

	// ===== REPORT THE VALIDATION CHECK RESULTS
	if( $check[ 'all' ] ) {
		respond( $vidpath, '{"status":"success","description":"Video meets resolution, framerate, and orientation requirements","found":' . json_encode( $found ) . ',"file":"' . $vidpath . '"}' );
	} else {
		$failed = [];
		if( ! $check[ 'resolution' ])  { array_push( $failed, "resolution requirements (found {$width}x{$height}; {$require[ 'height' ]}P required)" ); }
		if( ! $check[ 'orientation' ]) { array_push( $failed, "orientation requirements (found {$width}x{$height}; wide orientation required)" ); }
		if( ! $check[ 'framerate' ])   { array_push( $failed, "framerate requirements (found {$fps} fps; {$require[ 'fps' ]} fps required)" ); }
		$f = sizeof( $failed );
		if( $f > 1 ) { $failed[ $f - 1 ] = 'or ' . $failed[ $f - 1 ]; }
		$message = implode( ', ', $failed );
		respond( $vidpath, '{"status":"fail","description":"Video does not meet ' . $message . '","found": ' . json_encode( $found ) . ',"file":"' . $vidpath . '"}' );
	}

} catch( Exception $e ) {
	respond( $vidpath, '{"status":"fail","description":"' . $e->getMessage() . '","file":"' . $vidpath . '"}' );
}
