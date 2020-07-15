<?php
	function decode_chunk( $data ) {
		$data = explode( ';base64,', $data );

		if ( ! is_array( $data ) || ! isset( $data[1] ) ) {
			return false;
		}

		$data = base64_decode( $data[1] );
		if ( ! $data ) {
			return false;
		}

		return $data;
	}

	function respond_not_found() {
		header( "HTTP/1.0 404 Not Found" );
		echo '{"status":"fail","detail":"Invalid UUID or filename"}';
		exit();
	}

	function respond_internal_server_error() {
		header( "HTTP/1.0 500 Internal Server Error" );
		echo '{"status":"fail","detail":"Bad chunk"}';
		exit();
	}

	$file_name = $_POST[ 'file' ];
	preg_match( '/\.(\w+)$/', $file_name, $match );
	$file_ext  = $match[ 1 ];
	$file_name = $_POST[ 'formid' ] . ".{$file_ext}";
	$uuid      = $_POST[ 'uuid' ];
	$file_type = $_POST[ 'file_type' ];

	if( ! preg_match( '/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/', $uuid )) { respond_not_found(); }
	if( ! preg_match( '/^(?:prelim|semfin|finals)\-\d/', $file_name )) { respond_not_found(); }


	$file_path = "/usr/local/vaztic/videos/{$uuid}";
	$file_data = decode_chunk( $_POST[ 'file_data' ] );
	$file      = "{$file_path}/$file_name";

	if( ! file_exists( $file_path )) { 
		mkdir( $file_path );
		chmod( $file_path, 0777 );
	}

	if( false === $file_data ) { respond_internal_server_error(); }

	file_put_contents( $file, $file_data, FILE_APPEND );

	header( "HTTP/1.0 200 OK" );
	echo '{"status":"success","detail":"' . $file_name . ' (' . $file_type . ') uploaded."}';
	exit();
?>
