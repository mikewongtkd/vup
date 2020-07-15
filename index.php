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
				  <div class="vaztic-upload-dropzone dropzone" action="upload.php" id="prelim-0"></div>
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
(() => {
	$( '.vaztic-upload-dropzone' ).dropzone({
		url: "upload.php",
		maxFilesize: 1024 * 1024,
		acceptedFiles: "video/*",
		dataType : "HTML",
		timeout: 0,
		success: ( file, response ) => {
			console.log( 'SUCCESS', file, response );
		},
		error: ( file, response ) => {
			console.log( 'ERROR', file, response );
		}
	});
});
    </script>

  </body>
</html>
