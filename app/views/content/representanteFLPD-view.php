<?php	

	use app\controllers\representanteController;

	include 'app/lib/barcode.php';
	
	$generator = new barcode_generator();
	$symbology="qr";
	$optionsQR=array('sx'=>4,'sy'=>4,'p'=>-10);	
	$fechahoy = date('Y-m-d');	

	$insFormulario = new representanteController();	

	$repreid=$insLogin->limpiarCadena($url[1]);

	$datos=$insFormulario->datosRepresentante($repreid);
	
	if($datos->rowCount()==1){
		$datos=$datos->fetch();
		$repre_sedeid = $datos['SEDE'];	
	}else{
		include "<?php echo APP_URL; ?>/app/views/inc/error_alert.php";
	}

	$sede=$insFormulario->informacionSede($repre_sedeid);
	if($sede->rowCount()==1){
		$sede=$sede->fetch(); 
		$club_nombre  = $sede['escuela_nombre'];
		$sede_nombre  = $sede['sede_nombre'];
		$sede_email   = $sede['sede_email'];
        $sede_logo    = $sede['sede_foto'];
	}
	
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Formulario PDP</title>
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
							<h1>PROTECCIÓN DE DATOS PERSONALES</h1>
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
											<img src="<?php echo APP_URL.'app/views/imagenes/fotos/sedes/'.$sede['sede_foto'] ?>" style="width: 160px; height: 190px;"/>									
										</address>
									</div>
									<!-- /.col -->
									<div class="col-sm-5 invoice-col">									
										<address class="text-center"><br><br><br>	
											<strong class="profile-username"><?php echo  $club_nombre; ?> </strong><br><br>
												<p class="lead">Fecha: <?php echo  $fechahoy; ?></p>								
										</address>
									</div>
									<!-- /.col -->								
								</div>
								<!-- Table row -->
								<div class="row" style="margin-right: 25px; margin-left: 25px;">	
									<strong class="profile-username text-center">FORMULARIO DE CONSENTIMIENTO INFORMADO DE RECOLECCIÓN Y TRATAMIENTO DE DATOS PERSONALES</strong><br><br>																	
								</div>
								<!-- /.row -->
								<div class="row">
									<div class="col-12 table-responsive">
										<table class="table table-striped table-sm">											
											<tbody>
												<tr>													
													<th>1. Datos del Titular y Representante del alumno</th>																					
												</tr>	
												<tr>
													<td colspan="3">- Nombre del representante: <?php echo $datos['REPRESENTANTE']; ?><br>
																	- Número de identificación: <?php echo $datos['repre_identificacion']; ?>
													</td>
												</tr>
												<tr>													
													<th>2. Finalidad del tratamiento de datos</th>																					
												</tr>	
												<tr>													
													<td colspan="3">En <?php echo  $club_nombre; ?>, recolectamos y tratamos los datos personales de nuestros alumnos y sus representantes legales con las siguientes finalidades:<br>
																	•	Gestión administrativa y operativa de la inscripción, pagos mensuales y participación del alumno en las actividades del <?php echo  $club_nombre; ?>.<br>
																	•	Comunicación de eventos, horarios de entrenamiento, fechas de torneos, actividades y todo tipo de información relevante.<br>
																	•	Atención de situaciones de emergencia, incluyendo acceso a información de contacto y datos médicos básicos proporcionados por el representante legal.<br>
																	•	Registro de contenido audiovisual (fotos y videos) para la promoción de actividades en redes sociales y material institucional, siempre con el consentimiento del representante del alumno.
													</td>												
												</tr>
												<tr>													
													<th>3. Datos a ser recolectados</th>																					
												</tr>	
												<tr>													
													<td colspan="3">Con el fin de llevar a cabo las finalidades descritas, recolectaremos los siguientes datos personales:<br>
																	•	Datos del alumno: Tipo de identificación, número de identificación, apellido paterno, apellido materno, primer nombre, segundo nombre, nacionalidad, fecha de nacimiento, dirección, tiene hermanos, sexo, fotografías del documento de identificación (anverso y reverso).<br>
																	•	Contacto de emergencia del alumno: Celular, nombre, parentesco.<br>
																	•	Datos del representante: Tipo de identificación, número de identificación, apellido paterno, apellido materno, primer nombre, segundo nombre, parentesco, sexo, dirección, correo, celular y si requiere factura.<br>
																	•	Información médica: Tipo de sangre, peso, talla, enfermedad diagnosticada, medicamentos, alergia a medicamentos, alergia a objetos, cirugías, dispone de carnet de vacunación COVID y vacunación habitual.<br>
																	•	Contenido audiovisual (fotos y videos): Se podrán tomar y usar imágenes de los alumnos en actividades propias del <?php echo  $club_nombre; ?> con fines promocionales o informativos
													</td>												
												</tr>
												<tr>													
													<th>4. Derechos del Titular de los Datos</th>																					
												</tr>	
												<tr>													
													<td colspan="3">El titular de los datos o su representante tiene derecho a acceder, rectificar, cancelar u oponerse al tratamiento de los datos personales en cualquier momento, de conformidad con la Ley de Protección de Datos Personales en Ecuador. Para ejercer estos derechos, puede contactarse a través del correo: <?php echo $sede_email ?></td>												
												</tr>
												<tr>													
													<th>5. Seguridad de los Datos</th>																					
												</tr>	
												<tr>													
													<td colspan="3">El <?php echo  $club_nombre; ?> adopta medidas de seguridad razonables y adecuadas para proteger los datos personales contra el acceso no autorizado, pérdida, destrucción, alteración o uso indebido.</td>												
												</tr>
												<tr>													
													<th>6. Consentimiento</th>																					
												</tr>	
												<tr>													
													<td colspan="3">Declaro que he leído y comprendido los términos de este consentimiento y autorizo al <?php echo  $club_nombre; ?> a recolectar y tratar los datos personales mencionados, en los términos señalados en este documento.</td>												
												</tr>
												<tr>
													<td></td>
												</tr>
												<tr>													
													<td colspan="3">Sí, autorizo el uso de imágenes de mi(s) representado(s) (fotos y videos) en las redes sociales y material promocional del <?php echo  $club_nombre; ?>.<br>
																	Sí, consiento el tratamiento de los datos personales de mi(s) representado(s) con las finalidades descritas.<br>
													</td>												
												</tr>
												<tr>
													<td></td>
												</tr>
											</tbody>
										</table>
									</div>
									<!-- accepted payments column -->
									<div class="col-4">										
									</div>
								</div>
								<!-- /.row -->								

								<!-- this row will not appear when printing -->
								<div class="row no-print">
									<div class="col-12">
										<a href="<?php echo APP_URL.'formularioLPPDF/'.$repreid.'/'; ?> " class="btn btn-dark float-right btn-sm" style="margin-right: 10px;" target="_blank"> <i class="fas fa-print"></i> Ver formulario </a>
										<?php include "./app/views/inc/btn_back.php";?>
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
	
	<!-- AdminLTE App -->
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
		
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>

	<!--script src="app/views/dist/js/main.js" ></script-->
	
	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>
    
	<script>
        // Esta función se llama cuando el botón es clickeado
        function printPage() {
            window.addEventListener("load", window.print());
        }
    </script>
	

  </body>
</html>