<?php
	use app\controllers\equipoController;
	$insEquipo = new equipoController();
	
	$equipo_torneoid 	= ($url[1] != "") ? $url[1] : 0;
	$equipo_id 		 	= ($url[2] != "") ? $url[2] : 0;
	$modulo_equipo		= '';
	$equipo_nombre 		= '';
	$equipo_categoria	= '';	
	
	$foto = APP_URL.'app/views/imagenes/fotos/equipos/equipo_default.jpg';

	if($equipo_torneoid != 0){
		$nombreTorneo=$insEquipo->BuscarTorneoEquipo($equipo_torneoid);		
		if($nombreTorneo->rowCount()==1){
			$nombreTorneo	=	$nombreTorneo->fetch();				
			$torneo_nombre	= 	$nombreTorneo['torneo_nombre'];		
		}
	}else{
		$torneo_nombre = '';
	}

	if($equipo_id != 0){
		$datosEquipo=$insEquipo->BuscarEquipo($equipo_id);		
		if($datosEquipo->rowCount()==1){
			$datosEquipo=$datosEquipo->fetch(); 
			if ($datosEquipo['equipo_foto']!=""){
				$foto = APP_URL.'app/views/imagenes/fotos/equipos/'.$datosEquipo['equipo_foto'];
			}else{
				$foto = APP_URL.'app/views/imagenes/fotos/equipos/equipo_default.jpg';
			}
			$modulo_equipo 	= 'actualizar';
			$equipo_profesorid	= $datosEquipo['equipo_profesorid'];
			$equipo_nombre		= $datosEquipo['equipo_nombre'];
			$equipo_categoria	= $datosEquipo['equipo_categoria'];
			$equipo_sede		= $datosEquipo['sede_nombre'];
			$equipo_estado		= $datosEquipo['ESTADO'];			
		}
	}else{
		$modulo_equipo 		= 'registrar';
		$equipo_profesorid	= '';
		$equipo_nombre 		= '';
		$equipo_categoria	= '';
		$equipo_sedeid		= '';
		$equipo_estado 		= 'A';
	}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?>| Equipos</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
		<!-- daterange picker -->
		<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/daterangepicker/daterangepicker.css">
	<!-- iCheck for checkboxes and radio inputs -->
	 <!-- DataTables -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
	
	<!-- Theme style -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>
    <!-- fileinput -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.css">
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
					<h4 class="m-0">Equipos para el torneo <?php echo $torneo_nombre; ?></h4>
				</div><!-- /.col -->
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="#">Inicio</a></li>
						<li class="breadcrumb-item active"><a href="<?php echo APP_URL."dashboard/" ?>">Dashboard</a></li>
					</ol>
				</div><!-- /.col -->
			</div><!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content-header -->

		<!-- Main content -->
		<section class="content">
			<div class="container-fluid">
			<!-- Small boxes (Stat box) -->
				<div class="card card-default">
					<div class="card-header" style='height: 40px;'>
						<h4 class="card-title">Ingreso de nuevo equipo</h4>
						<div class="card-tools">
							<button type="button" style='font-size: 14px; height: 40px;' class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>

					<div class="card-body">
						<div class="row">
							<div class="col-md-12">	
								<form class="FormularioAjax" id="quickForm" action="<?php echo APP_URL; ?>app/ajax/equipoAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
									<input type="hidden" name="modulo_equipo" value="<?php echo $modulo_equipo; ?>">
									<input type="hidden" name="equipo_torneoid" value="<?php echo $equipo_torneoid; ?>">
									<input type="hidden" name="equipo_id" value="<?php echo $equipo_id; ?>">
									<div class="row" style="font-size: 13px; height: 187px;">
										<div class="col-md-2">
											<div class="form-group">
												<label for="equipo_foto">Foto</label>		
												<div class="input-group">											
													<div class="fileinput fileinput-new" data-provides="fileinput">
														<div class="fileinput-new thumbnail" style="width: 110px; height: 130px;" data-trigger="fileinput"><img src="<?php echo $foto; ?>"> </div>
														<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 116px; max-height: 144px"></div>
														<div>
															<span class="bton bton-white bton-file" style="font-size: 13px;">
																<span class="fileinput-new">Seleccionar Foto</span>
																<span class="fileinput-exists">Cambiar</span>
																<input type="file" name="equipo_foto" id="foto" accept="image/*">
															</span>
															<a href="#" class="bton bton-orange fileinput-exists" data-dismiss="fileinput">Remover</a>
														</div>
													</div>
												</div>		
											</div>
										<!-- /.form-group -->								
										</div>
										<div class="col-sm-10">
											<div class="row">
												<div class="col-md-2">
													<div class="form-group">
														<label for="equipo_sedeid">Sede</label>
														<select class="form-control select2" id="equipo_sedeid" name="equipo_sedeid">									
															<?php
																if($equipo_sedeid == 0){	
																	echo "<option value='0' selected='selected'>- Seleccionar sede -</option>";
																}else{
																	echo "<option value='0'>- Seleccionar sede -</option>";	
																}
															?>
															<?php echo $insEquipo->listarOptionSede($equipo_sedeid); ?>
														</select>	
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="equipo_nombre">Nombre equipo</label>
														<input type="text"  class="form-control select2" id="equipo_nombre" name="equipo_nombre" value="<?php echo $equipo_nombre; ?>">
													</div> 
												</div>
												<div class="col-md-3">										
													<div class="form-group">
														<label for="equipo_categoria">Categoría</label>
														<input type="text" class="form-control" id="equipo_categoria" name="equipo_categoria" value="<?php echo $equipo_categoria; ?>">
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="equipo_profesorid">Profesor a cargo</label>
														<select class="form-control select2" id="equipo_profesorid" name="equipo_profesorid">									
															<?php
																if($equipo_profesorid == 0){	
																	echo "<option value='0' selected='selected'>Seleccionar un profesor</option>";
																}else{
																	echo "<option value='0'>- Seleccionar un profesor -</option>";	
																}
															?>
															<?php echo $insEquipo->listarResponsable($equipo_profesorid); ?>
														</select>	
													</div>
												</div> 
												<div class="col-md-12">						
													<button type="submit" class="btn btn-success btn-xs">Guardar</button>
													<a href="<?php echo APP_URL.'equipoList/'.$equipo_torneoid.'/'; ?>" class="btn btn-info btn-xs">Cancelar</a>
													<button type="reset" class="btn btn-dark btn-xs">Limpiar</button>						
												</div>	
											</div>
										</div>
									</div>									
								</form>		
								
								<div class="tab-custom-content">
									<h4 class="card-title">Equipos ingresados para el torneo <?php echo $torneo_nombre; ?></h4>
								</div>										
								<div class="tab-content" id="custom-content-above-tabContent" style="font-size: 13px;">	
									<table id="example1" class="table table-bordered table-striped table-sm" style="font-size: 13px;">
										<thead>
											<tr>
												<th>Sede</th>
												<th>Torneo</th>
												<th>Equipo</th>
												<th>Categoría</th>
												<th>Profesor a cargo</th>
												<th>Estado</th>
												<th>Jugadores</th>
												<th style="width: 255px;">Operaciones</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												echo $insEquipo->listarEquiposTorneo($equipo_torneoid); 
											?>							
										</tbody>	
									</table>
								</div>
							</div>	
						</div>
					</div>
					<div class="card-footer">						
						<?php include "./app/views/inc/btn_back.php";?>					
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
	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>
    	
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
        	}
			}).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');			    
		});
	</script>

	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>
    
  </body>
</html>








