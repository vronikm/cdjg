<?php	
	use app\controllers\reporteController;

	include 'app/lib/barcode.php';
	
	$generator = new barcode_generator();
	$symbology="qr";
	$optionsQR=array('sx'=>4,'sy'=>4,'p'=>-10);		

	$insDetalle   = new reporteController();	
	$empleado_id  = $_POST['empleado_id'];
	$fecha_inicio = $_POST['fecha_inicio'];
	$fecha_fin    = $_POST['fecha_fin'];

	$datosAsistencia=$insDetalle->seleccionarDatos("Unico","sujeto_empleado","empleado_id",$empleado_id);
	if($datosAsistencia->rowCount()==1){
		$datosAsistencia		= $datosAsistencia->fetch();
		$empleado_nombre 		= $datosAsistencia['empleado_nombre'];
	}else{
		$empleado_nombre 	= "";
	}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Reporte de asistencia de empleados</title>
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
							<h5 class="m-0">Detalle de asistencias empleado <?php echo $empleado_nombre; ?></h5>
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
			
			<section class="content">
				<div class="container-fluid">
					<div class="row">
						<div class="col-1">
						</div>
						<div class="col-10">
							<!-- Main content -->
							<div class="invoice p-3 mb-3">							
								<div class="col-sm-11 invoice-col">									
									<address class="text-center"><br>
										<strong class="profile-username">CLUB DEPORTIVO JORGE GUZMAN</strong><br><br>											
										<div class="row">
											<div class="row">
												<div class="col-4"></div>														
												<div class="col-12">
													Empleado: <?php echo $empleado_nombre;?>
												</div>
												<div class="col-11">
													Fecha de generación: <?php echo date('d-m-Y');?>
												</div>
											</div>
											<!-- /.col -->
										</div>
									</address>
								</div>							
								
								<div class="card-body">									
									<table id="example1" class="table table-bordered table-striped table-sm" style="font-size: 13px;">
										<thead>
											<tr>
												<th>Nombre empleado</th>
												<th>Fecha</th>
												<th>Hora</th>
												<th>Tipo</th>
												<th>Ubicación</th>														
											</tr>
										</thead>
										<tbody>
											<?php 
												echo $insDetalle->listarMarcacionesEmpleado($empleado_id, $fecha_inicio, $fecha_fin); 
											?>							
										</tbody>	
									</table>
								</div>	

								<div class="row no-print">
									<div class="col-12">
										<button class="btn btn-dark btn-back btn-sm" onclick="cerrarPagina()">Regresar</button>
									</div>
								</div>
							</div>
							<!-- /.invoice -->							
						</div><!-- /.col -->
					</div><!-- /.row -->
				</div><!-- /.container-fluid -->
			</section>      
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
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>

	<script>
        function cerrarPagina() {
            window.close();
        }
    </script>

     <!-- Page specific script -->
	 <script>
		$(function () {
			$("#example1").DataTable({
			"responsive": true, 
			"lengthChange": false, 
			"autoWidth": false,
			"paging": false, // Deshabilitar la paginación
			"searching": false, // Deshabilitar la búsqueda
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
				},
				"buttons": {
					"copy": "Copiar",
					"print": "Imprimir",
					"text": 'Imprimir Tabla',
					"title": 'Datos de Alumnos',
					"messageTop": 'Generado por el sistema de gestión de alumnos.',
					"messageBottom": 'Página generada automáticamente.',
					customize: function(win) {
						$(win.document.body)
							.css('font-family', 'Arial')
							.css('background-color', '#f3f3f3');

						// Cambiar el estilo de la tabla impresa
						$(win.document.body).find('table')
							.addClass('display')  // Añadir una clase CSS a la tabla impresa
							.css('font-size', '12pt')
							.css('border', '1px solid black');

						// Agregar logotipo al principio
						$(win.document.body).prepend(
							'<img src="https://example.com/logo.png" style="position:absolute; top:0; left:0; width:100px;" />'
						);

						// Modificar título y agregar estilos CSS adicionales
						$(win.document.body).find('h1')
							.css('text-align', 'center')
							.css('color', '#4CAF50');
					}
				}
			},
			"buttons": ["copy", "csv", "excel", "pdf", "print"]
			}).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
			$('#example2').DataTable({
				"paging": true,
				"lengthChange": false,
				"searching": false,
				"ordering": true,
				"info": true,
				"autoWidth": false,
				"responsive": true,
			});
		});
	</script>
  </body>
</html>