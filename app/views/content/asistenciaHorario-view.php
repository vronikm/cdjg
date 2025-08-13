<?php
	date_default_timezone_set("America/Guayaquil");

	use app\controllers\asistenciaController;
	$insHorario = new asistenciaController();			
	
	$horario_id = ($url[1] != "") ? $url[1] : 0;	
	$modulo_horario = ($horario_id == 0) ? 'registrar_horario' : 'actualizar_horario';
	
	$datos=$insHorario->seleccionarDatos("Unico","asistencia_horario","horario_id",$horario_id);
	if($datos->rowCount()==1){
		$datos=$datos->fetch();
		$lugar_sedeid 		= $datos['horario_sedeid'];
		$horario_nombre 	= $datos['horario_nombre'];
		$horario_detalle	= $datos['horario_detalle'];
		$horario_estado		= $datos['horario_estado'];
	}else{
		$lugar_sedeid = isset($_POST['horario_sedeid']) ? $insHorario->limpiarCadena($_POST['horario_sedeid']) : 0;
		$horario_nombre 	= "";
		$horario_detalle	= "";
		$horario_estado		= "";
	}

	$encabezado_vista = ($modulo_horario == 'registrar_horario') ? 'Creación de horario Sede ' : 'Edición horario '.$horario_nombre.' '.$horario_detalle.' Sede ';

	$sede=$insHorario->seleccionarDatos("Unico","general_sede","sede_id",$lugar_sedeid);
	if($sede->rowCount()==1){
		$sede=$sede->fetch();
		$sede_nombre = $sede['sede_nombre'];		
	}else{		
		$sede_nombre = "";
	}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Horarios</title>

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
							<h4 class="m-0"> <?php echo $encabezado_vista. $sede_nombre; ?></h4>
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

					<div class="card card-default">						
						<div class="card-header" style='height: 40px;'>
							<h3 class="card-title">Datos del horario</h3>

							<div class="card-tools">
								<button type="button" class="btn btn-tool" data-card-widget="collapse">
									<i class="fas fa-minus"></i>
								</button>
							</div>
						</div>

						<div class="card-body">						

							<form class="FormularioAjax" id="quickForm" action="<?php echo APP_URL; ?>app/ajax/asistenciaAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
								<input type="hidden" name="modulo_asistencia" value="<?php echo $modulo_horario; ?>">
								<input type="hidden" name="lugar_sedeid" value="<?php echo $lugar_sedeid; ?>">	
								<input type="hidden" name="horario_id" value="<?php echo $horario_id; ?>">										
								
								<div class="row" style='font-size: 14px;'>
									<div class="col-md-3">
										<div class="form-group">
											<label for="horario_nombre">Horario nombre</label>
											<input type="text" class="form-control" style='height: 31px;' id="horario_nombre" name="horario_nombre" placeholder="Nombre" value="<?php echo $horario_nombre; ?>" required >
										</div>
									</div>
								
									<div class="col-md-9">
										<div class="form-group">
											<label for="horario_detalle">Horario descripción</label>	
											<input type="text" class="form-control" style='height: 31px;' id="horario_detalle" name="horario_detalle" placeholder="Descripción" value="<?php echo $horario_detalle; ?>">
										</div>
									</div>									
								</div>
								<div class="tab-custom-content">
									<p class="lead mb-0" style="font-size:15px; height: 23px;">Horario de entrenamiento</p>
								</div>
								<div class="tab-content" id="custom-content-above-tabContent">
									<table id="presupuesto" name="presupuesto" class="table table-bordered table-striped table-sm" style='font-size: 14px;'>
										<thead>
											<tr style="text-align: center;">
												<th>Día</th>
												<th>Lugar entrenamiento</th>
												<th>Hora</th>
												<th>Profesor</th>
												<th><button type="button" class="btn btn-info btn-xs float-right btn_add" id="agregar" name="agregar">Agregar</button></th>
											</tr>
											<?php echo $insHorario->listarDetalleHorario($horario_id); ?>
										</thead>
										<tbody>
											<?php 
												//echo $insLugar->listarLugar(); 
											?>								
										</tbody>
									</table>
								</div>

								<button type="submit" class="btn btn-success btn-sm">Guardar</button>								
								<button onclick="cerrarPestana()" class="btn btn-info btn-sm">Cancelar</button>
								<button type="reset" class="btn btn-dark btn-sm">Limpiar</button>								
							</form>	

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

	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>

	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery-validation/jquery.validate.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery-validation/additional-methods.min.js"></script>

	

	<script>
		$(document).ready(function() {
			$(".btn_add").on("click", function() {
				// Columna 1: Días de la semana
				var column1 = "<select class='form-control' name='dia[]'>" +
							"<option value='1'>Lunes</option>" +
							"<option value='2'>Martes</option>" +
							"<option value='3'>Miércoles</option>" +
							"<option value='4'>Jueves</option>" +
							"<option value='5'>Viernes</option>" +
							"<option value='6'>Sábado</option>" +
							"<option value='7'>Domingo</option>" +
							"</select>";
				
				// Columna 2: Lugares de entrenamiento con PHP
				var column2 = "<select class='form-control' id='lugar' name='lugar[]'><?php echo addslashes($insHorario->listarOptionLugar($lugar_sedeid,0)); ?></select>";
				
				// Columna 3: Horarios con PHP
				var column3 = "<select class='form-control' id='hora' name='hora[]'><?php echo addslashes($insHorario->listarOptionHora(0)); ?></select>";
				
				// Columna 4: Profesores con PHP
				var column4 = "<select class='form-control' id='profesor' name='profesor[]'><?php echo addslashes($insHorario->listarOptionProfesor($lugar_sedeid,0)); ?></select>";
				
				// Agregar una nueva fila a la tabla
				$("#presupuesto").append(
					"<tr><td>" + column1 + "</td>" +
					"<td>" + column2 + "</td>" +
					"<td>" + column3 + "</td>" +
					"<td>" + column4 + "</td>" +                    
					"<td><button type='button' class='btn btn-danger btn-sm btn-icon icon-left btn_remove float-right'>Eliminar<i class='entypo-trash'></i></button></td></tr>"
				);			    
			});

			// Evento para eliminar fila
			$(document).on("click", ".btn_remove", function() {
				$(this).closest("tr").remove();
			});
		});
	</script>
	<script type="text/javascript">
        function cerrarPestana() {
            window.close();
        }
    </script>
  </body>
</html>