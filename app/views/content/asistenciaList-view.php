<?php
	use app\controllers\asistenciaController;
	$insVerAsistencia = new asistenciaController();

	if(isset($_POST['alumno_sedeid'])){
		$alumno_sedeid = $insVerAsistencia->limpiarCadena($_POST['alumno_sedeid']);
	} ELSE{
		$alumno_sedeid = "";
	}

	if(isset($_POST['alumno_identificacion'])){
		$alumno_identificacion = $insVerAsistencia->limpiarCadena($_POST['alumno_identificacion']);
	} ELSE{
		$alumno_identificacion = "";
	}

	if(isset($_POST['alumno_primernombre'])){
		$alumno_primernombre = $insVerAsistencia->limpiarCadena($_POST['alumno_primernombre']);
	} ELSE{
		$alumno_primernombre = "";
	}

	if(isset($_POST['alumno_apellidopaterno'])){
		$alumno_apellidopaterno = $insVerAsistencia->limpiarCadena($_POST['alumno_apellidopaterno']);
	} ELSE{
		$alumno_apellidopaterno = "";
	}
	
	// Agregar estas variables al inicio
	if(isset($_POST['asistencia_anio'])){
		$asistencia_anio = $insVerAsistencia->limpiarCadena($_POST['asistencia_anio']);
	} ELSE{
		$asistencia_anio = "";
	}

	if(isset($_POST['asistencia_mes'])){
		$asistencia_mes = $insVerAsistencia->limpiarCadena($_POST['asistencia_mes']);
	} ELSE{
		$asistencia_mes = "";
	}
		
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Asistencias</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/1104523691001_2.png">
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
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css?v=1.0">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/alt/estilos.css">
    
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
				<h5 class="m-0">Consulta de asistencias</h5>
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
			<form action="<?php echo APP_URL."asistenciaList/" ?>" method="POST" autocomplete="off" enctype="multipart/form-data" >
			
			<div class="container-fluid">
				<div class="card card-default">
					<div class="card-header">
					<h3 class="card-title">Alumnos</h3>
					<div class="card-tools">
						<button type="button" class="btn btn-tool" data-card-widget="collapse">
						<i class="fas fa-minus"></i>
						</button>
					</div>
					</div>  

					<!-- card-body -->                
					<div class="card-body">
						<div class="row align-items-end">
							<div class="col-sm-2">
								<div class="form-group input-group-sm">
									<label for="alumno_identificacion">Identificación</label>                        
									<input type="text" class="form-control" id="alumno_identificacion" name="alumno_identificacion" placeholder="Identificación" value="<?php echo $alumno_identificacion; ?>">
								</div>        
							</div>
							<div class="col-sm-2">
								<div class="form-group input-group-sm">
									<label for="alumno_apellidopaterno">Apellido paterno</label>
									<input type="text" class="form-control" id="alumno_apellidopaterno" name="alumno_apellidopaterno" placeholder="Primer apellido" value="<?php echo $alumno_apellidopaterno; ?>">
								</div>         
							</div>
							<div class="col-md-2">
								<div class="form-group input-group-sm">
									<label for="alumno_primernombre">Primer nombre</label>
									<input type="text" class="form-control" id="alumno_primernombre" name="alumno_primernombre" placeholder="Primer nombre" value="<?php echo $alumno_primernombre; ?>">
								</div>
							</div>  
							<!-- Selector de Año -->
							<div class="col-md-2">
								<div class="form-group input-group-sm">
									<label for="asistencia_anio">Año</label>
									<select class="form-control select2" id="asistencia_anio" name="asistencia_anio">
										<option value="">Todos</option>
										<option value="2024" <?php echo ($asistencia_anio == "2024") ? "selected" : ""; ?>>2024</option>
										<option value="2025" <?php echo ($asistencia_anio == "2025") ? "selected" : ""; ?>>2025</option>
										<option value="2026" <?php echo ($asistencia_anio == "2026") ? "selected" : ""; ?>>2026</option>
									</select>
								</div>
							</div>

							<!-- Selector de Mes -->
							<div class="col-md-2">
								<div class="form-group input-group-sm">
									<label for="asistencia_mes">Mes</label>
									<select class="form-control select2" id="asistencia_mes" name="asistencia_mes">
										<option value="">Todos</option>
										<option value="01" <?php echo ($asistencia_mes == "01") ? "selected" : ""; ?>>Enero</option>
										<option value="02" <?php echo ($asistencia_mes == "02") ? "selected" : ""; ?>>Febrero</option>
										<option value="03" <?php echo ($asistencia_mes == "03") ? "selected" : ""; ?>>Marzo</option>
										<option value="04" <?php echo ($asistencia_mes == "04") ? "selected" : ""; ?>>Abril</option>
										<option value="05" <?php echo ($asistencia_mes == "05") ? "selected" : ""; ?>>Mayo</option>
										<option value="06" <?php echo ($asistencia_mes == "06") ? "selected" : ""; ?>>Junio</option>
										<option value="07" <?php echo ($asistencia_mes == "07") ? "selected" : ""; ?>>Julio</option>
										<option value="08" <?php echo ($asistencia_mes == "08") ? "selected" : ""; ?>>Agosto</option>
										<option value="09" <?php echo ($asistencia_mes == "09") ? "selected" : ""; ?>>Septiembre</option>
										<option value="10" <?php echo ($asistencia_mes == "10") ? "selected" : ""; ?>>Octubre</option>
										<option value="11" <?php echo ($asistencia_mes == "11") ? "selected" : ""; ?>>Noviembre</option>
										<option value="12" <?php echo ($asistencia_mes == "12") ? "selected" : ""; ?>>Diciembre</option>
									</select>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group input-group-sm">
									<label for="alumno_sedeid">Sede</label>
									<select class="form-control select2" id="alumno_sedeid" name="alumno_sedeid">		
										<?php
											if($rolid == 1 || $rolid == 2){
												if($alumno_sedeid == 0){	
													echo "<option value='0' selected='selected'>Todas</option>";
												}else{
													echo "<option value='0'>Todas</option>";	
												}
											}
										?>																		
										<?php echo $insVerAsistencia->listarSede($alumno_sedeid, $_SESSION['rol'], $_SESSION['usuario']); ?>
									</select>	
								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group input-group-sm">									
									<button type="submit" class="form-control btn btn-sm bg-lightblue">Buscar</button>
								</div>
							</div>

						</div>
					
					</div>
				</div>
            </div>  
			</form>

			<div class="container-fluid">
			<!-- Small boxes (Stat box) -->
				<div class="card card-default">
					<div class="card-header">
						<h3 class="card-title">Resultado de la búsqueda</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>

					<div class="card-body">
						<table id="example1" class="table table-bordered table-striped table-sm">
							<thead>
								<tr>
									<th>Nombres y Apellidos Jugador/a</th>
									<th>Edad</th>
									<th>1</th>
									<th>2</th>
									<th>3</th>
									<th>4</th>
									<th>5</th>
									<th>6</th>
									<th>7</th>
									<th>8</th>
									<th>9</th>
									<th>10</th>
									<th>11</th>
									<th>12</th>
									<th>13</th>
									<th>14</th>
									<th>15</th>
									<th>16</th>
									<th>17</th>
									<th>18</th>
									<th>19</th>
									<th>20</th>
									<th>21</th>
									<th>22</th>
									<th>23</th>
									<th>24</th>
									<th>25</th>
									<th>26</th>
									<th>27</th>
									<th>28</th>
									<th>29</th>
									<th>30</th>
									<th>31</th>
									<th>% Asistencia</th>
								</tr>
							</thead>
							<!-- Cambiar la llamada en el tbody -->
							<tbody>
								<?php 
									echo $insVerAsistencia->listarAsistenciaAlumnos(
										$alumno_identificacion,
										$alumno_apellidopaterno, 
										$alumno_primernombre, 
										$asistencia_anio,
										$asistencia_mes,
										$alumno_sedeid
									); 
								?>								
							</tbody>
						</table>	
					</div>
				</div>
			<!-- /.row -->
			</div><!-- /.container-fluid -->

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

	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>
  </body>
</html>








