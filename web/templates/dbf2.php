<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title>jQuery File Tree Demo</title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		
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
		<link rel="stylesheet" href="./bootstrap-3.2.0-dist/css/bootstrap.min.css" />
		<script src="./bootstrap-3.2.0-dist/js/bootstrap.min.js"></script>
		
		<script type="text/javascript">
			
			$(document).ready( function() {
				
				$('#explorer').fileTree({ root: './', script: './getfiles', folderEvent: 'click', expandSpeed: 750, collapseSpeed: 750, multiFolder: false }, function(file) { 
					file = file.substring(2);
					
					$('#txtFile').val(file);
				});

				$('#explorer').on('filetreeexpand', 
                        function (e, data){
                            $('#txtPath').val(data.rel);
                });
				
				$("#btn_import").click(function(){
                    $('#processing-modal').modal('toggle');

                    $('#label-process').html('Importando: ' + $('#txtFile').val());

                    $.post('./import', {dbf: $('#txtFile').val()},
	                    function(respuesta) {
	                    	$('#processing-modal').modal('hide');
	                        console.log(respuesta);
	                }).error(
	                    function(){
	                        console.log('Error al ejecutar la petición');
	                    }
	                );
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
	      </div>
	    </div>
		
		<div class="example">
			<label for="txtPath">Selected Path:</label>
            <input id="txtPath" type="text" class="form-control" placeholder="Path" name="txtPath" required disabled>

            <label for="txtFile">Selected File:</label>
            <input id="txtFile" type="text" class="form-control" placeholder="File" name="txtFile" required disabled>

			<div id="explorer" class="file_explorer"></div>

			<button id="btn_import" class="btn btn-lg btn-primary btn-block">Import</button>
		</div>
	
	  <!-- Static Modal -->
	  <div class="modal modal-static fade" id="processing-modal" role="dialog" aria-hidden="true">
	      <div class="modal-dialog">
	          <div class="modal-content">
	              <div class="modal-body">
	                  <div class="text-center">
	                      <img src="./img/procesando.gif" class="icon" />
	                      <h5 id="label-process">Procesando... 
	                      </h5>
	                  </div>
	              </div>
	          </div>
	      </div>
	  </div>

	</body>
</html>