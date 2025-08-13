<?php
	use app\controllers\pagosController;
	$insAlumno = new pagosController();	

	$pagoid=$insLogin->limpiarCadena($url[1]);

	$datos=$insAlumno->BuscarPago($pagoid);
	
	if($datos->rowCount()==1){
		$datos=$datos->fetch(); 

		if ($datos['pago_archivo']!=""){
			$imagen = APP_URL.'app/views/imagenes/pagos/'.$datos['pago_archivo'];
		}else{
			$imagen = APP_URL.'app/views/dist/img/sinpago.jpg';
		} 
	}else{
		include "<?php echo APP_URL; ?>/app/views/inc/error_alert.php";
	}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Madificación pagos</title>
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
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>

	<!-- fileinput -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.css">
    
	<style>
		.errorMSG {
		  display: none;
		}

		input:invalid {
		  box-shadow: 0 0 2px 1px red;
		}

		input:invalid ~ .errorMSG{
		 
		  width: 180px;
		  font-size: 12px;		  
		  color: red;
		  vertical-align: top;
		  margin: 0;
		}

		input:focus:invalid {
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
							<h1 class="m-0">Modificación rubro: <?php echo $datos['RUBRO']; ?></h1>
						</div><!-- /.col -->
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Inicio</a></li>
								<li class="breadcrumb-item active">Ficha Alumno</li>
							</ol>
						</div><!-- /.col -->
					</div><!-- /.row -->
				</div><!-- /.container-fluid -->
			</div>
			<!-- /.content-header -->

			<!-- Main content -->
			<section class="content">				
				<!-- /.container-fluid información alumno -->
				<div class="container-fluid">

					<div class="row">
						<div class="col-md-3">
							<div class="card card-secondary">		
								<div class="card-header">
									<h3 class="card-title">Pago realizado</h3>
								</div>						
								<div class="card-body">																			
									<div class="row">									
										
										<div class="col-md-6">
											<div class="form-group">
												<label for="pago_valor">Pago</label>
												<input type="text" class="pull-right form-control" style="text-align:right;" value="<?php echo $datos['pago_valor']; ?>" disabled>
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label for="pago_saldo">Saldo</label>
												<input type="text" class="form-control" style="text-align:right;" value="<?php echo $datos['pago_saldo']; ?>" disabled>
											</div>
										</div>								
									
										<div class="col-md-12 ">
											<div class="form-group">
												<label for="pago_archivo">Imagen pago</label>
												<div class="text-center">	
													<div class="row">
														<div class="col-sm-6">							
															<a href="<?php echo $imagen ?>" data-toggle="lightbox" data-title="Pago" data-gallery="gallery">
																<img src="<?php echo $imagen ?>" class="profile-user-img img-fluid mb-2" alt="white sample"/>
															</a>	
														</div>
													</div>
												</div>													
											</div>
										<!-- /.form-group -->	
										</div>												
									</div>									
								</div><!-- /.card-body -->
							</div>
							<!-- /.card -->
						</div>

						<div class="col-md-9">
							<div class="card">
								
								<div class="card-body">
									<div class="tab-content">
										<div class="active tab-pane" id="pension"> 
											<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/pagosAjax.php" method="POST" autocomplete="off" >
											<input type="hidden" name="modulo_pagos" value="actualizaruniforme">											
											<input type="hidden" name="pago_id" value="<?php echo $pagoid; ?>">
																	<!-- Post -->
												<div class="row">
													<div class="col-md-3">
														<div class="form-group campo">
															<label for="pago_fecha">Fecha de pago</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="pago_fecha" name="pago_fecha" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="<?php echo $datos['pago_fecha']; ?>" required>
																
															</div>
															<!-- /.input group -->
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label for="pago_fecharegistro">Fecha de registro</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="pago_fecharegistro" name="pago_fecharegistro" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="<?php echo $datos['pago_fecharegistro']; ?>" required>
															</div>
															<!-- /.input group -->
														</div>								
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label for="pago_periodo">Periodo(mes/año)</label>															
															<input type="text" class="form-control" id="pago_periodo" name="pago_periodo" value="<?php echo $datos['pago_periodo']; ?>" required>															
														</div>								
													</div>
													
													<div class="col-md-3">
														<div class="form-group">
														<label for="pago_talla">Talla</label>
														<select class="form-control select2" id="pago_talla" name="pago_talla" required>																									
															<?php echo $insAlumno->listarOptionTalla($datos['pago_talla']); ?>
														</select>	
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_valor">Valor</label>
															<input type="text" class="pull-right form-control" style="text-align:right;" id="pago_valor" name="pago_valor" placeholder="0.00" value="<?php echo $datos['pago_valor']; ?>" required>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_saldo">Saldo</label>
															<input type="text" class="form-control" style="text-align:right;" id="pago_saldo" name="pago_saldo" placeholder="0.00" value="<?php echo $datos['pago_saldo']; ?>">
														</div>
													</div>
													
													<div class="col-md-4">
														<div class="form-group">
														<label for="pago_formapagoid">Forma de pago</label>
														<select class="form-control select2" id="pago_formapagoid" name="pago_formapagoid" onchange="ocultarDiv()" >																									
															<?php echo $insAlumno->listarOptionPagoid($datos['pago_formapagoid']); ?>
														</select>	
														</div>
													</div>
													<div class="col-md-2 oculto" id="miDiv">
														<div class="form-group">
															<label for="pago_archivo">Imagen pago</label>		
															<div class="input-group">											
																<div class="fileinput fileinput-new" data-provides="fileinput">
																	<div class="fileinput-new thumbnail" style="width: 130px; height: 158px;" data-trigger="fileinput">
																		<img src="<?php echo $imagen ?>" id="miImagen">
																	</div>
																	<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 130px; max-height: 158px"></div>
																	<div>
																		<span class="bton bton-white bton-file">
																			<span class="fileinput-new">Subir Pago</span>
																			<span class="fileinput-exists">Cambiar</span>
																			<input type="file" name="pago_archivo" id="pago_archivo">
																		</span>
																		<a href="#" class="bton bton-orange fileinput-exists" data-dismiss="fileinput">X</a>
																	</div>
																</div>
															</div>		
														</div>
													<!-- /.form-group -->	
													</div>

													<div class="col-md-10">
														<div class="form-group">
														<label for="pago_concepto">Detalle</label>
														<textarea class="form-control" id="pago_concepto" name="pago_concepto" placeholder="Detalle del pago" rows="5" ><?php echo $datos['pago_concepto']; ?></textarea>
														</div>
													</div>													
												</div>
												<button type="submit" class="btn btn-success btn-sm">Actualizar</button>
												<?php include "./app/views/inc/btn_back.php";?>
											</form>										
										</div>									
									</div>
									<!-- /.tab-content -->
								</div><!-- /.card-body -->
							</div>
							<!-- /.card -->
						</div>
					</div>
				</div>
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

	<!-- AdminLTE App -->
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
		
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>

	<!--script src="app/views/dist/js/main.js" ></script-->
    
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
		$(document).ready(function () {
			$("#pago_fecha").keyup(function () {
				var value = $(this).val();
				$("#pago_fecharegistro").val(value);	
				
				var fecha = new Date(value);
				// Array con los nombres de los meses
				var nombresMeses = [
				"Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
				"Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
				];

				// Obtener el mes (los meses van de 0 a 11 en JavaScript)
				var mesNumero = fecha.getMonth();
				var mesNombre = nombresMeses[mesNumero];
				var año = fecha.getFullYear();

				$("#pago_periodo").val(mesNombre + " / " + año );
			});
		});
		
	</script>

	<script>
		function ocultarDiv() {
			var select = document.getElementById("pago_formapagoid");
			var div = document.getElementById("miDiv");
			var input = document.getElementById("pago_archivo");	
			var imagen = document.getElementById("miImagen");		

			if (select.value === "FEF" || select.value === "FJU") {
				div.style.display = "none"; // Ocultar el div si se selecciona "Ocultar Div"
				input.value = null;		
				imagen.src = "";	
			} else {
				div.style.display = "block"; // Mostrar el div por defecto
				input.value = null;		
				imagen.src = "";		
			}
		}
	</script>

	<!-- Page specific script -->
	<script>
		$(function () {
			$(document).on('click', '[data-toggle="lightbox"]', function(event) {
			event.preventDefault();
			$(this).ekkoLightbox({
				alwaysShowClose: true
			});
			});

			$('.filter-container').filterizr({gutterPixels: 3});
			$('.btn[data-filter]').on('click', function() {
			$('.btn[data-filter]').removeClass('active');
			$(this).addClass('active');
			});
		})
	</script>

  </body>
</html>