<!DOCTYPE html>
<html lang="en">
	<head>
		<title>DBF2</title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="DBF2 Application">
		<meta name="author" content="Jorge Alberto Ponce Turrubiates">
		
		<link rel="shortcut icon" type="image/x-icon" href="./img/favicon.ico">

		<style type="text/css">
			.body {
			 	min-height: 2000px;
			}

			.navbar-static-top {
			  	margin-bottom: 19px;
			}

			.navbar
			{
				min-height:124px;
			}

			.bsnavbar
			{
			  	margin-bottom: 19px;
			  	height:65px;
				min-height:124px;
			}
			
			.example {
				float: left;
				margin: 15px;
			}
			
			.file_explorer {
				width: 300px;
				height: 300px;
				border-top: solid 1px #BBB;
				border-left: solid 1px #BBB;
				border-bottom: solid 1px #FFF;
				border-right: solid 1px #FFF;
				background: #FFF;
				overflow: scroll;
				padding: 5px;
			}

			.modal-static { 
		        position: fixed;
		        top: 50% !important; 
		        left: 50% !important; 
		        margin-top: -100px;  
		        margin-left: -100px; 
		        overflow: visible !important;
		    }

		    .modal-static,
		    .modal-static .modal-dialog,
		    .modal-static .modal-content {
		        width: 200px; 
		        height: 200px; 
		    }

		    .modal-static .modal-dialog,
		    .modal-static .modal-content {
		        padding: 0 !important; 
		        margin: 0 !important;
		    }

		    .modal-static .modal-content .icon {
		    }
		</style>
		
		<script src="./jQueryFileTree-master/jquery-1.9.1.js" type="text/javascript"></script>
		<script src="./jQueryFileTree-master/jqueryFileTree.js" type="text/javascript"></script>
		<link href="./jQueryFileTree-master/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />
		<link rel="stylesheet" href="./metro-bootstrap-master/dist/css/metro-bootstrap.css" />
		<script src="./bootstrap-3.2.0-dist/js/bootstrap.min.js"></script>
		
		<script type="text/javascript">
			// Drop Table
			function dropTable(dbfName){
				$.post('./drop', {dbf: dbfName},
		                    function(response) {
		                    	console.log(response);
		                }).error(
		                    function(){
		                        console.log('Error executing Post');
		                    }
		                );
			}

			$(document).ready( function() {
				
				$('#explorer').fileTree({ root: './', script: './getfiles', folderEvent: 'click', expandSpeed: 750, collapseSpeed: 750, multiFolder: false }, function(file) { 				
					// Gets File Name
					file = file.substring(2);
					var files = file.split("/");
					var arrLen = files.length - 1;
					file = files[arrLen];

					$('#txtFile').val(file);
				});

				$('#explorer').on('filetreeexpand', 
                        function (e, data){
                            $('#txtPath').val(data.rel);
                });
				
				$("#btn_import").click(function(){
                    if ($('#txtFile').val() == '')
          				alert ('Must Select a DBF FIle');
          			else {
          				$('#processing-modal').modal('toggle');
          				
          				var filename = $('#txtPath').val() + $('#txtFile').val();

	                    $('#label-process').html('Importing: ' + $('#txtFile').val());

	                    $.post('./import', {dbf: filename},
		                    function(response) {
		                    	$('#processing-modal').modal('hide');

		                    	switch(response) {
								    case "1":
								        alert("DBF File not exists");
								        break;
								    case "2":
								    	var r = confirm("Table Already Exists, Want you drop the Table?");
										if (r == true) {
										    dropTable(filename);
										} 								        

								        break;
								    case "3":
								        alert("Error in Server COM");   
								}
		                }).error(
		                    function(){
		                        console.log('Error executing Post');
		                    }
		                );
          			}
                });

                $("#btn_credits").click(function() {
                    $('#window-credits').modal('toggle');
                });
			});
		</script>

	</head>
	
	<body>
		<div class="navbar navbar-default navbar-static-top bsnavbar">
	      <div class="container">
	      	<div class="navbar-header">
	          <h1>DBF2</h1>
	    	</div>

	    	<ul class="nav navbar-nav navbar-right">
              <li class="active" id="btn_credits"><a href="#">Credits<span class="sr-only">(current)</span></a></li>
            </ul>
	      </div>
	    </div>
		
		<div class="container">
			<div class="example">
				<label for="txtPath">Selected Path:</label>
	            <input id="txtPath" type="text" class="form-control" placeholder="Path" name="txtPath" value = "<?php echo $data['dbfdir'] ?>" required disabled>

	            <label for="txtFile">Selected File:</label>
	            <input id="txtFile" type="text" class="form-control" placeholder="File" name="txtFile" required disabled>

				<div id="explorer" class="file_explorer"></div>

				<button id="btn_import" class="btn btn-lg btn-primary btn-block">Import</button>
			</div>
		</div>
	
		<!-- Static Modal Credits -->
        <div class="modal fade" id="window-credits" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Credits</h4>
                     </div>

                    <div class="modal-body">
                        <center>
                            <p><h3>Jorge Alberto Ponce Turrubiates</h3></p>
                            <p><h5><a href="mailto:the.yorch@gmail.com<">the.yorch@gmail.com</a></h5></p>
                            <p><h5><a href="http://the-yorch.blogspot.mx/">Blog</a></h5></p>
                            <p><h5><a href="https://bitbucket.org/yorch81">BitBucket</a></h5></p>
                            <p><h5><a href="https://github.com/yorch81">GitHub</a></h5></p>
                            <p></p>
                        </center>
                    </div>
                </div>
            </div>
        </div>

		<!-- Static Modal -->
		<div class="modal modal-static fade" id="processing-modal" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-body">
						<div class="text-center">
							<img src="./img/processing.gif" class="icon" />
							<h5 id="label-process">Processing ...</h5>
						</div>
					</div>
				</div>
			</div>
	  	</div>
	</body>
</html>