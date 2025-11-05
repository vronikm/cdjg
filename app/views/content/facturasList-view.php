<?php
	use app\controllers\facturasController;
	$insFactura = new facturasController();

	if(isset($_POST['alumno_sedeid'])){
		$alumno_sedeid = $insFactura->limpiarCadena($_POST['alumno_sedeid']);
	} ELSE{
		$alumno_sedeid = "";
	}

	if(isset($_POST['alumno_identificacion'])){
		$alumno_identificacion = $insFactura->limpiarCadena($_POST['alumno_identificacion']);
	} ELSE{
		$alumno_identificacion = "";
	}

	if(isset($_POST['alumno_nombre1'])){
		$alumno_primernombre = $insFactura->limpiarCadena($_POST['alumno_nombre1']);
	} ELSE{
		$alumno_primernombre = "";
	}

	if(isset($_POST['alumno_apellido1'])){
		$alumno_apellidopaterno = $insFactura->limpiarCadena($_POST['alumno_apellido1']);
	} ELSE{
		$alumno_apellidopaterno = "";
	}
	
	if(isset($_POST['alumno_ano'])){
		$alumno_anio = $insFactura->limpiarCadena($_POST['alumno_ano']);
	} ELSE{
		$alumno_anio = "";
	}
		
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Facturas</title>
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
				<h5 class="m-0">Registro de facturas</h5>
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
			<form action="<?php echo APP_URL."facturasList/" ?>" method="POST" autocomplete="off" enctype="multipart/form-data" >
			
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
									<label for="alumno_apellido1">Apellido paterno</label>
									<input type="text" class="form-control" id="alumno_apellido1" name="alumno_apellido1" placeholder="Primer apellido" value="<?php echo $alumno_apellidopaterno; ?>">
								</div>         
							</div>
							<div class="col-md-2">
								<div class="form-group input-group-sm">
									<label for="alumno_nombre1">Primer nombre</label>
									<input type="text" class="form-control" id="alumno_nombre1" name="alumno_nombre1" placeholder="Primer nombre" value="<?php echo $alumno_primernombre; ?>">
								</div>
							</div>  

							<div class="col-md-2">
								<div class="form-group">
									<div class="form-group input-group-sm">
										<label for="alumno_ano">Año</label>
										<input type="text" class="form-control" id="alumno_ano" name="alumno_ano" placeholder="año" value="<?php echo $alumno_anio; ?>">
									</div>	
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
										<?php echo $insFactura->listarSedeFacturas($alumno_sedeid, $_SESSION['rol'], $_SESSION['usuario']); ?>
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
									<th>Identificación</th>
									<th>Nombres</th>
									<th>Apellidos</th>
									<th>Año</th>									
									<th>Opciones</th>
								</tr>
							</thead>
							<tbody>
								<?php 
									echo $insFactura->listarAlumnosFacturas($alumno_identificacion,$alumno_apellidopaterno, $alumno_primernombre, $alumno_anio, $alumno_sedeid); 
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








