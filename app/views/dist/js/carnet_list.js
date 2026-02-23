$(function () {
    // Inicializar DataTable
    $("#example1").DataTable({
        "order": [[5, "asc"]], // Ordena la sexta columna ("Condición") por defecto de manera ascendente
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
    
    // ✅ FUNCIÓN PARA CONSULTAR CARNETS PENDIENTES VÍA AJAX
    function consultarCarnetsPendientes(callback) {
        $.ajax({
            url: APP_URL + 'app/ajax/carnetAjax.php',
            type: 'POST',
            data: {
                modulo_carnet: 'imprimir_carnetspendientes'
            },
            dataType: 'json',
            success: function(response) {
                if(response.tipo === 'success') {
                    callback(response.total);
                } else {
                    console.error('Error al obtener carnets pendientes');
                    callback(0);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                callback(0);
            }
        });
    }
    
    // ✅ ACTUALIZAR CONTADOR AL CARGAR LA PÁGINA
    consultarCarnetsPendientes(function(total) {
        $('#contadorCarnets').html(total);
    });
    
    // ✅ BOTÓN IMPRIMIR TODOS CON CONSULTA AJAX
    $('#btnImprimirTodos').on('click', function() {
        var btn = $(this);
        var badge = $('#contadorCarnets');
        
        // Deshabilitar botón mientras consulta
        btn.prop('disabled', true);
        badge.html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Consultar carnets pendientes en tiempo real
        consultarCarnetsPendientes(function(totalPendientes) {
            // Actualizar badge
            badge.html(totalPendientes);
            
            // Habilitar botón
            btn.prop('disabled', false);
            
            // Validar si hay carnets
            if(totalPendientes === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin carnets pendientes',
                    html: `
                        <div style="padding: 15px;">
                            <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745;"></i>
                            <p style="margin-top: 15px; font-size: 16px;">
                                No hay carnets pendientes de impresión este mes
                            </p>
                            <p style="color: #6c757d; font-size: 14px;">
                                Todos los carnets ya han sido generados
                            </p>
                        </div>
                    `,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
            
            // Mostrar modal de confirmación
            Swal.fire({
            title: '¿Imprimir carnets?',
            html: `
                <div style="text-align: left; padding: 12px;">
                    <p style="margin-bottom: 10px;">
                        <i class="fas fa-print" style="color: #28a745; font-size: 18px;"></i> 
                        <strong>Carnets pendientes de impresión:</strong>
                    </p>

                    <p style="text-align: center; margin: 12px 0;">
                        <span style="color: #28a745; font-size: 42px; font-weight: bold;">${totalPendientes}</span>
                    </p>

                    <hr style="margin: 12px 0;">

                    <p style="margin-bottom: 6px;">
                        <i class="fas fa-file-pdf" style="color: #dc3545;"></i> 
                        Se generará un archivo PDF
                    </p>
                    <p style="margin-bottom: 6px;">
                        <i class="fas fa-layer-group" style="color: #17a2b8;"></i> 
                        Formato: <strong>10 carnets por hoja A4</strong>
                    </p>
                   <p style="margin-bottom: 0;">
                        <i class="fas fa-calendar-alt" style="color: #ffc107;"></i> 
                        Mes: <strong>${MES_ACTUAL}</strong>
                    </p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Sí, imprimir',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            customClass: {
                confirmButton: 'btn btn-success btn-sm mx-1',
                cancelButton: 'btn btn-secondary btn-sm mx-1'
            },
            buttonsStyling: false,
            width: '420px',
            padding: '1em'

            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar mensaje de generación
                    Swal.fire({
                        title: 'Generando PDF...',
                        html: `
                            <div style="padding: 20px;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #28a745;"></i>
                                <p style="margin-top: 15px; font-size: 16px;">
                                    Preparando <strong>${totalPendientes}</strong> carnets para impresión
                                </p>
                                <p style="color: #6c757d; font-size: 14px; margin-top: 10px;">
                                    Por favor espere...
                                </p>
                            </div>
                        `,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Abrir PDF en nueva ventana
                    window.open(APP_URL + 'carnetPDF/', '_blank');
                    
                    // Cerrar mensaje y recargar después de 2 segundos
                    setTimeout(function() {
                        Swal.fire({
                            icon: 'success',
                            title: '¡PDF Generado!',
                            html: `
                                <div style="padding: 15px;">
                                    <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745;"></i>
                                    <p style="margin-top: 15px; font-size: 16px;">
                                        Los carnets se han enviado a impresión
                                    </p>
                                </div>
                            `,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Recargar la página para actualizar el listado
                            location.reload();
                        });
                    }, 2000);
                }
            });
        });
    });
    
    // Seleccionar/deseleccionar todos
    $('#seleccionarTodos').on('change', function() {
        $('.chk-reimpresion').prop('checked', $(this).prop('checked'));
    });
    
    // Actualizar checkbox principal si se deselecciona alguno
    $('.chk-reimpresion').on('change', function() {
        if(!$(this).prop('checked')) {
            $('#seleccionarTodos').prop('checked', false);
        }
        
        // Si todos están marcados, marcar el principal
        if($('.chk-reimpresion:checked').length === $('.chk-reimpresion').length) {
            $('#seleccionarTodos').prop('checked', true);
        }
    });
    
    // Reimprimir carnets seleccionados
    $('#btnReimprimirSeleccionados').on('click', function(e) {
        e.preventDefault();
        
        var seleccionados = [];
        $('.chk-reimpresion:checked').each(function() {
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
            cancelButtonText: 'Cancelar',
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
