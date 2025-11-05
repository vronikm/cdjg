<?php
	date_default_timezone_set("America/Guayaquil");

	use app\controllers\facturasController;
	include 'app/lib/barcode.php';
	
	$insFactura = new facturasController();	
	$generator = new barcode_generator();

	$symbology = "code128"; // Cambiar tipo de código
	$options = array('sx'=>1,'sy'=>0.5,'p'=>1); // Ajustar tamaño y padding
	$claveAcceso = "1607202501110326917900120030040000128821234567814";
	

	$alumno=$insFactura->limpiarCadena($url[1]);

	$fecha_inicio= date('Y-m-d');
	$fecha_fin= date('Y-m-d');

	$datos=$insFactura->BuscarAlumnoFactura($alumno, $fecha_inicio,$fecha_fin);	

	if($datos->rowCount()==1){
		$datos=$datos->fetch(); 
		
		/* validar correo */
		$error='N';
		$disabled='';

		if (!filter_var($datos['repre_correo'], FILTER_VALIDATE_EMAIL)) {
			$mail = '<p class="text-danger">'.$datos['repre_correo'].'</p>';
			$correo = '<strong class="text-danger"><i class="fas fa-envelope mr-1"></i> Correo no válido</strong>';
			$error='S';
			$disabled='disabled';
		}else {
			$mail = '<p class="text-muted">'.$datos['repre_correo'].'</p>';
			$correo = '<strong><i class="fas fa-envelope mr-1"></i> Correo</strong>';
		}

		/* validar cedula */
		if ($datos['repre_tipoidentificacion'] == 'CED') {
			if (!$insFactura->validarCedula($datos['repre_identificacion'])) {
				$identificacion = '<p class="text-danger">'.$datos['repre_identificacion'].'</p>';
				$cedula = '<strong class="text-danger"><i class="fas fa-address-card mr-1"></i> Cédula no válida</strong>';
				$error='S';
				$disabled='disabled';
			}else {
				$identificacion = '<p class="text-muted">'.$datos['repre_identificacion'].'</p>';
				$cedula = '<strong><i class="fas fa-address-card mr-1"></i> Identificación</strong>';
			}
		}else{
			$cedula = '<p class="text-muted">'.$datos['repre_identificacion'].'</p>';
		}

	}else{
		include "<?php echo APP_URL; ?>/app/views/inc/error_alert.php";
	}

	$datosEscuela=$insFactura->informacionEscuela();
	if($datosEscuela->rowCount()==1){
		$datosEscuela=$datosEscuela->fetch(); 
	}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Facturas</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/1104523691001_2.png">
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
									<h3 class="card-title">Representante</h3>
								</div>
								
								<!-- Bloque Representante -->
								<div class="card-body">
									<strong><i class="fas fa-user mr-1"></i> Nombres</strong>
									<p class="text-muted" id="representante_nombre"><?php echo $datos['representante']?></p>

									<hr>									
									<div id="representante_identificacion">
										<?php echo $cedula.$identificacion?>									
									</div>

									<hr>
									<strong><i class="fas fa-map-marker-alt mr-1"></i> Dirección</strong>									
									<p class="text-muted" id="representante_direccion"><?php echo $datos['repre_direccion']; ?></p>

									<hr>
									<div id="representante_correo">
										<?php echo $correo.$mail; ?>
									</div>

									<hr>
									<strong><i class="fas fa-phone mr-1"></i> Teléfono</strong>
									<p class="text-muted" id="representante_celular"><?php echo $datos['repre_celular']; ?></p>

									<hr>
									<strong><i class="fas fa-print mr-1"></i> Pagos receptados</strong>
									<p class="text-muted" id="representante_pagos"><?php echo $datos['pagos']; ?></p>

									<hr>
									<strong><i class="fas fa-print mr-1"></i> Facturas generadas</strong>
									<p class="text-muted" id="representante_facturas"><?php echo $datos['pagos']; ?></p>
								</div>
								
								
								<div class="card-footer">
									<div class="text-right">													
										<a href="#" class="btn btn-sm bg-olive" data-target="#modal-representante" data-toggle="modal">
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
													<input type="date" class="form-control form-control-sm" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
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
													<input type="date" class="form-control form-control-sm" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
												</div>
											</div>
										</div>

										<!-- Botón -->
										<div class="col-md-4">
											<div class="form-group mb-0 d-flex justify-content-center">
												<a href="#" id="btn-generar-factura" 
													class="btn btn-sm bg-lightblue btn-ctrl-sm <?php echo $disabled; ?>" 
													data-toggle="modal" data-target="#modal-factura">
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
															<th>Detalle</th>																											
															<th>Alumno</th>
															<th>IVA</th>		
															<th>Selección</th>																
														</tr>
													</thead>
													<tbody id="tabla_pagos" >
														<?php 
															echo $insFactura->listarPagosFactura($alumno,$fecha_inicio, $fecha_fin); 
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
															<th>Alumno</th>	
															<th>IVA</th>	
															<th style="width:280px;">Opciones</th>															
														</tr>
													</thead>
													<tbody id="tabla_facturas" >
														<?php 
															echo $insFactura->listarPagosFactura($alumno,$fecha_inicio,$fecha_fin); 
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

					<div class="modal-header bg-olive py-2 px-3">
						<h6 class="modal-title mb-0"><?php echo $datos['representante']; ?></h6>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<div class="modal-body">
						<div class="form-group form-group-sm">
							<label for="identificacion">Identificación</label>
							<input type="text" class="form-control form-control-sm" id="identificacion" name="identificacion" required utocomplete="off" value="<?php echo $datos['repre_identificacion']; ?>">	
						</div>
						<div class="form-group form-group-sm">
							<label for="direccion">Dirección</label>
							<input type="text" class="form-control form-control-sm" id="direccion" name="direccion" required utocomplete="off" value="<?php echo $datos['repre_direccion']; ?>">	
						</div>
						<div class="form-group form-group-sm">
							<label for="correo">Correo</label>
							<input type="email" class="form-control form-control-sm" id="correo" name="correo" required utocomplete="off" value="<?php echo $datos['repre_correo']; ?>">	
						</div>
						<div class="form-group form-group-sm">
							<label for="celular">Teléfono</label>
							<input type="text" class="form-control form-control-sm" id="celular" name="celular" required utocomplete="off" value="<?php echo $datos['repre_celular']; ?>">	
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

	<form id="form-factura" method="post">
		
		<input type="hidden" name="representante_id" value="<?php echo $datos['repre_id']; ?>">
		<input type="hidden" name="identificacion" value="<?php echo $datos['repre_identificacion']; ?>">
		<input type="hidden" name="nombre" value="<?php echo $datos['representante']; ?>">
		<input type="hidden" name="correo" value="<?php echo $datos['repre_correo']; ?>">
		<input type="hidden" name="direccion" value="<?php echo $datos['repre_direccion']; ?>">

		<input type="hidden" name="establecimiento" value="003">
		<input type="hidden" name="puntoemision" value="004">
		<input type="hidden" name="secuencial" value="000012882">
		<input type="hidden" name="fecha" value="<?php echo date('Y-m-d'); ?>">

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

						<!-- LOGO Y DATOS EMISOR -->
						<div class="row mb-3">
							<div class="col-md-4 text-center">
								<img src="<?php echo APP_URL.'app/views/dist/img/logos/'.$datosEscuela['escuela_logo'] ?>" style="width: 220px; height: 130px;"/>
								
							</div>
							<div class="col-md-8">
								<h5 class="font-weight-bold mb-1"><?php echo $datosEscuela['escuela_nombre']; ?></h5>
								<p class="mb-1"><strong>R.U.C.:</strong> <?php echo $datosEscuela['escuela_ruc']; ?></p>
								<p class="mb-1"><strong>Dirección Matriz:</strong> <?php echo $datosEscuela['escuela_direccion']; ?></p>
								<p class="mb-1"><strong>Dirección Sucursal:</strong> <?php echo $datosEscuela['escuela_direccion']; ?></p>
								<p class="mb-1"><strong>Teléfonos:</strong> <?php echo $datosEscuela['escuela_telefono']; ?></p>
								<p class="mb-0"><strong>Obligado a llevar contabilidad:</strong> NO</p>
							</div>
						</div>

						<!-- DATOS FACTURA Y CLIENTE -->
						<div class="row mb-3">
							<!-- FACTURA -->
							<div class="col-md-6 border p-2">
								<h6 class="font-weight-bold">Factura</h6>
								<p class="mb-1"><strong>No.:</strong> 003-004-000012882</p>
								<p class="mb-1">
									<?php												
										$svg = $generator->render_svg($symbology, $claveAcceso, $options); 
										echo $svg;
									?>
								</p>
								<p class="mb-1"><strong>Clave de Acceso:</strong> 1607202501110326917900120030040000128821234567814</p>
								<p class="mb-1"><strong>Número de Autorización:</strong> 1607202501110326917900120030040000128821234567814</p>
								<p class="mb-1"><strong>Fecha Autorización:</strong> 16/07/2025 13:03</p>
								<p class="mb-1"><strong>Ambiente:</strong> Producción</p>
								<p class="mb-1"><strong>Emisión:</strong> Normal</p>
								<p class="mb-0"><strong>Esquema:</strong> Offline</p>
							</div>

							<!-- CLIENTE -->
							<div class="col-md-6 border p-2">
								<h6 class="font-weight-bold">Datos del Cliente</h6>
								<p class="mb-1"><strong>Cliente:</strong> <?php echo $datos['representante']; ?></p>
								<p class="mb-1"><strong>Identificación:</strong> <?php echo $datos['repre_identificacion']; ?></p>
								<p class="mb-1"><strong>Dirección:</strong> <?php echo $datos['repre_direccion']; ?></p>
								<p class="mb-1"><strong>Teléfono:</strong> <?php echo $datos['repre_celular']; ?></p>
								<p class="mb-0"><strong>Email:</strong> <?php echo $datos['repre_correo']; ?></p>
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
								<p><strong>Vendedor:</strong> CHRISTIAN GUTIERREZ</p>
								<p><strong>Forma de Pago:</strong> TARJETA DE CRÉDITO</p>
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
										<td class="text-right"><b>SUBTOTAL 15%</b>:</td>
										<td class="text-right" id="subtotal15">0.00</td>
									</tr>
									<tr>
										<td class="text-right"><b>IVA 15%</b>:</td>
										<td class="text-right" id="iva15">0.00</td>
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
						<button type="submit" class="btn bg-lightblue btn-sm">
							<i class="fas fa-save mr-1"></i> Guardar Factura
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>
	
    
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
		$(document).ready(function(){

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
					let datos = JSON.parse(respuesta);

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
		document.getElementById("btn-generar-factura").addEventListener("click", function() {
			let pagosSeleccionados = document.querySelectorAll(".chk-pago:checked");
			let tbody = document.getElementById("detalle-factura");
			let total = 0;
			let subtotal0 = 0;
			let subtotal15 = 0;
			let iva15 = 0;

			tbody.innerHTML = ""; // limpiar antes de cargar

			pagosSeleccionados.forEach(pago => {
				let pagoId  = pago.getAttribute("data-id");
				let fecha   = pago.getAttribute("data-fecha");
				let codigo  = pago.getAttribute("data-codigo");
				let detalle = pago.getAttribute("data-detalle");
				let valor   = parseFloat(pago.getAttribute("data-valor"));
				let iva     = parseFloat(pago.getAttribute("data-iva")) || 0;

				total += valor;
				if (iva === 0) {
					subtotal0 += valor;
				} else if (iva === 15) {
					subtotal15 += valor;
					iva15 += valor * 0.15;
				}

				let row = `
					<tr data-id="${pagoId}">
						<td>${codigo}</td>
						<td class="text-right">1.00</td>
						<td class="detalle-concepto">${detalle}</td>
						<td class="text-right">${valor.toFixed(2)}</td>
						<td class="text-right">0.00</td>
						<td class="text-right detalle-total">${valor.toFixed(2)}</td>
					</tr>
				`;
				tbody.insertAdjacentHTML("beforeend", row);
			});

			document.getElementById("total-factura").innerText = total.toFixed(2);
			document.getElementById("subtotal0").innerText    = subtotal0.toFixed(2);
			document.getElementById("subtotal15").innerText   = subtotal15.toFixed(2);
			document.getElementById("iva15").innerText        = iva15.toFixed(2);
			document.getElementById("total").innerText        = (total + iva15).toFixed(2);
		});

	</script>

  </body>
</html>