<?php
	date_default_timezone_set("America/Guayaquil");

	use app\controllers\asistenciaController;
	$insHora = new asistenciaController();	

	$horaid = ($url[1] != "") ? $url[1] : 0;	

	if($horaid != 0){
		$datos=$insHora->BuscarHora($horaid);		
		if($datos->rowCount()==1){
			$datos=$datos->fetch(); 
			$modulo_hora = 'actualizar';
			$hora_inicio = $datos['hora_inicio'];
			$hora_fin = $datos['hora_fin'];
			$detalle = $datos['hora_detalle'];
			$estado = $datos['hora_estado'];
		}
	}else{
		$modulo_hora = 'registrar';
		$hora_inicio = '';
		$hora_fin = '';
		$detalle = '';
		$estado = 'A';
	}	
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Ingreso Hora</title>

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
	<!-- Theme style >
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.min.css"-->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">	
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
							<h4 class="m-0">Horas</h4>
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
					<div class="card card-default">						
						<div class="card-header" style="font-size: 13px; height: 40px;">
							<h3 class="card-title">Ingreso de nueva hora</h3>
							<div class="card-tools">
								<button type="button" class="btn btn-tool" data-card-widget="collapse">
									<i class="fas fa-minus"></i>
								</button>
								<button type="button" class="btn btn-tool" data-card-widget="remove">
									<i class="fas fa-times"></i>
								</button>
							</div>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-12">

								<form class="FormularioAjax" id="quickForm" action="<?php echo APP_URL; ?>app/ajax/asistenciaAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
									<input type="hidden" name="modulo_asistencia" value="<?php echo $modulo_hora; ?>">
									<input type="hidden" name="hora_id" value="<?php echo $horaid; ?>">
									<div class="row" style='font-size: 14px;'>
										<div class="col-md-2">
											<div class="form-group" style='font-size: 13px; height: 15px;'>
												<label for="hora_inicio">Hora inicio</label>

												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="far fa-clock"></i></span>
													</div>
													<input type="text" class="form-control" style='font-size: 15px; height: 30px;' id="hora_inicio" name="hora_inicio" data-inputmask='"mask": "99:99:99"' data-mask value="<?php echo $hora_inicio; ?>">
												</div>
												<!-- /.input group -->
											</div>	
										</div>
										<div class="col-md-2">
											<div class="form-group" style='font-size: 13px; height: 20px;'>
												<label for="hora_fin">Hora fin</label>
												<div class="input-group">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="far fa-clock"></i></spa>
													</div>
													<input type="text" class="form-control" style='font-size: 15px; height: 30px;' id="hora_fin" name="hora_fin" data-inputmask='"mask": "99:99:99"' data-mask value="<?php echo $hora_fin; ?>">
												</div>
												<!-- /.input group -->
											</div>	
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="detalle">Detalle</label>
												<input type="text" class="form-control" style='font-size: 15px; height: 30px;' id="detalle" name="detalle" value="<?php echo $detalle; ?>">
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group" style='font-size: 13px;'>
												<label for="estado">Estado</label>
												<select class="form-control" style='font-size: 13px; height: 31px;' id="estado" name="estado">		
													<?php 
														if($estado == 'A'){
															echo '<option value="A" selected>Activo</option>
																<option value="I" >Inactivo</option>';
														}else{
															echo '<option value="A" >Activo</option> 
																<option value="I" selected>Inactivo</option>';	
														}
													?>
												</select>	
											</div>
										</div>
										
										<div class="col-md-12">						
											<button type="submit" class="btn btn-success btn-xs">Guardar</button>
											<a href="<?php echo APP_URL; ?>asistenciaHora/" class="btn btn-info btn-xs">Cancelar</a>
											<button type="reset" class="btn btn-dark btn-xs">Limpiar</button>						
										</div>
									</div>	
								</form>
									<div class="tab-custom-content">
										<p class="lead mb-0" style="font-size:15px; height: 23px;">Horas ingresadas</p>
									</div>
									<div class="tab-content" id="custom-content-above-tabContent">
										<table id="example1" class="table table-bordered table-striped table-sm" style="font-size: 13px;">
											<thead>
												<tr>
													<th>N.</th>
													<th>Hora inicio</th>
													<th>hora fin</th>
													<th>Detalle</th>
													<th>Estado</th>															
													<th style="width:150px;">Opciones</th>																
												</tr>
											</thead>
											<tbody>
												<?php 
													echo $insHora->listarHoras(); 
												?>								
											</tbody>
										</table>
									</div>							
								</div>
							</div>
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

	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js" ></script>

	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery-validation/jquery.validate.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery-validation/additional-methods.min.js"></script>
	
	<script>
		$(function () {

			//Datemask dd/mm/yyyy
			$('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
			//Datemask2 mm/dd/yyyy
			$('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
			//Money Euro
			$('[data-mask]').inputmask()

		})  
	</script>	

	<script>
		$(function () {
			
			$('#quickForm').validate({
				rules: {
				hora_inicio: {
					required: true       
				},
				hora_fin: {
					required: true
				},
				},
				messages: {
				hora_inicio: {
					required: "Por favor ingrese una hora"
				},
				hora_fin: {
					required: "Por favor ingrese una hora",
					minlength: "Your password must be at least 5 characters long"
				},
				},
				errorElement: 'span',
				errorPlacement: function (error, element) {
				error.addClass('invalid-feedback');
				element.closest('.form-group').append(error);
				},
				highlight: function (element, errorClass, validClass) {
				$(element).addClass('is-invalid');
				},
				unhighlight: function (element, errorClass, validClass) {
				$(element).removeClass('is-invalid');
				}
			});
		});
	</script>
	


  </body>
</html>