<?php
	use app\controllers\escuelaController;
	$insSede = new escuelaController();	
	
	$sedeid = ($url[1] != "") ? $url[1] : 0;	

	$foto = APP_URL.'app/views/dist/img/Logos/LogoCDJG.png';
	
	if($sedeid != 0){
		$datosSede=$insSede->verSedeControlador($sedeid);		
		if($datosSede->rowCount()==1){
			$datosSede=$datosSede->fetch(); 
			if ($datosSede['sede_foto']!=""){
				$foto = APP_URL.'app/views/imagenes/fotos/sedes/'.$datosSede['sede_foto'];
			}else{
				$foto = APP_URL.'app/views/dist/img/foto.jpg';
			}
			$modulo_sede 		= 'actualizar';
			$sede_nombre		= $datosSede['sede_nombre'];
			$sede_direccion		= $datosSede['sede_direccion'];
			$sede_email 		= $datosSede['sede_email'];
			$sede_telefono		= $datosSede['sede_telefono'];
			$sede_inscripcion	= $datosSede['sede_inscripcion'];
			$sede_pension		= $datosSede['sede_pension'];
			$sede_foto			= $datosSede['sede_foto'];
		}
	}else{
		$modulo_sede 		= 'registrar';
		$sede_nombre 		= '';
		$sede_direccion		= '';
		$sede_email 		= '';		
		$sede_telefono 		= '';
		$sede_inscripcion	= '';
		$sede_pension		= '';
		$sede_foto 			= '';
	}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?>| Sedes</title>
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

	<style>
		input:invalid {
		  box-shadow: 0 0 2px 1px red;
		}
		input:focus:invalid {
		  box-shadow: none;
		}
		textarea:invalid {
		  box-shadow: 0 0 2px 1px red;
		}
		textarea:focus:invalid {
		  box-shadow: none;
		}
	</style>	
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
					<h4 class="m-0">Sedes</h4>
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
						<h4 class="card-title">Ingreso de nueva sede</h4>
						<div class="card-tools">							
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-12">	
								<form class="FormularioAjax" id="quickForm" action="<?php echo APP_URL; ?>app/ajax/escuelaAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
									<input type="hidden" name="modulo_sede" value="<?php echo $modulo_sede; ?>">
									<input type="hidden" name="sede_id" value="<?php echo $sedeid; ?>">
									<div class="row" style="font-size: 13px; height: 187px;">
										<div class="col-md-2">
											<div class="form-group">
												<label for="sede_foto">Foto</label>		
												<div class="input-group">											
													<div class="fileinput fileinput-new" data-provides="fileinput">
														<div class="fileinput-new thumbnail" style="width: 116px; height: 144px;" data-trigger="fileinput"><img src="<?php echo $foto; ?>"> </div>
														<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 130px"></div>
														<div>
															<span class="bton bton-white bton-file" style="font-size: 13px;">
																<span class="fileinput-new">Seleccionar Foto</span>
																<span class="fileinput-exists">Cambiar</span>
																<input type="file" name="sede_foto" id="foto" accept="image/*">
															</span>
															<a href="#" class="bton bton-orange fileinput-exists" data-dismiss="fileinput">Remover</a>
														</div>
													</div>
												</div>		
											</div>
										<!-- /.form-group -->								
										</div>
										<div class="col-sm-10">
											<div class="row" style="font-size: 13px;">
												<div class="col-md-3">
													<div class="form-group">
														<label for="sede_nombre">Nombre sede</label>
														<input type="text" class="form-control select2" id="sede_nombre" name="sede_nombre" value="<?php echo $sede_nombre; ?>" required>
													</div> 
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="sede_email">Correo</label>
														<input type="text" class="form-control" id="sede_email" name="sede_email" value="<?php echo $sede_email; ?>" required>
													</div> 
												</div>
												<div class="col-md-2">
													<div class="form-group">
														<label for="sede_telefono">Celular</label>
														<input type="text" class="form-control" id="sede_telefono" name="sede_telefono" data-inputmask='"mask": "0999999999"' data-mask value="<?php echo $sede_telefono; ?>" required>
													</div> 
												</div>
												<div class="col-md-2">
													<div class="form-group">
														<label for="sede_inscripcion">Valor inscripción</label>
														<input type="text" class="form-control" id="sede_inscripcion" name="sede_inscripcion" value="<?php echo $sede_inscripcion; ?>">	
													</div>
												</div>
												<div class="col-md-2">
													<div class="form-group">
														<label for="sede_pension">Valor pensión</label>
														<input type="text" class="form-control" id="sede_pension" name="sede_pension" value="<?php echo $sede_pension; ?>">	
													</div>
												</div>
												<div class="col-md-6">										
													<div class="form-group">
														<label for="sede_direccion">Dirección</label>
														<input type="text" class="form-control" id="sede_direccion" name="sede_direccion" value="<?php echo $sede_direccion; ?>" required>
													</div>
												</div>											
												<div class="col-md-12">						
													<button type="submit" class="btn btn-success btn-xs">Guardar</button>
													<a href="<?php echo APP_URL; ?>sedeList/" class="btn btn-info btn-xs">Cancelar</a>
													<button type="reset" class="btn btn-dark btn-xs">Limpiar</button>						
												</div>	
											</div>
										</div>
									</div>									
								</form>									
								<div class="tab-custom-content">
									<h4 class="card-title">Sedes ingresadas</h4>
								</div>										
								<div class="tab-content" id="custom-content-above-tabContent" style="font-size: 13px;">	
									<table id="example1" class="table table-bordered table-striped table-sm" style="font-size: 13px;">
										<thead>
											<tr>
												<th style="width: 100px;">Nombre</th>
												<th>Dirección</th>
												<th>Correo</th>
												<th style="width: 60px;">Celular</th>
												<th style="width: 60px;">V.Inscripcion</th>
												<th style="width: 60px;">V.Pensión</th>
												<th style="width: 200px;">Opciones</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												echo $insSede->listarSedes(); 
											?>							
										</tbody>	
									</table>
								</div>
							</div>	
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
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>
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
			},
			}).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');			    
		});
	</script>    
  </body>
</html>








