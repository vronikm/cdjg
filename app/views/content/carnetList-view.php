<?php
	use app\controllers\carnetController;
	$insCarnet = new carnetController();
	

?>

<html lang="es">
  <head>
    <meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?> | Carnets</title>
	<link rel="icon" type="image/png" href="<?php echo APP_URL; ?>app/views/dist/img/Logos/LogoCDJG.png">
	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/fontawesome-free/css/all.min.css">
	<!-- DataTables -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/plugins/select2/css/select2.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/adminlte.css">
	<!-- SweetAlert2 -->
	<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/dist/css/sweetalert2.min.css">
	<script src="<?php echo APP_URL; ?>app/views/dist/js/sweetalert2.all.min.js"></script>
  </head>
  
  <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
      <!-- Navbar -->
      <?php require_once "app/views/inc/navbar.php"; ?>
      
      <!-- Main Sidebar Container -->
      <?php require_once "app/views/inc/main-sidebar.php"; ?>
      
      <!-- Content Wrapper -->
      <div class="content-wrapper">
		<!-- Content Header -->
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h3 class="m-0">Carnets del Mes</h3>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>dashboard/">Inicio</a></li>
							<li class="breadcrumb-item active">Carnets</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<!-- Main content -->
		<section class="content">
			<div class="container-fluid">
				<!-- Card principal -->
				<div class="card card-default">
					<div class="card-header">
						<h3 class="card-title">
							<i class="fas fa-id-card"></i> 
							Alumnos con pago de pensión - <?php $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'America/Guayaquil', IntlDateFormatter::GREGORIAN, 'MMMM yyyy');
        													echo ucfirst($formatter->format(new DateTime()));?>
						</h3>
						<div class="card-tools">	
							<!-- Botón imprimir todos -->
							<a href="<?php echo APP_URL.'carnetPlantillaPDF/'; ?>" 
							   class="btn btn-success btn-sm" 
							   style="margin-right: 10px;" 
							   target="_blank">
								<i class="fas fa-print"></i> Imprimir Todos
							</a>
							
							<!-- Botón reimprimir seleccionados -->
							<button type="button" 
									id="btnReimprimirSeleccionados" 
									class="btn btn-warning btn-sm" 
									style="margin-right: 10px;">
								<i class="fas fa-redo"></i> Reimprimir Seleccionados
							</button>
							
							<button type="button" class="btn btn-tool" data-card-widget="collapse">
								<i class="fas fa-minus"></i>
							</button>
						</div>
					</div>
					
					<div class="card-body">
						<div class="alert alert-info">
							<i class="fas fa-info-circle"></i>
							<strong>Información:</strong> 
							Los carnets se generan automáticamente para todos los alumnos con pago de pensión del mes actual.
							Use los checkboxes para reimprimir carnets extraviados (se cobrará $1.00 por reimpresión).
						</div>
						
						<form id="formReimpresion" class="FormularioAjax" data-form="save">
							<table id="example1" class="table table-bordered table-striped table-sm">
								<thead>
									<tr>
										<th>Identificación</th>
										<th>Nombres</th>
										<th>Apellidos</th>	
										<th>Carnet</th>
										<th>Fecha Últ Pensión</th>
										<th>Condición</th>
										<th>Ver Carnet</th>
										<th style="text-align: center;">
											<div class="custom-control custom-checkbox">
												<input class="custom-control-input" 
													   type="checkbox" 
													   id="seleccionarTodos">
												<label for="seleccionarTodos" class="custom-control-label">
													Reimprimir
												</label>
											</div>
										</th>
									</tr>
								</thead>
								<tbody>
									<?php echo $insCarnet->listarAlumnos(); ?>								
								</tbody>
							</table>
						</form>
					</div>
				</div>
			</div>
		</section>
      </div>

      <?php require_once "app/views/inc/footer.php"; ?>

      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <!-- jQuery -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- DataTables -->
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables/jquery.dataTables.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
	<!-- AdminLTE App -->
	<script src="<?php echo APP_URL; ?>app/views/dist/js/adminlte.min.js"></script>
	
	<!-- Script personalizado -->
	<script>
		$(function () {
			// Inicializar DataTable
			$("#example1").DataTable({
				"responsive": true, 
				"lengthChange": false, 
				"autoWidth": false,
				"language": {
					"decimal": "",
					"emptyTable": "No hay carnets para emitir este mes",
					"info": "Mostrando _START_ a _END_ de _TOTAL_ carnets",
					"infoEmpty": "Mostrando 0 a 0 de 0 carnets",
					"infoFiltered": "(filtrado de _MAX_ carnets totales)",
					"thousands": ",",
					"lengthMenu": "Mostrar _MENU_ carnets",
					"loadingRecords": "Cargando...",
					"processing": "Procesando...",
					"search": "Buscar:",
					"zeroRecords": "No se encontraron carnets",
					"paginate": {
						"first": "Primero",
						"last": "Último",
						"next": "Siguiente",
						"previous": "Anterior"
					}
				}
			});
			
			// Seleccionar/deseleccionar todos
			$('#seleccionarTodos').on('change', function() {
				$('.chk-pago').prop('checked', $(this).prop('checked'));
			});
			
			// Actualizar checkbox principal si se deselecciona alguno
			$('.chk-pago').on('change', function() {
				if(!$(this).prop('checked')) {
					$('#seleccionarTodos').prop('checked', false);
				}
				
				// Si todos están marcados, marcar el principal
				if($('.chk-pago:checked').length === $('.chk-pago').length) {
					$('#seleccionarTodos').prop('checked', true);
				}
			});
			
			// Reimprimir carnets seleccionados
			$('#btnReimprimirSeleccionados').on('click', function(e) {
				e.preventDefault();
				
				var seleccionados = [];
				$('.chk-pago:checked').each(function() {
					seleccionados.push($(this).val());
				});
				
				if(seleccionados.length === 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Sin selección',
						text: 'Debe seleccionar al menos un alumno para reimprimir',
						confirmButtonColor: '#3085d6'
					});
					return;
				}
				
				Swal.fire({
					title: '¿Reimprimir carnets?',
					html: `Se generará un cargo de <strong>$1.00</strong> por cada carnet extraviado.<br>
						   Alumnos seleccionados: <strong>${seleccionados.length}</strong><br>
						   Total a cobrar: <strong>$${seleccionados.length}.00</strong>`,
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Sí, procesar reimpresión',
					cancelButtonText: 'Cancelar'
				}).then((result) => {
					if (result.isConfirmed) {
						procesarReimpresion(seleccionados);
					}
				});
			});
			
			// Función para procesar reimpresión
			function procesarReimpresion(ids) {
				$.ajax({
					url: '<?php echo APP_URL; ?>app/ajax/carnetAjax.php',
					type: 'POST',
					data: {
						modulo_carnet: 'procesar_reimpresion',
						pagos_seleccionados: ids
					},
					dataType: 'json',
					beforeSend: function() {
						Swal.fire({
							title: 'Procesando...',
							text: 'Generando pagos y preparando carnets',
							allowOutsideClick: false,
							didOpen: () => {
								Swal.showLoading();
							}
						});
					},
					success: function(response) {
						if(response.tipo === 'redireccionar') {
							Swal.fire({
								icon: 'success',
								title: response.titulo,
								text: response.texto,
								confirmButtonColor: '#3085d6'
							}).then(() => {
								window.open(response.url, '_blank');
								location.reload();
							});
						} else {
							Swal.fire({
								icon: response.icono,
								title: response.titulo,
								text: response.texto,
								confirmButtonColor: '#3085d6'
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Ocurrió un error al procesar la reimpresión',
							confirmButtonColor: '#d33'
						});
					}
				});
			}
		});
	</script>

	<script src="<?php echo APP_URL; ?>app/views/dist/js/ajax.js"></script>
	<script src="<?php echo APP_URL; ?>app/views/dist/js/main.js"></script>
  </body>
</html>