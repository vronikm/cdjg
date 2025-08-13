<?php
	setlocale(LC_TIME, 'es_EC.UTF-8');

	use app\controllers\pagosController;
	$insAlumno = new pagosController();	

	$alumno=$insLogin->limpiarCadena($url[1]);

	$datos=$insAlumno->BuscarAlumnoDescuento($alumno);
	
	if($datos->rowCount()==1){
		$datos=$datos->fetch(); 
		
		if ($datos['alumno_imagen']!=""){
			$foto = APP_URL.'app/views/imagenes/fotos/alumno/'.$datos['alumno_imagen'];
		}else{
			$foto=APP_URL.'app/views/dist/img/alumno.jpg';
		}		
		
		$descuento=$insAlumno->BuscarDescuento($alumno);
		if($descuento->rowCount()==1){
			$descuento=$descuento->fetch(); 
			
			$modulo_pagos			= "descuentoUP";
			$descuento_id 			= $descuento["descuento_id"];
			$descuento_rubroid 		= $descuento["descuento_rubroid"];
			$descuento_alumnoid 	= $descuento["descuento_alumnoid"];
			$descuento_valor 		= $descuento["descuento_valor"];
			$descuento_detalle 		= $descuento["descuento_detalle"];
			$descuento_fecha 		= $descuento["descuento_fecha"];
			$descuento_estado		= $descuento["descuento_estado"];
			
		}else{
			
			$modulo_pagos			= "descuento";
			$descuento_id 			= "";
			$descuento_rubroid 		= "";	 
			$descuento_alumnoid 	= ""; 
			$descuento_valor 		= ""; 		
			$descuento_detalle 		= "";
			$descuento_fecha 		= date('Y-m-d');
			$descuento_estado		= "";
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
	<title><?php echo APP_NAME; ?> | Descuentos</title>
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
							<h1 class="m-0">Descuentos alumno</h1>
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
							<!-- Profile Image -->
							<div class="card card-primary card-outline">
								<div class="card-body box-profile">
									<div class="text-center">
										<img class="profile-user-img img-fluid img-circle"
											src="<?php echo $foto; ?>"
											alt="User profile picture">
									</div>

									<h3 class="profile-username text-center"><?php echo $datos['alumno_primernombre']." ".$datos['alumno_apellidopaterno'] ; ?></h3>

									<p class="text-muted text-center"><?php echo $datos['alumno_identificacion']; ?></p>

									<ul class="list-group list-group-unbordered mb-3">
										<li class="list-group-item">
											<b>Categoría</b> <a class="float-right"><?php echo $datos['anio']; ?></a>
										</li>
										<li class="list-group-item">
											<b>Estado del alumno</b> <a class="float-right"><?php echo $datos['estado']; ?></a>
										</li>
										<li class="list-group-item">
											<b>Fecha de ingreso</b> <a class="float-right"><?php echo $datos['alumno_fechaingreso']; ?></a>
										</li>
									</ul>
								</div>
								<!-- /.card-body -->
							</div>
							<!-- /.card -->
						</div>

						<div class="col-md-9">
							<div class="card">						
								<div class="card-body">
									<div class="tab-content">
										<div class="active tab-pane" id="pension"> 
											<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/pagosAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
											<input type="hidden" name="modulo_pagos" value="<?php echo $modulo_pagos; ?>">											
											<input type="hidden" name="descuento_alumnoid" value="<?php echo $datos['alumno_id']; ?>">											
																
											<div class="row">
												<div class="col-md-3">
													<div class="form-group">
													<label for="descuento_rubroid">Tipo de descuento</label>
													<select class="form-control select2" id="descuento_rubroid" name="descuento_rubroid" >																									
														<?php echo $insAlumno->listarOptionDescuento($descuento_rubroid); ?>
													</select>	
													</div>
												</div>	
												<div class="col-md-3">
													<div class="form-group">
														<label for="descuento_valor">Valor</label>
														<input type="text" class="pull-right form-control" style="text-align:right;" id="descuento_valor" name="descuento_valor" placeholder="0.00" pattern="^\d+(\.\d{1,2})?$" value="<?php echo $descuento_valor; ?>" required>
													</div>
												</div>											
												<div class="col-md-3">
													<div class="form-group">
														<label for="descuento_fecha">Fecha de registro</label>
														<div class="input-group">
															<div class="input-group-prepend">
																<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
															</div>
															<input type="date" class="form-control" id="descuento_fecha" name="descuento_fecha" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="<?php echo $descuento_fecha; ?>" required>
														</div>
														<!-- /.input group -->
													</div>								
												</div>
												<div class="col-md-3">
													<div class="form-group">
													<label for="descuento_estado">Estado</label>
													<select class="form-control select2" id="descuento_estado" name="descuento_estado" >	
														<?php
														if ($descuento_estado != ""){													
															if ($descuento_estado == "S"){
																echo "<option value='S' selected>Activo</option>
																	 <option value='N'>Inactivo</option>";
															}else{
																echo "<option value='S'>Activo</option>
																	 <option value='N' selected>Inactivo</option>";
															}		
														}else{
															echo "<option value='S' selected>Activo</option>
															<option value='N'>Inactivo</option>";
														}											
														?>
													</select>	
													</div>
												</div>																							
											
												<div class="col-md-12">
													<div class="form-group">
													<label for="descuento_detalle">Detalle</label>
													<textarea class="form-control" id="descuento_detalle" name="descuento_detalle" placeholder="Detalle del descuento" rows="3"><?php echo $descuento_detalle; ?></textarea>
													</div>
												</div>											
											</div>	
											
											<button type="submit" class="btn btn-success btn-sm">Guardar</button>
											<a class="btn btn-dark btn-sm" href="#" onclick="cerrarVentana(); return false;">Regresar</a>
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
	<script>
		function cerrarVentana() {
			window.close();
		}
	</script>

  </body>
</html>