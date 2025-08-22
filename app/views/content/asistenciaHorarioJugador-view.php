<?php
	use app\controllers\asistenciaController;
	$insAsignar = new asistenciaController();	

	$horario_id = ($url[1] != "") ? $url[1] : 0;
	$sede_id = ($url[2] != "") ? $url[2] : 0;
	$vista = ($url[3] != "") ? $url[3]."/" : "";

	$url = ($vista != "") ? 'asistenciaHorarioLista/'.$horario_id : 'asistenciaListHorario';
	

	if($sede_id != 0){
		$sede=$insAsignar->BuscarSede($sede_id);		
		if($sede->rowCount()==1){
			$sede =	$sede->fetch();	
			$sede_nombre = $sede['sede_nombre'];
			/*		
			if($sede['sede_id'] ==1){
				$sede_nombre="JIPIRO";
			}
			else{
				$sede_nombre = $sede['sede_nombre'];
			}
				*/
		}
	}else{
		$sede_nombre 	= '';
	}
	
	$modulo_asistencia	= '';

	if($horario_id != 0){
		$nombreHorario=$insAsignar->buscarHorario($horario_id);		
		if($nombreHorario->rowCount()==1){
			$nombreHorario		=	$nombreHorario->fetch();				
			$horario_nombre		= 	$nombreHorario['horario_nombre'];		
			$horario_detalle	= 	$nombreHorario['horario_detalle'];		
		}
	}else{
		$horario_nombre 	= '';
		$horario_detalle	= '';
	}

	if(isset($_POST['alumno_identificacion'])){
		$alumno_identificacion = $insAsignar->limpiarCadena($_POST['alumno_identificacion']);
	} ELSE{
		$alumno_identificacion = "";
	}

	if(isset($_POST['alumno_nombre1'])){
		$alumno_primernombre = $insAsignar->limpiarCadena($_POST['alumno_nombre1']);
	} ELSE{
		$alumno_primernombre = "";
	}

	if(isset($_POST['alumno_apellido1'])){
		$alumno_apellidopaterno = $insAsignar->limpiarCadena($_POST['alumno_apellido1']);
	} ELSE{
		$alumno_apellidopaterno = "";
	}
	
	if(isset($_POST['alumno_anio'])){
		$alumno_anio = $insAsignar->limpiarCadena($_POST['alumno_anio']);
	} ELSE{
		$alumno_anio = "";
	}	
?>


