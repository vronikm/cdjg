<?php
	use app\controllers\representanteController;
	$insRepre = new representanteController();

	use app\controllers\alumnoController;
	$insAlumno = new alumnoController();
	
	$repreid=$insAlumno->limpiarCadena($url[1]);
	$unidadid ="";
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Registro nuevo alumno</title>
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
							<h1 class="m-0">Nuevo Alumno</h1>
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
					<div class="card">
						<div class="card-header p-2">
							<ul class="nav nav-pills">
								<li class="nav-item"><a class="nav-link active" href="#informacionp" data-toggle="tab">Información Personal</a></li>
								<li class="nav-item"><a class="nav-link" href="#cedula" data-toggle="tab">Cédula</a></li>
								<li class="nav-item"><a class="nav-link" href="#contactoem" data-toggle="tab">Contacto emergencia</a></li>											
								<li class="nav-item"><a class="nav-link" href="#informacionm" data-toggle="tab">Información Médica</a></li>
								<li class="nav-item"><a class="nav-link" href="#horario" data-toggle="tab">Horario</a></li>							
							</ul>
						</div><!-- /.card-header -->
					
						<div class="card-body">
							<div class="tab-content">
								<!-- Tab de información personal del alumno -->
								<div class="active tab-pane" id="informacionp"> 
									<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/alumnoAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data">
										<input type="hidden" name="modulo_alumno" value="registrar">	
										<input type="hidden" name="alumno_repreid" value="<?php echo $repreid; ?>">																					

										<!-- Primera sección foto-->
										<div class="row">
											<div class="col-md-2">
												<div class="form-group">
													<label for="alumno_foto">Foto (250KB)</label>		
													<div class="input-group">											
														<div class="fileinput fileinput-new" data-provides="fileinput">
															<div class="fileinput-new thumbnail" style="width: 130px; height: 158px;" data-trigger="fileinput">
																<img src=""></div>
															<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 130px; max-height: 158px"></div>
															<div>
																<span class="bton bton-white bton-file">
																	<span class="fileinput-new">Seleccionar Foto</span>
																	<span class="fileinput-exists">Cambiar</span>
																	<input type="file" name="alumno_foto" id="foto" accept="image/*">
																</span>
																<a href="#" class="bton bton-orange fileinput-exists" data-dismiss="fileinput">Remover</a>
															</div>
														</div>
													</div>		
												</div>
												<!-- /.form-group -->	
											</div>
											<div class="col-md-10"> 
												<div class="row">
													<div class="col-md-4">
														<div class="form-group">
															<label for="alumno_identificacion">Identificación</label>                        
															<input type="text" class="form-control" id="alumno_identificacion" name="alumno_identificacion" placeholder="Identificación" required="required">
														</div>
													</div>											
													<div class="col-md-4">                        
														<div class="form-group">
															<label for="alumno_apellido1">Apellido paterno</label>
															<input type="text" class="form-control" id="alumno_apellido1" name="alumno_apellido1" placeholder="Primer apellido" required="required">
														</div>
													</div>
													<div class="col-md-4">
														<label for="alumno_apellido2">Apellido materno</label>
														<input type="text" class="form-control" id="alumno_apellido2" name="alumno_apellido2" placeholder="Segundo apellido">
													</div>
													<div class="col-sm-4">
														<div class="form-group">
															<label for="alumno_tipoidentificacion">Tipo identificación</label>
															<select id="alumno_tipoidentificacion" class="form-control select2" name="alumno_tipoidentificacion">																					
																<?php echo $insAlumno->listarCatalogoTipoDocumento(); ?>
															</select>
														</div>          
													</div>
													<div class="col-md-4">                        
														<div class="form-group">
															<label for="alumno_nombre1">Primer nombre</label>
															<input type="text" class="form-control" id="alumno_nombre1" name="alumno_nombre1" placeholder="Primer nombre" required="required">
														</div>
													</div>
													<div class="col-md-4">
														<label for="alumno_nombre2">Segundo nombre</label>
														<input type="text" class="form-control" id="alumno_nombre2" name="alumno_nombre2" placeholder="Segundo nombre">
													</div>    
													<div class="col-md-4">
														<div class="form-group">
															<label for="alumno_nacionalidadid">Nacionalidad</label>
															<select class="form-control select2" style="width: 100%;" id="alumno_nacionalidadid" name="alumno_nacionalidadid">
																<?php echo $insAlumno->listarCatalogoNacionalidad(); ?>
															</select>
														</div> 
													</div>
													<div class="col-md-4">									
														<div class="form-group">
															<label for="alumno_fechanacimiento">Fecha nacimiento</label>
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
																</div>
																<input type="date" class="form-control" id="alumno_fechanacimiento" name="alumno_fechanacimiento" required>
																<div class="input-group-append">
																	<span id="edad_texto" class="input-group-text" style="min-width:50px; font-weight:bold; color:#007bff;">--</span>
																</div>
															</div>
														</div>												
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="alumno_genero">Género</label>
															<div class="form-check">
																<input class="col-sm-1 form-check-input" type="radio" id="alumno_generoM" name="alumno_genero" value="M" required="required">
																<label class="col-sm-5 form-check-label" for="alumno_generoM">Masculino</label>
																<input class="col-sm-1 form-check-input" type="radio" id="alumno_generoF" name="alumno_genero" value="F">
																<label class="col-sm-4 form-check-label" for="alumno_generoF">Femenino</label>
															</div> 
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="alumno_direccion">Dirección</label>
															<textarea class="form-control" id="alumno_direccion" name="alumno_direccion" placeholder="Barrio, Calle principal, #casa, calle secundaria"></textarea>
														</div>	
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label for="alumno_unidadid">Unidad Educativa</label>
															<select class="form-control select2" style="width: 100%;" id="alumno_unidadid" name="alumno_unidadid">
																<?php echo $insAlumno->listarUnidadEducativa($unidadid); ?>
															</select>
														</div> 
													</div>
													<div class="col-md-2">
														<div class="form-group">
															<label for="alumno_hermanos">Tiene hermanos?</label>
															<!-- radio -->
															<div class="form-check">
																<input class="col-sm-1 form-check-input" type="radio" id="alumno_hermanosSi" name="alumno_hermanos" value="S" required="required">
																<label class="col-sm-6 form-check-label" for="alumno_hermanosSi">Si</label>
																<input class="col-sm-1 form-check-input" type="radio" id="alumno_hermanosNo" name="alumno_hermanos" value="N" >
																<label class="col-sm-4 form-check-label" for="alumno_hermanosNo">No</label>
															</div>
														</div>
													</div>
												</div>
												<!-- Fin primera sección foto-->
											</div>
										</div> <!--fin col md 10-->

										<!-- Segunda sección foto-->
										<div class="row">
											<div class="col-md-3">
												<div class="form-group">
													<label for="alumno_fechaingreso">Fecha ingreso</label>
													<div class="input-group">
														<div class="input-group-prepend">
															<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
														</div>
														<input type="date" class="form-control" id="alumno_fechaingreso" name="alumno_fechaingreso" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask required="required">
														<div class="input-group-append">
															<span id="ingreso_texto" class="input-group-text" style="min-width:50px; font-weight:bold; color:#007bff;">--</span>
														</div>
													</div>
												<!-- /.input group -->
												</div>								
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label for="alumno_sedeid">Sede</label>
													<select class="form-control select2" id="alumno_sedeid" name="alumno_sedeid">									
														<?php echo $insAlumno->listarOptionSede($_SESSION['rol'], $_SESSION['usuario']); ?>
													</select>	
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label for="Numcarnet">Número de carnet</label>
													<input type="text" class="form-control" id="alumno_carnet" name="alumno_carnet" placeholder="Número de carnet" inputmode="numeric" pattern="[0-9]*" required="required"> 
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label for="Numcamiseta">Número de camiseta</label>
													<input type="text" class="form-control" id="alumno_numcamiseta" name="alumno_numcamiseta" placeholder="Número de camiseta" inputmode="numeric" pattern="[0-9]*"> 
												</div>
											</div> 	 
											<div class="col-md-3">
												<div class="form-group">
													<label for="alumno_observacion">Observación</label>
													<textarea class="form-control" id="alumno_observacion" name="alumno_observacion" placeholder="Observacion"></textarea>
												</div>	
											</div>    
										</div>  <!--./row line 874--> 
										<!-- Fin segunda sección foto-->			
								</div>

									<!-- Tab de información médica del alumno -->
									<div class="tab-pane" id="informacionm">
										<div class="row">
											<div class="col-md-3">
												<div class="form-group">
													<label for="infomedic_tiposangre">Tipo de sangre</label>
													<input type="text" class="form-control" id="infomedic_tiposangre" name="infomedic_tiposangre" placeholder="Tipo de sangre">                          
												</div>
											</div> 
											<div class="col-md-3">
												<div class="form-group">
													<label for="Peso">Peso (Kg)</label>
													<input type="text" class="form-control" id="infomedic_peso" name="infomedic_peso" placeholder="Peso en kilogramos">                          
												</div>
											</div>   
											<div class="col-md-3">
												<div class="form-group">
													<label for="Talla">Talla (cm)</label>
													<input type="text" class="form-control" id="infomedic_talla" name="infomedic_talla" placeholder="Talla en centímetros">                          
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label for="Enfermedad">Enfermedad diagnosticada</label>
													<input type="text" class="form-control" id="infomedic_enfermedad" name="infomedic_enfermedad" placeholder="Breve descripción de enfermedad">                          
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label for="Medicamentos">Medicamentos</label>
													<input type="text" class="form-control" id="infomedic_medicamentos" name="infomedic_medicamentos" placeholder="Breve descripción de dósis de medicamentos">                          
												</div>
											</div> 
											<div class="col-md-3">
												<div class="form-group">
													<label for="Alergia1">Alergia a medicamentos</label>
													<input type="text" class="form-control" id="infomedic_alergia1" name="infomedic_alergia1" placeholder="Alergia a medicamentos">                          
												</div>
											</div> 
											<div class="col-md-3">
												<div class="form-group">
													<label for="Alergia2">Alergia a objetos</label>
													<input type="text" class="form-control" id="infomedic_alergia2" name="infomedic_alergia2" placeholder="Alergia a objetos">                          
												</div>
											</div>  
											<div class="col-md-3">
												<div class="form-group">
													<label for="Cirugias">Cirugías</label>
													<input type="text" class="form-control" id="infomedic_cirugias" name="infomedic_cirugias" placeholder="Breve descripción de cirugías">                          
												</div>
											</div>  
											<div class="col-md-3">
												<div class="form-group">
													<label for="Observacion">Observación</label>
													<input type="text" class="form-control" id="infomedic_observacion" name="infomedic_observacion" placeholder="Observacion">                          
												</div>
											</div>  
											<div class="col-md-3">
												<div class="form-group">
													<label for="Covid">Carnet vacunación Covid</label>
													<div class="form-check">
														<input class="col-sm-1 form-check-input" type="radio" id="infomedic_covidSi" name="infomedic_covid" value="S" >
														<label class="col-sm-6 form-check-label" for="infomedic_covidSi">Si</label>
														<input class="col-sm-1 form-check-input" type="radio" id="infomedic_covidNo" name="infomedic_covid" value="N" >
														<label class="col-sm-4 form-check-label" for="infomedic_covidNo">No</label>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Vacunas">Carnet vacunación habitual</label>
													<div class="form-check">
														<input class="col-sm-1 form-check-input" type="radio" id="infomedic_vacunasSi" name="infomedic_vacunas" value="S" >
														<label class="col-sm-6 form-check-label" for="infomedic_vacunasSi">Si</label>
														<input class="col-sm-1 form-check-input" type="radio" id="infomedic_vacunasNo" name="infomedic_vacunas" value="N" >
														<label class="col-sm-4 form-check-label" for="infomedic_vacunasNo">No</label>
													</div>                         
												</div>
											</div>  
										</div>
									</div>

									<!-- Tab información contacto de emergencia -->
									<div class="tab-pane" id="contactoem">
										<div class="row">
											<div class="col-md-3">
												<div class="form-group">
													<label for="CEmergencia">Celular</label>
													<input type="text" class="form-control" id="cemer_celular" name="cemer_celular" placeholder="+593">                          
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label for="Nomcontactoemer">Nombre contacto</label>
													<input type="text" class="form-control" id="cemer_nombre" name="cemer_nombre" placeholder="Nombre contacto">                          
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="cemer_parentesco">Parentesco</label>
													<select class="form-control select2" style="width: 100%;" id="cemer_parentesco" name="cemer_parentesco">
														<?php echo $insAlumno->listarCatalogoParentesco($cemer_parentesco); ?>
													</select>
												</div> 
											</div>
										</div>		
									</div>

									<!-- Tab cedula del alumno -->
									<div class="tab-pane" id="cedula">
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="alumno_cedulaA">Anverso</label>		
													<div class="input-group">											
														<div class="fileinput fileinput-new" data-provides="fileinput">
															<div class="fileinput-new thumbnail" style="width: 330px; height: 210px;" data-trigger="fileinput">
																<img src=""></div>
															<div class="fileinput-preview fileinput-exists thumbnail" style="width: 330px; height: 210px"></div>
															<div>
																<span class="bton bton-white bton-file">
																	<span class="fileinput-new">Seleccionar Foto</span>
																	<span class="fileinput-exists">Cambiar</span>
																	<input type="file" name="alumno_cedulaA" id="foto" accept="image/*">
																</span>
																<a href="#" class="bton bton-orange fileinput-exists" data-dismiss="fileinput">Remover</a>
															</div>
														</div>
													</div>		
												</div>
												<!-- /.form-group -->	
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label for="alumno_cedulaR">Reverso</label>		
													<div class="input-group">											
														<div class="fileinput fileinput-new" data-provides="fileinput">
															<div class="fileinput-new thumbnail" style="width: 330px; height: 210px;" data-trigger="fileinput">
																<img src=""></div>
															<div class="fileinput-preview fileinput-exists thumbnail" style="width: 330px; height: 210px"></div>
															<div>
																<span class="bton bton-white bton-file">
																	<span class="fileinput-new">Seleccionar Foto</span>
																	<span class="fileinput-exists">Cambiar</span>
																	<input type="file" name="alumno_cedulaR" id="foto" accept="image/*">
																</span>
																<a href="#" class="bton bton-orange fileinput-exists" data-dismiss="fileinput">Remover</a>
															</div>
														</div>
													</div>		
												</div>
												<!-- /.form-group -->	
											</div>
										</div>
									</div>
									
									<!-- Tab horario del alumno -->
									<div class="tab-pane" id="horario">
										<div class="container-fluid">													
											<!-- Table row -->
											<div class="row">
												<div class="col-md-4">
													<div class="form-group">
														<label for="horarioid">Horarios</label>
														<select id="horarioid" class="form-control select2" name="horarioid">
															<option value="">Seleccione un horario</option>																					
															<?php echo $insAlumno->listarhorarios(); ?>
														</select>
													</div>          
												</div>

												<div class="col-md-12 table-responsive">
													<table class="table table-striped table-bordered table-sm">											
														<thead>																
															<tr>		
																<th></th>												
																<th>LUNES</th>	
																<th>MARTES</th>
																<th>MIERCOLES</th>
																<th>JUEVES</th>
																<th>VIERNES</th>																																		
															</tr>
														</thead>	
														<tbody id="tabla_horario">				
														</tbody>
													</table>
												</div>
												<!-- /.col -->
											</div>
											
										</div><!-- /.container-fluid -->
									</div>
									<!-- /.tab-pane -->		
									<div class="card-footer">						
										<button type="submit" class="btn btn-success btn-sm">Guardar</button>
										<button type="reset" class="btn btn-dark btn-sm">Limpiar</button>
										<!--a class="btn btn-dark btn-sm" href="#" onclick="cerrarVentana(); return false;">Regresar</a-->					
										<?php include "./app/views/inc/btn_back.php";	?>						
									</div>	
								</form>	
								<!-- /.tab-pane -->
							</div>
							<!-- /.tab-content -->
						</div><!-- /.card-body -->
					</div>
					<!-- /.card -->
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

	<script>
		document.getElementById("alumno_fechanacimiento").addEventListener("input", function() {
			const fechaNacimiento = new Date(this.value);
			const edadTexto = document.getElementById("edad_texto");

			if (!isNaN(fechaNacimiento)) {
				const hoy = new Date();
				let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
				const mes = hoy.getMonth() - fechaNacimiento.getMonth();

				// Ajuste si no ha cumplido años aún este año
				if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
					edad--;
				}

				edadTexto.textContent = `${edad} años`;
			} else {
				edadTexto.textContent = "--";
			}
		});

		document.getElementById("alumno_carnet").addEventListener("input", function() {
			this.value = this.value.replace(/\D/g, ''); // Reemplaza cualquier carácter no numérico
		});

		document.getElementById("alumno_numcamiseta").addEventListener("input", function() {
			this.value = this.value.replace(/\D/g, ''); // Reemplaza cualquier carácter no numérico
		});
	</script>

	<script>
		document.getElementById("alumno_fechaingreso").addEventListener("input", function() {
			const fechaIngreso = new Date(this.value);
			const ingresoTexto = document.getElementById("ingreso_texto");

			if (!isNaN(fechaIngreso)) {
				const hoy = new Date();
				let edad = hoy.getFullYear() - fechaIngreso.getFullYear();
				const mes = hoy.getMonth() - fechaIngreso.getMonth();

				// Ajuste si no ha cumplido años aún este año
				if (mes < 0 || (mes === 0 && hoy.getDate() < fechaIngreso.getDate())) {
					edad--;
				}

				ingresoTexto.textContent = `${edad} años`;
			} else {
				ingresoTexto.textContent = "--";
			}
		});
	</script>

	<!-- horarioid-->
	<script>
		$(document).ready(function() {
			$('#horarioid').change(function() {
				var horario_id = $(this).val();

				if (horario_id) {
					$.ajax({
						type: 'POST',
						url: '<?php echo APP_URL; ?>app/ajax/alumnoAjax.php',
						data: {
							modulo_alumno: 'cargarHorario',
							horarioid: horario_id							
						},
						success: function(response) {
							$('#tabla_horario').html(response);
						}
					});
				} else {
					$('#horarioid').html('<option value="">Seleccione un horario</option>');
				}
			});
		});
	</script>	

	<script>
		function cerrarVentana() {
			window.close();
		}
	</script>
  </body>
</html>