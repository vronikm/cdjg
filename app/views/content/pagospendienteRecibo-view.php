<?php

	use app\controllers\pagosController;

	include 'app/lib/barcode.php';
	
	$generator = new barcode_generator();
	$symbology="qr";
	$optionsQR=array('sx'=>4,'sy'=>4,'p'=>-10);	


	$insAlumno = new pagosController();	

	$pagoid=$insLogin->limpiarCadena($url[1]);
	//$mensaje=$insLogin->limpiarCadena($url[2]);	

	$alerta = "";

	// Capturamos el valor enviado en la URL
	$envio = $url[2] ?? ""; // <---- Asegúrate que $url esté disponible. $url[2] sería 1 o 0

	if($envio !== ""){
		if($envio == "1"){
			$alerta = [
				"tipo" => "simple",
				"titulo" => "Correo enviado",
				"texto" => "El correo fue enviado exitosamente.",
				"icono" => "success"
			];
		} elseif($envio == "0"){
			$alerta = [
				"tipo" => "simple",
				"titulo" => "Error de envío",
				"texto" => "No se pudo enviar el correo. Por favor, intente nuevamente.",
				"icono" => "error"
			];
		}
	}

	$datos=$insAlumno->generarReciboPendiente($pagoid);
	
	if($datos->rowCount()==1){
		$datos=$datos->fetch(); 

		$fecha_recibo = strrev($datos["transaccion_recibo"]);
		$first12Chars =  strrev(substr($datos["transaccion_recibo"], 0, 12));
		$nombre_sede  = mb_convert_encoding($datos["escuela_nombre"], 'ISO-8859-1', 'UTF-8');
		
		$pairs = [];
		$length = strlen($first12Chars);

		for ($i = 0; $i < $length; $i += 2) {
			$pairs[] = substr($first12Chars, $i, 2);
		}
		$recibo_hora = $pairs[4].":".$pairs[2].":".$pairs[0];
		
	}else{
		include "<?php echo APP_URL; ?>/app/views/inc/error_alert.php";
	}

	$sede=$insAlumno->informacionSede($datos["alumno_sedeid"]);
	if($sede->rowCount()==1){
		$sede=$sede->fetch(); 
	}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Recibo</title>
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
	<!-- dropzonejs -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/dropzone/min/dropzone.min.css">
	
	<!-- Theme style -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">


	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>

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
							<h1 class="m-0">Recibo <?php echo $datos['RUBRO']." ".$datos['pago_periodo'] ; ?></h1>
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
			
			<section class="content">
				<div class="container-fluid">
					<div class="row">
						<div class="col-1">
						</div>
						<div class="col-10">
							<!-- Main content -->
							<div class="invoice p-3 mb-3">							
								<!-- info row -->
								<div class="row invoice-info">
									<div class="col-sm-6 invoice-col">										
										<address class="text-center">												
											<img src="<?php echo APP_URL.'app/views/imagenes/fotos/sedes/'.$sede['sede_foto'] ?>" style="width: 150px; height: 190px;"/>
											<br>Dirección: <?php echo $sede["sede_direccion"]; ?><br>
											Celular: <?php echo $sede["sede_telefono"]; ?>									
										</address>
									</div>
									<!-- /.col -->
									<div class="col-sm-6 invoice-col">									
										<address class="text-center">	
											<strong class="profile-username"><?php echo $nombre_sede ?> </strong><br><br>								
											<div class="row">
												<div class="col-12 table-responsive">
													<div class="row">
														<div class="col-4"></div>														
														<div class="col-4">
															<table class="table table-striped table-sm">
																<thead>
																	<tr style="font-size: 12px">
																		<th>DIA</th>
																		<th>MES</th>
																		<th>AÑO</th>																
																	</tr>
																</thead>
																<tbody>
																	<tr style="font-size: 12px">
																		<td><?php echo  date('d', strtotime($datos['transaccion_fecharegistro'])); ?></td>
																		<td><?php echo date('m', strtotime($datos['transaccion_fecharegistro'])); ?></td>
																		<td><?php echo date('Y', strtotime($datos['transaccion_fecharegistro'])); ?></td>												
																	</tr>														
																</tbody>
															</table>
														</div>
														<div class="col-4"></div>
													</div>	
												</div>
												<!-- /.col -->
											</div>
										</address>
									</div>
									<!-- /.col -->								
								</div>
								<!-- Table row -->
								<div class="row">
									<div class="col-12 table-responsive">
										<table class="table table-striped table-sm">											
											<tbody>
												<tr>													
													<th style="width:200px;">POR</th>	
													<th>$ <?php echo $datos['transaccion_valor']; ?></th>
													<th class="text-right">RECIBO </th>
													<th class="text-center"><?php echo $datos['transaccion_recibo']; ?></th>																							
												</tr>	
												<tr>													
													<th>Recibo de: </th>	
													<td colspan="3"><?php echo $datos['alumno_primernombre']." ".$datos['alumno_segundonombre']." ".$datos['alumno_apellidopaterno'].$datos['alumno_apellidomaterno']." (".date('Y', strtotime($datos['alumno_fechanacimiento'])).")"; ?></td>																										
												</tr>	
												<tr>													
													<th>La Cantidad de: </th>	
													<td colspan="3"><?php echo ucfirst($insAlumno->textoLetras($datos['transaccion_valor'])); ?></td>																										
												</tr>		
												<tr>													
													<th>Por Concepto de: </th>	
													<td colspan="3"><?php echo $datos['RUBRO']." ".$datos['TORNEO']." ".$datos['pago_periodo'].". ".$datos['transaccion_concepto']; ?></td>																										
												</tr>	
												<tr>													
													<th>Forma de pago: </th>	
													<td colspan="3"><?php echo $datos['FORMAPAGO']; ?></td>																										
												</tr>							
											</tbody>
										</table>
									</div>
									<!-- /.col -->
								</div>
								<!-- /.row -->
								<div class="row">
									<div class="col-4">
										<p class="lead">MONTO</p>

										<div class="table-responsive table-sm">
											<table class="table">
												<tr>
													<th style="width:50%">SUBTOTAL:</th>
													<td>$ <?php echo number_format($datos['pago_valor'] + $datos['pago_saldo'], 2); ?></td>
												</tr>
												<tr>
													<th>ABONO:</th>
													<td>$ <?php echo $datos['transaccion_valor']; ?></td>
												</tr>
												<tr>
													<th>SALDO:</th>
													<td>$ <?php echo number_format($datos['transaccion_valorcalculado'] - $datos['transaccion_valor'], 2); ?></td>
												</tr>												
											</table>
										</div>
									</div>
									<!-- accepted payments column -->
									<div class="col-4">										
									</div>

									<div class="col-4">										
										<?php											
											$svg = $generator->render_svg($symbology,"Recibo ".$datos["transaccion_recibo"]. "\n".$datos["transaccion_fecharegistro"]. " | ".$recibo_hora."\n".$nombre_sede."\n".$sede["sede_telefono"]."\n".$sede["sede_email"], $optionsQR);											
											echo $svg;  
										?>								
									</div>
									<!-- /.col -->
									
									<!-- /.col -->
								</div>
								<!-- /.row -->

								<!-- this row will not appear when printing -->
								<div class="row no-print">
									<div class="col-12">
                                        <a href="<?php echo APP_URL.'pagospendienteReciboEnvio/'.$pagoid.'/'; ?> " class="btn btn-success btn-sm float-right" style="margin-right: 240px;" id="btn_correo"> <i class="fas fa-credit-card"></i> Enviar recibo</a>                                        
										<a href="<?php echo APP_URL.'pagospendienteReciboPDF/'.$pagoid.'/'; ?> " class="btn btn-dark float-right btn-sm" style="margin-right: 5px;" target="_blank"> <i class="fas fa-print"></i> Ver recibo</a>
										<button class="btn btn-dark btn-sm" onclick="cerrarVentana()">Cerrar</button>
									</div>
								</div>
							</div>
							<!-- /.invoice -->
						</div><!-- /.col -->
						<div class="col-1">
						</div>
					</div><!-- /.row -->
				</div><!-- /.container-fluid -->
			</section>      
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
	<!-- dropzonejs -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/dropzone/min/dropzone.min.js"></script>
	<!-- AdminLTE App -->
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>		
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>

	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>
    
	<script>
        // Esta función se llama cuando el botón es clickeado
        function printPage() {
            window.addEventListener("load", window.print());
        }

		function cerrarVentana() {
			window.close();
		}
    </script>

	<?php if($alerta): ?>
		<script>
		document.addEventListener("DOMContentLoaded", function() {
			let alerta = <?php echo json_encode($alerta); ?>;
			alertas_ajax(alerta); // Usamos tu función de alertas que ya tienes en ajax.js
		});
		</script>
	<?php endif; ?>
	
  </body>
</html>