<?php
	use app\controllers\reporteController;
	$insResultado = new reporteController();
	$sede_id 	  = ($url[1] != "") ? $url[1] : 0;

	if(isset($_POST['alumno_sedeid'])){
		$alumno_sedeid = $insResultado->limpiarCadena($_POST['alumno_sedeid']);
	} ELSE{
		$alumno_sedeid = 0;
	}

	if(isset($_POST['pago_fecha_inicio'])){
		$fecha_inicio = $insResultado->limpiarCadena($_POST['pago_fecha_inicio']);
	} ELSE{
		$fecha_inicio = $insResultado->fechaPagosResumen();
		$fecha_inicio = $fecha_inicio->fetch(); 
		$fecha_inicio = $fecha_inicio['FECHA_MAXIMA'];
	}

	if(isset($_POST['pago_fecha_fin'])){
		$fecha_fin = $insResultado->limpiarCadena($_POST['pago_fecha_fin']);
	} ELSE{
		$fecha_fin = $insResultado->fechaPagosResumen();
		$fecha_fin = $fecha_fin->fetch(); 
		$fecha_fin = $fecha_fin['FECHA_MAXIMA'];
	}	
?>

<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>CDJG| Reporte de resultados</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.svg">
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
				<h1 class="m-0">Resultado mensual </h1>
				</div><!-- /.col -->
				<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Nuevo</a></li>
					<li class="breadcrumb-item active">Dashboard</li>
				</ol>
				</div><!-- /.col -->
			</div><!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content-header -->

		<!-- Section listado de alumnos -->
		<section class="content">
			<form action="<?php echo APP_URL."balanceResultados/" ?>" method="POST" autocomplete="off" enctype="multipart/form-data" >			
			<div class="container-fluid">
				<div class="card card-default">
					<div class="card-header">
						<h3 class="card-title">Criterios de cálculo</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
							<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>  
					<!-- card-body -->                
					<div class="card-body">
						<div class="row">
							<div class="col-md-3">
								<div class="form-group campo">
									<label for="pago_fecha">Fecha inicio</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
										</div>
										<input type="date" class="form-control" id="pago_fecha_inicio" name="pago_fecha_inicio" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" value=<?php echo $fecha_inicio;?> data-mask required>										
									</div>
									<!-- /.input group -->
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group campo">
									<label for="pago_fecha">Fecha fin</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
										</div>
										<input type="date" class="form-control" id="pago_fecha_fin" name="pago_fecha_fin" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" value=<?php echo $fecha_fin;?> data-mask required>										
									</div>
									<!-- /.input group -->
								</div>
							</div>	
							<div class="col-md-3">
								<div class="form-group">
									<label for="alumno_sedeid">Sede</label>
									<select class="form-control select2" id="alumno_sedeid" name="alumno_sedeid" required>																									
										<?php echo $insResultado->listarSedebusqueda($alumno_sedeid); ?>
									</select>	
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label for="alumno_sedeid">.</label>
									<button type="submit" class="form-control btn btn-info">Buscar</button>
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
						<h3 class="card-title" style="text-align: right">Balance</h3>
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
									<th>SEDE</th>
									<th>RUBRO</th>					
									<th>CANTIDAD</th>
									<th>MONTO INGRESO</th>
									<th>MONTO EGRESOS</th>
								</tr>
							</thead>
							<tbody>
								<?php
									echo $insResultado->balanceResultadosConsolidado($fecha_inicio, $fecha_fin);
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
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/datatables.js?v=1.0.1"></script> 
  </body>
</html>








