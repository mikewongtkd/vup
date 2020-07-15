<?php

$require = [
	'width'  => 1280,
	'height' => 720,
	'fps'    => 60
];

function vidpath( $vid ) {
	$vidpath = $vid; # MW Some transformation here
	if( ! file_exists( $vidpath )) { return null; }
	return $vidpath;
}

function respond( $vidpath, $result, $db ) {
	$db->exec( "insert into vidcheck ( vid, lastchecked, result ) values ( '$vidpath', DateTime( 'now' ), '$result' )" );
	echo( $result );
	exit();
}

$formid = $_POST[ 'formid' ];
$uuid   = $_POST[ 'uuid' ];
$vidpath = vidpath( $vid );

if( ! $vidpath ) {
	echo( '{"status":"fail","description":"Invalid Video ID/Path"}' );
	exit();
}

try {
	$response   = `/usr/local/bin/ffprobe $vidpath 2>&1 | grep fps`;
	preg_match( '/,\s*(\d+)x(\d+)\s*/', $response, $matches );
	$width      = $matches[ 1 ];
	$height     = $matches[ 2 ];
	preg_match( '/,\s*(\d+(?:\.d+)?)\s*fps\s*/', $response, $matches );
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

	// ===== REPORT THE VALIDATION CHECK RESULTS
	if( $check[ 'all' ] ) {
		respond( $vidpath, '{"status":"success","description":"Video meets resolution, framerate, and orientation requirements","found":' . json_encode( $found ) . '}', $db );
	} else {
		$failed = [];
		if( ! $check[ 'resolution' ])  { array_push( $failed, "resolution requirements (found {$width}x{$height}; {$require[ 'height' ]}P required)" ); }
		if( ! $check[ 'orientation' ]) { array_push( $failed, "orientation requirements (found {$width}x{$height}; wide orientation required)" ); }
		if( ! $check[ 'framerate' ])   { array_push( $failed, "framerate requirements (found {$fps} fps; {$require[ 'fps' ]} fps required)" ); }
		$f = sizeof( $failed );
		if( $f > 1 ) { $failed[ $f - 1 ] = 'or ' . $failed[ $f - 1 ]; }
		$message = implode( ', ', $failed );
		respond( $vidpath, '{"status":"fail","description":"Video does not meet ' . $message . '","found": ' . json_encode( $found ) . '}', $db );
	}

} catch( Exception $e ) {
	respond( $vidpath, '{"status":"fail","description":"' . $e->getMessage() . '"}', $db );
}
