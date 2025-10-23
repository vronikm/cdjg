<?php
	date_default_timezone_set("America/Guayaquil");

	use app\controllers\pagosController;
	$insAlumno = new pagosController();	

	$alumno=$insLogin->limpiarCadena($url[1]);

	$datos=$insAlumno->BuscarAlumno($alumno);
	
	if($datos->rowCount()==1){
		$datos=$datos->fetch(); 
		
		if ($datos['alumno_imagen']!=""){
			$foto = APP_URL.'app/views/imagenes/fotos/alumno/'.$datos['alumno_imagen'];
		}else{
			$foto=APP_URL.'app/views/dist/img/alumno.jpg';
		}

		if($datos['pendiente']==1){
			$pendiente = 'Pendiente';
			$clase = '<a class="float-right text-danger">';
		}else{
			$pendiente = 'Al día';
			$clase = '<a class="float-right">';
		}
	
		$sede=$insAlumno->informacionSede($datos['alumno_sedeid']);
		if($sede->rowCount()==1){
			$sede=$sede->fetch(); 
		}

		$mesesEnEspanol = [
			'January' => 'Enero',
			'February' => 'Febrero',
			'March' => 'Marzo',
			'April' => 'Abril',
			'May' => 'Mayo',
			'June' => 'Junio',
			'July' => 'Julio',
			'August' => 'Agosto',
			'September' => 'Septiembre',
			'October' => 'Octubre',
			'November' => 'Noviembre',
			'December' => 'Diciembre'
		];

		$fechahoy = date('Y-m-d');
		// Crear un objeto DateTime
		$dateTime = new DateTime($fechahoy);
		// Obtener el nombre completo del mes
		$nombreMes = $dateTime->format('F');
		// Obtener el año
		$nombreMesEspanol = $mesesEnEspanol[$nombreMes];

		$anio = $dateTime->format('Y');
		$nombreMes = $nombreMesEspanol." / ".$anio;

		$saldo = "0.00";
		
		$descuento=$insAlumno->AlumnoDescuento($alumno);
		if($descuento->rowCount()==1){
			$descuento=$descuento->fetch(); 
			if($descuento["descuento_rubroid"] == 'DBC'){
				$textodescuento ="Estudiante tiene Beca. ";
				$textodescripcion =$descuento["descuento_detalle"];
				$rubro_valor = $descuento["descuento_valor"];
				$rubro_inscripcion = $descuento['descuento_valor'];
				$disabled = "disabled";
				$alert = "alert-warning";
				$alerta = "S";		
				$beca = "S";	
			}if($descuento["descuento_rubroid"] == 'DDS'){
				$textodescuento ="Estudiante tiene Descuento. ";
				$textodescripcion =$descuento["descuento_detalle"];
				$rubro_valor = $descuento["descuento_valor"];
				$rubro_inscripcion = $sede['sede_inscripcion'];
				$alert = "alert-info";
				$disabled = " ";
				$alerta = "S";	
				$beca = "N";				
			}
		}else{
			$textodescuento ="";
			$textodescripcion ="";
			$rubro_valor = $sede['sede_pension'];			
			$rubro_inscripcion = $sede['sede_inscripcion'];
			$disabled = " ";
			$alerta = "N";
			$beca = "N";
		}

		if($beca != "S"){
			$pension = $insAlumno->pensionesPendientes($alumno);
			if($pension != ""){
				$pendiente = "Pendiente";
				$clase = '<a class="float-right text-danger">';
			}
		}else{
			$pension = "";
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
	<title><?php echo APP_NAME; ?> | Registro de pagos</title>
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

	<!-- Ekko Lightbox -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/ekko-lightbox/ekko-lightbox.css">
    
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
							<h1 class="m-0">Pagos alumno</h1>
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
											<b>Estado alumno</b> <a class="float-right"><?php echo $datos['estado']; ?></a>
										</li>
										<li class="list-group-item">
											<b>Fecha de ingreso</b> <a class="float-right"><?php echo $datos['alumno_fechaingreso']; ?></a>
										</li>
										<li class="list-group-item">
											<b>Estado pagos</b> 												
											<?php												
												echo $clase.$pendiente.'</a>'; 												
											?>
										</li>
										<li class="list-group-item">
											<b>Detalle rubros pendientes</b> 
											<table class="table table-sm">	

												<?php 
												echo $pension;
												echo $insAlumno->pagosPendintes($alumno); 
												?>											
											</table>											
										</li>
									</ul>
								</div>
								<!-- /.card-body -->
							</div>
							<!-- /.card -->
						</div>

						<div class="col-md-9">
							<div class="card">
								<div class="card-header p-2">
									<ul class="nav nav-pills">
										<li class="nav-item"><a class="nav-link active" href="#pension" data-toggle="tab">Pensiones</a></li>
										<li class="nav-item"><a class="nav-link" href="#inscripcion" data-toggle="tab">Inscripción</a></li>
										<li class="nav-item"><a class="nav-link" href="#torneo" data-toggle="tab">Campeonatos</a></li>
										<li class="nav-item"><a class="nav-link" href="#uniforme" data-toggle="tab">Nuevo Uniforme</a></li>										
										<li class="nav-item"><a class="nav-link" href="#kit" data-toggle="tab">Adicionales entrenamiento</a></li>									
										<li class="nav-item"><a class="nav-link" href="#otros" data-toggle="tab">Otros</a></li>									
									</ul>
								</div><!-- /.card-header -->
							
								<div class="card-body">
									<div class="tab-content">
										<!-- /.tab-pane -->
										<div class="active tab-pane" id="pension"> 
											<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/pagosAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
											<input type="hidden" name="modulo_pagos" value="registrar">											
											<input type="hidden" name="pago_alumnoid" value="<?php echo $datos['alumno_id']; ?>">
											<input type="hidden" name="pago_rubro" value="pension">
																	<!-- Post -->
											<div class="row">
												<div class="col-md-4">
													<div class="form-group campo">
														<label for="pago_fecha">Fecha de pago</label>
														<div class="input-group">
															<div class="input-group-prepend">
																<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
															</div>
															<input type="date" class="form-control" id="pago_fecha" name="pago_fecha" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="<?php echo $fechahoy; ?>" <?php echo $disabled; ?> required>
															
														</div>
														<!-- /.input group -->
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="pago_fecharegistro">Fecha de registro</label>
														<div class="input-group">
															<div class="input-group-prepend">
																<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
															</div>
															<input type="date" class="form-control" id="pago_fecharegistro" name="pago_fecharegistro" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="<?php echo $fechahoy; ?>" <?php echo $disabled; ?> required>
														</div>
														<!-- /.input group -->
													</div>								
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="pago_periodo">Periodo(mes/año)</label>															
														<input type="text" class="form-control" id="pago_periodo" name="pago_periodo" value="<?php echo $nombreMes; ?>" <?php echo $disabled; ?> required>															
													</div>								
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="pago_valor">Pago</label>
														<input type="text" class="pull-right form-control" style="text-align:right;" id="pago_valor" name="pago_valor" placeholder="0.00" pattern="^\d+(\.\d{1,2})?$" <?php echo ' value="'.$rubro_valor.'" '.$disabled; ?>  required>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label for="pago_saldo">Saldo</label>
														<input type="text" class="form-control" style="text-align:right;" id="pago_saldo" name="pago_saldo" placeholder="0.00" <?php echo ' value="'.$saldo.'" '.$disabled; ?>>
													</div>
												</div>
												
												<div class="col-md-4">
													<div class="form-group">
													<label for="pago_formapagoid">Forma de pago</label>
													<select class="form-control select2" id="pago_formapagoid" name="pago_formapagoid" <?php echo $disabled; ?>>																									
														<?php echo $insAlumno->listarOptionPago(); ?>
													</select>	
													</div>
												</div>
												<div class="container-fluid">
													<div class="row mb-2">
														<div class="col-md-2">
															<div class="form-group">
																<label for="pago_archivo">Imagen Pago</label>		
																<div class="input-group">											
																	<div class="fileinput fileinput-new" data-provides="fileinput">
																	<div class="fileinput-new thumbnail" style="width: 130px; height: 158px;" data-trigger="fileinput"><img src="" id="miImagen"></div>
																			<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 130px; max-height: 158px"></div>
																		<div>
																			<span class="bton bton-white bton-file">
																				<span class="fileinput-new">Subir Pago</span>
																				<span class="fileinput-exists">Cambiar</span>
																				<input type="file" name="pago_archivo" id="pago_archivo" <?php echo $disabled; ?>>
																			</span>
																			<a href="#" class="bton bton-orange fileinput-exists" data-dismiss="fileinput">X</a>
																		</div>
																	</div>
																</div>		
															</div>
														<!-- /.form-group -->	
														</div>
														<div class="col-md-10">
															<div class="col-md-12">
																<div class="form-group">
																<label for="pago_concepto">Detalle</label>
																<textarea class="form-control" id="pago_concepto" name="pago_concepto" placeholder="Detalle del pago" rows="3" <?php echo $disabled; ?>></textarea>
																</div>
															</div>
															<?php 
																if($alerta == "S"){
																	echo '
																	<div class="col-md-12">
																		<div class="alert '.$alert.' alert-dismissible">
																			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
																			<h5><i class="icon fas fa-info"></i> Aviso!</h5>
																			'.$textodescuento.$textodescripcion.'
																		</div>
																	</div>
																	';
																}
															?>
														</div>
														
													</div>
												</div>
											</div>		
																					
											<!-- /.post -->
											<?php
												if($beca != 'S'){
													echo '						
														<div class="card-footer">						
															<button type="submit" class="btn btn-success btn-sm">Guardar</button>
															<button type="reset" class="btn btn-warning btn-sm">Limpiar</button>	
															<button class="btn btn-dark btn-sm" onclick="cerrarVentana()">Cerrar</button>					
														</div>
													';
												}
												
											?>
											</form>

											<div class="tab-custom-content">
												<p class="lead mb-0">Pagos realizados</p>
											</div>
											<div class="tab-content" id="custom-content-above-tabContent">
												<table id="example1" class="table table-bordered table-striped table-sm">
													<thead>
														<tr>
															<th>No</th>															
															<th>Fecha registro</th>
															<th>Mes/Año</th>
															<th>Pago</th>
															<th>Saldo</th>	
															<th>Recibo</th>													
															<th>Estado</th>															
															<th style="width:280px;">Opciones</th>																
														</tr>
													</thead>
													<tbody>
														<?php 
															echo $insAlumno->listarPagosRubro($alumno,'RPE'); 
														?>								
													</tbody>
												</table>
											</div>
											
										</div>											

										<!-- /.tab-pane -->
										<div class="tab-pane" id="inscripcion">
											<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/pagosAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
											<input type="hidden" name="modulo_pagos" value="registrar">											
											<input type="hidden" name="pago_alumnoid" value="<?php echo $datos['alumno_id']; ?>">
											<input type="hidden" name="pago_rubro" value="inscripcion">
																	<!-- Post -->
												<div class="row">
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_fecha">Fecha pago inscripción</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="pago_fecha" name="pago_fecha" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask <?php echo $disabled; ?> required>
															</div>
															<!-- /.input group -->
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_fecharegistro">Fecha registro inscripción</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="pago_fecharegistro" name="pago_fecharegistro" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask <?php echo $disabled; ?> required>
															</div>
															<!-- /.input group -->
														</div>								
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_periodo">Periodo inscripción</label>															
															<input type="text" class="form-control" id="pago_periodo" name="pago_periodo" <?php echo $disabled; ?> required>															
														</div>								
													</div>
													
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_valor">Pago inscripción</label>
															<input type="text" class="pull-right form-control" style="text-align:right;" id="pago_valor" name="pago_valor" placeholder="0.00" <?php echo ' value="'.$rubro_inscripcion.'" '.$disabled; ?>  required>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_saldo">Saldo inscripción</label>
															<input type="text" class="form-control" style="text-align:right;" id="pago_saldo" name="pago_saldo" placeholder="0.00"  <?php echo ' value="'.$saldo.'" '.$disabled; ?> >
														</div>
													</div>
													
													<div class="col-md-4">
														<div class="form-group">
														<label for="pago_formapagoid">Forma de pago inscripción</label>
														<select class="form-control select2" id="pago_formapagoid" name="pago_formapagoid" onchange="ocultarDiv()" <?php echo $disabled; ?>>																									
															<?php echo $insAlumno->listarOptionPago(); ?>
														</select>	
														</div>
													</div>
													
													<div class="container-fluid">
														<div class="row mb-2">

															<div class="col-2">
																<div class="form-group">
																	<label for="pago_archivo">Imagen pago</label>		
																	<div class="input-group">											
																		<div class="fileinput fileinput-new" data-provides="fileinput">
																		<div class="fileinput-new thumbnail" style="width: 130px; height: 158px;" data-trigger="fileinput"><img src="" id="miImagen"></div>
																			<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 130px; max-height: 158px"></div>
																			<div>
																				<span class="bton bton-white bton-file">
																					<span class="fileinput-new">Subir Pago</span>
																					<span class="fileinput-exists">Cambiar</span>
																					<input type="file" name="pago_archivo" id="pago_archivo" <?php echo $disabled; ?>>
																				</span>
																				<a href="#" class="bton bton-orange fileinput-exists" data-dismiss="fileinput">X</a>
																			</div>
																		</div>
																	</div>		
																</div>
															<!-- /.form-group -->	
															</div>
															<div class="col-10">
																<div class="col-12">
																	<div class="form-group">
																	<label for="pago_concepto">Detalle inscripción</label>
																	<textarea class="form-control" id="pago_concepto" name="pago_concepto" placeholder="Detalle del pago" rows="3" <?php echo $disabled; ?>></textarea>
																	</div>
																</div>
																<?php 
																	if($beca == 'S'){
																		echo '
																		<div class="col-md-12">
																			<div class="alert '.$alert.' alert-dismissible">
																				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
																				<h5><i class="icon fas fa-info"></i> Aviso!</h5>
																				'.$textodescuento.$textodescripcion.'
																			</div>
																		</div>
																		';
																	}
																?>
															</div>
														</div>
													</div>
												</div>

													
											
											<!-- /.post -->
											<?php
												if($beca != 'S'){
													echo '
														<div class="card-footer">						
															<button type="submit" class="btn btn-success btn-sm">Guardar</button>
															<button type="reset" class="btn btn-warning btn-sm">Limpiar</button>
															<button class="btn btn-dark btn-sm" onclick="cerrarVentana()">Cerrar</button>						
														</div>
													';							
												}
											?>
											</form>	
											<div class="tab-custom-content">
												<p class="lead mb-0">Pagos realizados de inscripción</p>
											</div>
											<div class="tab-content" id="custom-content-above-tabContent">
												<table id="example1" class="table table-bordered table-striped table-sm">
													<thead>
														<tr>
															<th>No</th>															
															<th>Fecha registro</th>
															<th>Mes/Año</th>
															<th>Pago</th>
															<th>Saldo</th>	
															<th>Recibo</th>													
															<th>Estado</th>															
															<th style="width:280px;">Opciones</th>																	
														</tr>
													</thead>
													<tbody>
														<?php 
															echo $insAlumno->listarPagosRubro($alumno,'RIN'); 
														?>								
													</tbody>
												</table>
											</div>
										</div>
										
										<!-- /.tab-pane -->
										<div class="tab-pane" id="torneo"> 
											<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/pagosAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
											<input type="hidden" name="modulo_pagos" value="registrarcampeonato">											
											<input type="hidden" name="pago_alumnoid" value="<?php echo $datos['alumno_id']; ?>">
											<input type="hidden" name="pago_rubro" value="campeonato">
																	<!-- Post -->
											<div class="row">
												<div class="col-md-4">
													<div class="form-group campo">
														<label for="pago_fecha">Fecha de pago campeonato</label>
														<div class="input-group">
															<div class="input-group-prepend">
																<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
															</div>
															<input type="date" class="form-control" id="pago_fecha" name="pago_fecha" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="<?php echo $fechahoy; ?>" required>
															
														</div>
														<!-- /.input group -->
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="pago_fecharegistro">Fecha de registro campeonato</label>
														<div class="input-group">
															<div class="input-group-prepend">
																<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
															</div>
															<input type="date" class="form-control" id="pago_fecharegistro" name="pago_fecharegistro" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="<?php echo $fechahoy; ?>" required>
														</div>
														<!-- /.input group -->
													</div>								
												</div>
												<div class="col-md-4">
													<div class="form-group">
													<label for="pago_campeonatoid">Campeonato</label>
													<select id="pago_campeonatoid" class="form-control select2" name="pago_campeonatoid" <?php echo $disabled; ?>>																									
														<?php echo $insAlumno->listarCampeonatos(); ?>
													</select>	
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="pago_valor">Pago campeonato</label>
														<input type="text" class="pull-right form-control" style="text-align:right;" id="pago_valor" name="pago_valor" placeholder="0.00" pattern="^\d+(\.\d{1,2})?$" value="" required>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label for="pago_saldo">Saldo campeonato</label>
														<input type="text" class="form-control" style="text-align:right;" id="pago_saldo" name="pago_saldo" placeholder="0.00" value="0.00"; >
													</div>
												</div>
												
												<div class="col-md-4">
													<div class="form-group">
													<label for="pago_formapagoid">Forma de pago campeonato</label>
													<select class="form-control select2" id="pago_formapagoid" name="pago_formapagoid">																									
														<?php echo $insAlumno->listarOptionPago(); ?>
													</select>	
													</div>
												</div>
												<div class="container-fluid">
													<div class="row mb-2">
														<div class="col-md-2">
															<div class="form-group">
																<label for="pago_archivo">Imagen Pago</label>		
																<div class="input-group">											
																	<div class="fileinput fileinput-new" data-provides="fileinput">
																	<div class="fileinput-new thumbnail" style="width: 130px; height: 158px;" data-trigger="fileinput"><img src="" id="miImagen"></div>
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
															<div class="col-md-12">
																<div class="form-group">
																<label for="pago_concepto">Detalle campeonato</label>
																<textarea class="form-control" id="pago_concepto" name="pago_concepto" placeholder="Detalle del pago" rows="3"></textarea>
																</div>
															</div>															
														</div>														
													</div>
												</div>
											</div>		
																					
											<!-- /.post -->
											<div class="card-footer">						
												<button type="submit" class="btn btn-success btn-sm">Guardar</button>
												<button type="reset" class="btn btn-warning btn-sm">Limpiar</button>	
												<button class="btn btn-dark btn-sm" onclick="cerrarVentana()">Cerrar</button>					
											</div>
											</form>

											<div class="tab-custom-content">
												<p class="lead mb-0">Pagos realizados de campeonatos</p>
											</div>
											<div class="tab-content" id="custom-content-above-tabContent">
												<table id="example1" class="table table-bordered table-striped table-sm">
													<thead>
														<tr>
															<th>No</th>															
															<th>Fecha registro</th>		
															<th>Campeonato</th>													
															<th>Pago</th>
															<th>Saldo</th>
															<th>Recibo</th>													
															<th>Estado</th>															
															<th style="width:280px;">Opciones</th>																
														</tr>
													</thead>
													<tbody>
														<?php 
															echo $insAlumno->listarPagosRubro($alumno,'RPC'); 
														?>								
													</tbody>
												</table>
											</div>
											
										</div>

										<!-- /.tab-pane -->
										<div class="tab-pane" id="uniforme">
											<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/pagosAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
											<input type="hidden" name="modulo_pagos" value="registraruniforme">											
											<input type="hidden" name="pago_alumnoid" value="<?php echo $datos['alumno_id']; ?>">
											<input type="hidden" name="pago_rubro" value="uniforme">
																	<!-- Post -->
												<div class="row">
													<div class="col-md-3">
														<div class="form-group">
															<label for="pago_fecha">Fecha pago uniforme</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="pago_fecha" name="pago_fecha" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask required>
															</div>
															<!-- /.input group -->
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label for="pago_fecharegistro">Fecha registro uniforme</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="pago_fecharegistro" name="pago_fecharegistro" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask required>
															</div>
															<!-- /.input group -->
														</div>								
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label for="pago_periodo">Periodo uniforme</label>															
															<input type="text" class="form-control" id="pago_periodo" name="pago_periodo" required>															
														</div>								
													</div>
																									
													<div class="col-md-3">
														<div class="form-group">
														<label for="pago_talla">Talla</label>
														<select class="form-control select2" id="pago_talla" name="pago_talla" required>																									
															<?php echo $insAlumno->listarOptionTalla(""); ?>
														</select>	
														</div>
													</div>
													
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_valor">Pago uniforme</label>
															<input type="text" class="pull-right form-control" style="text-align:right;" id="pago_valor" name="pago_valor" placeholder="0.00" required>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_saldo">Saldo uniforme</label>
															<input type="text" class="form-control" style="text-align:right;" id="pago_saldo" name="pago_saldo" placeholder="0.00">
														</div>
													</div>
													
													<div class="col-md-4">
														<div class="form-group">
														<label for="pago_formapagoid">Forma de pago uniforme</label>
														<select class="form-control select2" id="pago_formapagoid" name="pago_formapagoid" onchange="ocultarDiv()" >																									
															<?php echo $insAlumno->listarOptionPago(); ?>
														</select>	
														</div>
													</div>													
													
													<div class="col-md-2" id="miDiv">
														<div class="form-group">
															<label for="pago_archivo">Imagen Pago</label>		
															<div class="input-group">											
																<div class="fileinput fileinput-new" data-provides="fileinput">
																	<div class="fileinput-new thumbnail" style="width: 130px; height: 158px;" data-trigger="fileinput"><img src="" id="miImagen"></div>
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
														<div class="col-md-12">
															<div class="form-group">
																<label for="pago_concepto">Detalle uniforme</label>
																<textarea class="form-control" id="pago_concepto" name="pago_concepto" placeholder="Detalle del pago" rows="3" ></textarea>
															</div>
														</div>
													</div>
												</div>											
											<!-- /.post -->
											<div class="card-footer">						
												<button type="submit" class="btn btn-success btn-sm">Guardar</button>
												<button type="reset" class="btn btn-warning btn-sm">Limpiar</button>	
												<button class="btn btn-dark btn-sm" onclick="cerrarVentana()">Cerrar</button>					
											</div>	
											</form>	
											
											<div class="tab-custom-content">
												<p class="lead mb-0">Pagos de Nuevo Uniforme realizados</p>
											</div>
											<div class="tab-content" id="custom-content-above-tabContent">
												<table id="example1" class="table table-bordered table-striped table-sm">
													<thead>
														<tr>
															<th>No</th>
															<th>Fecha registro</th>
															<th>Periodo</th>
															<th>Talla</th>
															<th>Pago</th>
															<th>Saldo</th>															
															<th>Recibo</th>
															<th>Estado</th>
															<th style="width:280px;">Opciones</th>																
														</tr>
													</thead>
													<tbody>
														<?php 
															echo $insAlumno->listarPagosRubro($alumno,'RNU');
														?>								
													</tbody>
												</table>
											</div>											
										</div>

										<!-- /.tab-pane -->										
										<div class="tab-pane" id="kit">
											<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/pagosAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
											<input type="hidden" name="modulo_pagos" value="registrar">											
											<input type="hidden" name="pago_alumnoid" value="<?php echo $datos['alumno_id']; ?>">
											<input type="hidden" name="pago_rubro" value="kit">
																	<!-- Post -->
												<div class="row">
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_fecha">Fecha pago accesorio entrenamiento</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="pago_fecha" name="pago_fecha" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask required>
															</div>
															<!-- /.input group -->
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_fecharegistro">Fecha registro accesorio entrenamiento</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="pago_fecharegistro" name="pago_fecharegistro" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask required>
															</div>
															<!-- /.input group -->
														</div>								
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_periodo">Periodo accesorio entrenamiento</label>															
															<input type="text" class="form-control" id="pago_periodo" name="pago_periodo" required>															
														</div>								
													</div>
													
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_valor">Pago accesorio entrenamiento</label>
															<input type="text" class="pull-right form-control" style="text-align:right;" id="pago_valor" name="pago_valor" placeholder="0.00" required>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_saldo">Saldo accesorio entrenamiento</label>
															<input type="text" class="form-control" style="text-align:right;" id="pago_saldo" name="pago_saldo" placeholder="0.00">
														</div>
													</div>
													
													<div class="col-md-4">
														<div class="form-group">
														<label for="pago_formapagoid">Forma de pago</label>
														<select class="form-control select2" id="pago_formapagoid" name="pago_formapagoid" onchange="ocultarDiv()" >																									
															<?php echo $insAlumno->listarOptionPago(); ?>
														</select>	
														</div>
													</div>													
													
													<div class="col-md-2" id="miDiv">
														<div class="form-group">
															<label for="pago_archivo">Imagen Pago</label>		
															<div class="input-group">											
																<div class="fileinput fileinput-new" data-provides="fileinput">
																	<div class="fileinput-new thumbnail" style="width: 130px; height: 158px;" data-trigger="fileinput"><img src="" id="miImagen"></div>
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
														<div class="col-md-12">
															<div class="form-group">
																<label for="pago_concepto">Detalle accesorios entrenamiento</label>
																<textarea class="form-control" id="pago_concepto" name="pago_concepto" placeholder="Detalle del pago" rows="3" ></textarea>
															</div>
														</div>
													</div>
												</div>										
												<!-- /.post -->
												<div class="card-footer">						
													<button type="submit" class="btn btn-success btn-sm">Guardar</button>
													<button type="reset" class="btn btn-warning btn-sm">Limpiar</button>
													<button class="btn btn-dark btn-sm" onclick="cerrarVentana()">Cerrar</button>		
												</div>	
											</form>	

											<div class="tab-custom-content">
												<p class="lead mb-0">Pagos realizados por accesorios de entrenamiento</p>
											</div>
											<div class="tab-content" id="custom-content-above-tabContent">
												<table id="example1" class="table table-bordered table-striped table-sm">
													<thead>
														<tr>
															<th>No</th>
															<th>Fecha registro</th>		
															<th>Periodo</th>													
															<th>Pago</th>
															<th>Saldo</th>														
															<th>Recibo</th>
															<th>Estado</th>
															<th style="width:300px;">Opciones</th>																
														</tr>
													</thead>
													<tbody>
														<?php 
															echo $insAlumno->listarPagosRubro($alumno,'RKE'); 
														?>								
													</tbody>
												</table>
											</div>	
										</div>

										<!-- /.tab-pane -->										
										<div class="tab-pane" id="otros">
											<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/pagosAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
											<input type="hidden" name="modulo_pagos" value="registrar">											
											<input type="hidden" name="pago_alumnoid" value="<?php echo $datos['alumno_id']; ?>">
											<input type="hidden" name="pago_rubro" value="otros">
																	<!-- Post -->
												<div class="row">
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_fecha">Fecha pago Otros</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="pago_fecha" name="pago_fecha" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask required>
															</div>
															<!-- /.input group -->
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_fecharegistro">Fecha registro Otros</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="pago_fecharegistro" name="pago_fecharegistro" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask required>
															</div>
															<!-- /.input group -->
														</div>								
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_periodo">Periodo Otros</label>															
															<input type="text" class="form-control" id="pago_periodo" name="pago_periodo" required>															
														</div>								
													</div>
													
													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_valor">Pago Otros</label>
															<input type="text" class="pull-right form-control" style="text-align:right;" id="pago_valor" name="pago_valor" placeholder="0.00" required>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label for="pago_saldo">Saldo Otros</label>
															<input type="text" class="form-control" style="text-align:right;" id="pago_saldo" name="pago_saldo" placeholder="0.00">
														</div>
													</div>
													
													<div class="col-md-4">
														<div class="form-group">
														<label for="pago_formapagoid">Forma de pago Otros</label>
														<select class="form-control select2" id="pago_formapagoid" name="pago_formapagoid" onchange="ocultarDiv()" >																									
															<?php echo $insAlumno->listarOptionPago(); ?>
														</select>	
														</div>
													</div>										

													<div class="col-md-2" id="miDiv">
														<div class="form-group">
															<label for="pago_archivo">Imagen Pago</label>		
															<div class="input-group">											
																<div class="fileinput fileinput-new" data-provides="fileinput">
																	<div class="fileinput-new thumbnail" style="width: 130px; height: 158px;" data-trigger="fileinput"><img src="" id="miImagen"></div>
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
														<div class="col-md-12">
															<div class="form-group">
															<label for="pago_concepto">Detalle Otros</label>
															<textarea class="form-control" id="pago_concepto" name="pago_concepto" placeholder="Detalle del pago" rows="3" ></textarea>
															</div>
														</div>
													</div>
												</div>											
											<!-- /.post -->

											<div class="card-footer">						
												<button type="submit" class="btn btn-success btn-sm">Guardar</button>
												<button type="reset" class="btn btn-warning btn-sm">Limpiar</button>	
												<button class="btn btn-dark btn-sm" onclick="cerrarVentana()">Cerrar</button>					
											</div>	
											</form>	
											
											<div class="tab-custom-content">
												<p class="lead mb-0">Pagos Otros realizados</p>
											</div>
											<div class="tab-content" id="custom-content-above-tabContent">
												<table id="example1" class="table table-bordered table-striped table-sm">
													<thead>
														<tr>
															<th>No</th>
															<th>Fecha registro</th>
															<th>Periodo</th>
															<th>Pago</th>
															<th>Saldo</th>														
															<th>Recibo</th>
															<th>Estado</th>		
															<th style="width:300px;">Opciones</th>																
														</tr>
													</thead>
													<tbody>
														<?php 
															echo $insAlumno->listarPagosRubro($alumno,'ROT');  
														?>								
													</tbody>
												</table>
											</div>											
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
	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>	
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/ekko-lightbox/ekko-lightbox.min.js"></script>
		
	<script>
		$(document).ready(function () {
			$("#pago_fecha").keyup(function () {
				var value = $(this).val();
				
				// Dividir la fecha manualmente para evitar problemas de zona horaria
				var partes = value.split('-'); // Formato esperado: YYYY-MM-DD
				
				if (partes.length === 3) {
					var año = parseInt(partes[0]);
					var mesNumero = parseInt(partes[1]) - 1; // Restar 1 porque los meses van de 0 a 11
					var dia = parseInt(partes[2]);
					
					// Array con los nombres de los meses
					var nombresMeses = [
						"Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
						"Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
					];
					
					var mesNombre = nombresMeses[mesNumero];
					$("#pago_periodo").val(mesNombre + " / " + año);
				}
			});
		});
	</script>

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
	</script>
	
	<script>
		// Guardar el tab activo cuando el usuario cambia de pestaña
		document.querySelectorAll('a[data-toggle="tab"]').forEach(tab => {
			tab.addEventListener('shown.bs.tab', function (e) {
				localStorage.setItem('activeTab', e.target.getAttribute('href'));
			});
		});

		// Antes de enviar cualquier formulario Ajax, guardar el tab activo
		document.querySelectorAll("form.FormularioAjax").forEach(form => {
			form.addEventListener("submit", function () {
				let activeTab = document.querySelector(".nav-pills .nav-link.active");
				if (activeTab) {
					localStorage.setItem("activeTab", activeTab.getAttribute("href"));
				}
			});
		});

		// Restaurar el tab activo después del reload
		window.addEventListener("load", function () {
			let activeTab = localStorage.getItem("activeTab");
			if (activeTab) {
				let tab = document.querySelector(`a[href="${activeTab}"]`);
				if (tab) {
					new bootstrap.Tab(tab).show(); // Bootstrap 4/5 activa el tab
				}
			}
		});
	</script>

	<script>
		function cerrarVentana() {
			window.close();
		}
    </script>
  </body>
</html>