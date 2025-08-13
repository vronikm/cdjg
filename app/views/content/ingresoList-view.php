<?php
	use app\controllers\balanceController;
	$insIngreso = new balanceController();
	
	$ingresoid = ($url[1] != "") ? $url[1] : 0;	

	$foto = APP_URL.'app/views/imagenes/fotos/ingresos/ingreso_default.jpg';

	if($ingresoid != 0){
		$datosIngreso=$insIngreso->BuscarIngreso($ingresoid);		
		if($datosIngreso->rowCount()==1){
			$datosIngreso=$datosIngreso->fetch(); 
			if ($datosIngreso['ingreso_imagenpago']!=""){
				$foto = APP_URL.'app/views/imagenes/fotos/ingresos/'.$datosIngreso['ingreso_imagenpago'];
			}else{
				$foto = APP_URL.'app/views/dist/img/ingreso_default.jpg';
			}
			$modulo_ingreso = 'actualizar';			

			$ingreso_sedeid			= $datosIngreso['ingreso_sedeid'];
			$ingreso_fecharecepcion	= $datosIngreso['ingreso_fecharecepcion'];
			$ingreso_empresa		= $datosIngreso['ingreso_empresa'];
			$ingreso_monto		 	= $datosIngreso['ingreso_monto'];
			$ingreso_formaentrega 	= $datosIngreso['ingreso_formaentrega'];
			$ingreso_concepto 		= $datosIngreso['ingreso_concepto'];
			$ingreso_descripcion 	= $datosIngreso['ingreso_descripcion'];
			
		}
	}else{
		$modulo_ingreso 		= 'registrar';
		$ingreso_sedeid			= '';
		$ingreso_fecharecepcion = '';
		$ingreso_empresa		= '';
		$ingreso_monto		 	= '';
		$ingreso_formaentrega 	= '';
		$ingreso_concepto		= '';
		$ingreso_descripcion 	= '';
			
	}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?>| Ingresos</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
		<!-- daterange picker -->
		<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/daterangepicker/daterangepicker.css">
	<!-- iCheck for checkboxes and radio inputs -->
	 <!-- DataTables -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
	
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
					<h4 class="m-0">Ingresos</h4>
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

		<!-- Main content -->
		<section class="content">
			<div class="container-fluid">
			<!-- Small boxes (Stat box) -->
				<div class="card card-default">
					<div class="card-header" style='height: 40px;'>
						<h4 class="card-title">Registro de nuevo ingreso</h4>
						<div class="card-tools">							
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>

					<div class="card-body">
						<div class="row">
							<div class="col-md-12">	
								<form class="FormularioAjax" id="quickForm" action="<?php echo APP_URL; ?>app/ajax/balanceAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
									<input type="hidden" name="modulo_ingreso" value="<?php echo $modulo_ingreso; ?>">
									<input type="hidden" name="ingreso_id" value="<?php echo $ingresoid; ?>">
									<div class="row" style="font-size: 13px; height: 187px;">
										<div class="col-md-2">
											<div class="form-group">
												<label for="ingreso_imagenpago">Foto</label>		
												<div class="input-group">											
													<div class="fileinput fileinput-new" data-provides="fileinput">
														<div class="fileinput-new thumbnail" style="width: 110px; height: 130px;" data-trigger="fileinput"><img src="<?php echo $foto; ?>"> </div>
														<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 116px; max-height: 144px"></div>
														<div>
															<span class="bton bton-white bton-file" style="font-size: 13px;">
																<span class="fileinput-new">Seleccionar Foto</span>
																<span class="fileinput-exists">Cambiar</span>
																<input type="file" name="ingreso_imagenpago" id="foto" accept="image/*">
															</span>
															<a href="#" class="bton bton-orange fileinput-exists" data-dismiss="fileinput">Remover</a>
														</div>
													</div>
												</div>		
											</div>
										<!-- /.form-group -->								
										</div>
										<div class="col-sm-10">
											<div class="row" style="font-size: 13px;">
												<div class="col-md-3">
													<div class="form-group">
														<label for="ingreso_sedeid">Sede</label>
														<select class="form-control select2" id="ingreso_sedeid" name="ingreso_sedeid" onchange="ocultarDiv()" >																									
															<?php echo $insIngreso->listarOptionSede($ingreso_sedeid); ?>
														</select>	
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="ingreso_fecharecepcion">Fecha de recepción</label>
														<div class="input-group">
															<div class="input-group-prepend">
																<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
															</div>
															<input type="date" class="form-control" name="ingreso_fecharecepcion" id="ingreso_fecharecepcion" data-inputmask-alias="datetime" data-inputmask-inputformat="mm/dd/yyyy" data-mask value="<?php echo $ingreso_fecharecepcion; ?>">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="ingreso_empresa">Nombre empresa</label>
														<input type="text"  class="form-control select2" id="ingreso_empresa" name="ingreso_empresa" value="<?php echo $ingreso_empresa; ?>">
													</div> 
												</div>
												<div class="col-md-2">
													<div class="form-group">
														<label for="ingreso_monto">Monto USD</label>
														<input type="text" class="form-control" id="ingreso_monto" name="ingreso_monto" value="<?php echo $ingreso_monto; ?>" required>
													</div> 
												</div>																									
												<div class="col-md-3">
													<div class="form-group">
														<label for="ingreso_formaentrega">Forma de recepción</label>
														<select class="form-control select2" id="ingreso_formaentrega" name="ingreso_formaentrega" onchange="ocultarDiv()" >																									
															<?php echo $insIngreso->listarFormaEntregaIngreso($ingreso_formaentrega); ?>
														</select>	
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group">
														<label for="ingreso_concepto">Concepto</label>
														<select class="form-control select2" id="ingreso_concepto" name="ingreso_concepto" onchange="ocultarDiv()" >																									
															<?php echo $insIngreso->listarTipoIngreso($ingreso_concepto); ?>
														</select>	
													</div>
												</div>													
												<div class="col-md-6">
													<div class="form-group">
														<label for="ingreso_descripcion">Descripción</label>
														<input type="text" class="form-control" id="ingreso_descripcion" name="ingreso_descripcion" value="<?php echo $ingreso_descripcion; ?>">
													</div>	
												</div>	
												<div class="col-md-12">						
													<button type="submit" class="btn btn-success btn-xs">Guardar</button>
													<a href="<?php echo APP_URL; ?>ingresoList/" class="btn btn-info btn-xs">Cancelar</a>
													<button type="reset" class="btn btn-dark btn-xs">Limpiar</button>						
												</div>	
											</div>
										</div>
									</div>									
								</form><br>
								
								<div class="tab-custom-content">
									<h4 class="card-title">Ingresos registrados</h4>
								</div>										
								<div class="tab-content" id="custom-content-above-tabContent" style="font-size: 13px;">	
									<table id="example1" class="table table-bordered table-striped table-sm" style="font-size: 13px;">
										<thead>
											<tr>
												<th>Sede</th>
												<th>Empresa</th>
												<th>Monto</th>
												<th>Fecha de recepción</th>
												<th>Opciones</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												echo $insIngreso->listarIngresos(); 
											?>							
										</tbody>	
									</table>
								</div>
							</div>	
						</div>
					</div>
				</div>
			<!-- /.row -->
			</div><!-- /.container-fluid -->
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
	<!-- DataTables  & Plugins -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables/jquery.dataTables.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jszip/jszip.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/pdfmake/pdfmake.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/pdfmake/vfs_fonts.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.print.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
	<!-- InputMask -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/moment/moment.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/inputmask/jquery.inputmask.min.js"></script>
	<!-- AdminLTE App -->
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>    
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>

	<!-- Aplicar la máscara de entrada para el campo ingreso_monto-->
	<script>
        $(document).ready(function(){
            Inputmask({
                alias: "currency",
                prefix: "$ ",  // Prefijo de la moneda
                groupSeparator: ",",
                autoGroup: true,
                digits: 2,
                digitsOptional: false,
                placeholder: "0"
            }).mask("#ingreso_monto");
        });
    </script>    
  </body>
</html>








