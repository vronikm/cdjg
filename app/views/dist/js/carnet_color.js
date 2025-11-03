$(function () {
    // Guardar los colores originales al cargar la página
    var coloresOriginales = {};
    $('.color-select:not(:disabled)').each(function() {
        var mes = $(this).data('mes');
        var colorInicial = $(this).val();
        coloresOriginales[mes] = colorInicial;
    });
    
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione un color'
    });
    
    /**
     * Función para actualizar disponibilidad de colores
     * Solo muestra "(En uso)" para colores ACTUALMENTE seleccionados
     * Muestra "(Ya asignado)" solo para colores originales de BD
     */
    function actualizarDisponibilidadColores() {
        // Recopilar colores ACTUALMENTE seleccionados
        var coloresActuales = {};
        $('.color-select:not(:disabled)').each(function() {
            var mes = $(this).data('mes');
            var valor = $(this).val();
            if(valor && valor != '0') {
                coloresActuales[mes] = valor;
            }
        });
        
        // Para cada select, actualizar opciones
        $('.color-select:not(:disabled)').each(function() {
            var selectActual = $(this);
            var mesActual = selectActual.data('mes');
            var valorActual = selectActual.val();
            
            // Recorrer todas las opciones
            selectActual.find('option').each(function() {
                var opcion = $(this);
                var colorId = opcion.val();
                
                // Ignorar opción por defecto
                if(colorId == '0') return;
                
                // Limpiar texto de la opción
                var textoBase = opcion.text()
                    .replace(' (En uso)', '')
                    .replace(' (Ya asignado)', '')
                    .trim();
                
                // Verificar si este color está ACTUALMENTE seleccionado en otro mes
                var estaSeleccionadoAhora = false;
                for(var mes in coloresActuales) {
                    if(coloresActuales[mes] == colorId && mes != mesActual) {
                        estaSeleccionadoAhora = true;
                        break;
                    }
                }
                
                // Verificar si es el color ORIGINAL de otro mes (de BD)
                var esColorOriginalDeOtroMes = false;
                for(var mes in coloresOriginales) {
                    // Solo si sigue siendo el color actual de ese mes
                    if(coloresOriginales[mes] == colorId 
                        && mes != mesActual 
                        && coloresActuales[mes] == colorId) {
                        esColorOriginalDeOtroMes = true;
                        break;
                    }
                }
                
                // Aplicar reglas según el estado ACTUAL
                if(colorId == valorActual) {
                    // Si es el valor actual de este select, siempre disponible
                    opcion.prop('disabled', false);
                    opcion.text(textoBase);
                } else if(estaSeleccionadoAhora && esColorOriginalDeOtroMes) {
                    // Color original que sigue asignado al mismo mes
                    opcion.prop('disabled', true);
                    opcion.text(textoBase + ' (Ya asignado)');
                } else if(estaSeleccionadoAhora) {
                    // Color seleccionado ahora en otro mes (cambio temporal)
                    opcion.prop('disabled', true);
                    opcion.text(textoBase + ' (En uso)');
                } else {
                    // Color libre
                    opcion.prop('disabled', false);
                    opcion.text(textoBase);
                }
            });
            
            // Refrescar Select2
            selectActual.trigger('change.select2');
        });
    }
    
    // Ejecutar al cargar
    actualizarDisponibilidadColores();
    
    // Actualizar al cambiar cualquier select
    $('.color-select').on('change', function() {
        var mes = $(this).data('mes');
        var selectedOption = $(this).find('option:selected');
        var colorHex = selectedOption.data('color');
        var colorId = $(this).val();
        
        // Actualizar vista previa
        if(colorHex) {
            $('#preview_' + mes).css('background-color', colorHex);
        } else if(colorId > 0) {
            $.ajax({
                url: '<?php echo APP_URL; ?>app/ajax/carnetAjax.php',
                method: 'POST',
                data: {
                    modulo_carnet: 'obtener_color',
                    color_id: colorId
                },
                success: function(response) {
                    try {
                        var data = JSON.parse(response);
                        if(data.color_hex) {
                            $('#preview_' + mes).css('background-color', data.color_hex);
                        }
                    } catch(e) {
                        console.error('Error parsing JSON:', e);
                    }
                }
            });
        } else {
            $('#preview_' + mes).css('background-color', '#FFFFFF');
        }
        
        // Actualizar disponibilidad
        actualizarDisponibilidadColores();
    });
    
    // Botón Restablecer: volver a valores originales
    $('button[type="reset"]').on('click', function(e) {
        e.preventDefault();
        
        // Restaurar valores originales
        $('.color-select:not(:disabled)').each(function() {
            var mes = $(this).data('mes');
            var colorOriginal = coloresOriginales[mes];
            
            // Restaurar valor
            $(this).val(colorOriginal).trigger('change');
            
            // Restaurar vista previa
            var colorHex = $(this).find('option:selected').data('color');
            if(colorHex) {
                $('#preview_' + mes).css('background-color', colorHex);
            }
        });
        
        // Actualizar disponibilidad
        setTimeout(function() {
            actualizarDisponibilidadColores();
        }, 100);
    });
    
    // Validación del formulario
    $('#quickForm').validate({
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
        },
        submitHandler: function(form) {
            // Validación de duplicados antes de enviar
            var colores = [];
            var duplicado = false;
            
            $('.color-select:not(:disabled)').each(function() {
                var valor = $(this).val();
                if(valor && valor != '0') {
                    if(colores.includes(valor)) {
                        duplicado = true;
                        return false;
                    }
                    colores.push(valor);
                }
            });
            
            if(duplicado) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error: Colores duplicados',
                    text: 'No puede asignar el mismo color a diferentes meses',
                    confirmButtonText: 'Entendido'
                });
                return false;
            }
            
            // Todo correcto, enviar formulario
            form.submit();
        }
    });
});