<?php
	use app\controllers\carnetController;
	$insCarnet = new carnetController();
?>

<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Carnets</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
	<!-- DataTables -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/select2/css/select2.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">
	<!-- SweetAlert2 -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">
	
  </head>
  
  <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
      <!-- Navbar -->
      <?php require_once "app/views/inc/navbar.php"; ?>
      
      <!-- Main Sidebar Container -->
      <?php require_once "app/views/inc/main-sidebar.php"; ?>
      
      <!-- Content Wrapper -->
      <div class="content-wrapper">
		<!-- Content Header -->
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h3 class="m-0">Carnets del Mes</h3>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>dashboard/">Inicio</a></li>
							<li class="breadcrumb-item active">Carnets</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<!-- Main content -->
		<section class="content">
			<div class="container-fluid">
				<!-- Card principal -->
				<div class="card card-default">
					<div class="card-header">
						<h3 class="card-title">
							<i class="fas fa-id-card"></i> 
							Alumnos con pago de pensión - <?php $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'America/Guayaquil', IntlDateFormatter::GREGORIAN, 'MMMM yyyy');
        													echo ucfirst($formatter->format(new DateTime()));?>
						</h3>
						<div class="card-tools">							
							<!-- Botón imprimir todos con confirmación -->
							<button type="button" 
									id="btnImprimirTodos" 
									class="btn btn-success btn-sm" 
									style="margin-right: 10px;">

								<i class="fas fa-print"></i> Imprimir Todos
								<span class="badge badge-light" id="contadorCarnets">
									<i class="fas fa-spinner fa-spin"></i>
								</span>
							</button>

							<button type="button" 
									id="btn-reimprimir-carnets"
									class="btn btn-warning btn-sm">
								<i class="fas fa-redo"></i> Reimprimir Carnets
							</button>

							<span id="contador-seleccion" 
								class="badge badge-warning" 
								style="display: none; font-size: 14px; padding: 8px 12px;">
								0 carnets seleccionados
							</span>

							<button type="button" 
									id="btn-limpiar-seleccion"
									class="btn btn-secondary btn-sm" 
									style="display: none;">
								<i class="fas fa-times"></i> Limpiar Selección
							</button>
							
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					
					<div class="card-body">						
						<div class="alert alert-info">
							<i class="fas fa-info-circle"></i>
							<strong>Información:</strong> 
							Los carnets se generan automáticamente para todos los alumnos con pago de pensión del mes actual.
							Use los checkboxes para reimprimir carnets extraviados (se cobrará $1.00 por reimpresión).
						</div>
						
						<form id="formReimpresion" class="FormularioAjax" data-form="save">
							<table id="example1" class="table table-bordered table-striped table-sm">
								<thead>
									<tr>
										<th>Identificación</th>
										<th>Nombres</th>
										<th>Apellidos</th>	
										<th>Carnet</th>
										<th>Fecha Últ Pensión</th>
										<th>Condición</th>
										<th>Ver Carnet</th>
										<th style="text-align: center;">
											<div class="custom-control custom-checkbox">
												<input class="custom-control-input" 
													   type="checkbox" 
													   id="seleccionarTodos">
												<label for="seleccionarTodos" class="custom-control-label">
													Reimprimir
												</label>
											</div>
										</th>
									</tr>
								</thead>
								<tbody>
									<?php echo $insCarnet->listarAlumnos(); ?>								
								</tbody>
							</table>
						</form>
					</div>
				</div>
			</div>
		</section>
      </div>

      <?php require_once "app/views/inc/footer.php"; ?>

      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <!-- jQuery -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- DataTables -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables/jquery.dataTables.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
	<!-- AdminLTE App -->
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/carnet_seleccion.js?v=1.0.1"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/carnet_list.js?v=1.0.5"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js"></script>
	<script>
		var APP_URL = '<?php echo APP_URL; ?>';
	</script>
  </body>
</html>