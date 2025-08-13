<?php
	use app\controllers\userController;
	$insUsuario = new userController();	

	$empleadoid=$insUsuario->limpiarCadena($url[1]);

	$datos=$insUsuario->BuscarEmpleado($empleadoid);
	if($datos->rowCount()==1){
		$datos=$datos->fetch();
		if ($datos['empleado_foto']!=""){
			$foto = APP_URL.'app/views/imagenes/fotos/empleado/'.$datos['empleado_foto'];
		}else{
			$foto = APP_URL.'app/views/dist/img/foto.jpg';
		}
		$usuario_identificacion = $datos['empleado_identificacion'];
		$usuario_nombre 	 	= $datos['empleado_nombre'];
		$usuario_correo 	 	= $datos['empleado_correo'];
		$usuario_celular 	 	= $datos['empleado_celular'];
		$usuario_sede 	 		= $datos['Sede'];
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?>| Nuevo usuario</title>
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

	<!-- Theme style -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">

	<!-- fileinput -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.css">
  </head>
  <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
		
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
						<h1 class="m-0">Nuevo usuario</h1>
					</div><!-- /.col -->
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="#">Nuevo</a></li>
							<li class="breadcrumb-item active">Dashboard v1</li>
						</ol>
					</div><!-- /.col -->
				</div><!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content-header -->

		<!-- Main content -->
		<section class="content">
			<form class="FormularioAjax" id="quickForm" action="<?php echo APP_URL; ?>app/ajax/usuarioAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
			<input type="hidden" name="modulo_usuario" value="registrar">
			<input type="hidden" name="usuario_empleadoid" value="<?php echo $empleadoid; ?>">
			<input type="hidden" name="usuario_nombre" value="<?php echo $usuario_nombre; ?>">
			<div class="container-fluid">
			<!-- Small boxes (Stat box) -->
				<div class="card card-default">
					<div class="card-header">
						<h3 class="card-title">Información de acceso al sistema</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>

					<!-- card-body -->					
					<div class="card-body">						
						<!-- row -->	
						<div class="row">						
							<div class="col-md-2">
								<div class="form-group">
									<label for="usuario_foto">Foto</label>		
									<div class="input-group">											
										<div class="fileinput fileinput-new" data-provides="fileinput">
											<div class="fileinput-new thumbnail" style="width: 116px; height: 144px;" data-trigger="fileinput">
												<img src="<?php echo $foto; ?>">
											</div>
											<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 116px; max-height: 144px"></div>
										</div>
									</div>		
								</div>
								<!-- /.form-group -->								
							</div>
							<!-- /.col -->
							<div class="col-md-10">
								<div class="row">
									<div class="col-md-2">
										<div class="form-group">
											<label for="usuario_identificacion">Identificación</label>                        
											<input type="text" class="form-control" id="usuario_identificacion" name="usuario_identificacion" value="<?php echo $usuario_identificacion; ?>" disabled>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label for="usuario_nombre">Nombre</label>
											<input type="text" class="form-control" id="usuario_nombre" name="usuario_nombre" value="<?php echo $usuario_nombre; ?>" disabled>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label for="usuario_email">Correo</label>
											<input type="email" class="form-control" id="usuario_email" name="usuario_email" value="<?php echo $usuario_correo; ?>" disabled>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label for="usuario_movil">Celular</label>
											<input type="text" class="form-control" id="usuario_movil" name="usuario_movil" value="<?php echo $usuario_celular; ?>" disabled>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label for="usuario_sede">Sede</label>
											<input type="text" class="form-control" id="usuario_sede" name="usuario_sede" value="<?php echo $usuario_sede; ?>" disabled>
										</div>
									</div>									
								</div>

								<div class="row">
								<div class="col-md-3">
										<div class="form-group">
										<label for="usuario_rolid">Seguridad Rol</label>
										<select class="form-control select2" id="usuario_rolid" name="usuario_rolid">											
											<option value="" selected="selected">Seleccionar rol</option>
											<?php echo $insUsuario->listarOptionRol(""); ?>
										</select>	

										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label for="usuario_usuario">Usuario</label>
											<input type="text" class="form-control" id="usuario_usuario" name="usuario_usuario" placeholder="Usuario" requerid>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label for="usuario_clave">Clave</label>
											<input type="password" class="form-control" id="usuario_clave" name="usuario_clave" requerid>	
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label for="usuario_clave2">Repetir clave</label>
											<input type="password" class="form-control" id="usuario_clave1" name="usuario_clave2" requerid>	
										</div>
									</div>
								</div>
								<!-- /.form-group -->
								<div class="form-group">
									<label for="usuario_sedeid">Asignar otra sede</label>
									<select class="duallistbox" id="usuario_sedeid" name="usuario_sedeid[]" multiple="multiple" value="<?php echo $usuario_celular; ?>" disabled>
										<?php echo $insUsuario->listarOptionSede(""); ?> 
									</select>
								</div>
							</div>
							<!-- /.col -->							
						</div>
						<!-- /.row -->
					</div> 
					<!-- /.card-body -->					
				</div>
			<!-- /.row -->
			</div><!-- /.container-fluid -->
			<div class="card-footer">						
				<button type="submit" class="btn btn-success btn-sm">Guardar</button>
				<button type="reset" class="btn btn-dark btn-sm">Limpiar</button>						
			</div>
			</form>
		</section>
		<!-- /.content -->  
	<?php
		}else{
			include "./app/views/inc/error_alert.php";
		}
	?>    
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
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery-validation/jquery.validate.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery-validation/additional-methods.min.js"></script>
    
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

	<script>
		$(function () {			
			$('#quickForm').validate({
				rules: {
					usuario_rolid: {
						required: true       
					},
					usuario_usuario: {
						required: true       
					},
					usuario_clave: {
						required: true       
					},
					usuario_clave2: {
						required: true
					},
				},
				messages: {
					usuario_rolid: {
						required: "Seleccione un rol"
					},
					usuario_usuario: {
						required: "Ingrese un nombre de usuario"
					},
					usuario_clave: {
						required: "La contraseña debe ser mayor a 6 caracteres",
						minlength: "Your password must be at least 7 characters long"
					},
					usuario_clave2: {
						required: "La contraseña debe ser mayor a 6 caracteres",
						minlength: "Your password must be at least 7 characters long"
					},
				},
				errorElement: 'span',
				errorPlacement: function (error, element) {
				error.addClass('invalid-feedback');
				element.closest('.form-group').append(error);
				},
				highlight: function (element, errorClass, validClass) {
				$(element).addClass('is-invalid');
				},
				unhighlight: function (element, errorClass, validClass) {
				$(element).removeClass('is-invalid');
				}
			});
		});
	</script>

  </body>
</html>