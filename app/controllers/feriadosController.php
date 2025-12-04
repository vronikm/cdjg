<?php
	namespace app\controllers;
	use app\models\mainModel;

	class feriadosController extends mainModel{

		/*----------  Controlador registrar feriado  ----------*/
		public function registrarFeriadoControlador(){

			# Almacenando datos#
		    $fecha = $this->limpiarCadena($_POST['feriado_fecha']);
		    $descripcion = $this->limpiarCadena($_POST['feriado_descripcion']);
		    $activo = $this->limpiarCadena($_POST['feriado_activo']);

		    # Verificando campos obligatorios #
		    if($fecha == "" || $descripcion == ""){
		    	$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "No has llenado todos los campos obligatorios",
					"icono" => "error"
				];
				return json_encode($alerta);
		    }

		    # Verificando integridad de los datos #
		    if($this->verificarDatos("[0-9\-]{10}", $fecha)){
		    	$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "La fecha no cumple con el formato solicitado",
					"icono" => "error"
				];
				return json_encode($alerta);
		    }

		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-\.\,]{1,255}", $descripcion)){
		    	$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "La descripción no cumple con el formato solicitado",
					"icono" => "error"
				];
				return json_encode($alerta);
		    }

		    # Verificar si la fecha ya existe #
		    $check_fecha = $this->ejecutarConsulta("SELECT feriado_id FROM asistencia_feriados WHERE feriado_fecha='$fecha'");
		    if($check_fecha->rowCount() > 0){
		    	$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "Ya existe un feriado registrado para esta fecha",
					"icono" => "error"
				];
				return json_encode($alerta);
		    }

		    $feriado_datos_reg = [
				[
					"campo_nombre" => "feriado_fecha",
					"campo_marcador" => ":Fecha",
					"campo_valor" => $fecha
				],
				[
					"campo_nombre" => "feriado_descripcion",
					"campo_marcador" => ":Descripcion",
					"campo_valor" => $descripcion
				],
				[
					"campo_nombre" => "feriado_activo",
					"campo_marcador" => ":Activo",
					"campo_valor" => $activo
				]
			];

			$registrar_feriado = $this->guardarDatos("asistencia_feriados", $feriado_datos_reg);

			if($registrar_feriado->rowCount() == 1){
				$alerta = [
					"tipo" => "limpiar",
					"titulo" => "Feriado registrado",
					"texto" => "El feriado ".$descripcion." se registró con éxito",
					"icono" => "success"
				];
			}else{
				$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "No se pudo registrar el feriado, por favor intente nuevamente",
					"icono" => "error"
				];
			}

			return json_encode($alerta);
		}

		/*----------  Controlador listar feriados  ----------*/
		public function listarFeriados($anio = ""){
			$tabla = "";

			$condicion = "";
			if($anio != ""){
				$condicion = "WHERE YEAR(feriado_fecha) = '$anio'";
			}

			$consulta_datos = "SELECT * FROM asistencia_feriados $condicion ORDER BY feriado_fecha ASC";

		    $datos = $this->ejecutarConsulta($consulta_datos);
		    $datos = $datos->fetchAll();

		    foreach($datos as $rows){
		    	$fecha_formato = date('d/m/Y', strtotime($rows['feriado_fecha']));
		    	$dia_semana = $this->obtenerDiaSemana($rows['feriado_fecha']);

		    	if($rows['feriado_activo'] == 1){
		    		$estado = '<span class="badge badge-success btn-xs">Activo</span>';
		    	}else{
		    		$estado = '<span class="badge badge-secondary btn-xs">Inactivo</span>';
		    	}

				$tabla.='
					<tr>
						<td>'.$rows['feriado_id'].'</td>
						<td>'.$fecha_formato.'</td>
						<td>'.$dia_semana.'</td>
						<td>'.$rows['feriado_descripcion'].'</td>
						<td>'.$estado.'</td>
						<td>
							<button type="button" class="btn btn-warning btn-sm" onclick="editarFeriado('.$rows['feriado_id'].', \''.$rows['feriado_fecha'].'\', \''.$rows['feriado_descripcion'].'\', '.$rows['feriado_activo'].')">
								<i class="fas fa-edit"></i>
							</button>
							<button type="button" class="btn btn-danger btn-sm" onclick="eliminarFeriado('.$rows['feriado_id'].', \''.$rows['feriado_descripcion'].'\')">
								<i class="fas fa-trash-alt"></i>
							</button>
						</td>
					</tr>
		        ';
		    }

		    return $tabla;
		}

		/*----------  Controlador actualizar feriado  ----------*/
		public function actualizarFeriadoControlador(){

			$id = $this->limpiarCadena($_POST['feriado_id']);

		    # Verificando feriado #
		    $datos = $this->ejecutarConsulta("SELECT * FROM asistencia_feriados WHERE feriado_id='$id'");
		    if($datos->rowCount() <= 0){
		        $alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "No hemos encontrado el feriado en el sistema",
					"icono" => "error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos = $datos->fetch();
		    }

		    # Almacenando datos#
		    $fecha = $this->limpiarCadena($_POST['feriado_fecha']);
		    $descripcion = $this->limpiarCadena($_POST['feriado_descripcion']);
		    $activo = $this->limpiarCadena($_POST['feriado_activo']);

		    # Verificando campos obligatorios #
		    if($fecha == "" || $descripcion == ""){
		    	$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "No has llenado todos los campos obligatorios",
					"icono" => "error"
				];
				return json_encode($alerta);
		    }

		    # Verificando integridad de los datos #
		    if($this->verificarDatos("[0-9\-]{10}", $fecha)){
		    	$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "La fecha no cumple con el formato solicitado",
					"icono" => "error"
				];
				return json_encode($alerta);
		    }

		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-\.\,]{1,255}", $descripcion)){
		    	$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "La descripción no cumple con el formato solicitado",
					"icono" => "error"
				];
				return json_encode($alerta);
		    }

		    # Verificar si la fecha ya existe (excepto el mismo registro) #
		    if($fecha != $datos['feriado_fecha']){
			    $check_fecha = $this->ejecutarConsulta("SELECT feriado_id FROM asistencia_feriados WHERE feriado_fecha='$fecha' AND feriado_id != '$id'");
			    if($check_fecha->rowCount() > 0){
			    	$alerta = [
						"tipo" => "simple",
						"titulo" => "Ocurrió un error inesperado",
						"texto" => "Ya existe un feriado registrado para esta fecha",
						"icono" => "error"
					];
					return json_encode($alerta);
			    }
			}

		    $feriado_datos_up = [
				[
					"campo_nombre" => "feriado_fecha",
					"campo_marcador" => ":Fecha",
					"campo_valor" => $fecha
				],
				[
					"campo_nombre" => "feriado_descripcion",
					"campo_marcador" => ":Descripcion",
					"campo_valor" => $descripcion
				],
				[
					"campo_nombre" => "feriado_activo",
					"campo_marcador" => ":Activo",
					"campo_valor" => $activo
				]
			];

			$condicion = [
				"condicion_campo" => "feriado_id",
				"condicion_marcador" => ":ID",
				"condicion_valor" => $id
			];

			if($this->actualizarDatos("asistencia_feriados", $feriado_datos_up, $condicion)){
				$alerta = [
					"tipo" => "recargar",
					"titulo" => "Feriado actualizado",
					"texto" => "El feriado ".$descripcion." se actualizó correctamente",
					"icono" => "success"
				];
			}else{
				$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "No se pudo actualizar el feriado, por favor intente nuevamente",
					"icono" => "error"
				];
			}

			return json_encode($alerta);
		}

		/*----------  Controlador eliminar feriado  ----------*/
		public function eliminarFeriadoControlador(){

			$id = $this->limpiarCadena($_POST['feriado_id']);

			# Verificando feriado #
		    $datos = $this->ejecutarConsulta("SELECT * FROM asistencia_feriados WHERE feriado_id='$id'");
		    if($datos->rowCount() <= 0){
		        $alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "No hemos encontrado el feriado en el sistema",
					"icono" => "error"
				];
				return json_encode($alerta);

		    }else{
		    	$datos = $datos->fetch();
		    }

		    $eliminarFeriado = $this->eliminarRegistro("asistencia_feriados", "feriado_id", $id);

		    if($eliminarFeriado->rowCount() == 1){
				$alerta = [
					"tipo" => "recargar",
					"titulo" => "Feriado eliminado",
					"texto" => "El feriado '".$datos['feriado_descripcion']."' ha sido eliminado del sistema exitosamente",
					"icono" => "success"
				];
			}else{
				$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "No se pudo eliminar el feriado, por favor intente nuevamente",
					"icono" => "error"
				];
			}

			return json_encode($alerta);
		}

		/*----------  Función auxiliar para obtener día de la semana  ----------*/
		private function obtenerDiaSemana($fecha){
			$dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
			$dia = date('w', strtotime($fecha));
			return $dias[$dia];
		}

	}