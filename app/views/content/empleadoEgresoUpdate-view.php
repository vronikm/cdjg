<?php
	date_default_timezone_set("America/Guayaquil");

	use app\controllers\empleadoController;
	$insEgreso = new empleadoController();	

	$empleadoid	= ($url[1] != "") ? $url[1] : 0;
	$egreso_id 	= ($url[2] != "") ? $url[2] : 0;

	$datosEgreso=$insEgreso->BuscarEgreso($egreso_id);

	if($datosEgreso->rowCount()==1){
		$datosEgreso=$datosEgreso->fetch();
		
		$egreso_formaegresoid	= $datosEgreso['egreso_formaegresoid'];
		$egreso_tipoid			= $datosEgreso['egreso_tipoid'];
		$egreso_empleadoid		= $datosEgreso['egreso_empleadoid'];
		$egreso_valor			= $datosEgreso['egreso_valor'];
		$egreso_pendiente		= $datosEgreso['egreso_pendiente'];
		$egreso_concepto		= $datosEgreso['egreso_concepto'];
		$egreso_fechaegreso		= $datosEgreso['egreso_fechaegreso'];
		$egreso_fecharegistro	= $datosEgreso['egreso_fecharegistro'];
		$egreso_periodo			= $datosEgreso['egreso_periodo'];
		$egreso_estado			= $datosEgreso['egreso_estado'];
		$egreso_fechasistema	= $datosEgreso['egreso_fechasistema'];

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
	<title><?php echo APP_NAME; ?> | Modificación egresos</title>
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
		.oculto{
			display: none;
		}

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
							<h1 class="m-0">Modificación de egreso <?php echo $egreso_fechaegreso?> </h1>
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
						<div class="col-md-12">
							<div class="card">

								<div class="card-body">
									<div class="tab-content">
											<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/empleadoAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
												<input type="hidden" name="modulo_egreso" value="actualizar">									
												<input type="hidden" name="egreso_empleadoid" value="<?php echo $empleadoid; ?>">
												<input type="hidden" name="egreso_id" value="<?php echo $egreso_id; ?>">
												<!-- Post -->
												<div class="row">
													<div class="col-md-4">
														<div class="form-group">
															<label for="egreso_fechaegreso">Fecha de egreso</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>																
																<input type="date" class="form-control" id="egreso_fechaegreso" name="egreso_fechaegreso" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="<?php echo $egreso_fechaegreso; ?>" required>																
															</div>
															<!-- /.input group -->
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="egreso_fecharegistro">Fecha de registro</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="egreso_fecharegistro" name="egreso_fecharegistro" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="<?php echo $egreso_fecharegistro; ?>" required>
															</div>
															<!-- /.input group -->
														</div>								
													</div>
													<div class="col-md-2">
														<div class="form-group">
															<label for="egreso_periodo">Periodo(mes/año)</label>															
															<input type="text" class="form-control" id="egreso_periodo" name="egreso_periodo" placeholder="Mes/año" value="<?php echo $egreso_periodo; ?>" required>															
														</div>								
													</div>
													<div class="col-md-2">
														<div class="form-group">
															<label for="egreso_tipoid">Tipo de egreso</label>
															<select class="form-control select2" id="egreso_tipoid" name="egreso_tipoid">																									
																<?php echo $insEgreso->listarTipoEgreso($egreso_tipoid); ?>
															</select>	
														</div>
													</div>
													<div class="col-md-2">
														<div class="form-group">
															<label for="egreso_valor">Valor</label>
															<input type="text" class="pull-right form-control" style="text-align:right;" id="egreso_valor" name="egreso_valor" placeholder="0.00" pattern="^\d+(\.\d{1,2})?$" value="<?php echo $egreso_valor; ?>" required>
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label for="egreso_formaegresoid">Periodicidad de descuento</label>
															<select class="form-control select2" id="egreso_formaegresoid" name="egreso_formaegresoid">																									
																<?php echo $insEgreso->listarPeriodicidadDescuento($egreso_formaegresoid); ?>
															</select>	
														</div>
													</div>
													<div class="col-md-7">	
														<div class="form-group">
															<label for="egreso_concepto">Detalle</label>
															<input type="text" class="form-control" id="egreso_concepto" name="egreso_concepto" value="<?php echo $egreso_concepto; ?>" required>
														</div>	
													</div>												
												</div>
												<div class="card-footer">						
													<button type="submit" class="btn btn-success btn-sm">Guardar</button>
													<button type="reset" class="btn btn-dark btn-sm">Limpiar</button>
													
													<a href="<?php echo APP_URL.'empleadoIE/'.$empleadoid.'/'; ?>" class="btn btn-info btn-sm">Cancelar</a>
												</div>					
											</form>											
									</div>
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
	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>    
		
	<script>
		$(document).ready(function () {
			$("#egreso_fechaegreso").keyup(function () {
				var value = $(this).val();				
				var fecha = new Date(value);				
				// Array con los nombres de los meses
				var nombresMeses = [
				"Enero","Febrero", "Marzo", "Abril", "Mayo", "Junio",
				"Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
				];
				// Obtener el mes (los meses van de 0 a 11 en JavaScript)
				var mesNumero = fecha.getMonth();
				var mesNombre = nombresMeses[mesNumero];
				var año = fecha.getFullYear();

				$("#egreso_periodo").val(mesNombre + " / " + año );
			});
		});		
	</script>
  </body>
</html>