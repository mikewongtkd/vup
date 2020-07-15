<?php

function get_forms_for_uuid() {
	$file = '/usr/local/vaztic/uuid2poomsae.json';
	$text = file_get_contents( $file );
	return json_decode( $text, true );
}

function respond_invalid_uuid() {
	header( 'HTTP/1.0 404 Not Found' );
	echo( 'Invalid uuid' );
	exit();
}

if( ! isset( $_GET[ 'uuid' ])) { respond_invalid_uuid(); }
$uuid = $_GET[ 'uuid' ];
if( ! preg_match( '/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/', $uuid )) { respond_invalid_uuid(); }

$rounds  = [[ id => 'prelim', name => 'Preliminary Round' ], [ id => 'semfin', name => 'Semi-Final Round' ], [ id => 'finals', name => 'Final Round' ]];
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
          <h1 class="jumbotron-heading">Upload your Poomsae Videos</h1>
		  <p class="lead text-muted">Welcome <b><?= $poomsae[ 'athname' ] ?></b>. Please upload your poomsae videos.</p>
        </div>
      </section>

      <div class="album py-5 bg-light">
        <div class="container">
		<h3><?= $poomsae[ 'divname' ] ?></h3>
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
					<p class="round-and-form"><b><?= $rname ?></b> <span class="primary"><?= $ordinal ?> form</span></p>
					<p class="poomsae-name"><h4><?= $formname ?></h4></p>
				  <form>
					  <p id="<?= $formid ?>-progress">Please choose a file to upload.</p>
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
        <p>&copy;2020 Vaztic LLC</p>
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
    <script src="https://sdk.amazonaws.com/js/aws-sdk-2.713.0.min.js"></script>
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

		reader = new FileReader();
		file   = target.get( 0 ).files[ 0 ];
		upload_file( target, 0 );
	}

<?php 
foreach( $rounds as $round ):
	if( array_key_exists( $round[ 'id' ], $poomsae )) { 
		$rid   = $round[ 'id' ];
		$list  = $poomsae[ $rid ];
	}

	foreach( $list as $i => $formname ): 
		$formid  = "{$rid}-{$i}";
?>
	$( '#<?= $formid ?>-upload' ).off( 'change' ).change( start_upload );
<?php 
	endforeach; 
endforeach;
?>

	// ============================================================
	function upload_file( target, start ) {
	// ============================================================
		chunk.next = start + chunk.size + 1;
		let blob   = file.slice( start, chunk.next );
		let formid = target.attr( 'name' );

		reader.onloadend = function( ev ) {
			if ( ev.target.readyState !== FileReader.DONE ) {
				return;
			}

			$.ajax( {
				url: 'chunk.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {
					file_data: ev.target.result,
					file: file.name,
					file_type: file.type,
					formid : formid,
					uuid : '<?= $uuid ?>'
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					console.log( jqXHR, textStatus, errorThrown );
				},
				success: function( data ) {
					var size_done = start + chunk.size;
					var percent_done = Math.floor( ( size_done / file.size ) * 100 );

					if ( chunk.next < file.size ) {
						// Update upload progress
						$( `#${formid}-progress` ).html( `Uploading File -  ${percent_done}%` );

						// More to upload, call function recursively
						upload_file( target, chunk.next );
					} else {
						// Update upload progress
						$( `#${formid}-progress` ).html( 'Upload Complete!' );
/*
						$.ajax({
							url: 'verify.php',
							type: 'POST',
							dataType: 'json',
							cache: false,
							data: {
								formid : formid,
								uuid : '<?= $uuid ?>'
							},
							error: ( jqXHR, textStatus, errorThrown ) => {
								console.log( jqXHR, textStatus, errorThrown );
							},
							success: ( response ) => {
							}
						});
 */
					}
				}
			} );
		};

		reader.readAsDataURL( blob );
	}
});
    </script>

  </body>
</html>
