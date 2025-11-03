<?php
	date_default_timezone_set("America/Guayaquil");

	use app\controllers\carnetController;
	
	$insColor = new carnetController();
	
	// Obtener todos los colores asignados por mes
	$coloresPorMes = [];
	$mesesBloqueados = [];
	for($mes = 1; $mes <= 12; $mes++) {
		$datos = $insColor->BuscarColorPorMes($mes);
		if($datos && $datos->rowCount() == 1){
			$datos = $datos->fetch();
			$coloresPorMes[$mes] = [
				'color_id' => $datos['color_id'],
				'color_hex' => $datos['color_hex'],
				'color_nombre' => $datos['color_nombre'],
				'bloqueado' => ($datos['color_bloqueado'] == 1 || $datos['total_carnets'] > 0),
				'total_carnets' => $datos['total_carnets']
			];
			$mesesBloqueados[$mes] = $coloresPorMes[$mes]['bloqueado'];
		} else {
			$coloresPorMes[$mes] = [
				'color_id' => 0,
				'color_hex' => '#FFFFFF',
				'color_nombre' => 'Sin asignar',
				'bloqueado' => false,
				'total_carnets' => 0
			];
			$mesesBloqueados[$mes] = false;
		}
	}
	
	$meses = [
		1 => 'Enero',
		2 => 'Febrero', 
		3 => 'Marzo',
		4 => 'Abril',
		5 => 'Mayo',
		6 => 'Junio',
		7 => 'Julio',
		8 => 'Agosto',
		9 => 'Septiembre',
		10 => 'Octubre',
		11 => 'Noviembre',
		12 => 'Diciembre'
	];
?>

<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?>| Configuración de Colores</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">

	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/carnetcolor_style.css">

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
						<h1 class="mb-2">
                            <i class="fas fa-palette text-primary"></i>
                            Configuración de colores para carnets
                        </h1>
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

		<!-- Section listado de alumnos -->
		<section class="content">	
			<div class="container-fluid">
				<div class="card card-default">
					<div class="card-header">
						<h3 class="card-title">Asigna un color único para cada mes del año</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>

					<div class="card-body">						
						<div class="row">
							<div class="col-md-12">	
								<form class="FormularioAjax" id="quickForm" action="<?php echo APP_URL; ?>app/ajax/carnetAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data">
									
									<input type="hidden" name="modulo_carnet" value="actualizar_colores">
									
									<div class="table-responsive">
										<table class="table table-hover">
											<thead>
												<tr>
													<th style="width: 150px;">Mes</th>
													<th>Color Asignado</th>
													<th style="width: 550px;">Vista Previa</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach($meses as $numMes => $nombreMes): 
													$mesBloqueado = $mesesBloqueados[$numMes];
													$colorData = $coloresPorMes[$numMes];
												?>
													<tr class="mes-row <?php echo $mesBloqueado ? 'table-secondary' : ''; ?>">
														<td>
															<span class="mes-label"><?php echo $nombreMes; ?></span>
															<?php if($mesBloqueado): ?>
																<span class="badge badge-warning ml-2">
																	<i class="fas fa-lock"></i> Bloqueado
																</span>
																<br>
																<small class="text-muted">
																	<?php echo $colorData['total_carnets']; ?> carnet(s) emitido(s)
																</small>
															<?php endif; ?>
														</td>
														<td>
															<select class="form-control select2 color-select" 
																	style="width: 100%;" 
																	id="color_mes_<?php echo $numMes; ?>" 
																	name="color_mes[<?php echo $numMes; ?>]"
																	data-mes="<?php echo $numMes; ?>"
																	<?php echo $mesBloqueado ? 'disabled' : ''; ?>>
																<?php echo $insColor->listarOptionColor($colorData['color_id'], $numMes); ?>
															</select>
															<?php if($mesBloqueado): ?>
																<input type="hidden" name="color_mes[<?php echo $numMes; ?>]" value="<?php echo $colorData['color_id']; ?>">
															<?php endif; ?>
														</td>
														<td>
															<div class="color-preview" 
																 id="preview_<?php echo $numMes; ?>"
																 style="background-color: <?php echo $colorData['color_hex']; ?>">
															</div>
														</td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>

									<div class="row mt-3">										
										<div class="col-md-12">						
											<button type="submit" class="btn btn-success">
												<i class="fas fa-save"></i> Guardar Configuración
											</button>
											<a href="<?php echo APP_URL; ?>catalogosNew/" class="btn btn-secondary">
												<i class="fas fa-times"></i> Cancelar
											</a>
											<button type="reset" class="btn btn-outline-dark">
												<i class="fas fa-eraser"></i> Restablecer
											</button>						
										</div>	
									</div>									
								</form>							
							</div>
						</div>
					</div>
				</div>
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
	<!-- AdminLTE App -->
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js"></script>
	<!-- Select2 -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/select2/js/select2.full.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery-validation/jquery.validate.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery-validation/additional-methods.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/carnet_color.js"></script>
  </body>
</html>