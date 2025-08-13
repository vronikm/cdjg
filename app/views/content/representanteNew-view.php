<?php
	use app\controllers\representanteController;
	$insRepre = new representanteController();
	
	$repreid=$insRepre->limpiarCadena($url[1]);
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Registro nuevo representante</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
	
	<!-- daterange picker -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/daterangepicker/daterangepicker.css">
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- Bootstrap Color Picker -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
	<!-- Tempusdominus Bootstrap 4 -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
	<!-- Bootstrap4 Duallistbox -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
	<!-- BS Stepper -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/bs-stepper/css/bs-stepper.min.css">
	<!-- dropzonejs -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/dropzone/min/dropzone.min.css">
	
	<!-- Theme style -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">


	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>

	<!-- fileinput -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.css">
    
	<style>
		input:invalid {
		  box-shadow: 0 0 2px 1px red;
		}
		input:focus:invalid {
		  box-shadow: none;
		}
		textarea:invalid {
		  box-shadow: 0 0 2px 1px red;
		}
		textarea:focus:invalid {
		  box-shadow: none;
		}
	</style>
  </head>

  <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

		<!-- Preloader -->
		<!--?php require_once "app/views/inc/preloader.php"; ?-->
		<!-- /.Preloader -->

		<!-- Navbar -->
		<?php require_once "app/views/inc/navbar.php"; ?>
		<!-- /.navbar -->

		<!-- Main Sidebar Container -->
		<?php require_once "app/views/inc/main-sidebar.php"; ?>
		<!-- /.Main Sidebar Container -->  

		<!-- vista -->
		<div class="content-wrapper">

			<!-- Content Header (Page header) -->
			<div class="content-header">
				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-sm-6">
							<h1 class="m-0">Nuevo Representante</h1>
						</div><!-- /.col -->
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Inicio</a></li>
								<li class="breadcrumb-item active">Ficha Representante</li>
							</ol>
						</div><!-- /.col -->
					</div><!-- /.row -->
				</div><!-- /.container-fluid -->
			</div>
			<!-- /.content-header -->

			<!-- Main content -->
			<section class="content">				
				<!-- /.container-fluid información representante -->
				<div class="container-fluid">						
					<div class="card">
						<div class="card-header p-2">
							<ul class="nav nav-pills">
								<li class="nav-item"><a class="nav-link active" href="#informacionp" data-toggle="tab">Información Personal</a></li>
								<li class="nav-item"><a class="nav-link" href="#conyuge" data-toggle="tab">Cónyuge</a></li>								
							</ul>
						</div><!-- /.card-header -->
					
						<div class="card-body">
							<div class="tab-content">
								<!-- Tab información del representante -->
								<div class="active tab-pane" id="informacionp">
									<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/representanteAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data">
									<input type="hidden" name="modulo_repre" value="registrar">																						
									<div class="row">
										<div class="col-sm-3">
											<div class="form-group">
												<label for="repre_tipoidentificacion">Tipo identificación</label>
												<select id="repre_tipoidentificacion" class="form-control custom-select2" name="repre_tipoidentificacion" >
													<?php echo $insRepre->listarCatalogoTipoDocumento(); ?>
												</select>
											</div>          
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="repre_identificacion">Identificación</label>                        
												<input type="text" class="form-control" id="repre_identificacion" name="repre_identificacion" placeholder="Identificación" required>
											</div>
										</div>                   
										<div class="col-md-3">                        
											<div class="form-group">
												<label for="repre_apellidopaterno">Apellido paterno</label>
												<input type="text" class="form-control" id="repre_apellidopaterno" name="repre_apellidopaterno" placeholder="Primer apellido" required>
											</div>
										</div>
										<div class="col-md-3">
											<label for="repre_apellidomaterno">Apellido materno</label>
											<input type="text" class="form-control" id="repre_apellidomaterno" name="repre_apellidomaterno" placeholder="Segundo apellido" >
										</div>
										<div class="col-md-3">                        
											<div class="form-group">
												<label for="repre_primernombre">Primer nombre</label>
												<input type="text" class="form-control" id="repre_primernombre" name="repre_primernombre" placeholder="Primer nombre" required>
											</div>
										</div>
										<div class="col-md-3">
											<label for="repre_segundonombre">Segundo nombre</label>
											<input type="text" class="form-control" id="repre_segundonombre" name="repre_segundonombre" placeholder="Segundo nombre" >
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="repre_parentesco">Parentesco</label>
												<select class="form-control select2" style="width: 100%;" id="repre_parentesco" name="repre_parentesco" required>													
													<?php echo $insRepre->listarCatalogoParentesco(); ?>
												</select>
											</div> 
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="repre_sexo">Género</label>
												<div class="form-check">
													<input class="col-sm-1 form-check-input" type="radio" id="repre_sexoM" value="M" name="repre_sexo" required>
													<label class="col-sm-5 form-check-label" for="repre_sexoM" style="font-size: 14px;">Masculino</label>
													<input class="col-sm-1 form-check-input" type="radio" id="repre_sexoF" value="F" name="repre_sexo" >
													<label class="col-sm-4 form-check-label" for="repre_sexoF" style="font-size: 14px;">Femenino</label>
												</div> 
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="repre_direccion">Dirección</label>
												<input type="text" class="form-control" id="repre_direccion" name="repre_direccion" placeholder="Barrio, Calle principal, #casa, calle secundaria" required>
											</div>
										</div>              
										<div class="col-md-3">
											<div class="form-group">
												<label for="repre_correo">Correo</label>
												<input type="text" class="form-control" id="repre_correo" name="repre_correo" placeholder="Correo" required>
											</div> 
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<label for="repre_celular">Celular</label>
												<input type="text" class="form-control" id="repre_celular" name="repre_celular" data-inputmask='"mask": "0999999999"' data-mask placeholder="Celular" required>
											</div> 
										</div>
									</div>									
								</div>

								<!-- Tab información del conyuge representante --> 
								<div class="tab-pane" id="conyuge">
									<div class="row">
										<div class="col-md-3">											
											<div class="form-group">
												<label for="TidentificacionCRep">Tipo identificación</label>
												<select id="conyuge_tipoidentificacion" class="form-control custom-select2" name="conyuge_tipoidentificacion" >
													<?php echo $insRepre->listarCatalogoTipoDocumento(); ?>
												</select>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="conyuge_identificacion">Identificación</label>                        
												<input type="text" class="form-control" id="conyuge_identificacion" name="conyuge_identificacion" placeholder="Identificación" >
											</div>
										</div>                   
										<div class="col-md-3">                        
											<div class="form-group">
												<label for="conyuge_apellidopaterno">Apellido paterno</label>
												<input type="text" class="form-control" id="conyuge_apellidopaterno" name="conyuge_apellidopaterno" placeholder="Primer apellido" >
											</div>
										</div>
										<div class="col-md-3">
											<label for="conyuge_apellidomaterno">Apellido materno</label>
											<input type="text" class="form-control" id="conyuge_apellidomaterno" name="conyuge_apellidomaterno" placeholder="Segundo apellido" >
										</div>
										<div class="col-md-3">                        
											<div class="form-group">
												<label for="conyuge_primernombre">Primer nombre</label>
												<input type="text" class="form-control" id="conyuge_primernombre" name="conyuge_primernombre" placeholder="Primer nombre" >
											</div>
										</div>
										<div class="col-md-3">
											<label for="conyuge_segundonombre">Segundo nombre</label>
											<input type="text" class="form-control" id="conyuge_segundonombre" name="conyuge_segundonombre" placeholder="Segundo nombre" >
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="conyuge_celular">Celular</label>
												<input type="text" class="form-control" id="conyuge_celular" name="conyuge_celular" data-inputmask='"mask": "0999999999"' data-mask placeholder="Celular" >
											</div> 
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="conyuge_correo">Correo</label>
												<input type="text" class="form-control" id="conyuge_correo" name="conyuge_correo" placeholder="Correo" >
											</div> 
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="conyuge_direccion">Dirección</label>
												<input type="text" class="form-control" id="conyuge_direccion" name="conyuge_direccion" placeholder="Barrio, Calle principal, #casa, calle secundaria" >	
											</div>
										</div>              
										<div class="col-md-4">
											<div class="form-group">
												<label for="conyuge_sexo">Género</label>
												<div class="form-check">
													<input class="col-sm-1 form-check-input" type="radio" id="conyuge_sexoM" value="M" name="conyuge_sexo" >
													<label class="col-sm-5 form-check-label" for="conyuge_sexoM">Masculino</label>
													<input class="col-sm-1 form-check-input" type="radio" id="conyuge_sexoF" value="F" name="conyuge_sexo" >
													<label class="col-sm-4 form-check-label" for="conyuge_sexoF">Femenino</label>
												</div> 
											</div>
										</div>               
									</div>										
								</div>										
								<div class="card-footer">						
									<button type="submit" class="btn btn-success btn-sm">Guardar</button>
									<button type="reset" class="btn btn-dark btn-sm">Limpiar</button>						
								</div>	
									
								</form>	
							</div>
							<!-- /.tab-pane -->
						</div><!-- /.card-body -->
						<!-- /.tab-content -->
					</div><!-- /.card -->
				</div><!-- /.container fluid -->
			</section>
			<!-- /.content -->      
		</div>
		<!-- /.vista -->

		<?php require_once "app/views/inc/footer.php"; ?>

		<!-- Control Sidebar -->
		<aside class="control-sidebar control-sidebar-dark">
		<!-- Control sidebar content goes here -->
		</aside>
      <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    
	<!-- jQuery -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	
	<!-- Select2 -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/select2/js/select2.full.min.js"></script>
	<!-- Bootstrap4 Duallistbox -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
	<!-- InputMask -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/moment/moment.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/inputmask/jquery.inputmask.min.js"></script>
	<!-- date-range-picker -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/daterangepicker/daterangepicker.js"></script>
	<!-- bootstrap color picker -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
	<!-- Tempusdominus Bootstrap 4 -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
	<!-- Bootstrap Switch -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
	<!-- BS-Stepper -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bs-stepper/js/bs-stepper.min.js"></script>
	<!-- dropzonejs -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/dropzone/min/dropzone.min.js"></script>

	<!-- AdminLTE App -->
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
		
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>

	<!--script src="app/views/dist/js/main.js" ></script-->
	
	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>
    
	<script>
		$(function () {
			//Initialize Select2 Elements
			$('.select2').select2()

			//Initialize Select2 Elements
			$('.select2bs4').select2({
			theme: 'bootstrap4'
			})

			//Datemask dd/mm/yyyy
			$('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
			//Datemask2 mm/dd/yyyy
			$('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
			//Money Euro
			$('[data-mask]').inputmask()

			//Date picker
			$('#reservationdate').datetimepicker({
				format: 'L'
			});

			//Date and time picker
			$('#reservationdatetime').datetimepicker({ icons: { time: 'far fa-clock' } });

			//Date range picker
			$('#reservation').daterangepicker()
			//Date range picker with time picker
			$('#reservationtime').daterangepicker({
			timePicker: true,
			timePickerIncrement: 30,
			locale: {
				format: 'MM/DD/YYYY hh:mm A'
			}
			})
			//Date range as a button
			$('#daterange-btn').daterangepicker(
			{
				ranges   : {
				'Today'       : [moment(), moment()],
				'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
				'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				'This Month'  : [moment().startOf('month'), moment().endOf('month')],
				'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				},
				startDate: moment().subtract(29, 'days'),
				endDate  : moment()
			},
			function (start, end) {
				$('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
			}
			)

			//Timepicker
			$('#timepicker').datetimepicker({
			format: 'LT'
			})

			//Bootstrap Duallistbox
			$('.duallistbox').bootstrapDualListbox()

			//Colorpicker
			$('.my-colorpicker1').colorpicker()
			//color picker with addon
			$('.my-colorpicker2').colorpicker()

			$('.my-colorpicker2').on('colorpickerChange', function(event) {
			$('.my-colorpicker2 .fa-square').css('color', event.color.toString());
			})

			$("input[data-bootstrap-switch]").each(function(){
			$(this).bootstrapSwitch('state', $(this).prop('checked'));
			})

		})
		// BS-Stepper Init
		document.addEventListener('DOMContentLoaded', function () {
			window.stepper = new Stepper(document.querySelector('.bs-stepper'))
		})

		// DropzoneJS Demo Code Start
		Dropzone.autoDiscover = false

		// Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
		var previewNode = document.querySelector("#template")
		previewNode.id = ""
		var previewTemplate = previewNode.parentNode.innerHTML
		previewNode.parentNode.removeChild(previewNode)

		var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
			url: "/target-url", // Set the url
			thumbnailWidth: 80,
			thumbnailHeight: 80,
			parallelUploads: 20,
			previewTemplate: previewTemplate,
			autoQueue: false, // Make sure the files aren't queued until manually added
			previewsContainer: "#previews", // Define the container to display the previews
			clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
		})

		myDropzone.on("addedfile", function(file) {
			// Hookup the start button
			file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file) }
		})

		// Update the total progress bar
		myDropzone.on("totaluploadprogress", function(progress) {
			document.querySelector("#total-progress .progress-bar").style.width = progress + "%"
		})

		myDropzone.on("sending", function(file) {
			// Show the total progress bar when upload starts
			document.querySelector("#total-progress").style.opacity = "1"
			// And disable the start button
			file.previewElement.querySelector(".start").setAttribute("disabled", "disabled")
		})

		// Hide the total progress bar when nothing's uploading anymore
		myDropzone.on("queuecomplete", function(progress) {
			document.querySelector("#total-progress").style.opacity = "0"
		})

		// Setup the buttons for all transfers
		// The "add files" button doesn't need to be setup because the config
		// `clickable` has already been specified.
		document.querySelector("#actions .start").onclick = function() {
			myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED))
		}
		document.querySelector("#actions .cancel").onclick = function() {
			myDropzone.removeAllFiles(true)
		}
		// DropzoneJS Demo Code End
	</script>
  </body>
</html>