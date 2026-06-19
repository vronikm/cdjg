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

	$cobrarReimpresion = $insColor->cobrarReimpresionCarnet();
	$valorReimpresion = $insColor->valorReimpresionCarnet();
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
						<span class="badge <?php echo $cobrarReimpresion ? 'badge-warning' : 'badge-success'; ?> ml-2">
							<?php echo $cobrarReimpresion ? 'Reimpresion con cargo' : 'Reimpresion sin cargo'; ?>
						</span>
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

									<div class="alert alert-warning border border-warning shadow-sm mb-4">
										<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
											<div class="mb-3 mb-md-0">
												<h5 class="mb-1">
													<i class="fas fa-money-check-alt"></i> Politica de reimpresion de carnets
												</h5>
												<small class="text-muted">
													Activa esta opcion si la reimpresion por carnet extraviado debe generar el cobro configurado.
												</small>
											</div>

											<div class="text-md-right">
												<input type="hidden" name="cobrar_reimpresion" value="0">
												<div class="custom-control custom-switch custom-switch-off-secondary custom-switch-on-warning">
													<input type="checkbox"
														   class="custom-control-input"
														   id="cobrar_reimpresion"
														   name="cobrar_reimpresion"
														   value="1"
														   <?php echo $cobrarReimpresion ? 'checked' : ''; ?>>
													<label class="custom-control-label font-weight-bold" for="cobrar_reimpresion">
														Cobrar reimpresion
													</label>
												</div>
												<small class="d-block mt-1 <?php echo $cobrarReimpresion ? 'text-warning' : 'text-success'; ?>">
													<?php echo $cobrarReimpresion ? 'Activo: se genera cobro ROT.' : 'Inactivo: la reimpresion no genera cobro.'; ?>
												</small>
												<label class="d-block mt-3 mb-1" for="valor_reimpresion">
													Valor del rubro
												</label>
												<div class="input-group input-group-sm">
													<div class="input-group-prepend">
														<span class="input-group-text">$</span>
													</div>
													<input type="number"
														   class="form-control text-right"
														   id="valor_reimpresion"
														   name="valor_reimpresion"
														   value="<?php echo number_format($valorReimpresion, 2, '.', ''); ?>"
														   min="0.01"
														   step="0.01"
														   required>
												</div>
											</div>
										</div>
									</div>
									
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
