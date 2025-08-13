<?php
	use app\controllers\empleadoController;
	$insempleado = new empleadoController();	

	$empleadoid		= $insempleado->limpiarCadena($url[1]);
	$foto 			= APP_URL.'app/views/dist/img/default.png';
	$empleado_sexoM = "";
	$empleado_sexoF	= "";

	$datosempleado=$insempleado->seleccionarDatos("Unico","sujeto_empleado","empleado_id",$empleadoid);
	if($datosempleado->rowCount()==1){
		$datosempleado=$datosempleado->fetch();
		if ($datosempleado['empleado_foto']!=""){
			$foto = APP_URL.'app/views/imagenes/fotos/empleado/'.$datosempleado['empleado_foto'];
		}else{
			$foto = APP_URL.'app/views/dist/img/default.png';
		}
		if ($datosempleado['empleado_genero']=='M'){
			$empleado_sexoM = "checked";
		}else{
			$empleado_sexoF = "checked";
		}

		$modulo_empleado 				= 'actualizar';		
		$empleado_sedeid 				= $datosempleado['empleado_sedeid'];
		$empleado_tipoidentificacion 	= $datosempleado['empleado_tipoidentificacion'];
		$empleado_identificacion 	  	= $datosempleado['empleado_identificacion'];
		$empleado_nombre		  		= $datosempleado['empleado_nombre'];
		$empleado_correo 			  	= $datosempleado['empleado_correo'];
		$empleado_celular 			  	= $datosempleado['empleado_celular'];
		$empleado_direccion 		  	= $datosempleado['empleado_direccion'];
		$empleado_tipopersonalid 		= $datosempleado['empleado_tipopersonalid'];
		$empleado_especialidadid 		= $datosempleado['empleado_especialidadid'];
		$empleado_fechaingreso			= $datosempleado['empleado_fechaingreso'];
		$empleado_sueldo				= $datosempleado['empleado_sueldo'];
	}else{
		$modulo_empleado 				= 'registrar';	
		$empleado_sedeid		  		= '';
		$empleado_tipoidentificacion	= '';
		$empleado_identificacion		= '';
		$empleado_nombre				= '';		
		$empleado_correo				= '';
		$empleado_celular				= '';
		$empleado_direccion				= '';
		$empleado_tipopersonalid		= '';
		$empleado_especialidadid 		= '';
		$empleado_fechaingreso			= '';
		$empleado_sueldo				= '';
	}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?>| empleados</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
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
				<h4 class="m-0">Empleados</h4>
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
			<div class="container-fluid">
			<!-- Small boxes (Stat box) -->
				<div class="card card-default">
					<div class="card-header" class="centered" >
						<h4 class="card-title">Ingreso de nuevo empleado</h4>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>

					<div class="card-body">
						<div class="row">
							<div class="col-md-12">	
								<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/empleadoAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
								<input type="hidden" name="modulo_empleado" value="<?php echo $modulo_empleado; ?>">
								<input type="hidden" name="empleado_id" value="<?php echo $empleadoid; ?>">

								<div class="row" style="font-size: 13px;">						
									<div class="col-md-2">
										<div class="form-group">
											<label for="empleado_foto">Foto</label>		
											<div class="input-group">											
												<div class="fileinput fileinput-new" data-provides="fileinput">
													<div class="fileinput-new thumbnail" style="width: 116px; height: 144px;" data-trigger="fileinput"><img src="<?php echo $foto; ?>"></div>
													<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 116px; max-height: 144px"></div>
													<div>
														<span class="bton bton-white bton-file" style="font-size: 13px;">
															<span class="fileinput-new">Seleccionar Foto</span>
															<span class="fileinput-exists">Cambiar</span>
															<input type="file" name="empleado_foto" id="foto" accept="image/*">
														</span>
														<a href="#" class="bton bton-orange fileinput-exists" style="font-size: 13px;" data-dismiss="fileinput">Remover</a>
													</div>
												</div>
											</div>		
										</div>
										<!-- /.form-group -->								
									</div>
									<!-- /.col -->
									<div class="col-sm-10">
										<div class="row" style="font-size: 13px;">
											<div class="col-md-2">
												<div class="form-group">
													<label for="empleado_sedeid">Sede</label>
													<select class="form-control select2" id="empleado_sedeid" name="empleado_sedeid">									
														<?php echo $insempleado->listarOptionSede($empleado_sedeid); ?>
													</select>	
												</div>
											</div> 
											<div class="col-sm-2">
												<div class="form-group">
													<label for="empleado_tipoidentificacion">Tipo identificación</label>
													<select id="empleado_tipoidentificacion" class="form-control custom-select2" name="empleado_tipoidentificacion" value="<?php echo $empleado_tipoidentificacion; ?>">
														<?php echo $insempleado->OptionTipoIdentificacion($empleado_tipoidentificacion); ?>
													</select>
												</div>          
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label for="empleado_identificacion">Identificación</label>                        
													<input type="text" class="form-control" id="empleado_identificacion" name="empleado_identificacion" value="<?php echo $empleado_identificacion; ?>" required>
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label for="empleado_nombre">Nombre y Apellido</label>
													<input type="text" class="form-control" id="empleado_nombre" name="empleado_nombre" value="<?php echo $empleado_nombre; ?>" required>
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label for="empleado_correo">Correo</label>
													<input type="email" class="form-control" id="empleado_correo" name="empleado_correo" value="<?php echo $empleado_correo; ?>" required>	
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label for="empleado_celular">Celular</label>
													<input type="text" class="form-control" id="empleado_celular" name="empleado_celular" data-inputmask='"mask": "0999999999"' data-mask value="<?php echo $empleado_celular; ?>" required>
												</div> 
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label for="empleado_fechaingreso">Fecha de ingreso</label>
													<div class="input-group">
														<div class="input-group-prepend">
															<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
														</div>
														<input type="date" class="form-control" name="empleado_fechaingreso" id="empleado_fechaingreso" data-inputmask-alias="datetime" data-inputmask-inputformat="mm/dd/yyyy" data-mask value="<?php echo $empleado_fechaingreso; ?>">
													</div>
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label for="empleado_sueldo">Honorarios USD</label>
													<input type="text" class="form-control" id="empleado_sueldo" name="empleado_sueldo" value="<?php echo $empleado_sueldo; ?>" required>
												</div> 
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label for="empleado_tipopersonalid">Tipo empleado</label>
													<select class="form-control select2" id="empleado_tipopersonalid" name="empleado_tipopersonalid" onchange="toggleEspecialidad()"> 									
														<?php echo $insempleado->listarTipoPersonal($empleado_tipopersonalid); ?>
													</select>	
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="empleado_especialidadid">Especialidad</label>
													<select class="form-control select2" id="empleado_especialidadid" name="empleado_especialidadid" disabled>									
														<?php echo $insempleado->OptionEspecialidad($empleado_especialidadid); ?>
													</select>	
												</div>
											</div> 
											<div class="col-md-8">
												<div class="form-group">
													<label for="empleado_direccion">Dirección</label>
													<input type="text" class="form-control" id="empleado_direccion" name="empleado_direccion" value="<?php echo $empleado_direccion; ?>" required>
												</div>	
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label for="empleado_genero">Género</label>
													<div class="form-check">
														<input class="col-sm-1 form-check-input" type="radio" id="empleado_generoM" name="empleado_genero" value="M" <?php echo $empleado_sexoM; ?> required>
														<label class="col-sm-5 form-check-label" for="empleado_generoM">Masculino</label>
														<input class="col-sm-1 form-check-input" type="radio" id="empleado_generoF" name="empleado_genero" value="F" <?php echo $empleado_sexoF; ?> >
														<label class="col-sm-4 form-check-label" for="empleado_generoF">Femenino</label>
													</div> 
												</div>
											</div>									
											<div class="col-md-12">						
												<button type="submit" class="btn btn-success btn-xs">Guardar</button>
												<a href="<?php echo APP_URL; ?>empleadoList/" class="btn btn-info btn-xs">Cancelar</a>												
												<button type="reset" class="btn btn-dark btn-xs">Limpiar</button>						
											</div>
										</div>								
									</div>
									<!-- /.col -->
								</div>
								</form>

								<div class="tab-custom-content">
									<h4 class="card-title">Empleados ingresados</h4>
								</div>
								
								<div class="tab-content" id="custom-content-above-tabContent" style="font-size: 13px;">
									<table id="example1" class="table table-bordered table-striped table-sm" style="font-size: 13px;">
										<thead>
											<tr>
												<th>Localización</th>
												<th>Identificación</th>
												<th>Nombre y apellido</th>
												<th>Correo</th>
												<th>Celular</th>												
												<th>Honorarios</th>
												<th>Acceso sistema</th>
												<th style="width: 180px;">Operaciones</th>
											</tr>
										</thead>
										<tbody>
											<?php 
												echo $insempleado->listarEmpleados(); 
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
	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js" ></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js" ></script>
	<!-- fileinput -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/fileinput/fileinput.js"></script>  
	
	<!-- Page specific script -->
	<script>
		$(function () {
			$("#example1").DataTable({
			"responsive": true, "lengthChange": false, "autoWidth": false,
			"language": {
				"decimal": "",
				"emptyTable": "No hay datos disponibles en la tabla",
				"info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
				"infoEmpty": "Mostrando 0 a 0 de 0 entradas",
				"infoFiltered": "(filtrado de _MAX_ entradas totales)",
				"infoPostFix": "",
				"thousands": ",",
				"lengthMenu": "Mostrar _MENU_ entradas",
				"loadingRecords": "Cargando...",
				"processing": "Procesando...",
				"search": "Buscar:",
				"zeroRecords": "No se encontraron registros coincidentes",
				"paginate": {
					"first": "Primero",
					"last": "Último",
					"next": "Siguiente",
					"previous": "Anterior"
				},
				"aria": {
					"sortAscending": ": activar para ordenar la columna ascendente",
					"sortDescending": ": activar para ordenar la columna descendente"
				}
			},
			}).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');			    
		});
	</script>

	<!-- Aplicar la máscara de entrada para el campo sueldo-->
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
            }).mask("#empleado_sueldo");
        });
    </script>
	<script>
		function toggleEspecialidad() {
			var tipoEmpleado = document.getElementById("empleado_tipopersonalid").value;
			var especialidad = document.getElementById("empleado_especialidadid");

			if (tipoEmpleado === "TPP") {
				especialidad.disabled = false; // Habilita el campo
			} else {
				especialidad.disabled = true; // Deshabilita el campo
			}
		}
	</script>
  </body>
</html>








