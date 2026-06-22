<?php
	date_default_timezone_set("America/Guayaquil");

	use app\controllers\facturasController;

	include 'app/lib/barcode.php';

	$generator = new barcode_generator();
	$symbology = "code128"; // Cambiar tipo de código
	$options = array('sx'=>1,'sy'=>0.5,'p'=>1); // Ajustar tamaño y padding
	$insAlumno = new facturasController();
	$sriConfig = $insAlumno->obtenerConfiguracionSri();
	$sriEmisor = $sriConfig['emisor'] ?? [];
	$sriAmbiente = ((string)($sriConfig['ambiente'] ?? '1') === '2') ? 'Produccion' : 'Pruebas';
	$sriIvaDefault = (float)($sriConfig['iva_tarifa_default'] ?? 0);
	$sriFormaPago = (string)($sriConfig['forma_pago_default'] ?? '20');
	$sriFormaPagoTexto = $sriConfig['formas_pago'][$sriFormaPago] ?? $sriFormaPago;
	$h = static function($valor){ return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); };

	$alumno=$insLogin->limpiarCadena($url[1]);

	$fecha_inicio= date('Y-m-d');
	$fecha_fin= date('Y-m-d');

	$datos=$insAlumno->BuscarAlumnoFactura($alumno, $fecha_inicio,$fecha_fin);
	$facturacionBloqueada = false;
	$facturacionBloqueadaTitulo = '';
	$facturacionBloqueadaTexto = '';

	if($datos->rowCount()==1){
		$datos=$datos->fetch();
		$clienteFacturaNombre = $datos['cliente_factura_nombre'] ?? $datos['representante'];
		$clienteFacturaIdentificacion = $datos['cliente_factura_identificacion'] ?? $datos['repre_identificacion'];
		$clienteFacturaDireccion = $datos['cliente_factura_direccion'] ?? $datos['repre_direccion'];
		$clienteFacturaCorreo = $datos['cliente_factura_correo'] ?? $datos['repre_correo'];
		$clienteFacturaCelular = $datos['cliente_factura_celular'] ?? $datos['repre_celular'];
		$clienteFacturaTipoIdentificacion = $datos['cliente_factura_tipoidentificacion'] ?? $datos['repre_tipoidentificacion'];
		$clienteFacturaOrigen = (($datos['repre_factura_a_nombre'] ?? 'REPRESENTANTE') === 'CONYUGE') ? 'Conyuge' : 'Representante';

		/* validar correo */
		$error='N';
		$disabled='';

		if(($datos['repre_requiere_factura'] ?? 'S') === 'N'){
			$facturacionBloqueada = true;
			$facturacionBloqueadaTitulo = 'No se puede facturar';
			$facturacionBloqueadaTexto = 'El representante esta configurado como no requiere factura.';
			$error='S';
			$disabled='disabled';
		}

		if (!filter_var($clienteFacturaCorreo, FILTER_VALIDATE_EMAIL)) {
			$mail = '<p class="text-danger">'.$h($clienteFacturaCorreo).'</p>';
			$correo = '<strong class="text-danger"><i class="fas fa-envelope mr-1"></i> Correo no válido</strong>';
			$error='S';
			$disabled='disabled';
		}else {
			$mail = '<p class="text-muted">'.$h($clienteFacturaCorreo).'</p>';
			$correo = '<strong><i class="fas fa-envelope mr-1"></i> Correo</strong>';
		}

		/* validar identificacion SRI */
		if (!$insAlumno->validarIdentificacionSri($clienteFacturaIdentificacion, $clienteFacturaTipoIdentificacion)) {
			$identificacion = '<p class="text-danger">'.$h($clienteFacturaIdentificacion).'</p>';
			$cedula = '<strong class="text-danger"><i class="fas fa-address-card mr-1"></i> Identificacion no valida SRI</strong>';
			$error='S';
			$disabled='disabled';
		}else {
			$identificacion = '<p class="text-muted">'.$h($clienteFacturaIdentificacion).'</p>';
			$cedula = '<strong><i class="fas fa-address-card mr-1"></i> Identificacion</strong>';
		}

	}else{
		$alumnoBasico = $insAlumno->obtenerAlumnoFacturaBasico($alumno);
		$facturacionBloqueada = true;
		$facturacionBloqueadaTitulo = 'No se puede facturar';
		$facturacionBloqueadaTexto = 'No se pudo cargar la informacion del alumno para facturacion.';

		if($alumnoBasico){
			$nombreAlumno = trim((string)($alumnoBasico['alumno'] ?? ''));
			$nombreAlumno = $nombreAlumno !== '' ? $nombreAlumno : 'ID '.$alumno;

			if((int)($alumnoBasico['alumno_repreid'] ?? 0) <= 0){
				$facturacionBloqueadaTexto = 'El alumno '.$nombreAlumno.' no tiene representante vinculado. Actualice la ficha del alumno y asigne un representante antes de generar la factura.';
			}elseif(empty($alumnoBasico['repre_id'])){
				$facturacionBloqueadaTexto = 'El representante vinculado al alumno '.$nombreAlumno.' no existe o fue eliminado. Revise la ficha del alumno antes de generar la factura.';
			}
		}

		$datos = [
			'repre_identificacion' => '',
			'repre_direccion' => '',
			'repre_correo' => '',
			'repre_celular' => '',
			'repre_tipoidentificacion' => '',
			'repre_id' => '',
			'representante' => 'Sin representante vinculado',
			'repre_requiere_factura' => 'N',
			'repre_factura_a_nombre' => 'REPRESENTANTE',
			'cliente_factura_nombre' => 'Sin representante vinculado',
			'cliente_factura_identificacion' => '',
			'cliente_factura_direccion' => '',
			'cliente_factura_correo' => '',
			'cliente_factura_celular' => '',
			'cliente_factura_tipoidentificacion' => '',
			'pagos' => 0
		];
		$clienteFacturaNombre = $datos['cliente_factura_nombre'];
		$clienteFacturaIdentificacion = $datos['cliente_factura_identificacion'];
		$clienteFacturaDireccion = $datos['cliente_factura_direccion'];
		$clienteFacturaCorreo = $datos['cliente_factura_correo'];
		$clienteFacturaCelular = $datos['cliente_factura_celular'];
		$clienteFacturaTipoIdentificacion = $datos['cliente_factura_tipoidentificacion'];
		$clienteFacturaOrigen = 'Representante';
		$error='S';
		$disabled='disabled';
		$mail = '<p class="text-danger">Sin correo</p>';
		$correo = '<strong class="text-danger"><i class="fas fa-envelope mr-1"></i> Correo</strong>';
		$identificacion = '<p class="text-danger">Sin identificacion</p>';
		$cedula = '<strong class="text-danger"><i class="fas fa-address-card mr-1"></i> Identificacion</strong>';
	}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Facturas</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">

	<!-- daterange picker -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/daterangepicker/daterangepicker.css">
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- Bootstrap Color Picker -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
	<!-- Tempusdominus Bootstrap 4 -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
	<!-- Bootstrap4 Duallistbox -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
	<!-- BS Stepper -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/bs-stepper/css/bs-stepper.min.css">

	<!-- Theme style -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">


	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>

	<!-- fileinput -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.css">

	<!-- Ekko Lightbox -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/ekko-lightbox/ekko-lightbox.css">

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
							<h3 class="m-0">Envio de facturas</h3>
						</div><!-- /.col -->
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Inicio</a></li>
								<li class="breadcrumb-item active">Ficha Alumno</li>
							</ol>
						</div><!-- /.col -->
					</div><!-- /.row -->
				</div><!-- /.container-fluid -->
			</div>
			<!-- /.content-header -->

			<!-- Main content -->
			<section class="content">
				<!-- /.container-fluid información alumno -->
				<div class="container-fluid">

					<div class="row">
						<div class="col-md-3">
							<div class="card card-olive">
								<div class="card-header">
									<h3 class="card-title">Cliente facturacion</h3>
								</div>

								<!-- Bloque Representante -->
								<div class="card-body">
									<strong><i class="fas fa-user mr-1"></i> Nombres</strong>
									<p class="text-muted" id="representante_nombre"><?php echo $h($clienteFacturaNombre); ?></p>

									<hr>
									<strong><i class="fas fa-file-invoice mr-1"></i> Factura</strong>
									<p class="text-muted" id="representante_factura_a_nombre"><?php echo $h($clienteFacturaOrigen); ?></p>

									<hr>
									<div id="representante_identificacion">
										<?php echo $cedula.$identificacion?>
									</div>

									<hr>
									<strong><i class="fas fa-map-marker-alt mr-1"></i> Dirección</strong>
									<p class="text-muted" id="representante_direccion"><?php echo $h($clienteFacturaDireccion); ?></p>

									<hr>
									<div id="representante_correo">
										<?php echo $correo.$mail; ?>
									</div>

									<hr>
									<strong><i class="fas fa-phone mr-1"></i> Teléfono</strong>
									<p class="text-muted" id="representante_celular"><?php echo $h($clienteFacturaCelular); ?></p>

									<hr>
									<strong><i class="fas fa-print mr-1"></i> Pagos receptados</strong>
									<p class="text-muted" id="representante_pagos"><?php echo $datos['pagos']; ?></p>

									<hr>
									<strong><i class="fas fa-print mr-1"></i> Facturas generadas</strong>
									<p class="text-muted" id="representante_facturas"><?php echo $insAlumno->contarFacturasGeneradas($alumno,'',''); ?></p>
								</div>


								<div class="card-footer">
									<div class="text-right">
										<a href="#" class="btn btn-sm <?php echo $facturacionBloqueada ? 'bg-gray disabled' : 'bg-olive'; ?>" <?php echo $facturacionBloqueada ? 'aria-disabled="true" tabindex="-1"' : 'data-target="#modal-representante" data-toggle="modal"'; ?>>
											<i class="fas fa-pen"></i> Actualizar
										</a>
									</div>
								</div>

								<!-- /.card-body -->
							</div>
						</div>

						<div class="col-md-9">
							<div class="card">
								<div class="card-header p-2">
									<div class="row align-items-end">
										<!-- Fecha inicio -->
										<div class="col-md-4">
											<div class="form-group mb-0">
												<label for="fecha_inicio">Fecha inicio</label>
												<div class="input-group input-group-sm">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
													</div>
													<input type="date" class="form-control form-control-sm" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" <?php echo $facturacionBloqueada ? 'disabled' : ''; ?>>
												</div>
											</div>
										</div>

										<!-- Fecha fin -->
										<div class="col-md-4">
											<div class="form-group mb-0">
												<label for="fecha_fin">Fecha fin</label>
												<div class="input-group input-group-sm">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
													</div>
													<input type="date" class="form-control form-control-sm" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>" <?php echo $facturacionBloqueada ? 'disabled' : ''; ?>>
												</div>
											</div>
										</div>

										<!-- Botón -->
										<div class="col-md-4">
											<div class="form-group mb-0 d-flex justify-content-center">
												<a href="#" id="btn-generar-factura"
													class="btn btn-sm bg-lightblue btn-ctrl-sm <?php echo $disabled; ?>"
													<?php echo $facturacionBloqueada ? 'aria-disabled="true"' : 'data-toggle="modal" data-target="#modal-factura"'; ?>>
													<i class="fas fa-print"></i> Generar Factura
												</a>
											</div>
										</div>
									</div>
								</div><!-- /.card-header -->

								<div class="card-body">
									<div class="tab-content">
										<!-- /.tab-pane -->
										<div class="active tab-pane" id="pension">

											<p class="lead mb-0">Pagos receptados</p>

											<div class="tab-content" id="custom-content-above-tabContent">
												<table class="table table-bordered table-striped table-sm">
													<thead>
														<tr>
															<th>No</th>
															<th>Fecha</th>
															<th>Pago</th>
															<th>Forma de pago</th>
															<th>Detalle</th>
															<th>Alumno</th>
															<th>Selección</th>
														</tr>
													</thead>
													<tbody id="tabla_pagos" >
														<?php
															if($facturacionBloqueada){
																echo '<tr><td colspan="7" class="text-center text-muted">Asigne un representante al alumno para consultar pagos facturables.</td></tr>';
															}else{
																echo $insAlumno->listarPagosFactura($alumno,$fecha_inicio, $fecha_fin);
															}
														?>
													</tbody>
												</table>
											</div>

											<div class="card-footer">
											</div>

											<div class="tab-custom-content">
												<p class="lead mb-0">Facturas generadas</p>
											</div>
											<div class="tab-content" id="custom-content-above-tabContent">
												<table class="table table-bordered table-striped table-sm">
													<thead>
														<tr>
															<th>No</th>
															<th>Fecha</th>
															<th>Pago</th>
															<th>Detalle</th>
															<th>Estado</th>
															<th style="width:280px;">Opciones</th>
														</tr>
													</thead>
													<tbody id="tabla_facturas" >
														<?php
															echo $insAlumno->listarFacturasGeneradas($alumno,'','');
														?>
													</tbody>
												</table>
											</div>

										</div>
									</div>
									<!-- /.tab-content -->
								</div><!-- /.card-body -->
							</div>
							<!-- /.card -->
						</div>
					</div>
				</div>
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

	<div class="modal fade" id="modal-representante">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<form class="FormularioAjax" id="quickForm" action="<?php echo APP_URL; ?>app/ajax/facturasAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
					<input type="hidden" name="modulo_facturas" value="ACTUALIZAR_REPRESENTANTE">
					<input type="hidden" name="usuario" value="<?php echo $_SESSION['usuario']; ?>">
					<input type="hidden" name="repre_id" value="<?php echo $datos['repre_id']; ?>">
					<input type="hidden" name="factura_a_nombre" value="<?php echo $h($datos['repre_factura_a_nombre'] ?? 'REPRESENTANTE'); ?>">

					<div class="modal-header bg-olive py-2 px-3">
						<h6 class="modal-title mb-0"><?php echo $h($clienteFacturaOrigen.' - '.$clienteFacturaNombre); ?></h6>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<div class="modal-body">

						<div class="form-group form-group-sm">
							<label for="identificacion">Identificación</label>
							<input type="text" class="form-control form-control-sm" id="identificacion" name="identificacion" required utocomplete="off" value="<?php echo $h($clienteFacturaIdentificacion); ?>">
						</div>
						<div class="form-group form-group-sm">
							<label for="direccion">Dirección</label>
							<input type="text" class="form-control form-control-sm" id="direccion" name="direccion" required utocomplete="off" value="<?php echo $h($clienteFacturaDireccion); ?>">
						</div>
						<div class="form-group form-group-sm">
							<label for="correo">Correo</label>
							<input type="email" class="form-control form-control-sm" id="correo" name="correo" required utocomplete="off" value="<?php echo $h($clienteFacturaCorreo); ?>">
						</div>
						<div class="form-group form-group-sm">
							<label for="celular">Teléfono</label>
							<input type="text" class="form-control form-control-sm" id="celular" name="celular" required utocomplete="off" value="<?php echo $h($clienteFacturaCelular); ?>">
						</div>
					</div>
					<div class="modal-footer justify-content-between py-2 px-3">
						<button type="button" class="btn bg-gray btn-sm" data-dismiss="modal">Cerrar</button>
						<button type="submit" class="btn bg-olive btn-sm">Guardar</button>
					</div>
				</form>
			</div>
			<!-- /.modal-content -->
		</div>
	<!-- /.modal-dialog -->
	</div>

	<div class="modal fade" id="modal-factura" tabindex="-1">
		<div class="modal-dialog modal-xl">
			<div class="modal-content border">

			<!-- HEADER -->
			<div class="modal-header bg-lightblue py-2 px-3">
				<h5 class="modal-title"><i class="fas fa-file-invoice-dollar mr-2"></i> Factura Electrónica</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>

			<!-- BODY -->
			<div class="modal-body">
				<div class="alert alert-warning py-2">
					<i class="fas fa-exclamation-triangle mr-1"></i>
					Vista previa. La validez tributaria requiere firma electronica y autorizacion del SRI.
				</div>

				<!-- LOGO Y DATOS EMISOR -->
				<div class="row mb-3">
				<div class="col-md-4 text-center">
					<img src="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png" alt="Logo CDJG" class="img-fluid mb-2" style="max-height:80px;">
				</div>
				<div class="col-md-8">
					<h5 class="font-weight-bold mb-1"><?php echo $h($sriEmisor['razon_social'] ?? ''); ?></h5>
					<p class="mb-1"><strong>R.U.C.:</strong> <?php echo $h($sriEmisor['ruc'] ?? ''); ?></p>
					<p class="mb-1"><strong>Direccion Matriz:</strong> <?php echo $h($sriEmisor['direccion_matriz'] ?? ''); ?></p>
					<p class="mb-1"><strong>Direccion Sucursal:</strong> <?php echo $h($sriEmisor['direccion_establecimiento'] ?? ''); ?></p>
					<p class="mb-1"><strong>Teléfonos:</strong> 0995762732</p>
					<p class="mb-0"><strong>Obligado a llevar contabilidad:</strong> <?php echo $h($sriEmisor['obligado_contabilidad'] ?? 'NO'); ?></p>
				</div>
				</div>

				<!-- DATOS FACTURA Y CLIENTE -->
				<div class="row mb-3">
				<!-- FACTURA -->
				<div class="col-md-6 border p-2">
					<h6 class="font-weight-bold">Factura</h6>
					<p class="mb-1"><strong>No.:</strong> Se asigna al guardar</p>
					<div class="border rounded p-2 mb-2 text-muted small">La clave de acceso y el XML se generan al guardar la factura.</div>
					<p class="mb-1"><strong>Clave de Acceso:</strong> Pendiente</p>
					<p class="mb-1"><strong>Numero de Autorizacion:</strong> Pendiente de autorizacion SRI</p>
					<p class="mb-1"><strong>Fecha Autorizacion:</strong> Pendiente</p>
					<p class="mb-1"><strong>Ambiente:</strong> <?php echo $sriAmbiente; ?></p>
					<p class="mb-1"><strong>Emision:</strong> Normal</p>
					<p class="mb-0"><strong>Esquema:</strong> Offline</p>
				</div>
				<!-- CLIENTE -->
				<div class="col-md-6 border p-2">
					<h6 class="font-weight-bold">Datos del Cliente</h6>
					<p class="mb-1"><strong>Cliente:</strong> <?php echo $h($clienteFacturaNombre); ?></p>
					<p class="mb-1"><strong>Factura:</strong> <?php echo $h($clienteFacturaOrigen); ?></p>
					<p class="mb-1"><strong>Identificación:</strong> <?php echo $h($clienteFacturaIdentificacion); ?></p>
					<p class="mb-1"><strong>Dirección:</strong> <?php echo $h($clienteFacturaDireccion); ?></p>
					<p class="mb-1"><strong>Teléfono:</strong> <?php echo $h($clienteFacturaCelular); ?></p>
					<p class="mb-0"><strong>Email:</strong> <?php echo $h($clienteFacturaCorreo); ?></p>
				</div>
				</div>

				<!-- DETALLE FACTURA -->
				<div class="table-responsive mb-3">
				<table class="table table-sm table-bordered">
					<thead class="thead-light">
					<tr>
						<th>Código</th>
						<th class="text-right">Cantidad</th>
						<th>Detalle</th>
						<th class="text-right">Precio Unitario</th>
						<th class="text-right">Descuento</th>
						<th class="text-right">Precio Total</th>
					</tr>
					</thead>
					<tbody id="detalle-factura">
					<!-- Aquí se cargan los pagos seleccionados -->
					</tbody>
					<tfoot>
					<tr>
						<th colspan="5" class="text-right">Total</th>
						<th class="text-right" id="total-factura">0.00</th>
					</tr>
					</tfoot>
				</table>
				</div>

				<!-- INFORMACIÓN ADICIONAL Y TOTALES -->
				<div class="row">
					<div class="col-md-6">
						<h6 class="font-weight-bold">Información Adicional</h6>
						<p><strong>Usuario:</strong> <?php echo $h($_SESSION['usuario'] ?? 'Sistema'); ?></p>
						<div class="form-group mb-2">
							<label class="font-weight-bold mb-1" for="factura_forma_pago">Forma de Pago:</label>
							<select class="form-control form-control-sm" id="factura_forma_pago" name="forma_pago">
								<?php foreach(($sriConfig['formas_pago'] ?? []) as $codigo => $nombre){ ?>
									<option value="<?php echo $h($codigo); ?>" <?php echo ((string)$codigo === $sriFormaPago) ? 'selected' : ''; ?>><?php echo $h($codigo.' - '.$nombre); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="col-md-6">
						<table class="table table-sm">
						<tr>
							<td class="text-right"><b>SUBTOTAL No objeto IVA</b>:</td>
							<td class="text-right">0.00</td>
						</tr>
						<tr>
							<td class="text-right"><b>SUBTOTAL Exento IVA</b>:</td>
							<td class="text-right">0.00</td>
						</tr>
						<tr>
							<td class="text-right"><b>SUBTOTAL 0%</b>:</td>
							<td class="text-right" id="subtotal0">0.00</td>
						</tr>
						<tr>
							<td class="text-right"><b>SUBTOTAL <?php echo number_format($sriIvaDefault, 0); ?>%</b>:</td>
							<td class="text-right" id="subtotalIva">0.00</td>
						</tr>
						<tr>
							<td class="text-right"><b>IVA <?php echo number_format($sriIvaDefault, 0); ?>%</b>:</td>
							<td class="text-right" id="ivaValor">0.00</td>
						</tr>
						<tr class="bg-light">
							<td class="text-right"><b>VALOR TOTAL</b>:</td>
							<td class="text-right font-weight-bold" id="total">0.00</td>
						</tr>
						</table>
					</div>
				</div>
			</div>

			<!-- FOOTER -->
			<div class="modal-footer justify-content-between py-2 px-3">
				<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
				<i class="fas fa-times mr-1"></i> Cerrar
				</button>
				<button type="button" id="btn-guardar-factura" class="btn bg-lightblue btn-sm">
				<i class="fas fa-paper-plane mr-1"></i> Emitir Factura
				</button>
			</div>
			</div>
		</div>
	</div>










	<!-- jQuery -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- Select2 -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/select2/js/select2.full.min.js"></script>
	<!-- Bootstrap4 Duallistbox -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
	<!-- InputMask -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/moment/moment.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/inputmask/jquery.inputmask.min.js"></script>
	<!-- date-range-picker -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/daterangepicker/daterangepicker.js"></script>
	<!-- bootstrap color picker -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
	<!-- Tempusdominus Bootstrap 4 -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
	<!-- Bootstrap Switch -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
	<!-- BS-Stepper -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bs-stepper/js/bs-stepper.min.js"></script>
	<!-- AdminLTE App -->
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>

	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/ekko-lightbox/ekko-lightbox.min.js"></script>

	<script>
		const facturacionBloqueada = <?php echo $facturacionBloqueada ? 'true' : 'false'; ?>;
		const facturacionBloqueadaTitulo = <?php echo json_encode($facturacionBloqueadaTitulo, JSON_UNESCAPED_UNICODE); ?>;
		const facturacionBloqueadaTexto = <?php echo json_encode($facturacionBloqueadaTexto, JSON_UNESCAPED_UNICODE); ?>;
	</script>

	<script>
		$(document).ready(function(){
			if(facturacionBloqueada){
				Swal.fire({
					title: facturacionBloqueadaTitulo,
					text: facturacionBloqueadaTexto,
					icon: "warning",
					confirmButtonText: "Entendido"
				});
				return;
			}

		$("#fecha_inicio, #fecha_fin").on("change", function(){
			let fecha_inicio = $("#fecha_inicio").val();
			let fecha_fin = $("#fecha_fin").val();
			let alumno = "<?php echo $alumno; ?>";

			$.ajax({
				url: "<?php echo APP_URL; ?>app/ajax/facturasAjax.php",
				type: "POST",
				data: {
					modulo_facturas: "CONSULTAR_FACTURAS",
					alumno: alumno,
					fecha_inicio: fecha_inicio,
					fecha_fin: fecha_fin
				},
				beforeSend: function(){
					$("#tabla_pagos").html("<tr><td colspan='8'>Cargando...</td></tr>");
					$("#tabla_facturas").html("<tr><td colspan='8'>Cargando...</td></tr>");
				},
				success: function(respuesta){
					let datos = typeof respuesta === "string" ? JSON.parse(respuesta) : respuesta;

					// Actualizar tablas
					$("#tabla_pagos").html(datos.pagos);
					$("#tabla_facturas").html(datos.facturas);

					// Actualizar información representante
					if(datos.representante){
						$("#representante_nombre").text(datos.representante.nombre);
						$("#representante_identificacion").text(datos.representante.identificacion);
						$("#representante_direccion").text(datos.representante.direccion);
						$("#representante_correo").text(datos.representante.correo);
						$("#representante_celular").text(datos.representante.celular);
						$("#representante_factura_a_nombre").text(datos.representante.factura_a_nombre);
						$("#representante_pagos").text(datos.representante.pagos);
						$("#representante_facturas").text(datos.representante.facturas);
					}
				},
				error: function(xhr, status, error){
					console.log("Error AJAX:", error);
				}
			});
		});

	});
	</script>

	<script>
		const valoresIncluyenIva = <?php echo !empty($sriConfig['valores_incluyen_iva']) ? 'true' : 'false'; ?>;
		const alumnoFactura = "<?php echo $alumno; ?>";

		document.getElementById("btn-generar-factura").addEventListener("click", function(event) {
			if(facturacionBloqueada){
				event.preventDefault();
				event.stopPropagation();
				Swal.fire({
					title: facturacionBloqueadaTitulo,
					text: facturacionBloqueadaTexto,
					icon: "warning",
					confirmButtonText: "Entendido"
				});
				return false;
			}

			let pagosSeleccionados = document.querySelectorAll(".chk-pago:checked");
			if(pagosSeleccionados.length === 0){
				event.preventDefault();
				event.stopPropagation();
				Swal.fire({title: "Seleccione pagos", text: "Debe seleccionar al menos un pago pendiente para generar la factura.", icon: "warning"});
				return false;
			}

			let tbody = document.getElementById("detalle-factura");
			let total = 0;
			let subtotal0 = 0;
			let subtotalIva = 0;
			let ivaValor = 0;

			tbody.innerHTML = "";

			pagosSeleccionados.forEach(pago => {
				let codigo = pago.getAttribute("data-codigo");
				let detalle = pago.getAttribute("data-detalle");
				let valor = parseFloat(pago.getAttribute("data-valor")) || 0;
				let tarifa = parseFloat(pago.getAttribute("data-tarifa")) || 0;
				let base = valor;
				let ivaLinea = 0;
				let totalLinea = valor;

				if(tarifa > 0){
					if(valoresIncluyenIva){
						base = valor / (1 + (tarifa / 100));
						ivaLinea = valor - base;
						totalLinea = valor;
					}else{
						ivaLinea = base * (tarifa / 100);
						totalLinea = base + ivaLinea;
					}
					subtotalIva += base;
				}else{
					subtotal0 += base;
				}

				total += totalLinea;
				ivaValor += ivaLinea;

				let row = `
					<tr>
						<td>${codigo}</td>
						<td class="text-right">1.00</td>
						<td>${detalle}</td>
						<td class="text-right">${base.toFixed(2)}</td>
						<td class="text-right">0.00</td>
						<td class="text-right">${base.toFixed(2)}</td>
					</tr>
				`;
				tbody.insertAdjacentHTML("beforeend", row);
			});

			document.getElementById("total-factura").innerText = (subtotal0 + subtotalIva).toFixed(2);
			document.getElementById("subtotal0").innerText = subtotal0.toFixed(2);
			document.getElementById("subtotalIva").innerText = subtotalIva.toFixed(2);
			document.getElementById("ivaValor").innerText = ivaValor.toFixed(2);
			document.getElementById("total").innerText = total.toFixed(2);
		});

		$("#btn-guardar-factura").on("click", function(){
			const pagos = $(".chk-pago:checked").map(function(){ return this.value; }).get();
			if(pagos.length === 0){
				Swal.fire({title: "Seleccione pagos", text: "Debe seleccionar al menos un pago pendiente para generar la factura.", icon: "warning"});
				return;
			}

			const formData = new FormData();
			formData.append("modulo_facturas", "GENERAR_FACTURA_ELECTRONICA");
			formData.append("alumno", alumnoFactura);
			formData.append("forma_pago", $("#factura_forma_pago").val() || "");
			pagos.forEach(id => formData.append("pagos[]", id));

			const boton = $(this);
			boton.prop("disabled", true);

			$.ajax({
				url: "<?php echo APP_URL; ?>app/ajax/facturasAjax.php",
				type: "POST",
				data: formData,
				processData: false,
				contentType: false,
				dataType: "json",
				success: function(respuesta){
					if(["factura_generada", "factura_autorizada", "factura_enviada", "factura_error_sri"].includes(respuesta.tipo)){
						$("#modal-factura").modal("hide");
						Swal.fire({
							title: respuesta.titulo,
							html: `<p>${respuesta.texto}</p><p><strong>No.:</strong> ${respuesta.numero || ""}</p><p class="small"><strong>Clave:</strong> ${respuesta.clave_acceso || ""}</p><p><strong>Estado:</strong> ${respuesta.estado_sri || ""}</p><p><a href="${respuesta.ride_url}" target="_blank">Ver RIDE</a> | <a href="${respuesta.xml_url}">Descargar XML</a></p>`,
							icon: respuesta.icono
						});
						$("#fecha_inicio").trigger("change");
					}else{
						Swal.fire({title: respuesta.titulo || "Atencion", text: respuesta.texto || "No fue posible completar la accion.", icon: respuesta.icono || "warning"});
					}
				},
				error: function(){
					Swal.fire({title: "Error", text: "No fue posible comunicarse con el servidor.", icon: "error"});
				},
				complete: function(){
					boton.prop("disabled", false);
				}
			});
		});

		function ejecutarAccionSri(facturaId, modulo, cargando, textoEspera){
			const formData = new FormData();
			formData.append("modulo_facturas", modulo);
			formData.append("factura_id", facturaId);

			Swal.fire({
				title: cargando,
				text: textoEspera || "Espere un momento mientras se comunica con el SRI.",
				allowOutsideClick: false,
				didOpen: () => Swal.showLoading()
			});

			$.ajax({
				url: "<?php echo APP_URL; ?>app/ajax/facturasAjax.php",
				type: "POST",
				data: formData,
				processData: false,
				contentType: false,
				dataType: "json",
				success: function(respuesta){
					Swal.fire({
						title: respuesta.titulo || "Resultado SRI",
						text: respuesta.texto || "Proceso finalizado.",
						icon: respuesta.icono || "info"
					});
					$("#fecha_inicio").trigger("change");
				},
				error: function(){
					Swal.fire({title: "Error", text: "No fue posible comunicarse con el servidor.", icon: "error"});
				}
			});
		}

		$(document).on("click", ".btn-emitir-sri", function(){
			ejecutarAccionSri($(this).data("id"), "EMITIR_FACTURA_SRI", "Emitiendo factura");
		});

		$(document).on("click", ".btn-consultar-sri", function(){
			ejecutarAccionSri($(this).data("id"), "CONSULTAR_FACTURA_SRI", "Consultando autorizacion");
		});

		$(document).on("click", ".btn-enviar-factura", function(){
			const facturaId = $(this).data("id");
			const email = $(this).data("email") || "";
			Swal.fire({
				title: "Enviar factura",
				text: "Se enviara el RIDE y XML autorizado a " + email + ".",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Enviar",
				cancelButtonText: "Cancelar"
			}).then((result) => {
				if(result.isConfirmed){
					ejecutarAccionSri(facturaId, "ENVIAR_FACTURA_CORREO", "Enviando factura", "Espere un momento mientras se prepara y envia el correo.");
				}
			});
		});
	</script>

  </body>
</html>
