	
		$(function () {
			$("#tablaFeriados").DataTable({
				"responsive": true, 
				"lengthChange": false, 
				"autoWidth": false,
				"order": [[1, "asc"]],
				"language": {
					"decimal": "",
					"emptyTable": "No hay feriados registrados",
					"info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
					"infoEmpty": "Mostrando 0 a 0 de 0 registros",
					"infoFiltered": "(filtrado de _MAX_ registros totales)",
					"infoPostFix": "",
					"thousands": ",",
					"lengthMenu": "Mostrar _MENU_ registros",
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
					},
                    "buttons": {
                        "copy": "Copiar",
                        "print": "Imprimir",
                        "text": 'Imprimir Tabla',
                        "title": 'Datos de Alumnos',
                        "messageTop": 'Generado por el sistema de gestión de alumnos.',
                        "messageBottom": 'Página generada automáticamente.',
                        customize: function(win) {
                            $(win.document.body)
                                .css('font-family', 'Arial')
                                .css('background-color', '#f3f3f3');

                            // Cambiar el estilo de la tabla impresa
                            $(win.document.body).find('table')
                                .addClass('display')  // Añadir una clase CSS a la tabla impresa
                                .css('font-size', '12pt')
                                .css('border', '1px solid black');

                            // Modificar título y agregar estilos CSS adicionales
                            $(win.document.body).find('h1')
                                .css('text-align', 'center')
                                .css('color', '#4CAF50');
                        }
                    }
				},
				"buttons": ["copy", "csv", "excel", "pdf", "print"]
			}).buttons().container().appendTo('#tablaFeriados_wrapper .col-md-6:eq(0)');			    
		});

		// Función para editar feriado
		function editarFeriado(id, fecha, descripcion, activo) {
			$('#edit_feriado_id').val(id);
			$('#edit_feriado_fecha').val(fecha);
			$('#edit_feriado_descripcion').val(descripcion);
			$('#edit_feriado_activo').val(activo);
			$('#modalEditarFeriado').modal('show');
		}

		// Función para eliminar feriado
		function eliminarFeriado(id, descripcion) {
			Swal.fire({
				title: '¿Estás seguro?',
				html: 'Estás a punto de eliminar el feriado:<br><strong>' + descripcion + '</strong>',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Sí, eliminar',
				cancelButtonText: 'Cancelar'
			}).then((result) => {
				if (result.isConfirmed) {
					// Crear formulario dinámico para enviar por AJAX
					let formData = new FormData();
					formData.append('modulo_feriado', 'eliminar');
					formData.append('feriado_id', id);

					fetch('<?php echo APP_URL; ?>app/ajax/feriadosAjax.php', {
						method: 'POST',
						body: formData
					})
					.then(response => response.json())
					.then(data => {
						if(data.tipo == "simple") {
							Swal.fire({
								icon: data.icono,
								title: data.titulo,
								text: data.texto,
								confirmButtonText: 'Aceptar'
							}).then(() => {
								if(data.icono == "success") {
									location.reload();
								}
							});
						}
					})
					.catch(error => {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Ocurrió un error al procesar la solicitud'
						});
					});
				}
			});
		}