<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo APP_NAME; ?>| Asignación horario</title>

		<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
		<!-- Google Font: Source Sans Pro -->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
		<!-- Font Awesome -->
		<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
		<!-- DataTables -->
		<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
		<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
		<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
		<!-- Theme style -->
		<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">


		<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">
		<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>

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
								<h5 class="m-0">Asignación Horario <?php echo "$horario_nombre - $horario_detalle - Sede $sede_nombre"; ?></h5>
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

				<!-- Section listado de alumnos -->
				<section class="content">					
					<div class="container-fluid">
						<form action="<?php echo APP_URL."asistenciaHorarioJugador/".$horario_id."/".$sede_id."/".$vista ?>" method="POST" autocomplete="off" enctype="multipart/form-data" >					
							<div class="card card-default">
								<div class="card-header" style='height: 40px;'>
									<h3 class="card-title">Búsqueda de alumnos</h3>
									<div class="card-tools">
										<button type="button" style='height: 40px;' class="btn btn-tool" data-card-widget="collapse">
										<i class="fas fa-minus"></i>
										</button>
									</div>
								</div>  

								<!-- card-body -->                
								<div class="card-body">
									<div class="row" style='font-size: 14px; height: 60px;'>
										<div class="col-sm-2">
											<div class="form-group">
												<label for="alumno_identificacion">Identificación</label>                        
												<input type="text" class="form-control" style='font-size: 13px; height: 31px;' id="alumno_identificacion" name="alumno_identificacion" placeholder="Identificación" value="<?php echo $alumno_identificacion; ?>">
											</div>        
										</div>
										<div class="col-sm-3">
											<div class="form-group">
												<label for="alumno_apellido1">Apellido paterno</label>
												<input type="text" class="form-control" style='font-size: 13px; height: 31px;' id="alumno_apellido1" name="alumno_apellido1" placeholder="Primer apellido" value="<?php echo $alumno_apellidopaterno; ?>">
											</div>         
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="alumno_nombre1">Primer nombre</label>
												<input type="text" class="form-control" style='font-size: 13px; height: 31px;' id="alumno_nombre1" name="alumno_nombre1" placeholder="Primer nombre" value="<?php echo $alumno_primernombre; ?>">
											</div>
										</div>  

										<div class="col-md-2">
											<div class="form-group">
												<div class="form-group">
													<label for="alumno_ano">Año</label>
													<input type="text" class="form-control" style='font-size: 13px; height: 31px;' id="alumno_anio" name="alumno_anio" placeholder="año" value="<?php echo $alumno_anio; ?>">
												</div>	
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<label for="alumno_sedeid">.</label>
												<button type="submit" style='font-size: 13px; height: 31px;' class="form-control btn btn-info">Buscar</button>
											</div>
										</div>
									</div>					
								</div>
							</div>
						</form>

						<div class="card card-default">
							<div class="card-header" style='height: 40px;'>
								<h3 class="card-title">Resultado de la búsqueda</h3>
								<div class="card-tools">							
									<button type="button" style='height: 40px;'class="btn btn-tool" data-card-widget="collapse">
										<i class="fas fa-minus"></i>
									</button>
								</div>
							</div>							
						
							<div class="card-body">
								<table id="example1" class="table table-bordered table-striped table-sm" style="font-size: 14px;">
									<thead>
										<tr>
											<th>Sede</th>	
											<th>Identificación</th>
											<th>Nombres y Apellidos</th>
											<th>Año</th>
											<th></th>	
										</tr>
									</thead>
									<tbody>
										<?php 												
											echo $insAsignar->listarAlumnos($horario_id,$alumno_identificacion,$alumno_apellidopaterno, $alumno_primernombre, $alumno_anio, $sede_id); 												
										?>								
									</tbody>
								</table>	
							</div>
							<div class="card-footer">		
								<!--a href="<?php echo APP_URL.'asistenciaListHorario/'; ?>" class="btn btn-dark btn-sm">Regresar</a>	
								<button class="btn btn-dark btn-back btn-sm" onclick="cerrarPestana()">Regresar</button-->	
								<a href="#" class="btn btn-dark btn-sm" onclick="document.getElementById('form-regresar').submit(); return false;">Regresar</a>								
								<form id="form-regresar" action="<?php echo APP_URL.$url."/" ?>" method="POST" autocomplete="off" enctype="multipart/form-data">	
									<input type="hidden" name="horario_sedeid" value="<?php echo $sede_id; ?>">
								</form>												
							</div>	
						</div>

					</div>				
				</section>				
			</div><!-- /.container-fluid -->
		
			<?php require_once "app/views/inc/footer.php"; ?>

			<!-- Control Sidebar -->
			<aside class="control-sidebar control-sidebar-dark">
			<!-- Control sidebar content goes here -->
			</aside>
			<!-- /.control-sidebar -->

		</div>
			
		<!-- fin -->

		<!-- ./wrapper -->
		<!-- jQuery -->
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery/jquery.min.js"></script>
		<!-- Bootstrap 4 -->
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
		<!-- DataTables  & Plugins -->
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables/jquery.dataTables.min.js"></script>
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jszip/jszip.min.js"></script>
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.print.min.js"></script>
		<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
		<!-- AdminLTE App -->
		<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>

		<!-- Page specific script -->
		<script>
			$(function () {
				$("#example1").DataTable({
				"responsive": true, "lengthChange": false, "autoWidth": false,
				"language": {
					"decimal": "",
					"emptyTable": "No hay datos disponibles en la tabla",
					"info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
					"infoEmpty": "Mostrando 0 a 0 de 0 entradas",
					"infoFiltered": "(filtrado de _MAX_ entradas totales)",
					"infoPostFix": "",
					"thousands": ",",
					"lengthMenu": "Mostrar _MENU_ entradas",
					"loadingRecords": "Cargando...",
					"processing": "Procesando...",
					"search": "Buscar:",
					"zeroRecords": "No se encontraron registros coincidentes",
					"paginate": {
						"first": "Primero",
						"last": "Último",
						"next": "Siguiente",
						"previous": "Anterior"
					},
					"aria": {
						"sortAscending": ": activar para ordenar la columna ascendente",
						"sortDescending": ": activar para ordenar la columna descendente"
					}
				},
				}).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');			    
			});
		</script>

	<script type="text/javascript">
		function cerrarPestana() {
			window.close();
		}
    </script>
		<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
		<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>

	</body>
</html>








