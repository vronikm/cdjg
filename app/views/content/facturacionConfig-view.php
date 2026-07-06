<?php
	use app\controllers\facturasController;

	$h = static function($valor){ return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); };
	$renderDiagnosticoFacturacion = static function(array $estado) use ($h): void {
		http_response_code(500);
		$appName = defined('APP_NAME') ? APP_NAME : 'Sistema';
		$appUrl = defined('APP_URL') ? APP_URL : '';
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo $h($appName); ?> | Diagnostico facturacion</title>
	<?php if($appUrl !== ''){ ?>
	<link rel="stylesheet" href="<?php echo $h($appUrl); ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
	<link rel="stylesheet" href="<?php echo $h($appUrl); ?>app/views/dist/css/adminlte.css">
	<?php } ?>
  </head>
  <body class="hold-transition sidebar-mini layout-fixed">
	<div class="container py-4">
		<div class="alert alert-danger">
			<h4 class="mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>No se pudo cargar la configuracion de facturacion</h4>
			<p class="mb-0">La vista no encontro la clase <strong><?php echo $h($estado['clase'] ?? ''); ?></strong>. Revise el detalle para corregir el despliegue en produccion.</p>
		</div>

		<div class="card card-outline card-danger">
			<div class="card-header">
				<h3 class="card-title">Diagnostico tecnico</h3>
			</div>
			<div class="card-body table-responsive p-0">
				<table class="table table-sm table-striped mb-0">
					<tbody>
						<tr><th style="width: 220px;">Clase esperada</th><td><?php echo $h($estado['clase'] ?? ''); ?></td></tr>
						<tr><th>Archivo esperado</th><td><?php echo $h($estado['archivo_controlador'] ?? ''); ?></td></tr>
						<tr><th>Archivo existe</th><td><?php echo $h($estado['archivo_existe'] ?? 'NO'); ?></td></tr>
						<tr><th>Archivo legible</th><td><?php echo $h($estado['archivo_legible'] ?? 'NO'); ?></td></tr>
						<tr><th>Archivo incluido</th><td><?php echo $h($estado['archivo_incluido'] ?? 'NO'); ?></td></tr>
						<tr><th>Clase disponible</th><td><?php echo $h($estado['clase_disponible'] ?? 'NO'); ?></td></tr>
						<tr><th>Autoload</th><td><?php echo $h($estado['autoload'] ?? ''); ?></td></tr>
						<?php if(!empty($estado['error'])){ ?>
						<tr><th>Error detectado</th><td><?php echo $h($estado['error']); ?></td></tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="callout callout-info">
			<h5>Como corregirlo</h5>
			<p>En Linux el nombre del archivo distingue mayusculas y minusculas. Debe existir exactamente:</p>
			<pre class="mb-2">app/controllers/facturasController.php</pre>
			<p class="mb-2">Si el archivo falta, ejecute en produccion:</p>
			<pre class="mb-2">cd /home/digitech/clubjorgeguzman
git pull --ff-only origin main</pre>
			<p class="mb-2">Si existe con otro nombre, renombrelo manteniendo exactamente las mismas mayusculas/minusculas:</p>
			<pre class="mb-0">mv app/controllers/FacturasController.php app/controllers/facturasController.php</pre>
		</div>
	</div>
  </body>
</html>
<?php
	};

	$controllerClass = facturasController::class;
	$controllerFile = dirname(__DIR__, 2)."/controllers/facturasController.php";
	$autoloadFile = dirname(__DIR__, 3)."/autoload.php";
	$diagnosticoFacturacion = [
		"clase" => $controllerClass,
		"archivo_controlador" => $controllerFile,
		"archivo_existe" => is_file($controllerFile) ? "SI" : "NO",
		"archivo_legible" => is_readable($controllerFile) ? "SI" : "NO",
		"archivo_incluido" => "NO",
		"clase_disponible" => class_exists($controllerClass, false) ? "SI" : "NO",
		"autoload" => $autoloadFile.(is_file($autoloadFile) ? " (existe)" : " (no existe)"),
		"error" => "",
	];

	if(!class_exists($controllerClass, false) && is_file($controllerFile) && is_readable($controllerFile)){
		try{
			require_once $controllerFile;
			$diagnosticoFacturacion["archivo_incluido"] = "SI";
		}catch(Throwable $error){
			$diagnosticoFacturacion["error"] = $error->getMessage();
			$renderDiagnosticoFacturacion($diagnosticoFacturacion);
			return;
		}
	}

	$diagnosticoFacturacion["clase_disponible"] = class_exists($controllerClass, false) ? "SI" : "NO";
	if(!class_exists($controllerClass, false)){
		$renderDiagnosticoFacturacion($diagnosticoFacturacion);
		return;
	}

	try{
		$insFactura = new facturasController();
		$sriConfig = $insFactura->obtenerConfiguracionSri();
		$certificado = $insFactura->obtenerInfoCertificadoSri();
	}catch(Throwable $error){
		$diagnosticoFacturacion["error"] = "La clase se cargo, pero fallo al iniciar la configuracion: ".$error->getMessage();
		$renderDiagnosticoFacturacion($diagnosticoFacturacion);
		return;
	}

	$emisor = $sriConfig['emisor'] ?? [];
	$correo = $sriConfig['correo'] ?? [];
	$smtp = $correo['smtp'] ?? [];
	$formasPago = $sriConfig['formas_pago'] ?? [];
	$selected = static function($actual, $valor){ return ((string)$actual === (string)$valor) ? 'selected' : ''; };
	$certEstado = $certificado['estado'] ?? 'NO_CONFIGURADO';
	$certClase = ($certEstado === 'VALIDO') ? 'success' : (($certEstado === 'CADUCADO' || $certEstado === 'CLAVE_INVALIDA') ? 'danger' : 'warning');
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Configuracion SRI</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">
  </head>
  <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
      <?php require_once "app/views/inc/navbar.php"; ?>
      <?php require_once "app/views/inc/main-sidebar.php"; ?>

      <div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2 align-items-center">
					<div class="col-sm-7">
						<h3 class="m-0">Configuracion SRI</h3>
					</div>
					<div class="col-sm-5">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>facturasList/">Facturacion</a></li>
							<li class="breadcrumb-item active">Configuracion</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<section class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-8">
						<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/facturasAjax.php" method="POST" autocomplete="off" data-recargar-directo>
							<input type="hidden" name="modulo_facturas" value="GUARDAR_CONFIG_SRI">
							<div class="card card-primary card-outline">
								<div class="card-header">
									<h3 class="card-title"><i class="fas fa-building mr-2"></i>Emisor y comprobantes</h3>
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label for="ambiente">Ambiente</label>
												<select class="form-control form-control-sm" id="ambiente" name="ambiente" required>
													<option value="1" <?php echo $selected($sriConfig['ambiente'] ?? '1', '1'); ?>>Pruebas</option>
													<option value="2" <?php echo $selected($sriConfig['ambiente'] ?? '1', '2'); ?>>Produccion</option>
												</select>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="ruc">RUC</label>
												<input type="text" class="form-control form-control-sm" id="ruc" name="ruc" maxlength="13" value="<?php echo $h($emisor['ruc'] ?? ''); ?>" required>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="obligado_contabilidad">Obligado contabilidad</label>
												<select class="form-control form-control-sm" id="obligado_contabilidad" name="obligado_contabilidad" required>
													<option value="NO" <?php echo $selected($emisor['obligado_contabilidad'] ?? 'NO', 'NO'); ?>>NO</option>
													<option value="SI" <?php echo $selected($emisor['obligado_contabilidad'] ?? 'NO', 'SI'); ?>>SI</option>
												</select>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="razon_social">Razon social</label>
												<input type="text" class="form-control form-control-sm" id="razon_social" name="razon_social" value="<?php echo $h($emisor['razon_social'] ?? ''); ?>" required>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="nombre_comercial">Nombre comercial</label>
												<input type="text" class="form-control form-control-sm" id="nombre_comercial" name="nombre_comercial" value="<?php echo $h($emisor['nombre_comercial'] ?? ''); ?>">
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="direccion_matriz">Direccion matriz</label>
												<input type="text" class="form-control form-control-sm" id="direccion_matriz" name="direccion_matriz" value="<?php echo $h($emisor['direccion_matriz'] ?? ''); ?>" required>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="direccion_establecimiento">Direccion establecimiento</label>
												<input type="text" class="form-control form-control-sm" id="direccion_establecimiento" name="direccion_establecimiento" value="<?php echo $h($emisor['direccion_establecimiento'] ?? ''); ?>" required>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="codigo_establecimiento">Establecimiento</label>
												<input type="text" class="form-control form-control-sm" id="codigo_establecimiento" name="codigo_establecimiento" maxlength="3" value="<?php echo $h($emisor['codigo_establecimiento'] ?? '001'); ?>" required>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="punto_emision">Punto emision</label>
												<input type="text" class="form-control form-control-sm" id="punto_emision" name="punto_emision" maxlength="3" value="<?php echo $h($emisor['punto_emision'] ?? '001'); ?>" required>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="secuencial_inicio">Nro. Inicio</label>
												<input type="number" min="1" max="999999999" step="1" class="form-control form-control-sm" id="secuencial_inicio" name="secuencial_inicio" value="<?php echo $h($sriConfig['secuencial_inicio'] ?? 1); ?>" required>
												<small class="form-text text-muted">Desde qué número se emitirán las facturas.</small>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="iva_tarifa_default">IVA</label>
												<select class="form-control form-control-sm" id="iva_tarifa_default" name="iva_tarifa_default" required>
													<option value="0" <?php echo $selected($sriConfig['iva_tarifa_default'] ?? 0, 0); ?>>0%</option>
													<option value="12" <?php echo $selected($sriConfig['iva_tarifa_default'] ?? 0, 12); ?>>12%</option>
													<option value="14" <?php echo $selected($sriConfig['iva_tarifa_default'] ?? 0, 14); ?>>14%</option>
													<option value="15" <?php echo $selected($sriConfig['iva_tarifa_default'] ?? 0, 15); ?>>15%</option>
												</select>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="forma_pago_default">Forma pago</label>
												<select class="form-control form-control-sm" id="forma_pago_default" name="forma_pago_default" required>
													<?php foreach($formasPago as $codigo => $nombre){ ?>
														<option value="<?php echo $h($codigo); ?>" <?php echo $selected($sriConfig['forma_pago_default'] ?? '20', $codigo); ?>><?php echo $h($codigo.' - '.$nombre); ?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="contribuyente_especial">Contribuyente especial</label>
												<input type="text" class="form-control form-control-sm" id="contribuyente_especial" name="contribuyente_especial" value="<?php echo $h($emisor['contribuyente_especial'] ?? ''); ?>">
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="agente_retencion">Agente retencion</label>
												<input type="text" class="form-control form-control-sm" id="agente_retencion" name="agente_retencion" value="<?php echo $h($emisor['agente_retencion'] ?? ''); ?>">
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="contribuyente_rimpe">RIMPE</label>
												<input type="text" class="form-control form-control-sm" id="contribuyente_rimpe" name="contribuyente_rimpe" value="<?php echo $h($emisor['contribuyente_rimpe'] ?? ''); ?>">
											</div>
										</div>
										<div class="col-12">
											<hr>
											<h6 class="text-muted mb-3"><i class="fas fa-envelope mr-1"></i>Correo de facturacion</h6>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="correo_from">Correo remitente</label>
												<input type="email" class="form-control form-control-sm" id="correo_from" name="correo_from" value="<?php echo $h($correo['from'] ?? ''); ?>" placeholder="facturacion@dominio.com">
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="correo_from_nombre">Nombre remitente</label>
												<input type="text" class="form-control form-control-sm" id="correo_from_nombre" name="correo_from_nombre" value="<?php echo $h($correo['from_name'] ?? APP_NAME); ?>" placeholder="<?php echo $h(APP_NAME); ?>">
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="smtp_activo">Metodo envio</label>
												<select class="form-control form-control-sm" id="smtp_activo" name="smtp_activo">
													<option value="1" <?php echo !empty($smtp['activo']) ? 'selected' : ''; ?>>SMTP autenticado</option>
													<option value="0" <?php echo empty($smtp['activo']) ? 'selected' : ''; ?>>mail() local</option>
												</select>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="smtp_host">Servidor SMTP</label>
												<input type="text" class="form-control form-control-sm" id="smtp_host" name="smtp_host" value="<?php echo $h($smtp['host'] ?? ''); ?>" placeholder="smtp.gmail.com">
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<label for="smtp_port">Puerto</label>
												<input type="number" min="1" max="65535" class="form-control form-control-sm" id="smtp_port" name="smtp_port" value="<?php echo $h($smtp['port'] ?? 587); ?>">
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<label for="smtp_seguridad">Seguridad</label>
												<select class="form-control form-control-sm" id="smtp_seguridad" name="smtp_seguridad">
													<option value="tls" <?php echo $selected($smtp['seguridad'] ?? 'tls', 'tls'); ?>>TLS</option>
													<option value="ssl" <?php echo $selected($smtp['seguridad'] ?? 'tls', 'ssl'); ?>>SSL</option>
													<option value="ninguna" <?php echo $selected($smtp['seguridad'] ?? 'tls', 'ninguna'); ?>>Ninguna</option>
												</select>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="smtp_usuario">Usuario SMTP</label>
												<input type="text" class="form-control form-control-sm" id="smtp_usuario" name="smtp_usuario" value="<?php echo $h($smtp['usuario'] ?? ''); ?>" autocomplete="off">
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group mb-md-0">
												<label for="smtp_clave">Clave SMTP</label>
												<div class="input-group input-group-sm">
													<input type="password" class="form-control" id="smtp_clave" name="smtp_clave" autocomplete="new-password" placeholder="<?php echo !empty($smtp['clave_configurada']) ? 'Clave guardada; deje vacio para conservar' : 'Clave o app password'; ?>">
													<div class="input-group-append">
														<button type="button" class="btn btn-outline-secondary btn-toggle-password" data-target="#smtp_clave" title="Ver clave" aria-label="Ver clave">
															<i class="fas fa-eye"></i>
														</button>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer text-right">
									<a href="<?php echo APP_URL; ?>facturasList/" class="btn btn-default btn-sm"><i class="fas fa-arrow-left mr-1"></i>Volver</a>
									<button type="submit" class="btn bg-lightblue btn-sm"><i class="fas fa-save mr-1"></i>Guardar</button>
								</div>
							</div>
						</form>
					</div>

					<div class="col-lg-4">
						<div class="card card-<?php echo $certClase; ?> card-outline">
							<div class="card-header">
								<h3 class="card-title"><i class="fas fa-key mr-2"></i>Firma electronica</h3>
							</div>
							<div class="card-body">
								<div class="mb-3">
									<span class="badge badge-<?php echo $certClase; ?>"><?php echo $h($certEstado); ?></span>
								</div>
								<dl class="row mb-0">
									<dt class="col-sm-4">Archivo</dt>
									<dd class="col-sm-8"><?php echo $h($certificado['archivo'] ?? ''); ?></dd>
									<dt class="col-sm-4">Titular</dt>
									<dd class="col-sm-8"><?php echo $h($certificado['titular'] ?? ''); ?></dd>
									<dt class="col-sm-4">Emisor</dt>
									<dd class="col-sm-8"><?php echo $h($certificado['emisor'] ?? ''); ?></dd>
                                    <dt class="col-sm-4">RUC firma</dt>
                                    <dd class="col-sm-8"><?php echo $h(($certificado['ruc'] ?? '') ?: 'Pendiente'); ?></dd>
									<dt class="col-sm-4">Vigencia</dt>
									<dd class="col-sm-8"><?php echo $h(($certificado['valido_hasta'] ?? '') ?: 'Pendiente'); ?></dd>
								</dl>
							</div>
						</div>

						<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/facturasAjax.php" method="POST" enctype="multipart/form-data" autocomplete="off" data-recargar-directo>
							<input type="hidden" name="modulo_facturas" value="SUBIR_CERTIFICADO_SRI">
							<div class="card card-default">
								<div class="card-header">
									<h3 class="card-title"><i class="fas fa-upload mr-2"></i>Cargar .p12</h3>
								</div>
								<div class="card-body">
									<div class="form-group">
										<label for="certificado">Archivo</label>
										<input type="file" class="form-control-file" id="certificado" name="certificado" accept=".p12,.pfx">
										<small class="form-text text-muted">Si ya hay una firma cargada, puede dejar el archivo vacio y actualizar solo la clave.</small>
									</div>
									<div class="form-group mb-0">
										<label for="clave_certificado">Clave</label>
										<div class="input-group input-group-sm">
											<input type="password" class="form-control" id="clave_certificado" name="clave_certificado" autocomplete="new-password" required>
											<div class="input-group-append">
												<button type="button" class="btn btn-outline-secondary" id="btn-ver-clave-certificado" title="Ver clave" aria-label="Ver clave">
													<i class="fas fa-eye"></i>
												</button>
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer text-right">
									<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check mr-1"></i>Validar y guardar</button>
								</div>
							</div>
						</form>

						<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/facturasAjax.php" method="POST" data-recargar-directo>
							<input type="hidden" name="modulo_facturas" value="PROBAR_CERTIFICADO_SRI">
							<button type="submit" class="btn btn-outline-secondary btn-sm btn-block"><i class="fas fa-shield-alt mr-1"></i>Probar firma guardada</button>
						</form>
						<form class="FormularioAjax mt-2" action="<?php echo APP_URL; ?>app/ajax/facturasAjax.php" method="POST" data-recargar-directo>
							<input type="hidden" name="modulo_facturas" value="PROBAR_CONEXION_SRI">
							<button type="submit" class="btn btn-outline-info btn-sm btn-block"><i class="fas fa-plug mr-1"></i>Probar conexion SRI</button>
						</form>
					</div>
				</div>
			</div>
		</section>
      </div>

      <?php require_once "app/views/inc/footer.php"; ?>
      <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery/jquery.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/select2/js/select2.full.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js"></script>
	<script>
		$(function(){
			$('#forma_pago_default').select2({theme: 'bootstrap4', width: '100%'});
			$('#btn-ver-clave-certificado').on('click', function(){
				const input = $('#clave_certificado');
				const icono = $(this).find('i');
				const visible = input.attr('type') === 'text';
				input.attr('type', visible ? 'password' : 'text');
				icono.toggleClass('fa-eye', visible).toggleClass('fa-eye-slash', !visible);
				$(this).attr('title', visible ? 'Ver clave' : 'Ocultar clave');
				$(this).attr('aria-label', visible ? 'Ver clave' : 'Ocultar clave');
			});
			$('.btn-toggle-password').on('click', function(){
				const input = $($(this).data('target'));
				const icono = $(this).find('i');
				const visible = input.attr('type') === 'text';
				input.attr('type', visible ? 'password' : 'text');
				icono.toggleClass('fa-eye', visible).toggleClass('fa-eye-slash', !visible);
				$(this).attr('title', visible ? 'Ver clave' : 'Ocultar clave');
				$(this).attr('aria-label', visible ? 'Ver clave' : 'Ocultar clave');
			});
		});
	</script>
  </body>
</html>
