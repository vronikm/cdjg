<?php
	use app\controllers\asistenciaController;
	$insAsistencia = new asistenciaController();

	$alumno=$insLogin->limpiarCadena($url[1]);

	$datos=$insAsistencia->BuscarAlumno($alumno);
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
	
		$sede=$insAsistencia->informacionSede($datos['alumno_sedeid']);
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

	}else{
		include "<?php echo APP_URL; ?>/app/views/inc/error_alert.php";
	}

	if(isset($_POST['alumno_anioasist'])){
		$alumno_anioasist = $insAsistencia->limpiarCadena($_POST['alumno_anioasist']);
	
	} ELSE{
		$alumno_anioasist = "2024";		
	}

	if(isset($_POST['alumno_mesasist'])){
		$alumno_mesasist = $insAsistencia->limpiarCadena($_POST['alumno_mesasist']);	
	} ELSE{
		$alumno_mesasist = "Enero";		
	}

	if(isset($_POST['alumno_sedeid'])){
		$alumno_sedeid = $insAsistencia->limpiarCadena($_POST['alumno_sedeid']);		
	} ELSE{
		$alumno_sedeid = "";		
	}

	if(isset($_POST['alumno_identificacion'])){
		$alumno_identificacion = $insAsistencia->limpiarCadena($_POST['alumno_identificacion']);
	} ELSE{
		$alumno_identificacion = "";
	}

	if(isset($_POST['alumno_nombre1'])){
		$alumno_primernombre = $insAsistencia->limpiarCadena($_POST['alumno_nombre1']);
	} ELSE{
		$alumno_primernombre = "";
	}

	if(isset($_POST['alumno_apellido1'])){
		$alumno_apellidopaterno = $insAsistencia->limpiarCadena($_POST['alumno_apellido1']);
	} ELSE{
		$alumno_apellidopaterno = "";
	}
	
	if(isset($_POST['alumno_anio'])){
		$alumno_anio = $insAsistencia->limpiarCadena($_POST['alumno_anio']);
	
	} ELSE{
		$alumno_anio = "";		
	}

	if($alumno_anio == ""){
		$categoria = 0;
	}else{
		$categoria = $alumno_anio;
	}
?>


<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?>| Ver asistencia</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
	  <!-- fullCalendar -->
	  <link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fullcalendar/main.css">
	<!-- DataTables -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">
    
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
				<h1 class="m-0">Registro de asistencia</h1>
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
			<form action="<?php echo APP_URL."buscarAsistencia/" ?>" method="POST" autocomplete="off" enctype="multipart/form-data" >			
			<!-- card-body -->                
			<div class="card-body">
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
										<b>Sede</b> <a class="float-right"><?php echo $datos['sede_nombre']; ?></a>
									</li>
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
										<b>ID</b> <a class="float-right"><?php echo $alumno; ?></a>
									</li>
								</ul>
							</div>
							<!-- /.card-body -->
						</div>
						<!-- /.card -->
					</div>
					<!-- /.col -->
					<div class="col-md-9">
						<div class="card card-primary card-outline">
							<div class="card-body p-0">
								<!-- THE CALENDAR -->
								<div id="calendar">
									<?php 
										if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'CalendarioEventos') {
											header('Content-Type: application/json');
											echo $insAsistencia->CalendarioEventos($alumno); 
											exit;
										}
									?>
								</div>
							</div>
							<!-- /.card-body -->
						</div>
					<!-- /.card -->
					</div>
					<!-- /.col -->
				</div>	
			</div>            
			</form>
		</section>
		<!-- /.section -->
      
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
	<!-- jQuery UI -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery-ui/jquery-ui.min.js"></script>
	<!-- DataTables  & Plugins -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables/jquery.dataTables.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jszip/jszip.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/pdfmake/pdfmake.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/pdfmake/vfs_fonts.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.print.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
	<!-- AdminLTE App -->
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>	
	<!-- fullCalendar 2.2.5 -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/moment/moment.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fullcalendar/main.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fullcalendar/locales/es.js"></script>

     <!-- Page specific script -->
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var calendarEl = document.getElementById('calendar');
			var calendar = new FullCalendar.Calendar(calendarEl, {
				initialView: 'dayGridMonth',
				locale: 'es', 
				aspectRatio: 2.14, // Cambia la relación de aspecto para hacerlo más pequeño
				events: <?php echo $insAsistencia->CalendarioEventos($alumno); ?>			
			});
			calendar.render();
		});
	</script>
  </body>
</html>








