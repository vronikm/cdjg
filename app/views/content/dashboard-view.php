<?php
	use app\controllers\dashboardController;

	$insDashboard = new dashboardController();
	$rolSesion = (int)($_SESSION['rol'] ?? 0);
	$usuarioSesion = $_SESSION['usuario'] ?? '';
	$sedesDashboard = $insDashboard->resumenSedesDeportivasDashboard($rolSesion, $usuarioSesion);
	$h = static function($valor){ return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); };

	$totalAlumnosActivos = 0;
	$totalAlumnosInactivos = 0;
	$totalCancelados = 0;
	$totalAlDia = 0;
	$totalPendientes = 0;

	foreach($sedesDashboard as $sede){
		$totalAlumnosActivos += (int)$sede['activos'];
		$totalAlumnosInactivos += (int)$sede['inactivos'];
		$totalCancelados += (int)$sede['pagos_cancelados'];
		$totalAlDia += (int)$sede['al_dia'];
		$totalPendientes += (int)$sede['pagos_pendientes'];
	}

	$representantes = $insDashboard->obtenerRepresentantesDeportivos($rolSesion, $usuarioSesion);
	$representantes = $representantes->rowCount() > 0 ? $representantes->fetch() : [];
	$totalRepresentantes = (int)($representantes['totalRepresentantes'] ?? 0);

	$renderInfoBox = static function($url, $color, $icono, $titulo, $valor) use ($h){
?>
	<div class="dashboard-metric-col col-md-6 col-sm-12 mb-3">
		<a href="<?php echo $h($url); ?>" class="text-decoration-none">
			<div class="info-box shadow-sm border mb-0">
				<span class="info-box-icon <?php echo $h($color); ?>">
					<i class="<?php echo $h($icono); ?>"></i>
				</span>
				<div class="info-box-content">
					<span class="info-box-text text-muted"><?php echo $h($titulo); ?></span>
					<span class="info-box-number h3 mb-0 text-dark"><?php echo number_format((int)$valor, 0, '.', ','); ?></span>
				</div>
			</div>
		</a>
	</div>
<?php
	};
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Dashboard</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">
	<style>
		@media (min-width: 1200px){
			.dashboard-metric-col{
				flex: 0 0 20%;
				max-width: 20%;
			}
		}
	</style>
  </head>
  <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
      <?php require_once "app/views/inc/navbar.php"; ?>
      <?php require_once "app/views/inc/main-sidebar.php"; ?>

      <div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2 align-items-center">
					<div class="col-sm-6">
						<h1 class="m-0">Dashboard</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>dashboard/">Inicio</a></li>
							<li class="breadcrumb-item active">Dashboard</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<section class="content">
			<div class="container-fluid">
				<?php if(empty($sedesDashboard)){ ?>
					<div class="alert alert-info">
						No hay sedes deportivas configuradas. La sede administrativa Matriz no se muestra en este tablero.
					</div>
				<?php } ?>

				<?php foreach($sedesDashboard as $sede){ ?>
					<div class="card card-default">
						<div class="card-header">
							<h3 class="card-title"><?php echo $h($sede['sede_nombre']); ?></h3>
							<div class="card-tools">
								<button type="button" class="btn btn-tool" data-card-widget="collapse">
									<i class="fas fa-minus"></i>
								</button>
							</div>
						</div>
						<div class="card-body">
							<div class="row">
								<?php
									$sedeId = (int)$sede['sede_id'];
									$renderInfoBox(APP_URL.'dashboardAlumnos/'.$sedeId.'/A/', 'bg-primary', 'fas fa-users', 'Alumnos activos', $sede['activos']);
									$renderInfoBox(APP_URL.'reportePagos/'.$sedeId.'/', 'bg-success', 'fas fa-check-circle', 'Pagos receptados', $sede['pagos_cancelados']);
									$renderInfoBox(APP_URL.'dashboardAlumnos/'.$sedeId.'/I/', 'bg-secondary', 'fas fa-user-slash', 'Alumnos inactivos', $sede['inactivos']);
									$renderInfoBox(APP_URL.'reportePagos/'.$sedeId.'/', 'bg-info', 'fas fa-user-check', 'Alumnos al dia', $sede['al_dia']);
									$renderInfoBox(APP_URL.'reportePendientes/'.$sedeId.'/', 'bg-danger', 'fas fa-exclamation-triangle', 'Alumnos con mora', $sede['pagos_pendientes']);
								?>
							</div>
						</div>
					</div>
				<?php } ?>

				<?php if(count($sedesDashboard) > 1){ ?>
				<div class="card card-default">
					<div class="card-header">
						<h3 class="card-title">CONSOLIDADO SEDES DEPORTIVAS</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					<div class="card-body">
						<div class="row">
							<?php
								$renderInfoBox(APP_URL.'representanteList/', 'bg-warning', 'fas fa-user-friends', 'Representantes', $totalRepresentantes);
								$renderInfoBox(APP_URL.'alumnoList/', 'bg-warning', 'fas fa-users', 'Alumnos activos', $totalAlumnosActivos);
								$renderInfoBox(APP_URL.'alumnoList/', 'bg-warning', 'fas fa-user-slash', 'Alumnos inactivos', $totalAlumnosInactivos);
								$renderInfoBox(APP_URL.'reportePagos/', 'bg-warning', 'fas fa-user-check', 'Alumnos al dia', $totalAlDia);
								$renderInfoBox(APP_URL.'cobranzaPension/', 'bg-warning', 'fas fa-exclamation-triangle', 'Alumnos con mora', $totalPendientes);
							?>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</section>
      </div>

      <?php require_once "app/views/inc/footer.php"; ?>
      <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery/jquery.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js"></script>
  </body>
</html>
