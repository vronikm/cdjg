<?php
	use app\controllers\empleadoController;
	$insDescEgreso = new empleadoController();	

	$egresoid=$insLogin->limpiarCadena($url[1]);

	$datos=$insDescEgreso->BuscarRubroEgreso($egresoid);
	
	if($datos->rowCount()==1){
		$datos=$datos->fetch(); 
		
		if ($datos['egreso_valor'] == $datos['egreso_pendiente']){
			$valor_descargado=0.00;
		}
		else{
			$valor_descargado=$datos['egreso_descargado'];
		}

	}else{
		include "<?php echo APP_URL; ?>/app/views/inc/error_alert.php";
	}
	$fechahoy = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Saldo pendiente egresos</title>
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
	
	<!-- Ekko Lightbox -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/ekko-lightbox/ekko-lightbox.css">
	
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
							<h1 class="m-0">Saldo pendiente: <?php echo $datos['RUBRO']; ?></h1>
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
				<!-- /.container-fluid informacion alumno -->
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-3">
							<div class="card card-secondary">		
								<div class="card-header">
									<h3 class="card-title">Saldo pendiente egresos</h3>
								</div>						
								<div class="card-body">																			
									<div class="row">
										<div class="col-md-12">
											<div class="form-group campo">
												<label for="egreso_fechaegreso">Fecha de egreso</label>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
													</div>
													<input type="date" class="form-control" value="<?php echo $datos['egreso_fechaegreso']; ?>" disabled>	
												</div>
												<!-- /.input group -->
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label for="egreso_fecharegistro">Fecha de registro</label>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
													</div>
													<input type="date" class="form-control" value="<?php echo $datos['egreso_fecharegistro']; ?>" disabled>
												</div>
												<!-- /.input group -->
											</div>								
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label for="egreso_periodo">Periodo(mes/año)</label>															
												<input type="text" class="form-control" value="<?php echo $datos['egreso_periodo']; ?>" disabled>															
											</div>								
										</div>										
										<div class="col-md-6">
											<div class="form-group">
												<label for="egreso_descargado">Descargado</label>
												<input type="text" class="pull-right form-control" style="text-align:right;" value="<?php echo $valor_descargado; ?>" disabled>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="egreso_pendiente">Pendiente</label>
												<input type="text" class="form-control" style="text-align:right;" value="<?php echo $datos['egreso_pendiente']; ?>" disabled>
											</div>
										</div>										
										<div class="col-md-12">
											<div class="form-group">
											<label for="egreso_formaegresoid">Forma de egreso</label>
											<select class="form-control select2" id="egreso_formaegresoid" name="egreso_formaegresoid" disabled>																									
												<?php echo $insDescEgreso->listarOptionDescuento($datos['egreso_formaegresoid']); ?>
											</select>	
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label for="egreso_concepto">Detalle</label>
												<textarea class="form-control" placeholder="Detalle del egreso" rows="3" disabled><?php echo $datos['egreso_concepto']; ?></textarea>
											</div>
										</div>											
									</div>									
								</div><!-- /.card-body -->
							</div>
							<!-- /.card -->
						</div>
						<div class="col-md-9">
							<div class="card">								
								<div class="card-body">									
									<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/empleadoAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
										<input type="hidden" name="modulo_egreso" value="descargoegreso">											
										<input type="hidden" name="trxegreso_egresoid" value="<?php echo $egresoid; ?>">		
										<input type="hidden" name="trxegreso_pendiente" value="<?php echo $datos['egreso_pendiente']; ?>">
										<input type="hidden" name="trxegreso_descargado" value="<?php echo $datos['egreso_descargado']; ?>">
										<input type="hidden" name="trxegreso_total" value="<?php echo $datos['egreso_valor']; ?>">
										<input type="hidden" name="trxegreso_formaegresoid" value="<?php echo $datos['RUBRO']; ?>">
										<!-- Post -->
											<div class="row">
												<div class="col-md-3">
													<div class="form-group campo">
														<label for="trxegreso_fecha">Fecha de descargo</label>
														<div class="input-group">
															<div class="input-group-prepend">
																<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
															</div>
															<input type="date" class="form-control" id="trxegreso_fecha" name="trxegreso_fecha" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="<?php echo $fechahoy; ?>" >														
														</div>
														<!-- /.input group -->
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="trxegreso_fecharegistro">Fecha de registro</label>
														<div class="input-group">
															<div class="input-group-prepend">
																<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
															</div>
															<input type="date" class="form-control" id="trxegreso_fecharegistro" name="trxegreso_fecharegistro" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="<?php echo $fechahoy; ?>" >
														</div>
														<!-- /.input group -->
													</div>								
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="trxegreso_periodo">Periodo(mes/año)</label>															
														<input type="text" class="form-control" id="trxegreso_periodo" name="trxegreso_periodo" value="<?php echo $datos['egreso_periodo']; ?>">															
													</div>								
												</div>	
												<div class="col-md-3">
													<div class="form-group">
														<label for="trxegreso_descargo">Valor descargo</label>
														<input type="text" class="pull-right form-control" style="text-align:right;" id="trxegreso_descargo" name="trxegreso_descargo" placeholder="0.00" value="<?php echo $datos['egreso_pendiente']; ?>" >
													</div>
												</div>	
												<div class="col-md-3">
													<div class="form-group">
														<label for="trxegreso_formaegresoid">Periodicidad de descuento</label>
														<select class="form-control select2" id="trxegreso_formaegresoid" name="trxegreso_formaegresoid">																									
															<?php echo $insDescEgreso->listarPeriodicidadDescuento($trxegreso_formaegresoid); ?>
														</select>	
													</div>
												</div>
												<div class="col-md-9">
													<div class="form-group">
														<label for="trxegreso_concepto">Detalle</label>
														<textarea class="form-control" id="trxegreso_concepto" name="trxegreso_concepto" placeholder="Detalle del egreso" rows="3" ><?php echo "Descargo pendiente del egreso ".$datos['RUBRO']." del periodo ".$datos['egreso_periodo']." por el valor de $".$datos['egreso_pendiente'] ." dólares"; ?></textarea>
													</div>
												</div>											
											</div>
											<button type="submit" class="btn btn-info btn-sm">Descargar</button>
											<?php include "./app/views/inc/btn_back.php";?>		
									</form>	
									<div class="tab-custom-content">
										<p class="lead mb-0">Descargos realizados</p>
									</div>
									<div class="tab-content" id="custom-content-above-tabContent">
										<table id="example1" class="table table-bordered table-striped table-sm">
											<thead>
												<tr>
													<th>No</th>
													<th>Fecha</th>
													<th>Periodo</th>
													<th>Egreso inicial</th>
													<th>Descargo</th>
													<th style="width:250px;">Opciones</th>																
												</tr>
											</thead>
											<tbody>
												<?php 
													echo $insDescEgreso->listarDescargosPendientes($egresoid); 
												?>								
											</tbody>
										</table>
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

	<!-- Ekko Lightbox -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/ekko-lightbox/ekko-lightbox.min.js"></script>
	
	<!-- AdminLTE App -->
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
		
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>

	<!--script src="app/views/dist/js/main.js" ></script-->
	
	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>
    
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