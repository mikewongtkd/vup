<?php

	$storeFolder = '/usr/local/vaztic/videos';
	 
	if ( empty( $_FILES )) { 
		echo( '{"status":"fail","details":"No files submitted."}' );
		exit();
	};

	$source = $_FILES[ 'file' ][ 'tmp_name' ];
	$target =  "{$storeFolder}/{$_FILES[ 'file' ][ 'name' ]}";
	move_uploaded_file( $source, $target );

	echo( '{"status":"success","details":"' . "{$source} -> {$target}" . '"}' );
	exit();

	/*
	 * Check for php ini file:
	 *
	 * $ php --ini
	 *
	 * Edit the following
	 * upload_tmp_dir = '/tmp/vaztic'
	 * upload_max_filesize = '1024M'
	 * post_max_size = '1024M'
	 * max_file_uploads = 5
	 * memory_limit = 512M
	 *
	 * $ chmod a+wx '/tmp/vaztic'
?>     
