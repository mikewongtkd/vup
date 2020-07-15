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
          <p class="lead text-muted">Welcome <code>Athlete Name</code>. Please upload your poomsae videos.</p>
        </div>
      </section>

      <div class="album py-5 bg-light">
        <div class="container">

          <div class="row">
            <div class="col-md-6">
              <div class="card mb-4 box-shadow">
                <div class="card-body">
                  <p class="card-text"><code>Poomsae Name</code></p>
				  <form>
					<p id="prelim-0-progress">Please choose a file and click on "Upload" to upload the file.</p>
					<input type="file" name="prelim-0" id="prelim-0-upload" />
					<input class="btn btn-primary" type="submit" value="Upload" id="prelim-0-upload-submit" />
				  </form>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group">
                      <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                    </div>
                    <small class="text-muted">Status</small>
                  </div>
                </div>
              </div>
            </div>

          </div>
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
	let chunk  = { size : 2048 * 1024 };

	function start_upload( ev ) {
		ev.preventDefault();

		reader = new FileReader();
		file   = $( '#prelim-0-upload' ).get( 0 ).files[ 0 ];
		upload_file( 0 );
	}

	$( '#prelim-0-upload-submit' ).off( 'click' ).click( start_upload );

	function upload_file( start ) {
		chunk.next = start + chunk.size + 1;
		var blob = file.slice( start, chunk.next );

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
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					console.log( jqXHR, textStatus, errorThrown );
				},
				success: function( data ) {
					var size_done = start + chunk.size;
					var percent_done = Math.floor( ( size_done / file.size ) * 100 );

					if ( chunk.next < file.size ) {
						// Update upload progress
						$( '#prelim-0-progress' ).html( `Uploading File -  ${percent_done}%` );

						// More to upload, call function recursively
						upload_file( chunk.next );
					} else {
						// Update upload progress
						$( '#prelim-0-progress' ).html( 'Upload Complete!' );
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
