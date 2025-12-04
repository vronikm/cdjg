<?php
	use app\controllers\feriadosController;
	$insFeriado = new feriadosController();

	// Variables de búsqueda
	if(isset($_POST['feriado_anio'])){
		$feriado_anio = $insFeriado->limpiarCadena($_POST['feriado_anio']);
	} ELSE{
		$feriado_anio = date('Y');
	}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Gestión de Feriados</title>
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
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css?v=1.0">
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
				<h5 class="m-0">Gestión de Feriados</h5>
				</div><!-- /.col -->
				<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>dashboard/">Inicio</a></li>
					<li class="breadcrumb-item active">Feriados</li>
				</ol>
				</div><!-- /.col -->
			</div><!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content-header -->

		<!-- Section -->
		<section class="content">
			
			<!-- Botón para agregar nuevo feriado -->
			<div class="container-fluid mb-3">
				<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNuevoFeriado">
					<i class="fas fa-plus"></i> Nuevo Feriado
				</button>
			</div>

			<!-- Filtro de búsqueda -->
			<form action="<?php echo APP_URL."feriadosList/" ?>" method="POST" autocomplete="off">
			<div class="container-fluid">
				<div class="card card-default">
					<div class="card-header">
					<h3 class="card-title">Filtros de búsqueda</h3>
					<div class="card-tools">
						<button type="button" class="btn btn-tool" data-card-widget="collapse">
						<i class="fas fa-minus"></i>
						</button>
					</div>
					</div>  

					<div class="card-body">
						<div class="row align-items-end">
							<div class="col-md-3">
								<div class="form-group input-group-sm">
									<label for="feriado_anio">Año</label>
									<select class="form-control" id="feriado_anio" name="feriado_anio">
										<?php
											$anio_actual = date('Y');
											for($i = $anio_actual - 2; $i <= $anio_actual + 5; $i++){
												$selected = ($i == $feriado_anio) ? "selected" : "";
												echo "<option value='$i' $selected>$i</option>";
											}
										?>
									</select>	
								</div>
							</div>

							<div class="col-md-2">
								<div class="form-group input-group-sm">									
									<button type="submit" class="form-control btn btn-sm bg-lightblue">
										<i class="fas fa-search"></i> Buscar
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
            </div>  
			</form>

			<!-- Tabla de resultados -->
			<div class="container-fluid">
				<div class="card card-default">
					<div class="card-header">
						<h3 class="card-title">Feriados registrados - Año <?php echo $feriado_anio; ?></h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>

					<div class="card-body">
						<table id="tablaFeriados" class="table table-bordered table-striped table-sm">
							<thead>
								<tr>
									<th>ID</th>
									<th>Fecha</th>
									<th>Día</th>
									<th>Descripción</th>
									<th>Estado</th>
									<th>Acciones</th>
								</tr>
							</thead>
							<tbody>
								<?php 
									echo $insFeriado->listarFeriados($feriado_anio); 
								?>								
							</tbody>
						</table>	
					</div>
				</div>
			</div>

		</section>
		<!-- /.section -->
      
      </div>
      <!-- /.vista -->

      <!-- Modal Nuevo Feriado -->
	  <div class="modal fade" id="modalNuevoFeriado" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
			<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/feriadosAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="modulo_feriado" value="registrar">
				
				<div class="modal-header bg-primary">
					<h5 class="modal-title">Registrar Nuevo Feriado</h5>
					<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="feriado_fecha">Fecha <span class="text-danger">*</span></label>
						<input type="date" class="form-control" id="feriado_fecha" name="feriado_fecha" required>
					</div>
					<div class="form-group">
						<label for="feriado_descripcion">Descripción <span class="text-danger">*</span></label>
						<input type="text" class="form-control" id="feriado_descripcion" name="feriado_descripcion" placeholder="Ej: Año Nuevo" required maxlength="255">
					</div>
					<div class="form-group">
						<label for="feriado_activo">Estado</label>
						<select class="form-control" id="feriado_activo" name="feriado_activo">
							<option value="1" selected>Activo</option>
							<option value="0">Inactivo</option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-primary">Guardar</button>
				</div>
			</form>
			</div>
		</div>
	  </div>

	  <!-- Modal Editar Feriado -->
	  <div class="modal fade" id="modalEditarFeriado" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
			<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/feriadosAjax.php" method="POST" autocomplete="off">
				<input type="hidden" name="modulo_feriado" value="actualizar">
				<input type="hidden" id="edit_feriado_id" name="feriado_id">
				
				<div class="modal-header bg-warning">
					<h5 class="modal-title">Editar Feriado</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="edit_feriado_fecha">Fecha <span class="text-danger">*</span></label>
						<input type="date" class="form-control" id="edit_feriado_fecha" name="feriado_fecha" required>
					</div>
					<div class="form-group">
						<label for="edit_feriado_descripcion">Descripción <span class="text-danger">*</span></label>
						<input type="text" class="form-control" id="edit_feriado_descripcion" name="feriado_descripcion" required maxlength="255">
					</div>
					<div class="form-group">
						<label for="edit_feriado_activo">Estado</label>
						<select class="form-control" id="edit_feriado_activo" name="feriado_activo">
							<option value="1">Activo</option>
							<option value="0">Inactivo</option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-warning">Actualizar</button>
				</div>
			</form>
			</div>
		</div>
	  </div>

      <?php require_once "app/views/inc/footer.php"; ?>

      <aside class="control-sidebar control-sidebar-dark">
      </aside>
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
	
	<script src="<?php echo APP_URL; ?>app/views/dist/js/feriados.js?v=1.0.2"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>
  </body>
</html>