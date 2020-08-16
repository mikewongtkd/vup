<?php

include_once( 'config.php' );

function get_forms_for_uuid() {
	global $vidroot;
	$file = "$vidroot/uuid2poomsae.json";
	$text = file_get_contents( $file );
	return json_decode( $text, true );
}

function respond_invalid_uuid() {
	header( 'HTTP/1.1 404 Not Found' );
	echo( 'Invalid uuid' );
	exit();
}

if( ! isset( $_GET[ 'uuid' ])) { respond_invalid_uuid(); }
$uuid = $_GET[ 'uuid' ];
if( ! preg_match( '/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/', $uuid )) { respond_invalid_uuid(); }

$rounds  = [[ 'id' => 'prelim', 'name' => 'Preliminary Round' ], [ 'id' => 'semfin', 'name' => 'Semi-Final Round' ], [ 'id' => 'finals', 'name' => 'Final Round' ]];
$lookup  = get_forms_for_uuid();

if( ! array_key_exists( $uuid, $lookup )) { respond_invalid_uuid(); }
$poomsae = $lookup[ $uuid ];

?>
<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="include/bootstrap/latest/css/bootstrap.min.css" />
    <link rel="stylesheet" href="include/alertifyjs/latest/css/alertify.min.css" />
    <link rel="stylesheet" href="include/alertifyjs/latest/css/themes/bootstrap.min.css" />
    <link rel="stylesheet" href="include/dropzone/latest/dropzone.min.css" />
    <link rel="stylesheet" href="include/fontawesome/latest/css/all.min.css" />
    <link rel="stylesheet" href="include/vup/css/vup.css" />
  </head>
  <body>
    <main role="main">

      <section class="jumbotron text-center">
        <div class="container">
          <h1 class="jumbotron-heading">Upload Your Poomsae Videos</h1>
		  <p class="lead text-muted">Welcome <b><?= $poomsae[ 'athname' ] ?></b>. Please upload your poomsae videos.</p>
        </div>
      </section>

      <div class="album py-5 bg-light">
        <div class="container">
		<div class="registration-info">
			<div class="athlete-name"><?= $poomsae[ 'athname' ] ?></div>
			<div class="division-name"><?= $poomsae[ 'divname' ] ?></div>
		</div>
<?php foreach( $rounds as $round ):
	if( array_key_exists( $round[ 'id' ], $poomsae )):
		$rid   = $round[ 'id' ];
		$rname = $round[ 'name' ];
		$list  = $poomsae[ $rid ];
?>
          <div class="row">

<?php foreach( $list as $i => $formname ):
	$ordinal = $i == 0 ? 'First' : 'Second';
	$formid  = "{$rid}-{$i}";
?>
            <div class="col-md-6">
              <div class="card mb-4 box-shadow">
                <div class="card-body">
					<p class="round-and-form"><b><?= $rname ?></b> <span class="primary"><?= $ordinal ?> Poomsae</span></p>
					<p class="poomsae-name"><h4><?= $formname ?></h4></p>
					<p class="vid-preview" id="<?= $formid ?>-preview">
						<?php if( file_exists( "$vidroot/$uuid/$formid.png" )):
							$message = "If you want to replace this video, please choose a file to upload.";
							$validation = "$vidroot/videos/$uuid/$formid.json";
							$transcode = "$vidroot/videos/$uuid/$formid.$ext";
							if( file_exists( $validation )) {
								$text    = file_get_contents( $validation );
								$results = json_decode( $text, true );
								$class   = null;
								if( $results[ 'status' ] == 'success' ) { $class = 'text-success'; } else
								if( $results[ 'status' ] == 'fail'    ) { $class = 'text-danger'; }
								$message = "<span class=\"{$class}\">{$results[ 'description' ]}.</span> {$message}";
							}
						?>
						<!-- <video class="video-keyframe" src="videos/<?= $uuid ?>/<?= $formid ?>.mp4" /> -->
						<img class="video-keyframe" src="thumbs/<?= $uuid ?>/<?= $formid ?>.png" />
						<?php else:
							$message = "Please choose a file to upload.";

						endif; ?>
					</p>
				  <form>
				  <p id="<?= $formid ?>-progress"><?= $message ?></p>
					<input type="file" name="<?= $formid ?>" id="<?= $formid ?>-upload" />
				  </form>
                </div>
              </div>
            </div>
<?php endforeach; ?>

          </div>
<?php
	endif;
endforeach;
?>

        </div>
      </div>

    </main>

    <footer class="text-muted">
      <div class="container">
        <p>&copy;2020 Mike Wong, Licensed to Vaztic LLC</p>
      </div>
    </footer>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="include/jquery/latest/jquery.min.js"></script>
    <script src="include/alertifyjs/latest/alertify.min.js"></script>
    <script src="include/bootstrap/latest/js/bootstrap.min.js"></script>
    <script src="include/dropzone/latest/dropzone.min.js"></script>
	<script src="include/tus-js/latest/tus.min.js"></script>
    <script>
$(() => {
	let reader = {};
	let file   = {};
	let chunk  = { size : 2 * 1024 * 1024 };
	// ============================================================
	function start_upload( ev ) {
	// ============================================================
		ev.preventDefault();

		let target = $( ev.target );
		let formid = target.attr( 'name' );
		file       = target.get( 0 ).files[ 0 ];
		console.dir(file);
		console.log(JSON.stringify(file));

		$( `#${formid}-progress` ).html( `<span class="spinner-border text-secondary" role="status"><span class="sr-only">Uploading</span></span> Uploading File<span class="percentage">, Please Wait</span>` );

		// Create a new tus upload
		var upload = new tus.Upload(file, {
			endpoint: "/files/", // need to configure a proxy to the tusd
			retryDelays: [0, 3000, 5000, 10000, 20000],
			overridePatchMethod: true,
			chunkSize: 1024*1024,
			metadata: {
				filename: file.name,
				filetype: file.type,
				formid: target.attr( 'name' ),
				uuid: '<?= $uuid ?>'
			},
			onError: function(error) {
				console.log("Failed because: " + error)
			},
			onProgress: function(bytesUploaded, bytesTotal) {
				var percent_done = Math.floor( ( bytesUploaded / bytesTotal ) * 100 );
				$( `#${formid}-progress .percentage` ).html( ` - ${percent_done}%` );
			},
			onSuccess: function() {
				console.log("Download %s from %s", upload.file.name, upload.url);
				// Update upload progress
				$( `#${formid}-progress` ).html( 'Upload Complete!' );
				// validate_video( formid, file.ext );

			}
		})
		// Start the upload
		upload.start()
	}

<?php
foreach( $rounds as $round ):
	if( ! array_key_exists( $round[ 'id' ], $poomsae )) { continue; }
	$rid   = $round[ 'id' ];
	$list  = $poomsae[ $rid ];

	foreach( $list as $i => $formname ):
		$formid  = "{$rid}-{$i}";
?>
	$( '#<?= $formid ?>-upload' ).off( 'change' ).change(( ev ) => { start_upload( ev ); $( 'input[type="file"]' ).hide(); });
<?php
	endforeach;
endforeach;
?>

});
    </script>

  </body>
</html>
