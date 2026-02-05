<?php
	use app\controllers\asistenciaController;
	$insAlumno = new asistenciaController();

	$horario_id = ($url[1] != "") ? $url[1] : 0;

	if($horario_id != 0){
		$horarioSede=$insAlumno->BuscarHorarioSede($horario_id);		
		if($horarioSede->rowCount()==1){
			$horarioSede	=	$horarioSede->fetch();				
		}
	}
	
	
	if(isset($_POST['fecha'])){
		$fechahoy =  $insAlumno->limpiarCadena($_POST['fecha']);		
	} ELSE{
		$fechahoy = date('Y-m-d');		
	}	
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?>| Alumnos </title>
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

		<nav class="main-header navbar navbar-expand navbar-white navbar-light">
			<!-- Left navbar links -->
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
				</li>
				<form action="<?php echo APP_URL."asistenciaAlumno/$horario_id" ?>" method="POST" autocomplete="off" enctype="multipart/form-data" >
				<div class="container-fluid">					
					<div class="col-xm-6">		
						<li class="nav-item d-sm-inline-block">
							<div class="card-comment">											
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
									</div>
									<?php 
										if($_SESSION['rol']!= 1 && $_SESSION['rol']!= 2 && $_SESSION['rol']!= 3){
											echo '<input class="form-control" value="'.$fechahoy.'" disabled>';
											echo '<input type="hidden" name="fecha" value="'.$fechahoy.'">';
										}else{
											echo '<input type="date" class="form-control" id="fecha" name="fecha" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask value="'.$fechahoy.'" required>
											  <span class="input-group-append">
												<button type="submit" class="btn btn-info btn-flat">Generar lista</button>
											</span>
											';
										}
									?>							
								</div>								
							</div>				
						</li>
					</div>
					
					
				</div>
					
				</form>	
				
			</ul>

			 <!-- Right navbar links -->
		</nav>
    	<!--?php require_once "app/views/inc/navbar.php"; ?-->
      	<!-- /.navbar -->

		<!-- Main Sidebar Container -->
		<?php require_once "app/views/inc/main-sidebar.php"; ?>
		<!-- /.Main Sidebar Container -->  

      <!-- vista -->
      <div class="content-wrapper">
		
		<!-- Main content -->
		<section class="content">
			<div class="container-fluid">
			<!-- Small boxes (Stat box) -->
				<div class="card card-default">
					

					<div class="card-body card-comments">
						
					
		                  
						<table id="example1" class="table table-bordered table-striped table-sm ">
							<thead>
								<tr>
									<th>Categoría</th>
									<th>Nombres</th>									
									<th>Asistencia</th>	
								</tr>
							</thead>
							<tbody>
								<?php 								
									if(isset($_POST['fecha'])){												
										echo $insAlumno->ListadoAlumnos($horarioSede["horario_id"],$fechahoy);		
									}else{
										echo $insAlumno->ListadoAlumnos($horarioSede["horario_id"], date('Y-m-d'));		
									}										
								?>								
							</tbody>	
						</table>	
						<div class="card-footer">
							<!--a href="<?php echo APP_URL.'equipoList/'.$horarioSede["horario_id"].'/'; ?>" class="btn btn-dark btn-sm">Regresar</a-->
							<button class="btn btn-dark btn-back btn-sm" onclick="cerrarPestana()">Regresar</button>
						</div>
					</div>					

				</div>
			<!-- /.row -->
			</div><!-- /.container-fluid -->
		</section>
		<!-- /.content -->
      
      </div>
      <!-- /.vista -->

      <?php require_once "app/views/inc/footer.php"; ?>

      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark">
				
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
	
	<!-- Page specific script -->
	<script>
		$(function () {
			$("#example1").DataTable({
				"responsive": true,
				"lengthChange": false, // Deshabilitar el cambio de longitud
				"autoWidth": false,
				"paging": false, // Deshabilitar la paginación
				"searching": true, // Habilitar la búsqueda
				"ordering": false, // Desactiva la opción de ordenar
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
			}).buttons().container().appendTo('#example1_wrapper .col-md-5:eq(0)');
		});
	</script>


	<script type="text/javascript">
		function cerrarPestana() {
			window.close();
		}
    </script>

	<script>
		// NUEVO: Evento para Toma de Asistencia
		$(document).on('click', '.btn-asistencia', function() {
			var estado = $(this).data('estado');
			var alumno_id = $(this).data('alumnoid');
			var fecha = $(this).data('fecha');
			var boton = $(this);

			$.ajax({
				type: 'POST',
				url: '<?php echo APP_URL; ?>app/ajax/asistenciaAjax.php',
				data: {
					modulo_asistencia: 'asistencia',
					estado: estado,
					fecha: fecha,
					alumno_id: alumno_id
				},
				beforeSend: function() {
					boton.prop('disabled', true);
				},
				success: function(response) {
					// Puedes mostrar un toast, cambiar color, etc.
					$('.btn-asistencia[data-alumnoid="' + alumno_id + '"]').removeClass('btn-info').addClass('btn-dark'); // Reset buttons
					boton.removeClass('btn-dark').addClass('btn-info'); // Highlight selected

					// Si quieres mostrar un mensaje de éxito:
					// alert("Asistencia guardada!");
				},
				error: function() {
					alert('Error al registrar asistencia.');
				},
				complete: function() {
					boton.prop('disabled', false);
				}
			});
		});
		
	</script>

	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>
    
  </body>
</html>








