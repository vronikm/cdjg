<?php
	use app\controllers\escuelaController;
	$insEscuela = new escuelaController();
	
	$escuela=$insEscuela->limpiarCadena($url[1]);

	$datos=$insEscuela->seleccionarDatos("Unico","general_escuela","escuela_id","1");
	$img_dir="../views/dist/img/Logos/";

	if($datos->rowCount()==1){
		$datos=$datos->fetch();	
							
		if ($datos['escuela_logo']!=""){
			$foto = APP_URL.'app/views/dist/img/Logos/'.$datos['escuela_logo'];
		}else{
			$foto="";	
		}
		$value_form="actualizar";
		$escuela_ruc=$datos['escuela_ruc'];
		$escuela_nombre=$datos['escuela_nombre'];
		$escuela_email=$datos['escuela_email'];
		$escuela_direccion=$datos['escuela_direccion'];
		$escuela_telefono=$datos['escuela_telefono'];
		$escuela_movil=$datos['escuela_movil'];
		$escuela_recibo=$datos['escuela_recibo'];
		$escuela_pension=$datos['escuela_pension'];
		$escuela_inscripcion=$datos['escuela_inscripcion'];
		
	}else{
		$value_form="registrar";
		$escuela_ruc="";
		$escuela_nombre="";
		$escuela_email="";
		$escuela_direccion="";
		$escuela_telefono="";
		$escuela_movil="";
		$escuela_recibo="";
		$escuela_pension="";
		$escuela_inscripcion="";
		$foto="";
	}					
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?>| Datos club</title>
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
						<h1 class="m-0">Club</h1>
					</div><!-- /.col -->
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="#">Nuevo</a></li>
							<li class="breadcrumb-item active">Dashboard v1</li>
						</ol>
					</div><!-- /.col -->
				</div><!-- /.row -->
			</div><!-- /.container-fluid -->
		</div>
		<!-- /.content-header -->

		<!-- Main content -->
		<section class="content">
		<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/escuelaAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
					<input type="hidden" name="modulo_escuela" value="<?php echo $value_form; ?>">	
					
			<div class="container-fluid">
			<!-- Small boxes (Stat box) -->
				<div class="card card-default">
					<div class="card-header">
						<h3 class="card-title">Información del club</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>

					<!-- card-body -->		
					
					<div class="card-body">													
							<!-- row -->	
							<div class="row">						
								<!-- /.col -->
								<div class="col-md-3">
									<div class="form-group">
										<label for="logo_foto">Logo</label>		
										<div class="input-group">											
											<div class="fileinput fileinput-new" data-provides="fileinput">
												<div class="fileinput-new thumbnail" style="width: 116px; height: 144px;" data-trigger="fileinput">
													<img src="<?php echo $foto; ?>">
												</div>
												<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 116px; max-height: 144px"></div>
												<div>
													<span class="bton bton-white bton-file">
														<span class="fileinput-new">Seleccionar Logo</span>
														<span class="fileinput-exists">Cambiar</span>
														<input type="file" name="escuela_logo" id="logo" accept="image/*">
													</span>
													<a href="#" class="bton bton-orange fileinput-exists" data-dismiss="fileinput">Remover</a>
												</div>
											</div>
										</div>		
									</div>
									<!-- /.form-group -->								
								</div>
								<!-- /.col -->
								<div class="col-md-9">
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label for="escuela_ruc">RUC</label>
												<input type="text" class="form-control" id="escuela_ruc" name="escuela_ruc" placeholder="RUC de la Escuela" value="<?php echo $escuela_ruc; ?>">
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="escuela_nombre">Nombre</label>
												<input type="text" class="form-control" id="escuela_nombre" name="escuela_nombre" placeholder="Nombre escuela" value="<?php echo $escuela_nombre; ?>">
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="escuela_email">Correo</label>
												<input type="email" class="form-control" id="escuela_email" name="escuela_email" placeholder="Correo" value="<?php echo $escuela_email; ?>">	
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="escuela_direccion">Dirección</label>
												<input type="text" class="form-control" id="escuela_direccion" name="escuela_direccion" placeholder="Dirección de la escuela" value="<?php echo $escuela_direccion; ?>">
											</div>
										</div>									
										<div class="col-md-3">
											<div class="form-group">
												<label for="escuela_telefono">Teléfono</label>
												<input type="text" class="form-control" id="escuela_telefono" name="escuela_telefono" placeholder="Teléfono, celular" value="<?php echo $escuela_telefono; ?>">	
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="escuela_movil">Celular</label>
												<input type="text" class="form-control" id="escuela_movil" name="escuela_movil" placeholder="Teléfono, celular" value="<?php echo $escuela_movil; ?>">	
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="escuela_recibo">Numero de Recibo</label>
												<input type="text" class="form-control" id="escuela_recibo" name="escuela_recibo" placeholder="N° de recibo" value="<?php echo $escuela_recibo; ?>">	
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="escuela_recibo">Valor pensión</label>
												<input type="text" class="form-control" id="escuela_pension" name="escuela_pension" placeholder="0.00" value="<?php echo $escuela_pension; ?>">	
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="escuela_recibo">Valor inscripción</label>
												<input type="text" class="form-control" id="escuela_inscripcion" name="escuela_inscripcion" placeholder="0.00" value="<?php echo $escuela_inscripcion; ?>">	
											</div>
										</div>
									</div>
								<!-- /.form-group -->								
								</div>
								<!-- /.col -->							
							</div>
							<!-- /.row -->							
						</div> 
						<!-- /.card-body -->				
					<!-- /.card-body -->					
				</div>
			<!-- /.row -->
			</div><!-- /.container-fluid -->
			<div class="card-footer">						
				<button type="submit" class="btn btn-success btn-sm">Guardar</button>										
			</div>

		</form>

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

	<!--script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script-->
	
	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>


  </body>
</html>