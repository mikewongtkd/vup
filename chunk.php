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

	$file_name = $_POST[ 'file' ];
	$file_type = $_POST[ 'file_type' ];

	$file_path = "/usr/local/vaztic/videos/{$file_name}";
	$file_data = decode_chunk( $_POST[ 'file_data' ] );

	if( false === $file_data ) {
		header( "HTTP/1.0 500 Internal Server Error" );
		echo '{"status":"fail","detail":"Bad chunk"}';
		exit();
	}

	file_put_contents( $file_path, $file_data, FILE_APPEND );


	echo '{"status":"success","detail":"' . $file_name . ' (' . $file_type . ') uploaded."}';
	exit();
?>
