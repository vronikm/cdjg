<?php
	use app\controllers\representanteController;
	$insRepre = new representanteController();	

	$repreid=$insRepre->limpiarCadena($url[1]);

	$repre_sexoM 		= "";
	$repre_sexoF 		= "";
	$repre_hermanosSi	= "";
	$repre_hermanosNo	= "";
	$conyuge_sexoM		= "";
	$conyuge_sexoF		= "";

	$datosrepresentante=$insRepre->seleccionarDatos("Unico","alumno_representante","repre_id",$repreid);
	if($datosrepresentante->rowCount()==1){
		$datosrepresentante=$datosrepresentante->fetch();
		if ($datosrepresentante['repre_sexo']=='M'){
			$repre_sexoM = "checked";
		}else{
			$repre_sexoF = "checked";
		}
	
		$repre_id					= $datosrepresentante['repre_id'];
		$repre_tipoidentificacion 	= $datosrepresentante['repre_tipoidentificacion'];
		$repre_identificacion 	  	= $datosrepresentante['repre_identificacion'];
		$repre_primernombre		  	= $datosrepresentante['repre_primernombre'];
		$repre_segundonombre 	 	= $datosrepresentante['repre_segundonombre'];
		$repre_apellidopaterno 	  	= $datosrepresentante['repre_apellidopaterno'];
		$repre_apellidomaterno 	 	= $datosrepresentante['repre_apellidomaterno'];
		$repre_direccion 		  	= $datosrepresentante['repre_direccion'];
		$repre_correo 			  	= $datosrepresentante['repre_correo'];
		$repre_celular 			  	= $datosrepresentante['repre_celular'];
		$repre_parentesco 		  	= $datosrepresentante['repre_parentesco'];
		
		$datosconyugerep=$insRepre->seleccionarDatos("Unico","alumno_representanteconyuge","conyuge_repid",$repreid);
		if($datosconyugerep->rowCount()==1){
			$datosconyugerep=$datosconyugerep->fetch();
			$conyuge_tipoidentificacion		=$datosconyugerep['conyuge_tipoidentificacion'];
			$conyuge_identificacion			=$datosconyugerep['conyuge_identificacion'];
			$conyuge_primernombre			=$datosconyugerep['conyuge_primernombre'];
			$conyuge_segundonombre			=$datosconyugerep['conyuge_segundonombre'];
			$conyuge_apellidopaterno		=$datosconyugerep['conyuge_apellidopaterno'];
			$conyuge_apellidomaterno		=$datosconyugerep['conyuge_apellidomaterno'];
			$conyuge_direccion				=$datosconyugerep['conyuge_direccion'];
			$conyuge_correo					=$datosconyugerep['conyuge_correo'];
			$conyuge_celular				=$datosconyugerep['conyuge_celular'];
			
			if ($datosconyugerep['conyuge_sexo']=='M'){
				$conyuge_sexoM = "checked";
			}else{
				$conyuge_sexoF = "checked";
			}

		}else{
			$conyuge_tipoidentificacion	="";
			$conyuge_identificacion		="";
			$conyuge_primernombre		="";
			$conyuge_segundonombre		="";
			$conyuge_apellidopaterno	="";
			$conyuge_apellidomaterno	="";
			$conyuge_direccion			="";
			$conyuge_correo				="";
			$conyuge_celular			="";
			$conyuge_sexoM 				="";
			$conyuge_sexoF 				="";
		}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Ficha representante</title>
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

	<!-- fileinput -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.css">
    
	<style>
		input:invalid {
		  box-shadow: 0 0 2px 1px red;
		}
		input:focus:invalid {
		  box-shadow: none;
		}
		textarea:invalid {
		  box-shadow: 0 0 2px 1px red;
		}
		textarea:focus:invalid {
		  box-shadow: none;
		}
	</style>
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
							<h1 class="m-0">Actualizar Representante</h1>
						</div><!-- /.col -->
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Inicio</a></li>
								<li class="breadcrumb-item active">Actualizar Representante</li>
							</ol>
						</div><!-- /.col -->
					</div><!-- /.row -->
				</div><!-- /.container-fluid -->
			</div>
			<!-- /.content-header -->

			<!-- Main content -->
			<section class="content">				
				<!-- /.container-fluid información representante -->
				<div class="container-fluid">						
					<div class="card">
						<div class="card-header p-2">
							<ul class="nav nav-pills">
							<li class="nav-item"><a class="nav-link active" href="#informacionp" data-toggle="tab">Información Personal</a></li>
								<li class="nav-item"><a class="nav-link" href="#conyuge" data-toggle="tab">Cónyuge</a></li>
								<input type="hidden" name="alumno_repreid" value="<?php echo $repreid; ?>">	
							</ul>
						</div><!-- /.card-header -->
						<div class="card-body">
							<div class="tab-content">
								<!-- Tab información del representante -->
								<div class="active tab-pane" id="informacionp">
									<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/representanteAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data">
									<input type="hidden" name="modulo_repre" value="actualizar">	
									<input type="hidden" name="repre_id" value="<?php echo $datosrepresentante['repre_id']; ?>">																					
									<div class="row">
										<div class="col-sm-3">
											<div class="form-group">
												<label for="repre_tipoidentificacion">Tipo identificación</label>
												<select class="form-control" id="repre_tipoidentificacion" name="repre_tipoidentificacion" >
													<?php echo $insRepre->listarOptionTipoIdentificacion($repre_tipoidentificacion); ?>
												</select>
											</div>          
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="repre_identificacion">Identificación</label>                        
												<input type="text" class="form-control" id="repre_identificacion" name="repre_identificacion" value="<?php echo $repre_identificacion; ?>" >
											</div>
										</div>                   
										<div class="col-md-3">                        
											<div class="form-group">
												<label for="repre_apellidopaterno">Apellido paterno</label>
												<input type="text" class="form-control" id="repre_apellidopaterno" name="repre_apellidopaterno" value="<?php echo $repre_apellidopaterno; ?>" >
											</div>
										</div>
										<div class="col-md-3">
											<label for="repre_apellidomaterno">Apellido materno</label>
											<input type="text" class="form-control" id="repre_apellidomaterno" name="repre_apellidomaterno" value="<?php echo $repre_apellidomaterno; ?>" >
										</div>
										<div class="col-md-3">                        
											<div class="form-group">
												<label for="repre_primernombre">Primer nombre</label>
												<input type="text" class="form-control" id="repre_primernombre" name="repre_primernombre" value="<?php echo $repre_primernombre; ?>" >
											</div>
										</div>
										<div class="col-md-3">
											<label for="repre_segundonombre">Segundo nombre</label>
											<input type="text" class="form-control" id="repre_segundonombre" name="repre_segundonombre" value="<?php echo $repre_segundonombre; ?>" >
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="repre_parentesco">Parentesco</label>
												<select class="form-control select2" style="width: 100%;" id="repre_parentesco" name="repre_parentesco" >													
													<?php echo $insRepre->listarOptionParentesco($repre_parentesco); ?>
												</select>
											</div> 
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="repre_sexo">Género</label>
												<div class="form-check">
													<input class="col-sm-1 form-check-input" type="radio" id="repre_sexoM" name="repre_sexo" value="M" <?php echo $repre_sexoM;?> >
													<label class="col-sm-5 form-check-label" for="repre_sexoM" style="font-size: 14px;">Masculino</label>
													<input class="col-sm-1 form-check-input" type="radio" id="repre_sexoF" name="repre_sexo" value="F" <?php echo $repre_sexoF;?> >
													<label class="col-sm-4 form-check-label" for="repre_sexoF" style="font-size: 14px;">Femenino</label>
												</div> 
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="repre_direccion">Dirección</label>
												<input type="text" class="form-control" id="repre_direccion" name="repre_direccion" value="<?php echo $repre_direccion; ?>" >
											</div>
										</div>              
										<div class="col-md-3">
											<div class="form-group">
												<label for="repre_correo">Correo</label>
												<input type="text" class="form-control" id="repre_correo" name="repre_correo" value="<?php echo $repre_correo; ?>" >
											</div> 
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<label for="repre_celular">Celular</label>
												<input type="text" class="form-control" id="repre_celular" name="repre_celular" value="<?php echo $repre_celular; ?>" >
											</div> 
										</div>
									</div>				
								</div>

								<!-- Tab información del conyuge representante --> 
								<div class="tab-pane" id="conyuge">
									<div class="row">
										<div class="col-md-3">											
											<div class="form-group">
												<label for="TidentificacionCRep">Tipo identificación</label>
												<select class="form-control" id="conyuge_tipoidentificacion" name="conyuge_tipoidentificacion" >
													<?php echo $insRepre->listarOptionTipoIdentificacion($conyuge_tipoidentificacion); ?> 
												</select>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="conyuge_identificacion">Identificación</label>                        
												<input type="text" class="form-control" id="conyuge_identificacion" name="conyuge_identificacion" value="<?php echo $conyuge_identificacion;?>" >
											</div>
										</div>                   
										<div class="col-md-3">                        
											<div class="form-group">
												<label for="conyuge_apellidopaterno">Apellido paterno</label>
												<input type="text" class="form-control" id="conyuge_apellidopaterno" name="conyuge_apellidopaterno" value="<?php echo $conyuge_apellidopaterno;?>" >
											</div>
										</div>
										<div class="col-md-3">
											<label for="conyuge_apellidomaterno">Apellido materno</label>
											<input type="text" class="form-control" id="conyuge_apellidomaterno" name="conyuge_apellidomaterno" value="<?php echo $conyuge_apellidomaterno;?>" >
										</div>
										<div class="col-md-3">                        
											<div class="form-group">
												<label for="conyuge_primernombre">Primer nombre</label>
												<input type="text" class="form-control" id="conyuge_primernombre" name="conyuge_primernombre" value="<?php echo $conyuge_primernombre;?>" >
											</div>
										</div>
										<div class="col-md-3">
											<label for="conyuge_segundonombre">Segundo nombre</label>
											<input type="text" class="form-control" id="conyuge_segundonombre" name="conyuge_segundonombre" value="<?php echo $conyuge_segundonombre;?>" >
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="conyuge_celular">Celular</label>
												<input type="text" class="form-control" id="conyuge_celular" name="conyuge_celular"value="<?php echo $conyuge_celular;?>" >
											</div> 
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="conyuge_correo">Correo</label>
												<input type="text" class="form-control" id="conyuge_correo" name="conyuge_correo" value="<?php echo $conyuge_correo;?>" >
											</div> 
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="conyuge_direccion">Dirección</label>
												<input type="text" class="form-control" id="conyuge_direccion" name="conyuge_direccion" value="<?php echo $conyuge_direccion;?>" >	
											</div>
										</div>              
										<div class="col-md-4">
											<div class="form-group">
												<label for="conyuge_sexo">Género</label>
												<div class="form-check">
													<input class="col-sm-1 form-check-input" type="radio" id="conyuge_sexoM" name="conyuge_sexo" value="M" <?php echo $conyuge_sexoM;?> >
													<label class="col-sm-5 form-check-label" for="conyuge_sexoM">Masculino</label>
													<input class="col-sm-1 form-check-input" type="radio" id="conyuge_sexoF" name="conyuge_sexo" value="F" <?php echo $conyuge_sexoF;?> >
													<label class="col-sm-4 form-check-label" for="conyuge_sexoF">Femenino</label>
												</div> 
											</div>
										</div>               
									</div>										
								</div>								
								<div class="card-footer">	
									<button type="submit" class="btn btn-success btn-sm">Actualizar</button>						
									<?php include "./app/views/inc/btn_back.php";	?>					
								</div>	
									
								</form>	
							</div>
							<!-- /.tab-pane -->
						</div><!-- /.card-body -->
					</div><!-- /.card -->
				</div>
			</section>
			<!-- /.content -->    
			
			<?php
				}else{
					include "./app/views/inc/error_alert.php";
				}
			?>
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

	<!--script src="app/views/dist/js/main.js" ></script-->
	
	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>
    
	<!-- Page specific script -->
	<script>
		$(function () {
			$("#representados").DataTable({
			"responsive": true, "lengthChange": false, "autoWidth": false,
			}).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');			    
		});
	</script>
  </body>
</html>