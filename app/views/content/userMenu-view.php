<?php
	use app\controllers\menuController;

	$insMenu = new menuController();

	$menuid = ($url[1] != "") ? $url[1] : 0;	

	if($menuid != 0){
		$datos=$insMenu->BuscarMenu($menuid);		
		if($datos->rowCount()==1){
			$datos=$datos->fetch(); 

			$modulo_usuario = 'actualizarMenu';
			$menu_nombre	= $datos['menu_nombre'];
			$menu_orden		= $datos['menu_orden'];
			$menu_padreid	= $datos['menu_padreid'];		
			$menu_hijo		= $datos['menu_hijo'];	
			$menu_vista		= $datos['menu_vista'];	
			$menu_icono		= $datos['menu_icono'];	
			$menu_estado	= $datos['menu_estado'];
		}
	}else{
		$modulo_usuario = 'crearMenu';
		$menu_nombre	= '';
		$menu_orden		= '';
		$menu_padreid	= '';		
		$menu_hijo		= 'S';	
		$menu_vista		= '';	
		$menu_icono		= '';		
		$menu_estado 	= 'A';
	}	
	
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Menu</title>
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
						<h4 class="m-0">Ingreso Menu</h4>
					</div><!-- /.col -->
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item">
								<a href="#">Nuevo Menu<i class="mdi mdi-roller-shade-closed:"></i></a>
							</li>
							<li class="breadcrumb-item active">Dashboard v1</li>
						</ol>
					</div><!-- /.col -->
				</div><!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content-header -->

		<!-- Main content -->
		<section class="content">
			<div class="container-fluid">
				<!-- Small boxes (Stat box) Nuevo menu-->
				<div class="card card-default">
					<div class="card-header">
						<h3 class="card-title">Nuevo menu</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>

					<div class="card-body">
						<form class="FormularioAjax" id="quickForm" action="<?php echo APP_URL; ?>app/ajax/menuAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
							<input type="hidden" name="modulo_menu" value="<?php echo $modulo_usuario; ?>">
							<input type="hidden" name="menu_id" value="<?php echo $menuid; ?>">											
									
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label for="menu_nombre">Nombre</label>
										<input type="text" class="form-control" id="menu_nombre" name="menu_nombre" value="<?php echo $menu_nombre; ?>">												
									</div>	
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="menu_vista">Vista</label>
										<input type="text" class="form-control" id="menu_vista" name="menu_vista" value="<?php echo $menu_vista; ?>">
									</div>
								</div>								
								<div class="col-md-4">
									<div class="form-group">
										<label for="menu_icono">Icono</label>
										<input type="text" class="form-control" id="menu_icono" name="menu_icono" value="<?php echo $menu_icono; ?>">
									</div>
								</div>								
							</div>	

							<div class="row">	
								<div class="col-md-3">
									<div class="form-group">
										<label for="menu_orden">Orden</label>
										<input type="text" class="form-control" id="menu_orden" name="menu_orden" value="<?php echo $menu_orden; ?>">
									</div>
								</div>												
								<div class="col-md-3">
									<div class="form-group">
										<label for="menu_idpadre">Id padre</label>
										<input type="text" class="form-control" id="menu_idpadre" name="menu_idpadre" value="<?php echo $menu_padreid; ?>">
									</div>
								</div>								
								<div class="col-md-3">
									<div class="form-group">
										<label for="menu_estado">Items</label>
										<select class="form-control" id="menu_hijo" name="menu_hijo">		
											<?php 
												if($menu_hijo == 'S'){
													echo '<option value="S" selected>Si</option>
														<option value="N" >No</option>';
												}else{
													echo '<option value="S" >Si</option>
														<option value="N" selected>No</option>';	
												}
											?>
										</select>	
									</div>
								</div>		
								<div class="col-md-3">
									<div class="form-group">
										<label for="menu_estado">Estado</label>
										<select class="form-control" id="menu_estado" name="menu_estado">		
											<?php 
												if($menu_estado == 'A'){
													echo '<option value="A" selected>Activo</option>
														<option value="I" >Inactivo</option>';
												}else{
													echo '<option value="A" >Activo</option>
														<option value="I" selected>Inactivo</option>';	
												}
											?>
										</select>	
									</div>
								</div>
										
								<div class="col-md-12">						
									<button type="submit" class="btn btn-success btn-sm">Guardar</button>
									<a href="<?php echo APP_URL; ?>userMenu/" class="btn btn-info btn-sm">Cancelar</a>
									<button type="reset" class="btn btn-dark btn-sm">Limpiar</button>						
								</div>
							</div>	
						</form>
					</div>
				</div>
				
				<!-- Small boxes (Stat box) Menu ingresado-->
				<div class="card card-default">
					<div class="card-header">
						<h3 class="card-title">Menu ingresados</h3>
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
									<th>ID</th>
									<th>Orden</th>
									<th>Padre</th>
									<th>Items</th>
									<th>Nombre</th>
									<th>Vista</th>
									<th>Icono</th>
									<th>Estado</th>
									<th>Opciones</th>
								</tr>
							</thead>
							<tbody>
								<?php 
									echo $insMenu->listarMenu(); 
								?>							
							</tbody>							
						</table>	
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








