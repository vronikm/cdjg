<?php
	use app\controllers\asistenciaController;
	$insHorario = new asistenciaController();	

	if(isset($_POST['horario_sedeid'])){
		$horario_sedeid = $insHorario->limpiarCadena($_POST['horario_sedeid']);
	} ELSE{
		$horario_sedeid = 0;
	}

	if(isset($_POST['horario_nombre'])){
		$horario_nombre = $insHorario->limpiarCadena($_POST['horario_nombre']);
	} ELSE{
		$horario_nombre = "";
	}

	if(isset($_POST['horario_detalle'])){
		$horario_detalle = $insHorario->limpiarCadena($_POST['horario_detalle']);
	} ELSE{
		$horario_detalle = "";
	}		
?>


<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?>| Horarios</title>
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
					<h4 class="m-0">Horarios</h4>
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

		<!-- Section listado de horarios -->
		<section class="content">			
			<div class="container-fluid">
				<div class="card card-default" style='height: 140px;'>
					<div class="card-header" style='height: 40px;'>
						<h3 class="card-title">Búsqueda de horarios</h3>
						<div class="card-tools">
							<?php
								if($horario_sedeid != 0){
									echo '										
										<form action="'.APP_URL.'asistenciaHorario/"  method="POST" autocomplete="off">								
											<input type="hidden" name="horario_sedeid" value="'.$horario_sedeid.'">						
											<button type="submit" class="btn float-right btn-ver btn-xs" >Nuevo</button>
										</form>	
									';
								}
							?>						
						</div>	
					</div>  

					<form action="<?php echo APP_URL."asistenciaListHorario/" ?>" method="POST" autocomplete="off" enctype="multipart/form-data" >
					<!-- card-body -->                
						<div class="card-body">
							<div class="row align-items-end">
								<div class="col-sm-3">
									<div class="form-group input-group-sm">
										<label for="horario_nombre">Horario nombre</label>                        
										<input type="text" class="form-control" style='font-size: 13px; height: 31px;' id="horario_nombre" name="horario_nombre" placeholder="Nombre" value="<?php echo $horario_nombre; ?>">
									</div>        
								</div>
								<div class="col-sm-3">
									<div class="form-group input-group-sm">
										<label for="horario_detalle">Horario detalle</label>
										<input type="text" class="form-control" style='font-size: 13px; height: 31px;' id="horario_detalle" name="horario_detalle" placeholder="Detalle" value="<?php echo $horario_detalle; ?>">
									</div>         
								</div>											
								<div class="col-md-3">
									<div class="form-group input-group-sm">
										<label for="horario_sedeid">Sede</label>
										<select class="form-control select2" style='font-size: 13px; height: 31px;' id="horario_sedeid" name="horario_sedeid">
											<?php
												if($horario_sedeid == 0){	
													echo "<option value='0' selected='selected'>Seleccionar sede</option>";
												}else{
													echo "<option value='0'>Seleccionar sede</option>";	
												}
											?>																		
											<?php echo $insHorario->listarOptionSedebusqueda($horario_sedeid); ?>
										</select>	
									</div>
								</div>

								<div class="col-md-3">
									<div class="form-group input-group-sm">
										<button type="submit" class="form-control btn btn-sm btn-info"><i class="fas fa-search"></i>Buscar</button>
									</div>
								</div>
							</div>						
						</div>
					</form>
				</div>
            </div> 
			<div class="container-fluid">
			<!-- Small boxes (Stat box) -->
				<div class="card card-default">
					<div class="card-header" style='height: 40px;'>
						<h3 class="card-title">Resultado de la búsqueda</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>

					<div class="card-body">
						<table id="example1" class="table table-bordered table-striped table-sm" style="font-size: 14px;">
							<thead>
								<tr>
									<th>Horario</th>
									<th>Detalle</th>
									<th>Estado</th>																
									<th>Alumnos</th>
									<th>Opciones horario</th>
									<th>Operaciones</th>
								</tr>
							</thead>
							<tbody>
								<?php 
									if($horario_nombre !='' || $horario_detalle !='' || $horario_sedeid != 0){
										echo $insHorario->listarHorarios($horario_nombre, $horario_detalle, $horario_sedeid); 
									}
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
    
  </body>
</html>