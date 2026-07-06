<?php

	namespace app\controllers;
	use app\models\mainModel;
	use app\services\SRI\FacturaElectronicaService;
	use app\services\SRI\FirmaElectronicaService;
	use app\services\SRI\WebServiceSRIService;

	/* Controlador para facturacion electronica, emision SRI y configuracion relacionada. */
	class facturasController extends mainModel{


		/*  funciones para el manejo de facturas */
		public function listarSedeFacturas($sedeid, $rolid = null, $usuario = null){
			$option="";

			if($rolid != 1 && $rolid != 2){
				$consulta_datos="SELECT S.sede_id, S.sede_nombre
									FROM general_sede S
									INNER JOIN seguridad_usuario_sede US ON US.usuariosede_sedeid = S.sede_id
									INNER JOIN seguridad_usuario U ON U.usuario_id = US.usuariosede_usuarioid
									WHERE U.usuario_usuario  = '".$usuario."'";
			}else{
				$consulta_datos="SELECT sede_id, sede_nombre FROM general_sede";
			}

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				if($sedeid == $rows['sede_id']){
					$option.='<option value='.$rows['sede_id'].' selected>'.$rows['sede_nombre'].'</option>';
				}else{
					$option.='<option value='.$rows['sede_id'].'>'.$rows['sede_nombre'].'</option>';
				}

			}
			return $option;
		}

		public function listarAlumnosFacturas($identificacion, $apellidopaterno, $primernombre, $anio, $sede){
			$tabla="";

			$condiciones = ["alumno_estado = 'A'"];
			$parametros = [];
			$filtrosTexto = [];

			if($identificacion !== ""){
				$filtrosTexto[] = "alumno_identificacion LIKE :identificacion";
				$parametros[':identificacion'] = $identificacion.'%';
			}

			if($primernombre !== ""){
				$filtrosTexto[] = "alumno_primernombre LIKE :primernombre";
				$parametros[':primernombre'] = $primernombre.'%';
			}

			if($apellidopaterno !== ""){
				$filtrosTexto[] = "alumno_apellidopaterno LIKE :apellidopaterno";
				$parametros[':apellidopaterno'] = $apellidopaterno.'%';
			}

			if(!empty($filtrosTexto)){
				$condiciones[] = '('.implode(' OR ', $filtrosTexto).')';
			}

			if($anio !== ""){
				$condiciones[] = "YEAR(alumno_fechanacimiento) = :anio";
				$parametros[':anio'] = $anio;
			}

			if($sede !== ""){
				if((int)$sede === 0){
					$condiciones[] = "alumno_sedeid <> 0";
				}else{
					$condiciones[] = "alumno_sedeid = :sede";
					$parametros[':sede'] = $sede;
				}
			}else{
				$condiciones[] = "1 = 0";
			}

			$consulta_datos = "SELECT *
								FROM sujeto_alumno
								WHERE ".implode(' AND ', $condiciones)."
								ORDER BY alumno_apellidopaterno, alumno_apellidomaterno, alumno_primernombre";
			$datos = $this->ejecutarConsulta($consulta_datos, $parametros);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				// Estado de facturacion del alumno (por prioridad):
				//   pagos pendientes de facturar -> azul (btn-info)
				//   ya tiene facturas (no anuladas) -> amarillo (btn-warning)
				//   sin pagos por facturar -> gris (btn-secondary)
				$alumnoId = (int)$rows['alumno_id'];
				try{
					$estado = $this->ejecutarConsulta(
						"SELECT
							(SELECT COUNT(*) FROM alumno_pago P
								WHERE P.pago_alumnoid = ".$alumnoId." AND P.pago_estado = 'C'
									AND NOT EXISTS (
										SELECT 1 FROM facturas_electronicas_detalle FD
										INNER JOIN facturas_electronicas FE ON FE.id = FD.factura_electronica_id
										WHERE FD.pago_id = P.pago_id AND FE.estado_sri <> 'ANULADA'
									)
							) AS pagos_pendientes,
							(SELECT COUNT(*) FROM facturas_electronicas FE2
								WHERE FE2.alumno_id = ".$alumnoId." AND FE2.estado_sri <> 'ANULADA'
							) AS facturas,
							(SELECT COUNT(*) FROM alumno_representante R
								WHERE R.repre_id = ".(int)($rows['alumno_repreid'] ?? 0)."
							) AS representante_valido,
							(SELECT COALESCE(MAX(R.repre_requiere_factura), 'N') FROM alumno_representante R
								WHERE R.repre_id = ".(int)($rows['alumno_repreid'] ?? 0)."
							) AS requiere_factura"
					)->fetch();
					$pagosPendientes = (int)($estado['pagos_pendientes'] ?? 0);
					$tieneFacturas   = (int)($estado['facturas'] ?? 0);
					$representanteValido = (int)($estado['representante_valido'] ?? 0) > 0;
					$requiereFactura = (($estado['requiere_factura'] ?? 'S') !== 'N');
				}catch(\Throwable $e){
					$pagosPendientes = 0;
					$tieneFacturas   = 0;
					$representanteValido = (int)($rows['alumno_repreid'] ?? 0) > 0;
					$requiereFactura = true;
				}

				$nombreAlumno = trim($rows['alumno_primernombre'].' '.$rows['alumno_segundonombre'].' '.$rows['alumno_apellidopaterno'].' '.$rows['alumno_apellidomaterno']);
				$nombreAlumno = $nombreAlumno !== '' ? $nombreAlumno : 'ID '.$alumnoId;
				$bloquearFactura = !$representanteValido || !$requiereFactura;

				if($bloquearFactura){
					$botonpago = "btn-danger";
				}elseif($tieneFacturas > 0){
					$botonpago = "btn-warning";    // ya tiene facturas generadas
				}elseif($pagosPendientes > 0){
					$botonpago = "btn-info";       // pagos pendientes de facturar
				}else{
					$botonpago = "btn-secondary";  // sin pagos por facturar
				}
				if($rows['alumno_estado']=="I"){
					$class = 'class="text-primary"';
				}else{
					$class = '';
				}

				if($bloquearFactura){
					$textoBloqueo = !$representanteValido
						? 'El alumno '.$nombreAlumno.' no tiene representante vinculado. Actualice la ficha del alumno y asigne un representante antes de generar la factura.'
						: 'El representante del alumno '.$nombreAlumno.' esta configurado como no requiere factura.';
					$mensajeBloqueo = htmlspecialchars(
						$textoBloqueo,
						ENT_QUOTES,
						'UTF-8'
					);
					$botonFactura = '<button type="button" class="btn float-right btn-danger btn-sm btn-factura-bloqueada" data-titulo="No se puede facturar" data-mensaje="'.$mensajeBloqueo.'">Registrar Factura</button>';
				}else{
					$botonFactura = '<a href="'.APP_URL.'facturasNew/'.$rows['alumno_id'].'/" class="btn float-right '.$botonpago.' btn-sm" target="_blank">Registrar Factura</a>';
				}

				$tabla.='
					<tr '.$class.'>
						<td>'.$rows['alumno_identificacion'].'</td>
						<td>'.$rows['alumno_primernombre'].' '.$rows['alumno_segundonombre'].'</td>
						<td>'.$rows['alumno_apellidopaterno'].' '.$rows['alumno_apellidomaterno'].'</td>
						<td>'.$rows['alumno_fechanacimiento'].'</td>
						<td>
							'.$botonFactura.'
						</td>
					</tr>';
			}
			return $tabla;
		}

		public function BuscarAlumnoFactura($alumnoid, $fecha_inicio,$fecha_fin){
			$consulta_datos="SELECT
								RE.repre_identificacion,
								RE.repre_direccion,
								RE.repre_correo,
								RE.repre_celular,
								RE.repre_tipoidentificacion,
								RE.repre_id,
								RE.representante,
								RE.repre_requiere_factura,
								RE.repre_factura_a_nombre,
								RE.cliente_factura_identificacion,
								RE.cliente_factura_direccion,
								RE.cliente_factura_correo,
								RE.cliente_factura_celular,
								RE.cliente_factura_tipoidentificacion,
								RE.cliente_factura_nombre,
								IFNULL(RP.pagos, 0) AS pagos   -- devuelve 0 si no hay pagos
							FROM (
								SELECT
									R.repre_identificacion,
									R.repre_direccion,
									R.repre_correo,
									R.repre_celular,
									R.repre_tipoidentificacion,
									R.repre_id,
									TRIM(CONCAT_WS(' ', R.repre_primernombre, R.repre_segundonombre, R.repre_apellidopaterno, R.repre_apellidomaterno)) AS representante,
									R.repre_requiere_factura,
									R.repre_factura_a_nombre,
									CASE WHEN R.repre_factura_a_nombre = 'CONYUGE' THEN COALESCE(C.conyuge_identificacion, '') ELSE R.repre_identificacion END AS cliente_factura_identificacion,
									CASE WHEN R.repre_factura_a_nombre = 'CONYUGE' THEN COALESCE(C.conyuge_direccion, '') ELSE R.repre_direccion END AS cliente_factura_direccion,
									CASE WHEN R.repre_factura_a_nombre = 'CONYUGE' THEN COALESCE(C.conyuge_correo, '') ELSE R.repre_correo END AS cliente_factura_correo,
									CASE WHEN R.repre_factura_a_nombre = 'CONYUGE' THEN COALESCE(C.conyuge_celular, '') ELSE R.repre_celular END AS cliente_factura_celular,
									CASE WHEN R.repre_factura_a_nombre = 'CONYUGE' THEN COALESCE(C.conyuge_tipoidentificacion, '') ELSE R.repre_tipoidentificacion END AS cliente_factura_tipoidentificacion,
									CASE WHEN R.repre_factura_a_nombre = 'CONYUGE' THEN TRIM(CONCAT_WS(' ', C.conyuge_primernombre, C.conyuge_segundonombre, C.conyuge_apellidopaterno, C.conyuge_apellidomaterno)) ELSE TRIM(CONCAT_WS(' ', R.repre_primernombre, R.repre_segundonombre, R.repre_apellidopaterno, R.repre_apellidomaterno)) END AS cliente_factura_nombre
								FROM sujeto_alumno A
								INNER JOIN alumno_representante R ON R.repre_id = A.alumno_repreid
								LEFT JOIN alumno_representanteconyuge C ON C.conyuge_repid = R.repre_id
								WHERE A.alumno_id = ".$alumnoid."
							) RE
								LEFT JOIN (
									SELECT R.repre_id, COUNT(*) AS pagos
									FROM alumno_representante R
									INNER JOIN sujeto_alumno AL ON AL.alumno_repreid = R.repre_id
									INNER JOIN alumno_pago P ON P.pago_alumnoid = AL.alumno_id
									WHERE P.pago_estado = 'C' AND P.pago_fecharegistro BETWEEN '".$fecha_inicio."' AND '".$fecha_fin."'
									GROUP BY R.repre_id
								) RP ON RP.repre_id = RE.repre_id";

			$datos = $this->ejecutarConsulta($consulta_datos);
			return $datos;
		}

		public function obtenerAlumnoFacturaBasico($alumnoid){
			$alumnoid = (int)$alumnoid;
			if($alumnoid <= 0){
				return false;
			}

			$consulta_datos="SELECT
								A.alumno_id,
								A.alumno_repreid,
								A.alumno_estado,
								TRIM(CONCAT_WS(' ', A.alumno_primernombre, A.alumno_segundonombre, A.alumno_apellidopaterno, A.alumno_apellidomaterno)) AS alumno,
								R.repre_id,
								R.repre_identificacion,
								TRIM(CONCAT_WS(' ', R.repre_primernombre, R.repre_segundonombre, R.repre_apellidopaterno, R.repre_apellidomaterno)) AS representante
							FROM sujeto_alumno A
							LEFT JOIN alumno_representante R ON R.repre_id = A.alumno_repreid
							WHERE A.alumno_id = ".$alumnoid."
							LIMIT 1";

			$datos = $this->ejecutarConsulta($consulta_datos);
			return $datos ? $datos->fetch() : false;
		}

		public function listarPagosFactura($alumnoid, $fecha_inicio, $fecha_fin){ //29052024
			$configSri = $this->sriConfig();
			$ivaTarifa = (float)($configSri['iva_tarifa_default'] ?? 0);
			$tabla="";

			$consulta_datos="SELECT
								ROW_NUMBER() OVER (ORDER BY RP.pago_id) AS fila_numero,
								RE.repre_identificacion,
								RE.repre_direccion,
								RE.repre_correo,
								RE.repre_celular,
								RE.repre_tipoidentificacion,
								RE.repre_id,
								RE.representante,
								RP.pago_fecharegistro, RP.pago_valor, RP.detalle,
								RP.alumno,
								RP.pago_id,
								RP.codigo,
								RP.forma_pago
							FROM (
								SELECT
									R.repre_identificacion,
									R.repre_direccion,
									R.repre_correo,
									R.repre_celular,
									R.repre_tipoidentificacion,
									R.repre_id,
									CONCAT(R.repre_primernombre, ' ', R.repre_segundonombre, ' ', R.repre_apellidopaterno, ' ', R.repre_apellidomaterno) AS representante
								FROM sujeto_alumno A
									INNER JOIN alumno_representante R ON R.repre_id = A.alumno_repreid
								WHERE A.alumno_id = ".$alumnoid."
							) RE
								LEFT JOIN (
									SELECT
										P.pago_fecharegistro, P.pago_valor, CONCAT(C.catalogo_descripcion, ' ', P.pago_periodo, ', ', P.pago_concepto ) AS detalle,
										CONCAT(AL.alumno_primernombre, ' ', AL.alumno_segundonombre, ' ', AL.alumno_apellidopaterno, ' ', AL.alumno_apellidomaterno) AS alumno,
										P.pago_id, R.repre_id, C.catalogo_valor AS codigo,
										F.catalogo_descripcion AS forma_pago
									FROM alumno_representante R
									INNER JOIN sujeto_alumno AL ON AL.alumno_repreid = R.repre_id
									INNER JOIN alumno_pago P ON P.pago_alumnoid = AL.alumno_id
									INNER JOIN general_tabla_catalogo C ON C.catalogo_valor = P.pago_rubroid
									LEFT JOIN general_tabla_catalogo F ON F.catalogo_valor = P.pago_formapagoid
									WHERE P.pago_estado = 'C' AND P.pago_fecharegistro BETWEEN '".$fecha_inicio."' AND '".$fecha_fin."'
										AND NOT EXISTS (
											SELECT 1 FROM facturas_electronicas_detalle FD
											INNER JOIN facturas_electronicas FE ON FE.id = FD.factura_electronica_id
											WHERE FD.pago_id = P.pago_id AND FE.estado_sri <> 'ANULADA'
										)
								) RP ON RP.repre_id = RE.repre_id";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$detallePago = htmlspecialchars($this->limpiarTextoFactura($rows['detalle'] ?? ''), ENT_QUOTES, 'UTF-8');
				$alumnoPago = htmlspecialchars($this->limpiarTextoFactura($rows['alumno'] ?? ''), ENT_QUOTES, 'UTF-8');
				$codigoPago = htmlspecialchars((string)($rows['codigo'] ?? ''), ENT_QUOTES, 'UTF-8');
				$fechaPago = htmlspecialchars((string)($rows['pago_fecharegistro'] ?? ''), ENT_QUOTES, 'UTF-8');
				$valorPago = number_format((float)($rows['pago_valor'] ?? 0), 2, '.', '');
				$formaPago = htmlspecialchars($this->limpiarTextoFactura($rows['forma_pago'] ?? ''), ENT_QUOTES, 'UTF-8');
                if(empty($rows['pago_id'])){
                    continue;
                }
				$checkbox_id = "pagoCheckbox".$rows['pago_id'];	// id ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºnico por pago
				$tabla.='
					<tr>
						<td>'.$rows['fila_numero'].'</td>
						<td>'.$fechaPago.'</td>
						<td>'.$valorPago.'</td>
						<td>'.$formaPago.'</td>
						<td>'.$detallePago.'</td>
						<td>'.$alumnoPago.'</td>
						<td>
							<div class="custom-control custom-checkbox">
								<input class="custom-control-input chk-pago"
									type="checkbox"
									id="'.$checkbox_id.'"
									name="pagos_seleccionados[]"
									value="'.$rows['pago_id'].'"
									data-codigo="'.$codigoPago.'"
									data-fecha="'.$fechaPago.'"
									data-detalle="Pago '.$detallePago.' del alumno '.$alumnoPago.'"
									data-valor="'.$valorPago.'"
									data-tarifa="'.$ivaTarifa.'">
								<label for="'.$checkbox_id.'" class="custom-control-label"></label>
							</div>
						</td>
					</tr>';
			}
            if($tabla===""){
                $tabla = '<tr><td colspan="7" class="text-center text-muted">No hay pagos pendientes de facturar en el rango seleccionado.</td></tr>';
            }
			return $tabla;
		}

		public function actualizarRepresentanteFactura(){

			$repreid=$this->limpiarCadena($_POST['repre_id']);

			# Verificando existencia de representante #
			$representante=$this->ejecutarConsulta("SELECT R.repre_id, R.repre_tipoidentificacion, R.repre_factura_a_nombre, C.conyuge_id, C.conyuge_tipoidentificacion FROM alumno_representante R LEFT JOIN alumno_representanteconyuge C ON C.conyuge_repid = R.repre_id WHERE R.repre_id='$repreid'");
			if($representante->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"El representante no se encuentra en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
			$representante=$representante->fetch();
		    }
			$facturaANombre = strtoupper(trim((string)($representante['repre_factura_a_nombre'] ?? 'REPRESENTANTE')));
			$actualizarConyuge = ($facturaANombre === 'CONYUGE');
			$clienteTexto = $actualizarConyuge ? 'conyuge' : 'representante';
			$tipoIdentificacion = $actualizarConyuge ? ($representante['conyuge_tipoidentificacion'] ?? 'CED') : ($representante['repre_tipoidentificacion'] ?? 'CED');

			if($actualizarConyuge && empty($representante['conyuge_id'])){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Conyuge no registrado",
					"texto"=>"El representante esta configurado para facturar a nombre del conyuge, pero no tiene datos de conyuge registrados.",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

			/*---------------Variables para el registro del tab Representante del alumno----------------*/
			$repre_identificacion 	  	= $this->limpiarCadena($_POST['identificacion']);
			$repre_direccion 		  	= $this->limpiarCadena($_POST['direccion']);
			$repre_correo 			  	= $this->limpiarCadena($_POST['correo']);
			$repre_celular 			  	= $this->limpiarCadena($_POST['celular']);

			# Verificando campos obligatorios #
			if($repre_identificacion=="" || $repre_direccion=="" || $repre_correo=="" || $repre_celular==""){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"No ha completado los campos obligatorios del ".$clienteTexto,
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

			if (!$this->validarIdentificacionSri($repre_identificacion, $tipoIdentificacion)) {
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"La cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dula ingresada no es vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lida",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

			if (!filter_var($repre_correo, FILTER_VALIDATE_EMAIL)) {
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"El correo ingresado no es vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

			if($actualizarConyuge){
				$conyuge_reg=[
					[
						"campo_nombre"=>"conyuge_identificacion",
						"campo_marcador"=>":Identificacion",
						"campo_valor"=>$repre_identificacion
					],
					[
						"campo_nombre"=>"conyuge_direccion",
						"campo_marcador"=>":Direccion",
						"campo_valor"=>$repre_direccion
					],
					[
						"campo_nombre"=>"conyuge_correo",
						"campo_marcador"=>":Correo",
						"campo_valor"=>$repre_correo
					],
					[
						"campo_nombre"=>"conyuge_celular",
						"campo_marcador"=>":Celular",
						"campo_valor"=>$repre_celular
					]
				];
				$condicion=[
					"condicion_campo"=>"conyuge_repid",
					"condicion_marcador"=>":Repreid",
					"condicion_valor"=>$repreid
				];

				if($this->actualizarDatos("alumno_representanteconyuge",$conyuge_reg,$condicion)){
					$alerta=[
						"tipo"=>"recargar",
						"titulo"=>"Informacion actualizada",
						"texto"=>"El conyuge de facturacion se actualizo correctamente",
						"icono"=>"success"
					];
				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Informacion no actualizada",
						"texto"=>"Por favor intente nuevamente",
						"icono"=>"alert"
					];
				}
				return json_encode($alerta);
			}

			$representante_reg=[

				[
					"campo_nombre"=>"repre_identificacion",
					"campo_marcador"=>":IdnetificacionRep",
					"campo_valor"=>$repre_identificacion
				],
				[
					"campo_nombre"=>"repre_direccion",
					"campo_marcador"=>":DireccionRep",
					"campo_valor"=>$repre_direccion
				],
				[
					"campo_nombre"=>"repre_correo",
					"campo_marcador"=>":CorreoRep",
					"campo_valor"=>$repre_correo
				],
				[
					"campo_nombre"=>"repre_celular",
					"campo_marcador"=>":CelularRep",
					"campo_valor"=>$repre_celular
				]
			];
			$condicion=[
				"condicion_campo"=>"repre_id",
				"condicion_marcador"=>":Repreid",
				"condicion_valor"=>$repreid
			];

			if($this->actualizarDatos("alumno_representante",$representante_reg,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Informacion actualizada",
					"texto"=>"El representante de facturacion se actualizo correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Informacion no actualizada",
					"texto"=>"Por favor intente nuevamente",
					"icono"=>"alert"
				];
			}
			return json_encode($alerta);
		}


		/**
		 * Defaults estructurales de la facturacion SRI (constantes de la app, no
		 * datos editables). Permiten que el sistema funcione aunque se borren
		 * config/sri.php y config/sri_local.php: los datos del emisor, parametros
		 * y formas de pago se leen de la BD; lo estructural vive aqui.
		 */
		private function sriEstructuraBase(){
			$raiz = dirname(__DIR__, 2);
			return [
				'ambiente' => (string)(getenv('CDJG_SRI_AMBIENTE') ?: '1'),
				'tipo_emision' => '1',
				'version_factura' => '2.1.0',
				'iva_tarifa_default' => (float)(getenv('CDJG_SRI_IVA_TARIFA') !== false ? getenv('CDJG_SRI_IVA_TARIFA') : 0),
				'valores_incluyen_iva' => true,
				'secuencial_inicio' => 1,
				'emisor' => [
					'ruc' => '', 'razon_social' => '', 'nombre_comercial' => '',
					'direccion_matriz' => '', 'direccion_establecimiento' => '',
					'codigo_establecimiento' => '001', 'punto_emision' => '001',
					'obligado_contabilidad' => 'NO', 'contribuyente_especial' => '',
					'agente_retencion' => '', 'contribuyente_rimpe' => '',
				],
				'firma' => [
					'archivo' => $raiz . '/storage/certificados/firma.p12',
					'clave' => getenv('CDJG_SRI_FIRMA_CLAVE') ?: '',
				],
				'correo' => [
					'from' => getenv('CDJG_FACTURACION_FROM') ?: 'facturacion@cdjg.local',
					'from_name' => getenv('CDJG_FACTURACION_FROM_NAME') ?: APP_NAME,
					'mailer' => getenv('CDJG_SMTP_HOST') ? 'smtp' : 'mail',
					'smtp' => [
						'activo' => getenv('CDJG_SMTP_HOST') ? true : false,
						'host' => getenv('CDJG_SMTP_HOST') ?: '',
						'port' => (int)(getenv('CDJG_SMTP_PORT') ?: 587),
						'usuario' => getenv('CDJG_SMTP_USER') ?: '',
						'clave' => getenv('CDJG_SMTP_PASS') ?: '',
						'seguridad' => getenv('CDJG_SMTP_SECURITY') ?: 'tls',
						'timeout' => 30,
					],
				],
				'webservices' => [
					'pruebas' => [
						'recepcion' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
						'autorizacion' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
					],
					'produccion' => [
						'recepcion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
						'autorizacion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
					],
				],
				'formas_pago' => [
					'01' => 'SIN UTILIZACION DEL SISTEMA FINANCIERO',
					'15' => 'COMPENSACION DE DEUDAS',
					'16' => 'TARJETA DE DEBITO',
					'17' => 'DINERO ELECTRONICO',
					'18' => 'TARJETA PREPAGO',
					'19' => 'TARJETA DE CREDITO',
					'20' => 'OTROS CON UTILIZACION DEL SISTEMA FINANCIERO',
					'21' => 'ENDOSO DE TITULOS',
				],
				'forma_pago_default' => getenv('CDJG_SRI_FORMA_PAGO') ?: '20',
				'impuestos' => [
					'IVA' => [
						'codigo' => '2',
						'tarifas' => [
							'0' => ['codigo' => '0', 'porcentaje' => 0],
							'12' => ['codigo' => '2', 'porcentaje' => 12],
							'14' => ['codigo' => '3', 'porcentaje' => 14],
							'15' => ['codigo' => '4', 'porcentaje' => 15],
							'NO_OBJETO' => ['codigo' => '6', 'porcentaje' => 0],
							'EXENTO' => ['codigo' => '7', 'porcentaje' => 0],
						],
					],
				],
				'storage' => [
					'xml_generados' => $raiz . '/storage/sri/xml/generados/',
					'xml_firmados' => $raiz . '/storage/sri/xml/firmados/',
					'xml_autorizados' => $raiz . '/storage/sri/xml/autorizados/',
					'ride' => $raiz . '/storage/sri/ride/',
					'logs' => $raiz . '/storage/sri/logs/',
					'certificados' => $raiz . '/storage/certificados/',
				],
			];
		}

		private function sriConfig(){
			// Base estructural en codigo (siempre presente, aunque falten los archivos)
			$config = $this->sriEstructuraBase();
			$configPath = dirname(__DIR__, 2).'/config/sri.php';
			if(is_file($configPath)){
				$archivo = require $configPath;
				if(is_array($archivo)){
					$config = array_replace_recursive($config, $archivo);
				}
			}

			// 1. Fuente principal: tabla facturas_electronicas_config
			$bd = $this->configEmisorDesdeBd();
			if(!empty($bd)){
				$config = array_replace_recursive($config, $bd);
			}else{
				// 2. Fallback legacy: config/sri_local.php (pre-wiring / BD no disponible)
				$localPath = dirname(__DIR__, 2).'/config/sri_local.php';
				if(is_file($localPath)){
					$local = require $localPath;
					if(is_array($local)){
						$config = array_replace_recursive($config, $local);
					}
				}
			}

			// 3. Catalogo de formas de pago desde la BD (si esta poblado)
			$formas = $this->formasPagoDesdeBd();
			if(!empty($formas)){
				$config['formas_pago'] = $formas;
			}

			return $config;
		}

		/**
		 * Devuelve el overlay de emisor + parametros desde la tabla
		 * facturas_electronicas_config, o [] si no hay fila configurada
		 * (RUC vacio) o la BD no esta disponible.
		 */
		private function configEmisorDesdeBd(){
			try{
				$stmt = $this->ejecutarConsulta("SELECT * FROM facturas_electronicas_config WHERE config_lock = 'X' LIMIT 1");
				$row = $stmt->fetch();
			}catch(\Throwable $e){
				return [];
			}
			if(!$row){
				return [];
			}
			$config = [
				'ambiente' => (string)$row['ambiente'],
				'tipo_emision' => (string)$row['tipo_emision'],
				'iva_tarifa_default' => (float)$row['iva_tarifa_default'],
				'forma_pago_default' => (string)$row['forma_pago_default'],
				'valores_incluyen_iva' => (bool)((int)$row['valores_incluyen_iva']),
				'secuencial_inicio' => max(1, (int)($row['secuencial_inicio'] ?? 1)),
			];

			if(trim((string)($row['ruc'] ?? '')) !== ''){
				$config['emisor'] = [
					'ruc' => (string)$row['ruc'],
					'razon_social' => (string)$row['razon_social'],
					'nombre_comercial' => (string)$row['nombre_comercial'],
					'direccion_matriz' => (string)$row['direccion_matriz'],
					'direccion_establecimiento' => (string)$row['direccion_establecimiento'],
					'codigo_establecimiento' => (string)$row['codigo_establecimiento'],
					'punto_emision' => (string)$row['punto_emision'],
					'obligado_contabilidad' => (string)$row['obligado_contabilidad'],
					'contribuyente_especial' => (string)$row['contribuyente_especial'],
					'agente_retencion' => (string)$row['agente_retencion'],
					'contribuyente_rimpe' => (string)$row['contribuyente_rimpe'],
				];
			}

			if(array_key_exists('correo_from', $row)){
				$smtpActivo = !empty($row['smtp_activo']) && trim((string)($row['smtp_host'] ?? '')) !== '';
				$config['correo'] = [
					'from' => trim((string)($row['correo_from'] ?? '')) ?: (getenv('CDJG_FACTURACION_FROM') ?: 'facturacion@cdjg.local'),
					'from_name' => trim((string)($row['correo_from_nombre'] ?? '')) ?: APP_NAME,
					'mailer' => $smtpActivo ? 'smtp' : 'mail',
					'smtp' => [
						'activo' => $smtpActivo,
						'host' => trim((string)($row['smtp_host'] ?? '')),
						'port' => max(1, (int)($row['smtp_port'] ?? 587)),
						'usuario' => trim((string)($row['smtp_usuario'] ?? '')),
						'clave' => $this->descifrarClaveFirma($row['smtp_clave_cifrada'] ?? ''),
						'clave_configurada' => trim((string)($row['smtp_clave_cifrada'] ?? '')) !== '',
						'seguridad' => in_array((string)($row['smtp_seguridad'] ?? 'tls'), ['tls', 'ssl', 'ninguna'], true) ? (string)$row['smtp_seguridad'] : 'tls',
						'timeout' => 30,
					],
				];
			}

			return $config;
		}

		/** Catalogo SRI de formas de pago desde la BD (codigo => nombre). */
		private function formasPagoDesdeBd(){
			try{
				$stmt = $this->ejecutarConsulta("SELECT codigo, nombre FROM facturas_electronicas_forma_pago WHERE activo = 1 ORDER BY orden, codigo");
				$rows = $stmt->fetchAll();
			}catch(\Throwable $e){
				return [];
			}
			$formas = [];
			foreach($rows as $r){
				$formas[(string)$r['codigo']] = (string)$r['nombre'];
			}
			return $formas;
		}

		public function obtenerConfiguracionSri(){
			return $this->sriConfig();
		}

		private function rutaConfigLocalSri(){
			return dirname(__DIR__, 2).'/config/sri_local.php';
		}

		private function textoConfigSri($valor, $limite=300){
			$valor = trim(strip_tags((string)$valor));
			$valor = preg_replace('/\s+/', ' ', $valor);
			return function_exists('mb_substr') ? mb_substr($valor, 0, $limite, 'UTF-8') : substr($valor, 0, $limite);
		}

        private function valorNoAplicaSri($valor){
            $valor = strtoupper(trim((string)$valor));
            return $valor === '' || in_array($valor, ['NO', 'N/A', 'NA', 'NINGUNO', 'NO APLICA', '0'], true);
        }

        private function normalizarTextoOpcionalSri($valor, $limite=300){
            $valor = $this->textoConfigSri($valor, $limite);
            return $this->valorNoAplicaSri($valor) ? '' : $valor;
        }

        private function normalizarNumeroOpcionalSri($valor, $limite=13){
            $valor = $this->textoConfigSri($valor, $limite);
            if($this->valorNoAplicaSri($valor)){ return ''; }
            return preg_replace('/\D+/', '', $valor);
        }

		private function autorizadoConfigSri(){
			return in_array((int)($_SESSION['rol'] ?? 0), [1, 2], true);
		}

		private function rutaClaveFirmaSri(){
			return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'certificados'.DIRECTORY_SEPARATOR.'firma.key.php';
		}

		/**
		 * Ruta del secreto de cifrado, fuera del control de versiones.
		 * Vive en storage/ (protegido por .htaccess) y NO debe versionarse.
		 */
		private function rutaSecretoCriptoSri(){
			return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'certificados'.DIRECTORY_SEPARATOR.'.cryptokey.php';
		}

		/**
		 * Secreto aleatorio (32 bytes) persistido en disco fuera del repo.
		 * Si $generar es true y no existe, se crea uno nuevo.
		 */
		private function obtenerSecretoCriptoSri($generar = false){
			$path = $this->rutaSecretoCriptoSri();
			if(is_file($path)){
				$raw = trim(str_replace('<?php exit; ?>', '', (string)file_get_contents($path)));
				$secreto = base64_decode($raw, true);
				if($secreto !== false && $secreto !== ''){
					return $secreto;
				}
			}
			if($generar && function_exists('random_bytes')){
				$this->protegerDirectorioSri(dirname($path));
				$secreto = random_bytes(32);
				if(file_put_contents($path, "<?php exit; ?>\n".base64_encode($secreto), LOCK_EX) !== false){
					@chmod($path, 0600);
					return $secreto;
				}
			}
			return '';
		}

		/**
		 * Llave para CIFRAR: la mas fuerte disponible.
		 * Prioridad: variable de entorno CDJG_SRI_CRYPTO_KEY (secreto explicito)
		 *  > secreto aleatorio en storage (se genera si falta)
		 *  > derivacion legacy (constantes publicas) solo como ultimo recurso.
		 */
		private function claveCriptoSri(){
			$env = getenv('CDJG_SRI_CRYPTO_KEY');
			if($env !== false && $env !== ''){
				return hash('sha256', $env, true);
			}
			$secreto = $this->obtenerSecretoCriptoSri(true);
			if($secreto !== ''){
				return hash('sha256', $secreto, true);
			}
			return hash('sha256', APP_SESSION_NAME.'|'.APP_URL, true);
		}

		/**
		 * Llaves candidatas para DESCIFRAR (incluye la legacy para poder leer
		 * datos cifrados con la llave antigua durante la transicion).
		 */
		private function clavesCriptoSriCandidatas(){
			$claves = [];
			$env = getenv('CDJG_SRI_CRYPTO_KEY');
			if($env !== false && $env !== ''){
				$claves[] = hash('sha256', $env, true);
			}
			$secreto = $this->obtenerSecretoCriptoSri(false);
			if($secreto !== ''){
				$claves[] = hash('sha256', $secreto, true);
			}
			$claves[] = hash('sha256', APP_SESSION_NAME.'|'.APP_URL, true); // legacy
			return $claves;
		}

		private function protegerDirectorioSri($dir){
			if(!is_dir($dir)){
				mkdir($dir, 0755, true);
			}

			$htaccess = rtrim($dir, '/\\').DIRECTORY_SEPARATOR.'.htaccess';
			// Apache 2.4 usa mod_authz_core (Require); el "Deny" legacy de 2.2 se
			// envuelve en IfModule para no romper el servidor cuando mod_access_compat
			// no esta cargado (provocaba HTTP 500 en vez de denegar limpiamente).
			$reglas = "Options -Indexes\nRequire all denied\n<IfModule mod_access_compat.c>\n    Deny from all\n</IfModule>\n";
			if(!is_file($htaccess) || trim((string)@file_get_contents($htaccess)) !== trim($reglas)){
				file_put_contents($htaccess, $reglas, LOCK_EX);
			}
		}

		private function cifrarTextoSri($texto){
			if(!function_exists('openssl_encrypt')){
				return '';
			}

			$iv = random_bytes(16);
			$cipher = openssl_encrypt((string)$texto, 'AES-256-CBC', $this->claveCriptoSri(), OPENSSL_RAW_DATA, $iv);

			if($cipher === false){
				return '';
			}

			return base64_encode(json_encode([
				'method' => 'AES-256-CBC',
				'iv' => base64_encode($iv),
				'data' => base64_encode($cipher)
			]));
		}

		private function guardarClaveFirmaSri($clave){
			$payload = $this->cifrarTextoSri($clave);
			if($payload === ''){
				return false;
			}

			return $this->guardarClaveFirmaEnBd($payload);
		}

		/** Persiste el blob cifrado de la clave de la firma en la BD (fila unica). */
		private function guardarClaveFirmaEnBd($payload){
			try{
				$conexion = $this->conectar();
				$stmt = $conexion->prepare(
					"INSERT INTO facturas_electronicas_certificado (cert_lock, clave_cifrada)
					 VALUES ('X', :clave)
					 ON DUPLICATE KEY UPDATE clave_cifrada = VALUES(clave_cifrada)"
				);
				return $stmt->execute([':clave' => $payload]);
			}catch(\Throwable $e){
				return false;
			}
		}

		/** Lee el blob cifrado de la clave de la firma desde la BD. */
		private function leerClaveFirmaDeBd(){
			try{
				$stmt = $this->ejecutarConsulta("SELECT clave_cifrada FROM facturas_electronicas_certificado WHERE cert_lock = 'X' LIMIT 1");
				$row = $stmt->fetch();
				return ($row && !empty($row['clave_cifrada'])) ? (string)$row['clave_cifrada'] : '';
			}catch(\Throwable $e){
				return '';
			}
		}

		/** Descifra un blob (base64 de JSON) probando las llaves candidatas. */
		private function descifrarClaveFirma($raw){
			$raw = trim((string)$raw);
			if($raw === '' || !function_exists('openssl_decrypt')){
				return '';
			}
			$payload = json_decode(base64_decode($raw), true);
			if(!is_array($payload) || empty($payload['iv']) || empty($payload['data'])){
				return '';
			}
			$method = $payload['method'] ?? 'AES-256-CBC';
			$data = base64_decode($payload['data']);
			$iv = base64_decode($payload['iv']);
			foreach($this->clavesCriptoSriCandidatas() as $key){
				$clave = openssl_decrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
				if($clave !== false && $clave !== ''){
					return (string)$clave;
				}
			}
			return '';
		}

		/**
		 * Migra la clave de la firma desde el archivo legacy (firma.key.php) a la BD,
		 * recifrandola con la llave endurecida. Idempotente.
		 */
		public function migrarClaveFirmaSriABd(){
			if($this->descifrarClaveFirma($this->leerClaveFirmaDeBd()) !== ''){
				return 'YA_EN_BD';
			}
			$path = $this->rutaClaveFirmaSri();
			if(!is_file($path)){
				return 'SIN_ARCHIVO';
			}
			$raw = trim(str_replace('<?php exit; ?>', '', (string)file_get_contents($path)));
			$clave = $this->descifrarClaveFirma($raw);
			if($clave === ''){
				return 'NO_DESCIFRABLE';
			}
			return $this->guardarClaveFirmaSri($clave) ? 'MIGRADA' : 'ERROR_BD';
		}

		private function leerClaveFirmaSri(){
			$config = $this->sriConfig();
			$env = getenv('CDJG_SRI_FIRMA_CLAVE');
			if($env !== false && $env !== ''){
				return (string)$env;
			}

			if(!empty($config['firma']['clave'])){
				return (string)$config['firma']['clave'];
			}

			// 1. Ubicacion principal: base de datos
			$clave = $this->descifrarClaveFirma($this->leerClaveFirmaDeBd());
			if($clave !== ''){
				return $clave;
			}

			// 2. Fallback: archivo legacy firma.key.php (compatibilidad / pre-migracion)
			$path = $this->rutaClaveFirmaSri();
			if(is_file($path)){
				$raw = trim(str_replace('<?php exit; ?>', '', (string)file_get_contents($path)));
				return $this->descifrarClaveFirma($raw);
			}

			return '';
		}

        private function extraerRucCertificadoSri(array $certInfo){
            $extensions = $certInfo['extensions'] ?? [];

            $normalizarValor = static function($valor){
                $valor = preg_replace('/[^\PC\s]+/u', '', (string)$valor);
                $valor = preg_replace('/\s+/', ' ', $valor);
                return trim($valor);
            };

            $oidsRuc = [
                '1.3.6.1.4.1.37746.3.11', // Security Data: RUC de la razon social
                '1.3.6.1.4.1.59382.3.11',
            ];

            foreach($oidsRuc as $oid){
                if(!empty($extensions[$oid])){
                    $valor = $normalizarValor($extensions[$oid]);
                    if(preg_match('/(?<!\d)\d{13}(?!\d)/', $valor, $match)){
                        return $match[0];
                    }
                }
            }

            $campos = array_merge($certInfo['subject'] ?? [], $extensions);
            foreach($campos as $clave=>$valor){
                $texto = strtoupper((string)$clave).' '.$normalizarValor($valor);
                if(preg_match('/(?:RUC|REGISTRO UNICO DE CONTRIBUYENTES)[^\d]*(\d{13})(?!\d)/u', $texto, $match)){
                    return $match[1];
                }
            }

            foreach($campos as $valor){
                $valor = $normalizarValor($valor);
                if(preg_match('/(?<!\d)\d{13}(?!\d)/', $valor, $match)){
                    return $match[0];
                }
            }
            return '';
        }

		public function obtenerInfoCertificadoSri(){
			$config = $this->sriConfig();
			$archivo = $config['firma']['archivo'] ?? '';
			$info = [
				'estado' => 'NO_CONFIGURADO',
				'archivo' => $archivo ? basename($archivo) : '',
				'ruta' => $archivo,
				'titular' => '',
				'emisor' => '',
                'ruc' => '',
				'serial' => '',
				'valido_desde' => '',
				'valido_hasta' => '',
				'dias_restantes' => null
			];

			if($archivo === '' || !is_file($archivo)){
				return $info;
			}

			$clave = $this->leerClaveFirmaSri();
			if($clave === ''){
				$info['estado'] = 'SIN_CLAVE';
				return $info;
			}

			if(!function_exists('openssl_pkcs12_read')){
				$info['estado'] = 'OPENSSL_NO_DISPONIBLE';
				return $info;
			}

			$certs = [];
			if(!openssl_pkcs12_read(file_get_contents($archivo), $certs, $clave) || empty($certs['cert'])){
				$info['estado'] = 'CLAVE_INVALIDA';
				return $info;
			}

			$certInfo = openssl_x509_parse($certs['cert']);
			if(!is_array($certInfo)){
				$info['estado'] = 'NO_LEIBLE';
				return $info;
			}

			$subject = $certInfo['subject'] ?? [];
			$issuer = $certInfo['issuer'] ?? [];
			$validFrom = $certInfo['validFrom_time_t'] ?? null;
			$validTo = $certInfo['validTo_time_t'] ?? null;
			$dias = $validTo ? (int)floor(($validTo - time()) / 86400) : null;

			$info['estado'] = ($validTo && $validTo < time()) ? 'CADUCADO' : 'VALIDO';
			$info['titular'] = $subject['CN'] ?? ($subject['commonName'] ?? '');
			$info['emisor'] = $issuer['CN'] ?? ($issuer['commonName'] ?? '');
            $info['ruc'] = $this->extraerRucCertificadoSri($certInfo);
            $rucEmisor = preg_replace('/\D+/', '', (string)($config['emisor']['ruc'] ?? ''));
            if($info['ruc'] !== '' && $rucEmisor !== '' && $info['ruc'] !== $rucEmisor){
                $info['estado'] = 'RUC_NO_COINCIDE';
            }
			$info['serial'] = (string)($certInfo['serialNumberHex'] ?? ($certInfo['serialNumber'] ?? ''));
			$info['valido_desde'] = $validFrom ? date('Y-m-d H:i', $validFrom) : '';
			$info['valido_hasta'] = $validTo ? date('Y-m-d H:i', $validTo) : '';
			$info['dias_restantes'] = $dias;

			return $info;
		}

		public function guardarConfiguracionSri(){
			if(!$this->autorizadoConfigSri()){
				return $this->respuestaJson('simple', 'Acceso restringido', 'Solo un usuario administrador puede modificar la configuracion SRI.', 'error');
			}

			$config = $this->sriConfig();
			$formasPago = $config['formas_pago'] ?? [];
			$ambiente = in_array((string)($_POST['ambiente'] ?? '1'), ['1', '2'], true) ? (string)$_POST['ambiente'] : '1';
			$ruc = preg_replace('/\D+/', '', (string)($_POST['ruc'] ?? ''));
			$establecimiento = str_pad(substr(preg_replace('/\D+/', '', (string)($_POST['codigo_establecimiento'] ?? '')), 0, 3), 3, '0', STR_PAD_LEFT);
			$puntoEmision = str_pad(substr(preg_replace('/\D+/', '', (string)($_POST['punto_emision'] ?? '')), 0, 3), 3, '0', STR_PAD_LEFT);
			$iva = (string)($_POST['iva_tarifa_default'] ?? '0');
			$formaPago = (string)($_POST['forma_pago_default'] ?? '20');
			$obligado = strtoupper((string)($_POST['obligado_contabilidad'] ?? 'NO')) === 'SI' ? 'SI' : 'NO';
			$secuencialInicio = max(1, min(999999999, (int)($_POST['secuencial_inicio'] ?? 1)));
			$correoFrom = $this->textoConfigSri($_POST['correo_from'] ?? ($config['correo']['from'] ?? ''), 150);
			$correoFromNombre = $this->textoConfigSri($_POST['correo_from_nombre'] ?? ($config['correo']['from_name'] ?? APP_NAME), 150);
			$smtpActivo = (int)($_POST['smtp_activo'] ?? 0) === 1 ? 1 : 0;
			$smtpHost = $this->textoConfigSri($_POST['smtp_host'] ?? '', 150);
			$smtpPort = max(1, min(65535, (int)($_POST['smtp_port'] ?? 587)));
			$smtpUsuario = $this->textoConfigSri($_POST['smtp_usuario'] ?? '', 150);
			$smtpSeguridad = in_array((string)($_POST['smtp_seguridad'] ?? 'tls'), ['tls', 'ssl', 'ninguna'], true) ? (string)$_POST['smtp_seguridad'] : 'tls';
			$smtpClave = (string)($_POST['smtp_clave'] ?? '');
			$smtpClaveCifrada = $smtpClave !== '' ? $this->cifrarTextoSri($smtpClave) : null;
			$smtpClaveYaConfigurada = !empty($config['correo']['smtp']['clave_configurada']) || !empty($config['correo']['smtp']['clave']);

			if(strlen($ruc) !== 13 || substr($ruc, -3) === '000'){
				return $this->respuestaJson('simple', 'RUC no valido', 'El RUC del emisor debe tener 13 digitos y terminar con un codigo de establecimiento valido.', 'error');
			}

			if($establecimiento === '000' || $puntoEmision === '000'){
				return $this->respuestaJson('simple', 'Establecimiento no valido', 'El establecimiento y punto de emision deben tener tres digitos y no pueden ser 000.', 'error');
			}

			if(!in_array($iva, ['0', '12', '14', '15'], true)){
				return $this->respuestaJson('simple', 'IVA no valido', 'Seleccione una tarifa IVA permitida por la configuracion del SRI.', 'error');
			}

			if(!isset($formasPago[$formaPago])){
				return $this->respuestaJson('simple', 'Forma de pago no valida', 'Seleccione una forma de pago del catalogo SRI.', 'error');
			}

			if($correoFrom !== '' && !filter_var($correoFrom, FILTER_VALIDATE_EMAIL)){
				return $this->respuestaJson('simple', 'Correo no valido', 'El correo remitente no tiene un formato valido.', 'warning');
			}

			if($smtpActivo === 1){
				if($correoFrom === '' || $smtpHost === ''){
					return $this->respuestaJson('simple', 'SMTP incompleto', 'Complete correo remitente y servidor SMTP.', 'warning');
				}
				if($smtpUsuario !== '' && $smtpClave === '' && !$smtpClaveYaConfigurada){
					return $this->respuestaJson('simple', 'Clave SMTP requerida', 'Ingrese la clave SMTP o app password para autenticar el envio.', 'warning');
				}
				if($smtpUsuario !== '' && $smtpClave !== '' && $smtpClaveCifrada === ''){
					return $this->respuestaJson('simple', 'Clave SMTP no guardada', 'No fue posible cifrar la clave SMTP. Verifique OpenSSL en PHP.', 'error');
				}
			}

			$local = [
				'ambiente' => $ambiente,
				'iva_tarifa_default' => (float)$iva,
				'forma_pago_default' => $formaPago,
				'emisor' => [
					'ruc' => $ruc,
					'razon_social' => $this->textoConfigSri($_POST['razon_social'] ?? '', 300),
					'nombre_comercial' => $this->textoConfigSri($_POST['nombre_comercial'] ?? '', 300),
					'direccion_matriz' => $this->textoConfigSri($_POST['direccion_matriz'] ?? '', 300),
					'direccion_establecimiento' => $this->textoConfigSri($_POST['direccion_establecimiento'] ?? '', 300),
					'codigo_establecimiento' => $establecimiento,
					'punto_emision' => $puntoEmision,
					'obligado_contabilidad' => $obligado,
                    'contribuyente_especial' => $this->normalizarNumeroOpcionalSri($_POST['contribuyente_especial'] ?? '', 13),
                    'agente_retencion' => $this->normalizarNumeroOpcionalSri($_POST['agente_retencion'] ?? '', 8),
                    'contribuyente_rimpe' => $this->normalizarTextoOpcionalSri($_POST['contribuyente_rimpe'] ?? '', 300)
				]
			];

			foreach(['razon_social', 'direccion_matriz', 'direccion_establecimiento'] as $campo){
				if($local['emisor'][$campo] === ''){
					return $this->respuestaJson('simple', 'Datos incompletos', 'Complete razon social, direccion matriz y direccion del establecimiento.', 'warning');
				}
			}

			$em = $local['emisor'];
			$guardado = $this->guardarConfigEmisorEnBd([
				'ambiente' => $local['ambiente'],
				'tipo_emision' => (string)($config['tipo_emision'] ?? '1'),
				'iva_tarifa_default' => (float)$local['iva_tarifa_default'],
				'forma_pago_default' => $local['forma_pago_default'],
				'valores_incluyen_iva' => !empty($config['valores_incluyen_iva']) ? 1 : 0,
				'secuencial_inicio' => $secuencialInicio,
				'ruc' => $em['ruc'],
				'razon_social' => $em['razon_social'],
				'nombre_comercial' => $em['nombre_comercial'],
				'direccion_matriz' => $em['direccion_matriz'],
				'direccion_establecimiento' => $em['direccion_establecimiento'],
				'codigo_establecimiento' => $em['codigo_establecimiento'],
				'punto_emision' => $em['punto_emision'],
				'obligado_contabilidad' => $em['obligado_contabilidad'],
				'contribuyente_especial' => $em['contribuyente_especial'],
				'agente_retencion' => $em['agente_retencion'],
				'contribuyente_rimpe' => $em['contribuyente_rimpe'],
				'correo_from' => $correoFrom,
				'correo_from_nombre' => $correoFromNombre,
				'smtp_activo' => $smtpActivo,
				'smtp_host' => $smtpHost,
				'smtp_port' => $smtpPort,
				'smtp_usuario' => $smtpUsuario,
				'smtp_clave_cifrada' => $smtpClaveCifrada,
				'smtp_seguridad' => $smtpSeguridad,
			]);

			if(!$guardado){
				return $this->respuestaJson('simple', 'No se pudo guardar', 'No fue posible guardar la configuracion en la base de datos.', 'error');
			}

			return $this->respuestaJson('recargar', 'Configuracion guardada', 'La configuracion de facturacion electronica fue actualizada.', 'success');
		}

		/** Upsert de la fila unica de configuracion del emisor en la BD. */
		private function guardarConfigEmisorEnBd(array $d){
			try{
				$conexion = $this->conectar();
				$stmt = $conexion->prepare(
					"INSERT INTO facturas_electronicas_config
						(config_lock, ambiente, tipo_emision, iva_tarifa_default, forma_pago_default,
						 valores_incluyen_iva, secuencial_inicio, ruc, razon_social, nombre_comercial, direccion_matriz,
						 direccion_establecimiento, codigo_establecimiento, punto_emision,
						 obligado_contabilidad, contribuyente_especial, agente_retencion,
						 contribuyente_rimpe, correo_from, correo_from_nombre, smtp_activo, smtp_host, smtp_port,
						 smtp_usuario, smtp_clave_cifrada, smtp_seguridad, updated_by)
					 VALUES ('X', :ambiente, :tipo_emision, :iva, :fp, :vii, :sini, :ruc, :rs, :nc, :dm, :de,
						 :ce, :pe, :oc, :cesp, :ar, :rimpe, :correo_from, :correo_from_nombre, :smtp_activo,
						 :smtp_host, :smtp_port, :smtp_usuario, :smtp_clave_cifrada, :smtp_seguridad, :uby)
					 ON DUPLICATE KEY UPDATE
						 ambiente=VALUES(ambiente), tipo_emision=VALUES(tipo_emision),
						 iva_tarifa_default=VALUES(iva_tarifa_default), forma_pago_default=VALUES(forma_pago_default),
						 valores_incluyen_iva=VALUES(valores_incluyen_iva), secuencial_inicio=VALUES(secuencial_inicio), ruc=VALUES(ruc),
						 razon_social=VALUES(razon_social), nombre_comercial=VALUES(nombre_comercial),
						 direccion_matriz=VALUES(direccion_matriz), direccion_establecimiento=VALUES(direccion_establecimiento),
						 codigo_establecimiento=VALUES(codigo_establecimiento), punto_emision=VALUES(punto_emision),
						 obligado_contabilidad=VALUES(obligado_contabilidad),
						 contribuyente_especial=VALUES(contribuyente_especial),
						 agente_retencion=VALUES(agente_retencion), contribuyente_rimpe=VALUES(contribuyente_rimpe),
						 correo_from=VALUES(correo_from), correo_from_nombre=VALUES(correo_from_nombre),
						 smtp_activo=VALUES(smtp_activo), smtp_host=VALUES(smtp_host), smtp_port=VALUES(smtp_port),
						 smtp_usuario=VALUES(smtp_usuario),
						 smtp_clave_cifrada=COALESCE(VALUES(smtp_clave_cifrada), smtp_clave_cifrada),
						 smtp_seguridad=VALUES(smtp_seguridad),
						 updated_by=VALUES(updated_by)"
				);
				return $stmt->execute([
					':ambiente'=>$d['ambiente'], ':tipo_emision'=>$d['tipo_emision'], ':iva'=>$d['iva_tarifa_default'],
					':fp'=>$d['forma_pago_default'], ':vii'=>$d['valores_incluyen_iva'], ':sini'=>$d['secuencial_inicio'], ':ruc'=>$d['ruc'],
					':rs'=>$d['razon_social'], ':nc'=>$d['nombre_comercial'], ':dm'=>$d['direccion_matriz'],
					':de'=>$d['direccion_establecimiento'], ':ce'=>$d['codigo_establecimiento'], ':pe'=>$d['punto_emision'],
					':oc'=>$d['obligado_contabilidad'], ':cesp'=>$d['contribuyente_especial'], ':ar'=>$d['agente_retencion'],
					':rimpe'=>$d['contribuyente_rimpe'], ':correo_from'=>$d['correo_from'], ':correo_from_nombre'=>$d['correo_from_nombre'],
					':smtp_activo'=>$d['smtp_activo'], ':smtp_host'=>$d['smtp_host'], ':smtp_port'=>$d['smtp_port'],
					':smtp_usuario'=>$d['smtp_usuario'], ':smtp_clave_cifrada'=>$d['smtp_clave_cifrada'],
					':smtp_seguridad'=>$d['smtp_seguridad'], ':uby'=>($_SESSION['usuarioid'] ?? null),
				]);
			}catch(\Throwable $e){
				return false;
			}
		}

		public function subirCertificadoSri(){
			if(!$this->autorizadoConfigSri()){
				return $this->respuestaJson('simple', 'Acceso restringido', 'Solo un usuario administrador puede cargar la firma electronica.', 'error');
			}

			$clave = (string)($_POST['clave_certificado'] ?? '');
			if($clave === ''){
				return $this->respuestaJson('simple', 'Clave requerida', 'Ingrese la clave de la firma electronica para validarla.', 'warning');
			}

			if(!function_exists('openssl_pkcs12_read')){
				return $this->respuestaJson('simple', 'OpenSSL no disponible', 'Active la extension OpenSSL de PHP para validar la firma electronica.', 'error');
			}

            $config = $this->sriConfig();
            $destino = $config['firma']['archivo'] ?? (dirname(__DIR__, 2).'/storage/certificados/firma.p12');
            $archivoSubido = !empty($_FILES['certificado']['tmp_name']) && ($_FILES['certificado']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
            $archivoValidar = $archivoSubido ? $_FILES['certificado']['tmp_name'] : $destino;

            if(!$archivoSubido && !is_file($destino)){
				return $this->respuestaJson('simple', 'Archivo requerido', 'Seleccione el archivo .p12 de la firma electronica.', 'warning');
			}

            if($archivoSubido){
                $nombre = $_FILES['certificado']['name'] ?? '';
                $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
                if(!in_array($extension, ['p12', 'pfx'], true)){
                    return $this->respuestaJson('simple', 'Formato no permitido', 'La firma debe ser un archivo .p12 o .pfx.', 'error');
                }

                if((int)($_FILES['certificado']['size'] ?? 0) > 5242880){
                    return $this->respuestaJson('simple', 'Archivo muy grande', 'La firma no debe superar 5 MB.', 'error');
                }
            }

			$contenido = file_get_contents($archivoValidar);
			if($contenido === false || strlen($contenido) < 10){
				return $this->respuestaJson('simple', 'Archivo no legible', 'No fue posible leer el archivo de firma electronica.', 'error');
			}

			$lecturaCertificado = $this->leerPkcs12ConVariantes($contenido, $clave, $archivoValidar);
			if(empty($lecturaCertificado['ok'])){
				return $this->respuestaJson('simple', 'Firma no valida', $lecturaCertificado['mensaje'] ?? 'No fue posible leer el certificado con la clave ingresada.', 'error');
			}
			$certs = $lecturaCertificado['certs'];
			$clave = $lecturaCertificado['clave'];
			$contenido = $lecturaCertificado['contenido'];

			$certInfo = openssl_x509_parse($certs['cert']);
            $rucEmisor = preg_replace('/\D+/', '', (string)($config['emisor']['ruc'] ?? ''));
            $rucCertificado = is_array($certInfo) ? $this->extraerRucCertificadoSri($certInfo) : '';
            if($rucCertificado !== '' && $rucEmisor !== '' && $rucCertificado !== $rucEmisor){
                return $this->respuestaJson('simple', 'Firma no corresponde al emisor', 'El RUC del certificado '.$rucCertificado.' no coincide con el RUC emisor configurado '.$rucEmisor.'. Cargue la firma electronica del contribuyente correcto.', 'error');
            }
			if(is_array($certInfo) && !empty($certInfo['validTo_time_t']) && $certInfo['validTo_time_t'] < time()){
				return $this->respuestaJson('simple', 'Firma caducada', 'El certificado seleccionado ya se encuentra caducado.', 'error');
			}

			$this->protegerDirectorioSri(dirname($destino));

            if($archivoSubido){
                if(file_put_contents($destino, $contenido, LOCK_EX) === false){
                    return $this->respuestaJson('simple', 'No se pudo guardar', 'Revise permisos de escritura en storage/certificados.', 'error');
                }
            }

			if(!$this->guardarClaveFirmaSri($clave)){
				return $this->respuestaJson('simple', 'Clave no guardada', 'La firma fue validada, pero no se pudo proteger la clave localmente.', 'error');
			}

			// Persistir metadatos del certificado para la administracion de la vista
			$this->guardarMetadatosCertificadoSri();

			return $this->respuestaJson('recargar', 'Firma cargada', 'La firma electronica fue validada y almacenada correctamente.', 'success');
		}

		/** Upsert de los metadatos del certificado (no toca clave_cifrada). */
		private function guardarMetadatosCertificadoSri(){
			try{
				$info = $this->obtenerInfoCertificadoSri();
				$toDt = static function($s){ $s = trim((string)$s); if($s === ''){ return null; } $ts = strtotime($s); return $ts ? date('Y-m-d H:i:s', $ts) : null; };
				$conexion = $this->conectar();
				$stmt = $conexion->prepare(
					"INSERT INTO facturas_electronicas_certificado
						(cert_lock, archivo, nombre_original, ruc_certificado, razon_social, serial, valido_desde, valido_hasta, estado, updated_by)
					 VALUES ('X', :archivo, :nombre, :ruc, :rs, :serial, :vd, :vh, :estado, :uby)
					 ON DUPLICATE KEY UPDATE
						 archivo=VALUES(archivo), nombre_original=VALUES(nombre_original), ruc_certificado=VALUES(ruc_certificado),
						 razon_social=VALUES(razon_social), serial=VALUES(serial), valido_desde=VALUES(valido_desde),
						 valido_hasta=VALUES(valido_hasta), estado=VALUES(estado), updated_by=VALUES(updated_by)"
				);
				return $stmt->execute([
					':archivo'=>(string)($info['ruta'] ?? ''),
					':nombre'=>(string)($info['archivo'] ?? ''),
					':ruc'=>(string)($info['ruc'] ?? ''),
					':rs'=>(string)($info['titular'] ?? ''),
					':serial'=>(string)($info['serial'] ?? ''),
					':vd'=>$toDt($info['valido_desde'] ?? ''),
					':vh'=>$toDt($info['valido_hasta'] ?? ''),
					':estado'=>(string)($info['estado'] ?? 'NO_CONFIGURADO'),
					':uby'=>($_SESSION['usuarioid'] ?? null),
				]);
			}catch(\Throwable $e){
				return false;
			}
		}

		public function probarCertificadoSri(){
			$info = $this->obtenerInfoCertificadoSri();
			if(($info['estado'] ?? '') === 'VALIDO'){
				return $this->respuestaJson('simple', 'Firma valida', 'Certificado vigente hasta '.$info['valido_hasta'].'.', 'success');
			}

			return $this->respuestaJson('simple', 'Firma pendiente', 'Estado actual del certificado: '.($info['estado'] ?? 'NO_CONFIGURADO').'.', 'warning');
		}

		public function probarConexionSri(){
			$config = $this->sriConfig();
			$ambiente = ((string)($config['ambiente'] ?? '1') === '2') ? 'produccion' : 'pruebas';
			$webService = new WebServiceSRIService($config);
			$resultado = $webService->verificarConectividad();
			if(!empty($resultado['recepcion']) && !empty($resultado['autorizacion'])){
				return $this->respuestaJson('simple', 'Conexion SRI disponible', 'Recepcion y autorizacion respondieron en ambiente '.$ambiente.'.', 'success');
			}

			$estado = 'Recepcion: '.(!empty($resultado['recepcion']) ? 'OK' : 'sin respuesta').'. Autorizacion: '.(!empty($resultado['autorizacion']) ? 'OK' : 'sin respuesta').'.';
			return $this->respuestaJson('simple', 'Conexion SRI parcial', $estado, 'warning');
		}

		private function limpiarErroresOpenSsl(){
			while(openssl_error_string() !== false){}
		}

		private function obtenerErroresOpenSsl(){
			$errores = [];
			while($error = openssl_error_string()){
				$errores[] = $error;
			}
			return implode(' | ', array_unique($errores));
		}

		private function variantesClaveCertificado($clave){
			$clave = (string)$clave;
			$variantes = [
				$clave,
				trim($clave),
				html_entity_decode($clave, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
				preg_replace('/^\xEF\xBB\xBF/', '', $clave)
			];

			if(function_exists('mb_convert_encoding')){
				$variantes[] = @mb_convert_encoding($clave, 'ISO-8859-1', 'UTF-8');
				$variantes[] = @mb_convert_encoding($clave, 'Windows-1252', 'UTF-8');
			}
			if(function_exists('iconv')){
				$latin1 = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $clave);
				$win1252 = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $clave);
				if($latin1 !== false){ $variantes[] = $latin1; }
				if($win1252 !== false){ $variantes[] = $win1252; }
			}

			$limpias = [];
			foreach($variantes as $variante){
				if($variante === null || $variante === false || $variante === ''){
					continue;
				}
				$limpias[$variante] = $variante;
			}
			return array_values($limpias);
		}

		private function leerPkcs12ConVariantes($contenido, $clave, $tmpPath){
			$errores = [];
			$requiereLegacy = false;
			foreach($this->variantesClaveCertificado($clave) as $clavePrueba){
				$this->limpiarErroresOpenSsl();
				$certs = [];
				if(openssl_pkcs12_read($contenido, $certs, $clavePrueba) && !empty($certs['cert']) && !empty($certs['pkey'])){
					return [
						'ok'=>true,
						'certs'=>$certs,
						'clave'=>$clavePrueba,
						'contenido'=>$contenido,
						'convertido'=>false
					];
				}
				$error = $this->obtenerErroresOpenSsl();
				if($error !== ''){
					$errores[] = $error;
					if(stripos($error, 'unsupported') !== false || stripos($error, 'inner_evp_generic_fetch') !== false || stripos($error, 'RC2') !== false){
						$requiereLegacy = true;
					}
				}
			}

			if($requiereLegacy){
				foreach($this->variantesClaveCertificado($clave) as $clavePrueba){
					$convertido = $this->convertirP12Legacy($tmpPath, $clavePrueba);
					if($convertido === null){
						continue;
					}
					$this->limpiarErroresOpenSsl();
					$certs = [];
					if(openssl_pkcs12_read($convertido, $certs, $clavePrueba) && !empty($certs['cert']) && !empty($certs['pkey'])){
						return [
							'ok'=>true,
							'certs'=>$certs,
							'clave'=>$clavePrueba,
							'contenido'=>$convertido,
							'convertido'=>true
						];
					}
					$error = $this->obtenerErroresOpenSsl();
					if($error !== ''){
						$errores[] = $error;
					}
				}

				return [
					'ok'=>false,
					'mensaje'=>'El certificado usa cifrado legacy incompatible con OpenSSL 3.x y no pudo convertirse automaticamente. Detalle: '.($errores ? implode(' | ', array_unique($errores)) : 'sin detalle OpenSSL')
				];
			}

			return [
				'ok'=>false,
				'mensaje'=>'No fue posible leer el certificado con la clave ingresada. Detalle OpenSSL: '.($errores ? implode(' | ', array_unique($errores)) : 'sin informacion adicional')
			];
		}

		private function convertirP12Legacy($p12Path, $clave){
			if(!function_exists('exec')){
				return null;
			}

			$openssl = null;
			$apacheDir = null;
			$apacheBase = 'C:/wamp64/bin/apache/';
			if(is_dir($apacheBase)){
				$entries = scandir($apacheBase) ?: [];
				rsort($entries);
				foreach($entries as $entry){
					if(strncmp($entry, 'apache', 6) !== 0){ continue; }
					$candidate = $apacheBase.$entry.'/bin/openssl.exe';
					if(is_file($candidate)){
						$openssl = $candidate;
						$apacheDir = $apacheBase.$entry;
						break;
					}
				}
			}
			if($openssl === null){
				return null;
			}

			$modulesDir = null;
			$moduleCandidates = [
				$apacheDir.'/conf/lib/ossl-modules',
				$apacheDir.'/bin/ossl-modules',
				$apacheDir.'/lib/ossl-modules',
				$apacheDir.'/ossl-modules',
				dirname($openssl).'/ossl-modules'
			];
			$phpBase = 'C:/wamp64/bin/php/';
			if(is_dir($phpBase)){
				$phpEntries = scandir($phpBase) ?: [];
				rsort($phpEntries);
				foreach($phpEntries as $phpEntry){
					if(strncmp($phpEntry, 'php', 3) !== 0){ continue; }
					$moduleCandidates[] = $phpBase.$phpEntry.'/extras/ssl';
					$moduleCandidates[] = $phpBase.$phpEntry.'/extras/openssl';
				}
			}
			foreach($moduleCandidates as $dir){
				if(is_file($dir.'/legacy.dll')){
					$modulesDir = $dir;
					break;
				}
			}

			$prefix = rtrim(sys_get_temp_dir(), '/\\').DIRECTORY_SEPARATOR.'sendero_cert_'.uniqid('', true);
			$tmpPem = $prefix.'.pem';
			$tmpP12 = $prefix.'_new.p12';
			$tmpPassIn = $prefix.'_in.pass';
			$tmpPassOut = $prefix.'_out.pass';
			file_put_contents($tmpPassIn, (string)$clave);
			file_put_contents($tmpPassOut, (string)$clave);

			$prevModules = getenv('OPENSSL_MODULES');
			if($modulesDir !== null){
				putenv('OPENSSL_MODULES='.str_replace('/', '\\', $modulesDir));
			}

			try{
				$providerArg = $modulesDir
					? sprintf(' -provider-path "%s" -provider legacy -provider default', str_replace('/', '\\', $modulesDir))
					: '';
				$cmd1 = sprintf(
					'"%s" pkcs12 -legacy%s -in "%s" -out "%s" -passin file:"%s" -nodes 2>&1',
					str_replace('/', '\\', $openssl),
					$providerArg,
					str_replace('/', '\\', $p12Path),
					str_replace('/', '\\', $tmpPem),
					str_replace('/', '\\', $tmpPassIn)
				);
				$out1 = []; $ret1 = -1;
				exec($cmd1, $out1, $ret1);
				if($ret1 !== 0 || !is_file($tmpPem) || filesize($tmpPem) < 10){
					return null;
				}

				$cmd2 = sprintf(
					'"%s" pkcs12 -export -in "%s" -out "%s" -passin file:"%s" -passout file:"%s" 2>&1',
					str_replace('/', '\\', $openssl),
					str_replace('/', '\\', $tmpPem),
					str_replace('/', '\\', $tmpP12),
					str_replace('/', '\\', $tmpPassIn),
					str_replace('/', '\\', $tmpPassOut)
				);
				$out2 = []; $ret2 = -1;
				exec($cmd2, $out2, $ret2);
				if($ret2 !== 0 || !is_file($tmpP12) || filesize($tmpP12) < 10){
					return null;
				}

				return file_get_contents($tmpP12) ?: null;
			}finally{
				if($modulesDir !== null){
					$prevModules !== false ? putenv('OPENSSL_MODULES='.$prevModules) : putenv('OPENSSL_MODULES=');
				}
				@unlink($tmpPem);
				@unlink($tmpP12);
				@unlink($tmpPassIn);
				@unlink($tmpPassOut);
			}
		}

		private function facturaElectronicaInstalada(){
			try{
				$check = $this->ejecutarConsulta("SHOW TABLES LIKE 'facturas_electronicas'");
				return $check->rowCount() > 0;
			}catch(\Throwable $e){
				return false;
			}
		}

		private function respuestaJson($tipo, $titulo, $texto, $icono='info', array $extra=[]){
			if(!headers_sent()){
				header('Content-Type: application/json; charset=UTF-8');
			}
			return json_encode(array_merge([
				"tipo"=>$tipo,
				"titulo"=>$titulo,
				"texto"=>$texto,
				"icono"=>$icono
			], $extra), JSON_UNESCAPED_UNICODE);
		}

		private function codigoSriTipoIdentificacion($tipo, $identificacion){
			$tipo = strtoupper(trim((string)$tipo));
			$identificacion = preg_replace('/\D+/', '', (string)$identificacion);
			if(in_array($tipo, ['RUC', '04'], true) || strlen($identificacion) === 13){ return '04'; }
			if(in_array($tipo, ['CED', 'CEDULA', '05'], true) || strlen($identificacion) === 10){ return '05'; }
			if(in_array($tipo, ['PAS', 'PASAPORTE', '06'], true)){ return '06'; }
			if(in_array($tipo, ['VENTA_FINAL', 'CONSUMIDOR_FINAL', '07'], true)){ return '07'; }
			return '08';
		}

		public function validarIdentificacionSri($identificacion, $tipo='CED'){
			$codigo = $this->codigoSriTipoIdentificacion($tipo, $identificacion);
			$identificacionLimpia = preg_replace('/\D+/', '', (string)$identificacion);
			if($codigo === '05'){
				return strlen($identificacionLimpia) === 10 && $this->validarCedula($identificacionLimpia);
			}
			if($codigo === '04'){
				return strlen($identificacionLimpia) === 13 && substr($identificacionLimpia, -3) !== '000';
			}
			if($codigo === '07'){
				return $identificacionLimpia === '9999999999999';
			}
			$identificacion = trim((string)$identificacion);
			return strlen($identificacion) >= 3 && strlen($identificacion) <= 20;
		}

		private function limpiarTextoFactura($valor){
			$valor = html_entity_decode((string)$valor, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			$valor = preg_replace('/<br\s*\/?>/i', ' ', $valor);
			$valor = strip_tags($valor);
			$valor = preg_replace('/Warning:\s*Undefined variable \$disabled in .*? on line \d+/i', ' ', $valor);
			$valor = preg_replace('/\s+/', ' ', $valor);
			return trim($valor, " \t\n\r\0\x0B,>");
		}
		private function normalizarTextoSri($valor, $limite=300){
			$valor = $this->limpiarTextoFactura($valor);
			return function_exists('mb_substr') ? mb_substr($valor, 0, $limite, 'UTF-8') : substr($valor, 0, $limite);
		}

		private function tarifaIvaSri($tarifa){
			$config = $this->sriConfig();
			$tarifa = (float)$tarifa;
			$tarifas = $config['impuestos']['IVA']['tarifas'] ?? [];
			$key = (string)(int)$tarifa;
			if(isset($tarifas[$key])){
				return ['codigo'=>'2', 'codigo_porcentaje'=>$tarifas[$key]['codigo'], 'porcentaje'=>(float)$tarifas[$key]['porcentaje']];
			}
			return ['codigo'=>'2', 'codigo_porcentaje'=>'0', 'porcentaje'=>0.00];
		}

		private function reservarSecuencialFactura(\PDO $conexion, $tipoComprobante, $establecimiento, $puntoEmision, $inicio = 1){
			$inicio = max(1, (int)$inicio);
			$insert = $conexion->prepare("INSERT IGNORE INTO facturas_electronicas_secuenciales (tipo_comprobante, establecimiento, punto_emision, secuencial_actual) VALUES (:tipo, :estab, :pto, 0)");
			$insert->execute([':tipo'=>$tipoComprobante, ':estab'=>$establecimiento, ':pto'=>$puntoEmision]);
			// Proximo numero = max(secuencial_actual + 1, Nro. Inicio configurado): respeta
			// el piso pero nunca retrocede (no se pueden repetir numeros ya emitidos).
			// CAST a UNSIGNED: con PDO emulated prepares :inicio llega como string y
			// GREATEST compararia como texto ('6' > '100'), ignorando el piso.
			$update = $conexion->prepare("UPDATE facturas_electronicas_secuenciales SET secuencial_actual = LAST_INSERT_ID(GREATEST(secuencial_actual + 1, CAST(:inicio AS UNSIGNED))) WHERE tipo_comprobante = :tipo AND establecimiento = :estab AND punto_emision = :pto");
			$update->execute([':tipo'=>$tipoComprobante, ':estab'=>$establecimiento, ':pto'=>$puntoEmision, ':inicio'=>$inicio]);
			$secuencial = (int)$conexion->lastInsertId();
			if($secuencial <= 0){ throw new \RuntimeException('No fue posible reservar el secuencial de la factura.'); }
			return str_pad((string)$secuencial, 9, '0', STR_PAD_LEFT);
		}

		private function obtenerRepresentanteFactura(\PDO $conexion, $alumnoid){
			$stmt = $conexion->prepare("SELECT A.alumno_id, A.alumno_repreid, R.repre_id, R.repre_identificacion, R.repre_direccion, R.repre_correo, R.repre_celular, R.repre_tipoidentificacion, R.repre_requiere_factura, R.repre_factura_a_nombre, TRIM(CONCAT_WS(' ', R.repre_primernombre, R.repre_segundonombre, R.repre_apellidopaterno, R.repre_apellidomaterno)) AS representante, C.conyuge_tipoidentificacion, C.conyuge_identificacion, C.conyuge_direccion, C.conyuge_correo, C.conyuge_celular, TRIM(CONCAT_WS(' ', C.conyuge_primernombre, C.conyuge_segundonombre, C.conyuge_apellidopaterno, C.conyuge_apellidomaterno)) AS conyuge FROM sujeto_alumno A INNER JOIN alumno_representante R ON R.repre_id = A.alumno_repreid LEFT JOIN alumno_representanteconyuge C ON C.conyuge_repid = R.repre_id WHERE A.alumno_id = :alumno");
			$stmt->execute([':alumno'=>(int)$alumnoid]);
			$representante = $stmt->fetch(\PDO::FETCH_ASSOC);
			if(!$representante){ throw new \RuntimeException('No se encontro el representante del alumno.'); }
			$requiereFactura = strtoupper(trim((string)($representante['repre_requiere_factura'] ?? 'S')));
			if($requiereFactura === 'N'){
				throw new \RuntimeException('El representante esta configurado como no requiere factura.');
			}
			$facturaANombre = strtoupper(trim((string)($representante['repre_factura_a_nombre'] ?? 'REPRESENTANTE')));
			if($facturaANombre === 'CONYUGE'){
				$representante['cliente_tipoidentificacion'] = (string)($representante['conyuge_tipoidentificacion'] ?? '');
				$representante['cliente_identificacion'] = (string)($representante['conyuge_identificacion'] ?? '');
				$representante['cliente_razon_social'] = (string)($representante['conyuge'] ?? '');
				$representante['cliente_direccion'] = (string)($representante['conyuge_direccion'] ?? '');
				$representante['cliente_correo'] = (string)($representante['conyuge_correo'] ?? '');
				$representante['cliente_celular'] = (string)($representante['conyuge_celular'] ?? '');
				$representante['cliente_origen'] = 'CONYUGE';
			}else{
				$representante['cliente_tipoidentificacion'] = (string)($representante['repre_tipoidentificacion'] ?? '');
				$representante['cliente_identificacion'] = (string)($representante['repre_identificacion'] ?? '');
				$representante['cliente_razon_social'] = (string)($representante['representante'] ?? '');
				$representante['cliente_direccion'] = (string)($representante['repre_direccion'] ?? '');
				$representante['cliente_correo'] = (string)($representante['repre_correo'] ?? '');
				$representante['cliente_celular'] = (string)($representante['repre_celular'] ?? '');
				$representante['cliente_origen'] = 'REPRESENTANTE';
			}
			return $representante;
		}

		private function obtenerPagosFacturaElectronica(\PDO $conexion, $representanteId, array $pagosIds){
			$pagosIds = array_values(array_unique(array_filter(array_map('intval', $pagosIds))));
			if(empty($pagosIds)){ throw new \RuntimeException('Seleccione al menos un pago para facturar.'); }
			$placeholders = [];
			$params = [':representante'=>(int)$representanteId];
			foreach($pagosIds as $i=>$pagoId){
				$key = ':pago'.$i;
				$placeholders[] = $key;
				$params[$key] = $pagoId;
			}
			$sql = "SELECT P.pago_id, P.pago_valor, P.pago_periodo, P.pago_concepto, C.catalogo_valor AS codigo, C.catalogo_descripcion, CONCAT(C.catalogo_descripcion, ' ', P.pago_periodo, ', ', P.pago_concepto) AS detalle, CONCAT(AL.alumno_primernombre, ' ', AL.alumno_segundonombre, ' ', AL.alumno_apellidopaterno, ' ', AL.alumno_apellidomaterno) AS alumno FROM alumno_representante R INNER JOIN sujeto_alumno AL ON AL.alumno_repreid = R.repre_id INNER JOIN alumno_pago P ON P.pago_alumnoid = AL.alumno_id INNER JOIN general_tabla_catalogo C ON C.catalogo_valor = P.pago_rubroid WHERE R.repre_id = :representante AND P.pago_estado = 'C' AND P.pago_id IN (".implode(',', $placeholders).") AND NOT EXISTS (SELECT 1 FROM facturas_electronicas_detalle FD INNER JOIN facturas_electronicas FE ON FE.id = FD.factura_electronica_id WHERE FD.pago_id = P.pago_id AND FE.estado_sri <> 'ANULADA')";
			$stmt = $conexion->prepare($sql);
			$stmt->execute($params);
			$pagos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			if(count($pagos) !== count($pagosIds)){ throw new \RuntimeException('Uno o mas pagos ya fueron facturados o no pertenecen al representante seleccionado.'); }
			return $pagos;
		}

		private function normalizarNombreArchivoLogo($valor){
			$valor = pathinfo((string)$valor, PATHINFO_FILENAME);
			$valor = preg_replace('/_\d+$/', '', $valor);
			$ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $valor);
			if($ascii !== false){ $valor = $ascii; }
			$valor = strtoupper((string)$valor);
			$valor = preg_replace('/[^A-Z0-9]+/', '_', $valor);
			return trim((string)$valor, '_');
		}

		private function resolverLogoSedeAlumno($alumnoId){
			$raiz = dirname(__DIR__, 2);
			$dirSedes = $raiz.'/app/views/imagenes/fotos/sedes';
			$urlSedes = APP_URL.'app/views/imagenes/fotos/sedes/';
			$dirLogos = $raiz.'/app/views/dist/img/Logos';
			$urlLogos = APP_URL.'app/views/dist/img/Logos/';
			$extensiones = ['jpg', 'jpeg', 'png', 'gif'];

			$candidatos = [];
			$patrones = [];
			$alumnoId = (int)$alumnoId;
			if($alumnoId > 0){
				try{
					$row = $this->ejecutarConsulta("SELECT S.sede_foto, S.sede_nombre FROM sujeto_alumno A INNER JOIN general_sede S ON S.sede_id = A.alumno_sedeid WHERE A.alumno_id = ".$alumnoId." LIMIT 1")->fetch();
					$sedeFoto = trim((string)($row['sede_foto'] ?? ''));
					$sedeNombre = trim((string)($row['sede_nombre'] ?? ''));
					if($sedeFoto !== ''){
						$candidatos[] = ['path'=>$dirSedes.'/'.$sedeFoto, 'url'=>$urlSedes.rawurlencode($sedeFoto)];
						$patrones[] = $this->normalizarNombreArchivoLogo($sedeFoto);
					}
					if($sedeNombre !== ''){
						$patrones[] = $this->normalizarNombreArchivoLogo($sedeNombre);
					}
				}catch(\Throwable $e){ /* fallback abajo */ }
			}

			foreach($candidatos as $candidato){
				$path = $candidato['path'];
				$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
				if(is_file($path) && in_array($ext, $extensiones, true)){
					return $candidato;
				}
			}

			$patrones = array_values(array_unique(array_filter($patrones)));
			if(is_dir($dirSedes) && !empty($patrones)){
				foreach(glob($dirSedes.'/*') ?: [] as $archivo){
					if(!is_file($archivo)){ continue; }
					$ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
					if(!in_array($ext, $extensiones, true)){ continue; }
					$nombreNormalizado = $this->normalizarNombreArchivoLogo(basename($archivo));
					foreach($patrones as $patron){
						if($patron !== '' && ($nombreNormalizado === $patron || strpos($nombreNormalizado, $patron) === 0 || strpos($patron, $nombreNormalizado) === 0)){
							return ['path'=>$archivo, 'url'=>$urlSedes.rawurlencode(basename($archivo))];
						}
					}
				}
			}

			$defaultSede = $dirSedes.'/default_sede.jpg';
			if(is_file($defaultSede)){
				return ['path'=>$defaultSede, 'url'=>$urlSedes.'default_sede.jpg'];
			}

			$defaultLogo = $dirLogos.'/LogoCDJG.png';
			if(is_file($defaultLogo)){
				return ['path'=>$defaultLogo, 'url'=>$urlLogos.'LogoCDJG.png'];
			}

			return ['path'=>'', 'url'=>''];
		}

		private function crearRideHtml(array $factura, array $detalles, array $config, $archivo){
			$emisor = $config['emisor'];
			$ambiente = (($factura['ambiente'] ?? '1') === '2') ? 'PRODUCCION' : 'PRUEBAS';
			$h = function($valor){ return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); };
			$estadoSri = (string)($factura['estado_sri'] ?? 'GENERADA');
			$estaAutorizada = $estadoSri === 'AUTORIZADO';
			$numeroAutorizacion = trim((string)($factura['numero_autorizacion'] ?? ''));
			$fechaAutorizacion = trim((string)($factura['fecha_autorizacion'] ?? ''));
			$bannerClase = $estaAutorizada ? 'ok' : 'warn';
			$bannerTexto = $estaAutorizada
				? '<strong>Documento autorizado por el SRI.</strong> Este RIDE corresponde al comprobante electronico autorizado.'
				: '<strong>Documento generado localmente.</strong> Para validez tributaria debe firmarse electronicamente y autorizarse en el SRI.';
			$filas = '';
			foreach($detalles as $detalle){
				$filas .= '<tr><td>'.$h($detalle['codigo']).'</td><td class="right">'.number_format((float)$detalle['cantidad'], 2, '.', '').'</td><td>'.$h($detalle['descripcion']).'</td><td class="right">'.number_format((float)$detalle['precio_unitario'], 2, '.', '').'</td><td class="right">'.number_format((float)$detalle['descuento'], 2, '.', '').'</td><td class="right">'.number_format((float)$detalle['precio_total_sin_impuesto'], 2, '.', '').'</td></tr>';
			}
			// Logo de la sede a la que pertenece el alumno (seccion de la escuela)
			$logoHtml = '';
			$alumnoId = (int)($factura['alumno_id'] ?? 0);
			if($alumnoId > 0){
				$logo = $this->resolverLogoSedeAlumno($alumnoId);
				if(!empty($logo['url'])){
					$logoHtml = '<img src="'.$h($logo['url']).'" alt="Logo sede" style="max-height:80px;max-width:220px;display:block;margin-bottom:8px">';
				}
			}

			$html = '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>RIDE '.$h($factura['numero']).'</title><style>body{font-family:Arial,sans-serif;font-size:12px;color:#222}.wrap{max-width:920px;margin:24px auto}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.box{border:1px solid #555;padding:10px}h1,h2,h3{margin:0 0 8px}table{width:100%;border-collapse:collapse;margin-top:12px}th,td{border:1px solid #999;padding:6px}th{background:#eee}.right{text-align:right}.warn{background:#fff3cd;border:1px solid #ffeeba;padding:8px;margin:10px 0}.ok{background:#d4edda;border:1px solid #c3e6cb;padding:8px;margin:10px 0}</style></head><body><div class="wrap"><div class="'.$bannerClase.'">'.$bannerTexto.'</div><div class="grid"><div class="box">'.$logoHtml.'<h2>'.$h($emisor['razon_social'] ?? '').'</h2><p><strong>RUC:</strong> '.$h($emisor['ruc'] ?? '').'</p><p><strong>Nombre comercial:</strong> '.$h($emisor['nombre_comercial'] ?? '').'</p><p><strong>Direccion matriz:</strong> '.$h($emisor['direccion_matriz'] ?? '').'</p><p><strong>Direccion establecimiento:</strong> '.$h($emisor['direccion_establecimiento'] ?? '').'</p><p><strong>Obligado contabilidad:</strong> '.$h($emisor['obligado_contabilidad'] ?? 'NO').'</p></div><div class="box"><h1>Factura</h1><p><strong>No.:</strong> '.$h($factura['numero']).'</p><p><strong>Clave de acceso:</strong> '.$h($factura['clave_acceso']).'</p><p><strong>Numero autorizacion:</strong> '.$h($numeroAutorizacion !== '' ? $numeroAutorizacion : 'Pendiente').'</p><p><strong>Fecha autorizacion:</strong> '.$h($fechaAutorizacion !== '' ? $fechaAutorizacion : 'Pendiente').'</p><p><strong>Estado:</strong> '.$h($estadoSri).'</p><p><strong>Ambiente:</strong> '.$h($ambiente).'</p><p><strong>Emision:</strong> NORMAL</p></div></div><div class="box" style="margin-top:16px"><h3>Datos del cliente</h3><p><strong>Cliente:</strong> '.$h($factura['cliente_razon_social']).'</p><p><strong>Identificacion:</strong> '.$h($factura['cliente_identificacion']).'</p><p><strong>Direccion:</strong> '.$h($factura['cliente_direccion']).'</p><p><strong>Email:</strong> '.$h($factura['cliente_email']).'</p></div><table><thead><tr><th>Codigo</th><th>Cantidad</th><th>Detalle</th><th>Precio unitario</th><th>Descuento</th><th>Total sin impuesto</th></tr></thead><tbody>'.$filas.'</tbody></table><table><tr><td class="right"><strong>Subtotal IVA</strong></td><td class="right">'.number_format((float)$factura['subtotal_iva'], 2, '.', '').'</td></tr><tr><td class="right"><strong>Subtotal 0%</strong></td><td class="right">'.number_format((float)$factura['subtotal_0'], 2, '.', '').'</td></tr><tr><td class="right"><strong>IVA</strong></td><td class="right">'.number_format((float)$factura['iva'], 2, '.', '').'</td></tr><tr><td class="right"><strong>Valor total</strong></td><td class="right"><strong>'.number_format((float)$factura['total'], 2, '.', '').'</strong></td></tr></table></div></body></html>';
			$directorioRide = dirname($archivo);
			if(!is_dir($directorioRide)){
				$warningDirectorio = '';
				set_error_handler(function($errno, $errstr) use (&$warningDirectorio){
					$warningDirectorio = trim((string)$errstr);
					return true;
				});
				try{
					$directorioCreado = mkdir($directorioRide, 0755, true);
				}finally{
					restore_error_handler();
				}
				if(!$directorioCreado && !is_dir($directorioRide)){
					$detalle = $warningDirectorio !== '' ? ' Detalle tecnico: '.$warningDirectorio : '';
					throw new \RuntimeException('No fue posible crear el directorio del RIDE. Revise permisos en storage/sri/ride.'.$detalle);
				}
			}

			$warningEscritura = '';
			set_error_handler(function($errno, $errstr) use (&$warningEscritura){
				$warningEscritura = trim((string)$errstr);
				return true;
			});
			try{
				$rideEscrito = file_put_contents($archivo, $html, LOCK_EX);
			}finally{
				restore_error_handler();
			}
			if($rideEscrito === false){
				$detalle = $warningEscritura !== '' ? ' Detalle tecnico: '.$warningEscritura : '';
				throw new \RuntimeException('No fue posible guardar el RIDE. Revise permisos en storage/sri/ride.'.$detalle);
			}

			return $archivo;
		}

		/** Genera el RIDE en PDF (FPDF) y lo guarda en $archivo. Devuelve la ruta. */
		private function crearRidePdf(array $factura, array $detalles, array $config, $archivo){
			require_once dirname(__DIR__, 2).'/app/lib/fpdf.php';

			$emisor = $config['emisor'] ?? [];
			$ambiente = (($factura['ambiente'] ?? '1') === '2') ? 'PRODUCCION' : 'PRUEBAS';
			$estadoSri = (string)($factura['estado_sri'] ?? 'GENERADA');
			$estaAutorizada = $estadoSri === 'AUTORIZADO';
			$numeroAutorizacion = trim((string)($factura['numero_autorizacion'] ?? ''));
			$fechaAutorizacion = trim((string)($factura['fecha_autorizacion'] ?? ''));

			// Logo local de la sede del alumno
			$logoPath = '';
			$alumnoId = (int)($factura['alumno_id'] ?? 0);
			if($alumnoId > 0){
				$logo = $this->resolverLogoSedeAlumno($alumnoId);
				$logoPath = (string)($logo['path'] ?? '');
			}

			$t = static function($s){ $c = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string)$s); return $c !== false ? $c : utf8_decode((string)$s); };
			$money = static function($v){ return number_format((float)$v, 2, '.', ''); };
			$anchoUtil = 186;

			$pdf = new \FPDF('P', 'mm', 'A4');
			$pdf->SetMargins(12, 12, 12);
			$pdf->SetAutoPageBreak(true, 15);
			$pdf->AddPage();

			// Banner de estado
			if($estaAutorizada){ $pdf->SetFillColor(212, 237, 218); } else { $pdf->SetFillColor(255, 243, 205); }
			$pdf->SetFont('Arial', 'B', 9);
			$pdf->SetTextColor(40, 40, 40);
			$pdf->Cell($anchoUtil, 8, $t($estaAutorizada ? 'DOCUMENTO AUTORIZADO POR EL SRI' : 'DOCUMENTO GENERADO LOCALMENTE - pendiente de autorizacion SRI'), 1, 1, 'C', true);
			$pdf->Ln(3);

			// Cabecera: logo + datos del emisor
			$yIni = $pdf->GetY();
			$xTexto = 12;
			if($logoPath !== ''){
				try{ $pdf->Image($logoPath, 12, $yIni, 32); $xTexto = 48; }catch(\Throwable $e){ $xTexto = 12; }
			}
			$anchoTexto = $anchoUtil - ($xTexto - 12);
			$pdf->SetXY($xTexto, $yIni);
			$pdf->SetFont('Arial', 'B', 12);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->MultiCell($anchoTexto, 6, $t($emisor['razon_social'] ?? ''), 0, 'L');
			$pdf->SetFont('Arial', '', 9);
			foreach([
				'RUC: '.($emisor['ruc'] ?? ''),
				'Nombre comercial: '.($emisor['nombre_comercial'] ?? ''),
				'Dir. matriz: '.($emisor['direccion_matriz'] ?? ''),
				'Dir. establecimiento: '.($emisor['direccion_establecimiento'] ?? ''),
				'Obligado a contabilidad: '.($emisor['obligado_contabilidad'] ?? 'NO'),
			] as $l){ $pdf->SetX($xTexto); $pdf->MultiCell($anchoTexto, 5, $t($l), 0, 'L'); }
			$yLogoBottom = ($logoPath !== '') ? $yIni + 26 : $pdf->GetY();
			if($pdf->GetY() < $yLogoBottom){ $pdf->SetY($yLogoBottom); }
			$pdf->Ln(2);

			// Caja: datos de la factura
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->SetFillColor(238, 238, 238);
			$pdf->Cell($anchoUtil, 7, $t('FACTURA  No. '.$factura['numero']), 1, 1, 'L', true);
			$pdf->SetFont('Arial', '', 9);
			$yBox = $pdf->GetY();
			foreach([
				'Clave de acceso: '.$factura['clave_acceso'],
				'Numero de autorizacion: '.($numeroAutorizacion !== '' ? $numeroAutorizacion : 'Pendiente'),
				'Fecha de autorizacion: '.($fechaAutorizacion !== '' ? $fechaAutorizacion : 'Pendiente'),
				'Estado: '.$estadoSri.'     Ambiente: '.$ambiente.'     Emision: NORMAL',
			] as $l){ $pdf->MultiCell($anchoUtil, 5, $t($l), 0, 'L'); }
			$pdf->Rect(12, $yBox, $anchoUtil, $pdf->GetY() - $yBox);
			$pdf->Ln(2);

			// Caja: datos del cliente
			$pdf->SetFont('Arial', 'B', 10);
			$pdf->SetFillColor(238, 238, 238);
			$pdf->Cell($anchoUtil, 7, $t('DATOS DEL CLIENTE'), 1, 1, 'L', true);
			$pdf->SetFont('Arial', '', 9);
			$yBox = $pdf->GetY();
			foreach([
				'Cliente: '.($factura['cliente_razon_social'] ?? ''),
				'Identificacion: '.($factura['cliente_identificacion'] ?? ''),
				'Direccion: '.($factura['cliente_direccion'] ?? ''),
				'Email: '.($factura['cliente_email'] ?? ''),
			] as $l){ $pdf->MultiCell($anchoUtil, 5, $t($l), 0, 'L'); }
			$pdf->Rect(12, $yBox, $anchoUtil, $pdf->GetY() - $yBox);
			$pdf->Ln(3);

			// Tabla de detalle
			$cols = [['Codigo', 20], ['Cant.', 16], ['Detalle', 86], ['P. Unit.', 22], ['Desc.', 20], ['Total', 22]];
			$pdf->SetFont('Arial', 'B', 8);
			$pdf->SetFillColor(238, 238, 238);
			foreach($cols as $c){ $pdf->Cell($c[1], 7, $t($c[0]), 1, 0, 'C', true); }
			$pdf->Ln();
			$pdf->SetFont('Arial', '', 8);
			foreach($detalles as $d){
				$detalleTxt = $t($d['descripcion'] ?? '');
				$nb = $this->fpdfContarLineas($pdf, 86, $detalleTxt);
				$rowH = max(5, $nb * 5);
				$y = $pdf->GetY();
				$pdf->Cell(20, $rowH, $t($d['codigo'] ?? ''), 1, 0, 'L');
				$pdf->Cell(16, $rowH, number_format((float)($d['cantidad'] ?? 1), 2, '.', ''), 1, 0, 'R');
				$xDet = $pdf->GetX();
				$pdf->MultiCell(86, 5, $detalleTxt, 1, 'L');
				$pdf->SetXY($xDet + 86, $y);
				$pdf->Cell(22, $rowH, $money($d['precio_unitario'] ?? 0), 1, 0, 'R');
				$pdf->Cell(20, $rowH, $money($d['descuento'] ?? 0), 1, 0, 'R');
				$pdf->Cell(22, $rowH, $money($d['precio_total_sin_impuesto'] ?? 0), 1, 1, 'R');
				if($pdf->GetY() < $y + $rowH){ $pdf->SetY($y + $rowH); }
			}
			$pdf->Ln(2);

			// Totales (bloque a la derecha)
			$bloqueW = 92; $xTot = 12 + $anchoUtil - $bloqueW;
			foreach([
				['Subtotal IVA', $money($factura['subtotal_iva'] ?? 0), false],
				['Subtotal 0%', $money($factura['subtotal_0'] ?? 0), false],
				['IVA', $money($factura['iva'] ?? 0), false],
				['VALOR TOTAL', $money($factura['total'] ?? 0), true],
			] as $r){
				$pdf->SetX($xTot);
				$pdf->SetFont('Arial', $r[2] ? 'B' : '', 9);
				$pdf->Cell(60, 7, $t($r[0]), 1, 0, 'R');
				$pdf->Cell(32, 7, $r[1], 1, 1, 'R');
			}

			$directorio = dirname($archivo);
			if(!is_dir($directorio)){ @mkdir($directorio, 0755, true); }
			$pdf->Output('F', $archivo);
			return $archivo;
		}

		/** Aproxima cuantas lineas ocupara $texto en un MultiCell de $ancho mm. */
		private function fpdfContarLineas($pdf, $ancho, $texto){
			$ancho = $ancho - 2;
			$texto = str_replace("\r", '', (string)$texto);
			$total = 0;
			foreach(explode("\n", $texto) as $parrafo){
				$palabras = preg_split('/ +/', trim($parrafo));
				if(empty($palabras) || $palabras === ['']){ $total++; continue; }
				$actual = ''; $cuenta = 1;
				foreach($palabras as $w){
					$prueba = $actual === '' ? $w : $actual.' '.$w;
					if($pdf->GetStringWidth($prueba) > $ancho && $actual !== ''){ $cuenta++; $actual = $w; }
					else{ $actual = $prueba; }
				}
				$total += $cuenta;
			}
			return max(1, $total);
		}

		public function contarFacturasGeneradas($alumnoid, $fecha_inicio, $fecha_fin){
			$alumnoid = (int)$alumnoid;
			// Rango de fechas valido -> filtra; vacio/invalido -> cuenta todas
			$filtroFecha = (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$fecha_inicio) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$fecha_fin))
				? " AND FE.fecha_emision BETWEEN '".$fecha_inicio."' AND '".$fecha_fin."'" : "";
			$consulta = "SELECT COUNT(*) AS total FROM sujeto_alumno A INNER JOIN alumno_representante R ON R.repre_id = A.alumno_repreid INNER JOIN facturas_electronicas FE ON FE.representante_id = R.repre_id WHERE A.alumno_id = ".$alumnoid." AND FE.estado_sri <> 'ANULADA'".$filtroFecha;
			$datos = $this->ejecutarConsulta($consulta)->fetch();
			return (int)($datos['total'] ?? 0);
		}

		public function listarFacturasGeneradas($alumnoid, $fecha_inicio, $fecha_fin){
			$alumnoid = (int)$alumnoid;
			// Rango de fechas valido -> filtra; vacio/invalido -> lista todas (carga inicial)
			$filtroFecha = (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$fecha_inicio) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$fecha_fin))
				? " AND FE.fecha_emision BETWEEN '".$fecha_inicio."' AND '".$fecha_fin."'" : "";
			$tabla = '';
			$consulta = "SELECT FE.id, FE.fecha_emision, FE.establecimiento, FE.punto_emision, FE.secuencial, FE.total, FE.estado_sri, FE.clave_acceso, FE.cliente_razon_social, FE.cliente_email, FE.numero_autorizacion, FE.mensaje_error FROM sujeto_alumno A INNER JOIN alumno_representante R ON R.repre_id = A.alumno_repreid INNER JOIN facturas_electronicas FE ON FE.representante_id = R.repre_id WHERE A.alumno_id = ".$alumnoid." AND FE.estado_sri <> 'ANULADA'".$filtroFecha." ORDER BY FE.fecha_emision DESC, FE.id DESC";
			$datos = $this->ejecutarConsulta($consulta)->fetchAll();
			$contador = 1;
			foreach($datos as $rows){
				$numero = $rows['establecimiento'].'-'.$rows['punto_emision'].'-'.$rows['secuencial'];
				$xmlUrl = APP_URL.'app/ajax/facturasAjax.php?modulo_facturas=DESCARGAR_XML&factura_id='.$rows['id'];
				$rideUrl = APP_URL.'app/ajax/facturasAjax.php?modulo_facturas=VER_RIDE&factura_id='.$rows['id'];
				$estado = (string)$rows['estado_sri'];
				$estadoClass = ($estado==='AUTORIZADO') ? 'badge-success' : (in_array($estado, ['DEVUELTA','NO_AUTORIZADO','ERROR'], true) ? 'badge-danger' : (in_array($estado, ['ENVIADA','RECIBIDA','FIRMADA'], true) ? 'badge-info' : 'badge-warning'));
				$acciones = '<a href="'.$rideUrl.'" class="btn btn-xs btn-outline-info" target="_blank"><i class="fas fa-file-invoice"></i> RIDE</a> <a href="'.$xmlUrl.'" class="btn btn-xs btn-outline-secondary"><i class="fas fa-code"></i> XML</a>';
				if($estado !== 'AUTORIZADO'){
					$acciones .= ' <button type="button" class="btn btn-xs btn-success btn-emitir-sri" data-id="'.$rows['id'].'"><i class="fas fa-paper-plane"></i> Emitir SRI</button>';
					$acciones .= ' <button type="button" class="btn btn-xs btn-outline-primary btn-consultar-sri" data-id="'.$rows['id'].'"><i class="fas fa-sync-alt"></i> Consultar</button>';
				}elseif(filter_var($rows['cliente_email'] ?? '', FILTER_VALIDATE_EMAIL)){
					$acciones .= ' <button type="button" class="btn btn-xs btn-outline-success btn-enviar-factura" data-id="'.$rows['id'].'" data-email="'.htmlspecialchars($rows['cliente_email'], ENT_QUOTES, 'UTF-8').'"><i class="fas fa-envelope"></i> Enviar</button>';
				}
				$autorizacion = !empty($rows['numero_autorizacion']) ? '<br><small class="text-success">Aut.: '.htmlspecialchars($rows['numero_autorizacion'], ENT_QUOTES, 'UTF-8').'</small>' : '';
				$error = !empty($rows['mensaje_error']) ? '<br><small class="text-danger">'.htmlspecialchars($this->limpiarTextoFactura($rows['mensaje_error']), ENT_QUOTES, 'UTF-8').'</small>' : '';
				$tabla .= '<tr><td>'.$contador.'</td><td>'.$rows['fecha_emision'].'</td><td>'.number_format((float)$rows['total'], 2, '.', '').'</td><td><strong>'.$numero.'</strong><br><small class="text-muted">Clave: '.$rows['clave_acceso'].'</small>'.$autorizacion.'</td><td><span class="badge '.$estadoClass.'">'.$estado.'</span>'.$error.'</td><td>'.$acciones.'</td></tr>';
				$contador++;
			}
			if($tabla===''){ $tabla = '<tr><td colspan="6" class="text-center text-muted">'.($filtroFecha === '' ? 'No hay facturas generadas.' : 'No hay facturas generadas en el rango seleccionado.').'</td></tr>'; }
			return $tabla;
		}

		private function extraerMensajesSri(array $resultado){
			$mensajes = [];
			if(!empty($resultado['mensaje'])){
				$mensajes[] = (string)$resultado['mensaje'];
			}
			foreach($resultado['comprobantes'] ?? [] as $comprobante){
				foreach($comprobante['mensajes'] ?? [] as $mensaje){
					$partes = array_filter([
						$mensaje['identificador'] ?? '',
						$mensaje['mensaje'] ?? '',
						$mensaje['informacion_adicional'] ?? '',
						$mensaje['tipo'] ?? ''
					]);
					if(!empty($partes)){
						$mensajes[] = implode(' - ', $partes);
					}
				}
			}
			foreach($resultado['autorizaciones'] ?? [] as $autorizacion){
				foreach($autorizacion['mensajes'] ?? [] as $mensaje){
					$partes = array_filter([
						$mensaje['identificador'] ?? '',
						$mensaje['mensaje'] ?? '',
						$mensaje['informacion_adicional'] ?? '',
						$mensaje['tipo'] ?? ''
					]);
					if(!empty($partes)){
						$mensajes[] = implode(' - ', $partes);
					}
				}
			}
			$texto = trim(implode(' | ', array_unique($mensajes)));
			return $texto !== '' ? $texto : 'El SRI no devolvio un mensaje detallado.';
		}

		private function normalizarFechaAutorizacionSri($fecha){
			if(empty($fecha)){
				return date('Y-m-d H:i:s');
			}
			$timestamp = strtotime((string)$fecha);
			return $timestamp ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');
		}

		private function crearXmlAutorizadoSri(array $autorizacion, $claveAcceso){
			$dom = new \DOMDocument('1.0', 'UTF-8');
			$dom->formatOutput = true;
			$root = $dom->createElement('autorizacion');
			$dom->appendChild($root);
			$root->appendChild($dom->createElement('estado', (string)($autorizacion['estado'] ?? 'AUTORIZADO')));
			$root->appendChild($dom->createElement('numeroAutorizacion', (string)($autorizacion['numero_autorizacion'] ?? $claveAcceso)));
			$root->appendChild($dom->createElement('fechaAutorizacion', (string)($autorizacion['fecha_autorizacion'] ?? date('c'))));
			$root->appendChild($dom->createElement('ambiente', (string)($autorizacion['ambiente'] ?? '')));
			$comprobante = $dom->createElement('comprobante');
			$comprobante->appendChild($dom->createCDATASection((string)($autorizacion['comprobante'] ?? '')));
			$root->appendChild($comprobante);
			return $dom->saveXML();
		}

		private function obtenerFacturaElectronicaPorId(\PDO $conexion, $facturaId){
			$stmt = $conexion->prepare("SELECT * FROM facturas_electronicas WHERE id = :id LIMIT 1");
			$stmt->execute([':id'=>(int)$facturaId]);
			$factura = $stmt->fetch(\PDO::FETCH_ASSOC);
			if(!$factura){
				throw new \RuntimeException('Factura electronica no encontrada.');
			}
			return $factura;
		}

		private function obtenerDetallesRideFactura(\PDO $conexion, $facturaId){
			$stmt = $conexion->prepare("SELECT codigo_principal AS codigo, descripcion, cantidad, precio_unitario, descuento, precio_total_sin_impuesto FROM facturas_electronicas_detalle WHERE factura_electronica_id = :id ORDER BY id");
			$stmt->execute([':id'=>(int)$facturaId]);
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}

        private function obtenerDetallesFacturaSri(\PDO $conexion, $facturaId){
            $stmt = $conexion->prepare("SELECT codigo_principal AS codigo, descripcion, cantidad, precio_unitario, descuento, precio_total_sin_impuesto, iva_tarifa, iva_codigo_porcentaje, iva_valor FROM facturas_electronicas_detalle WHERE factura_electronica_id = :id ORDER BY id");
            $stmt->execute([':id'=>(int)$facturaId]);
            $detalles = [];
            foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
                $detalles[] = [
                    'codigo'=>$row['codigo'],
                    'descripcion'=>$row['descripcion'],
                    'cantidad'=>(float)$row['cantidad'],
                    'precio_unitario'=>(float)$row['precio_unitario'],
                    'descuento'=>(float)$row['descuento'],
                    'precio_total_sin_impuesto'=>(float)$row['precio_total_sin_impuesto'],
                    'impuestos'=>[[
                        'codigo'=>'2',
                        'codigo_porcentaje'=>$row['iva_codigo_porcentaje'],
                        'tarifa'=>(float)$row['iva_tarifa'],
                        'base_imponible'=>(float)$row['precio_total_sin_impuesto'],
                        'valor'=>(float)$row['iva_valor']
                    ]]
                ];
            }
            return $detalles;
        }

        private function obtenerPagosFacturaSri(\PDO $conexion, $facturaId){
            $stmt = $conexion->prepare("SELECT forma_pago, total, plazo, unidad_tiempo FROM facturas_electronicas_pagos WHERE factura_electronica_id = :id ORDER BY id");
            $stmt->execute([':id'=>(int)$facturaId]);
            $pagos = [];
            foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
                $pago = ['forma_pago'=>$row['forma_pago'], 'total'=>(float)$row['total']];
                if(!empty($row['plazo'])){
                    $pago['plazo'] = (int)$row['plazo'];
                    $pago['unidad_tiempo'] = $row['unidad_tiempo'] ?: 'dias';
                }
                $pagos[] = $pago;
            }
            return $pagos;
        }

        private function impuestosTotalesFacturaSri(array $detalles){
            $grupos = [];
            foreach($detalles as $detalle){
                foreach($detalle['impuestos'] as $impuesto){
                    $key = $impuesto['codigo'].'|'.$impuesto['codigo_porcentaje'].'|'.$impuesto['tarifa'];
                    if(!isset($grupos[$key])){
                        $grupos[$key] = [
                            'codigo'=>$impuesto['codigo'],
                            'codigo_porcentaje'=>$impuesto['codigo_porcentaje'],
                            'base_imponible'=>0.00,
                            'tarifa'=>(float)$impuesto['tarifa'],
                            'valor'=>0.00
                        ];
                    }
                    $grupos[$key]['base_imponible'] += (float)$impuesto['base_imponible'];
                    $grupos[$key]['valor'] += (float)$impuesto['valor'];
                }
            }
            return array_values($grupos);
        }

        private function regenerarXmlFacturaSri(\PDO $conexion, array $factura, array $config, FacturaElectronicaService $service){
            $detalles = $this->obtenerDetallesFacturaSri($conexion, $factura['id']);
            $pagos = $this->obtenerPagosFacturaSri($conexion, $factura['id']);
            if(empty($detalles)){ throw new \RuntimeException('La factura no tiene detalles para regenerar el XML.'); }
            if(empty($pagos)){ $pagos = [['forma_pago'=>(string)($config['forma_pago_default'] ?? '20'), 'total'=>(float)$factura['total']]]; }
            $datosFactura = [
                'clave_acceso'=>$factura['clave_acceso'],
                'establecimiento'=>$factura['establecimiento'],
                'punto_emision'=>$factura['punto_emision'],
                'secuencial'=>$factura['secuencial'],
                'fecha_emision'=>date('d/m/Y', strtotime($factura['fecha_emision'])),
                'cliente'=>[
                    'tipo_identificacion'=>$factura['cliente_tipo_identificacion'],
                    'identificacion'=>$factura['cliente_identificacion'],
                    'razon_social'=>$factura['cliente_razon_social'],
                    'direccion'=>$factura['cliente_direccion']
                ],
                'totales'=>[
                    'subtotal'=>(float)$factura['subtotal'],
                    'descuento'=>(float)$factura['descuento'],
                    'total'=>(float)$factura['total'],
                    'impuestos'=>$this->impuestosTotalesFacturaSri($detalles)
                ],
                'detalles'=>$detalles,
                'pagos'=>$pagos,
                'info_adicional'=>array_filter([
                    'Email'=>$factura['cliente_email'] ?? '',
                    'Telefono'=>$factura['cliente_telefono'] ?? ''
                ])
            ];
            $xml = $service->generarXMLFactura($datosFactura);
            $validacionXml = $service->validarXML($xml);
            if(empty($validacionXml['valido'])){ throw new \RuntimeException('El XML regenerado no es valido: '.implode('; ', $validacionXml['errores'] ?? [])); }
            $xmlPath = $service->guardarXML($xml, $factura['clave_acceso'], 'generados');
            $this->actualizarFacturaSri($conexion, $factura['id'], [
                'estado_sri'=>'GENERADA',
                'xml_generado'=>$xmlPath,
                'xml_firmado'=>null,
                'xml_autorizado'=>null,
                'numero_autorizacion'=>null,
                'fecha_autorizacion'=>null,
                'mensaje_error'=>null
            ]);
            $facturaActualizada = $this->obtenerFacturaElectronicaPorId($conexion, $factura['id']);
            $this->actualizarRideFacturaSri($conexion, $facturaActualizada, $config);
            return $xmlPath;
        }

		private function actualizarNumeracionFacturaSriConConfig(\PDO $conexion, array $factura, array $config, FacturaElectronicaService $service){
			$emisor = $config['emisor'] ?? [];
			$establecimientoConfig = str_pad(substr(preg_replace('/\D+/', '', (string)($emisor['codigo_establecimiento'] ?? '')), 0, 3), 3, '0', STR_PAD_LEFT);
			$puntoEmisionConfig = str_pad(substr(preg_replace('/\D+/', '', (string)($emisor['punto_emision'] ?? '')), 0, 3), 3, '0', STR_PAD_LEFT);

			if($establecimientoConfig === '000' || $puntoEmisionConfig === '000'){
				throw new \RuntimeException('Configure un establecimiento y punto de emision validos antes de reenviar.');
			}

			$mensajeError = strtoupper((string)($factura['mensaje_error'] ?? ''));
			$requiereNuevoSecuencial = strpos($mensajeError, 'SECUENCIAL REGISTRADO') !== false || preg_match('/(^|\D)45(\D|$)/', $mensajeError);

			if(!$requiereNuevoSecuencial && $establecimientoConfig === (string)$factura['establecimiento'] && $puntoEmisionConfig === (string)$factura['punto_emision']){
				return $factura;
			}

			$ruc = preg_replace('/\D+/', '', (string)($emisor['ruc'] ?? ''));
			if(strlen($ruc) !== 13){
				throw new \RuntimeException('Configure un RUC emisor valido antes de reenviar.');
			}

			$tipoComprobante = (string)($factura['tipo_comprobante'] ?? '01');
			$secuencial = $this->reservarSecuencialFactura($conexion, $tipoComprobante, $establecimientoConfig, $puntoEmisionConfig, (int)($config['secuencial_inicio'] ?? 1));
			$fechaEmision = !empty($factura['fecha_emision']) ? date('dmY', strtotime($factura['fecha_emision'])) : date('dmY');
			$claveAcceso = $service->generarClaveAcceso(
				$fechaEmision,
				$tipoComprobante,
				$ruc,
				(string)($config['ambiente'] ?? '1'),
				$establecimientoConfig.$puntoEmisionConfig,
				$secuencial,
				$service->generarCodigoNumerico(),
				(string)($config['tipo_emision'] ?? '1')
			);

			$this->actualizarFacturaSri($conexion, $factura['id'], [
				'clave_acceso'=>$claveAcceso,
				'establecimiento'=>$establecimientoConfig,
				'punto_emision'=>$puntoEmisionConfig,
				'secuencial'=>$secuencial,
				'ambiente'=>(string)($config['ambiente'] ?? '1'),
				'tipo_emision'=>(string)($config['tipo_emision'] ?? '1'),
				'estado_sri'=>'GENERADA',
				'xml_generado'=>null,
				'xml_firmado'=>null,
				'xml_autorizado'=>null,
				'ride_html'=>null,
				'numero_autorizacion'=>null,
				'fecha_autorizacion'=>null,
				'mensaje_error'=>null
			]);

			return $this->obtenerFacturaElectronicaPorId($conexion, $factura['id']);
		}

		private function actualizarFacturaSri(\PDO $conexion, $facturaId, array $campos){
			if(empty($campos)){
				return;
			}
			$set = [];
			$params = [':id'=>(int)$facturaId];
			foreach($campos as $campo=>$valor){
				$set[] = $campo.' = :'.$campo;
				$params[':'.$campo] = $valor;
			}
			$sql = "UPDATE facturas_electronicas SET ".implode(', ', $set)." WHERE id = :id";
			$stmt = $conexion->prepare($sql);
			$stmt->execute($params);
		}

		private function actualizarRideFacturaSri(\PDO $conexion, array $factura, array $config){
			$detalles = $this->obtenerDetallesRideFactura($conexion, $factura['id']);
			$numero = $factura['establecimiento'].'-'.$factura['punto_emision'].'-'.$factura['secuencial'];
			$ridePath = rtrim($config['storage']['ride'], '/\\').DIRECTORY_SEPARATOR.$factura['clave_acceso'].'.html';
			$ridePath = $this->crearRideHtml([
				'numero'=>$numero,
				'alumno_id'=>$factura['alumno_id'] ?? null,
				'clave_acceso'=>$factura['clave_acceso'],
				'estado_sri'=>$factura['estado_sri'],
				'ambiente'=>$factura['ambiente'],
				'cliente_razon_social'=>$factura['cliente_razon_social'],
				'cliente_identificacion'=>$factura['cliente_identificacion'],
				'cliente_direccion'=>$factura['cliente_direccion'],
				'cliente_email'=>$factura['cliente_email'],
				'numero_autorizacion'=>$factura['numero_autorizacion'] ?? '',
				'fecha_autorizacion'=>$factura['fecha_autorizacion'] ?? '',
				'subtotal_iva'=>$factura['subtotal_iva'],
				'subtotal_0'=>$factura['subtotal_0'],
				'iva'=>$factura['iva'],
				'total'=>$factura['total']
			], $detalles, $config, $ridePath);
			$this->actualizarFacturaSri($conexion, $factura['id'], ['ride_html'=>$ridePath]);
			return $ridePath;
		}

		private function emitirFacturaSriLocal($facturaId, $soloConsulta=false){
			$config = $this->sriConfig();
			$conexion = $this->conectar();
			$factura = $this->obtenerFacturaElectronicaPorId($conexion, $facturaId);
			$service = new FacturaElectronicaService($config);

			if($factura['estado_sri'] === 'AUTORIZADO'){
				return [
					'exito'=>true,
					'estado'=>'AUTORIZADO',
					'titulo'=>'Factura ya autorizada',
					'mensaje'=>'La factura ya se encuentra autorizada por el SRI.',
					'factura'=>$factura
				];
			}

			if(!$soloConsulta){
				$xmlFirmadoPath = $factura['xml_firmado'] ?? '';
                $estadoRequiereXmlNuevo = in_array((string)$factura['estado_sri'], ['ERROR', 'DEVUELTA', 'NO_AUTORIZADO'], true);
				$debeFirmar = $xmlFirmadoPath === '' || !is_file($xmlFirmadoPath) || $estadoRequiereXmlNuevo;
				$claveFirma = '';
				if($debeFirmar){
					$claveFirma = $this->leerClaveFirmaSri();
					if($claveFirma === ''){
						throw new \RuntimeException('Cargue la firma electronica y su clave antes de emitir.');
					}
				}
                if($estadoRequiereXmlNuevo){
                    $factura = $this->actualizarNumeracionFacturaSriConConfig($conexion, $factura, $config, $service);
                    $factura['xml_generado'] = $this->regenerarXmlFacturaSri($conexion, $factura, $config, $service);
                    $factura['xml_firmado'] = '';
                    $factura['xml_autorizado'] = '';
                    $xmlFirmadoPath = '';
                }
				if($debeFirmar){
					$xmlGeneradoPath = $factura['xml_generado'] ?? '';
					if($xmlGeneradoPath === '' || !is_file($xmlGeneradoPath)){
						throw new \RuntimeException('No se encontro el XML generado para firmar.');
					}
					$firma = new FirmaElectronicaService($config);
					$firma->cargarCertificado($config['firma']['archivo'] ?? '', $claveFirma);
					$xmlFirmado = $firma->firmarXML(file_get_contents($xmlGeneradoPath));
					if(!$firma->verificarFirma($xmlFirmado)){
						throw new \RuntimeException('La firma electronica no pudo verificarse estructuralmente.');
					}
					$xmlFirmadoPath = $service->guardarXML($xmlFirmado, $factura['clave_acceso'], 'firmados');
					$this->actualizarFacturaSri($conexion, $facturaId, [
						'estado_sri'=>'FIRMADA',
						'xml_firmado'=>$xmlFirmadoPath,
						'mensaje_error'=>null
					]);
				}else{
					$xmlFirmado = file_get_contents($xmlFirmadoPath);
				}

				$webService = new WebServiceSRIService($config);
				$resultado = $webService->procesarComprobante($xmlFirmado, $factura['clave_acceso']);
			}else{
				$webService = new WebServiceSRIService($config);
				$consulta = $webService->consultarAutorizacion($factura['clave_acceso']);
				$resultado = [
					'exito'=>!empty($consulta['exito']),
					'etapa'=>!empty($consulta['exito']) ? 'autorizado' : 'consulta',
					'resultado'=>$consulta
				];
			}

			$autorizaciones = $resultado['resultado']['autorizaciones'] ?? [];
			$autorizacion = null;
			foreach($autorizaciones as $item){
				if(($item['estado'] ?? '') === 'AUTORIZADO'){
					$autorizacion = $item;
					break;
				}
			}
			if($autorizacion === null && !empty($autorizaciones)){
				$autorizacion = $autorizaciones[0];
			}

			if($autorizacion && ($autorizacion['estado'] ?? '') === 'NO AUTORIZADO'){
				$mensajeNoAutorizado = $this->extraerMensajesSri(['autorizaciones'=>[$autorizacion]]);
				$this->actualizarFacturaSri($conexion, $facturaId, [
					'estado_sri'=>'NO_AUTORIZADO',
					'mensaje_error'=>$mensajeNoAutorizado
				]);
				$facturaActualizada = $this->obtenerFacturaElectronicaPorId($conexion, $facturaId);
				return [
					'exito'=>false,
					'estado'=>'NO_AUTORIZADO',
					'titulo'=>'Factura no autorizada',
					'mensaje'=>$mensajeNoAutorizado,
					'factura'=>$facturaActualizada
				];
			}

			if(!empty($resultado['exito']) && $autorizacion){
				$xmlAutorizado = $this->crearXmlAutorizadoSri($autorizacion, $factura['clave_acceso']);
				$xmlAutorizadoPath = $service->guardarXML($xmlAutorizado, $factura['clave_acceso'], 'autorizados');
				$this->actualizarFacturaSri($conexion, $facturaId, [
					'estado_sri'=>'AUTORIZADO',
					'numero_autorizacion'=>($autorizacion['numero_autorizacion'] ?? $factura['clave_acceso']),
					'fecha_autorizacion'=>$this->normalizarFechaAutorizacionSri($autorizacion['fecha_autorizacion'] ?? null),
					'xml_autorizado'=>$xmlAutorizadoPath,
					'mensaje_error'=>null
				]);
				$facturaActualizada = $this->obtenerFacturaElectronicaPorId($conexion, $facturaId);
				$this->actualizarRideFacturaSri($conexion, $facturaActualizada, $config);
				return [
					'exito'=>true,
					'estado'=>'AUTORIZADO',
					'titulo'=>'Factura autorizada',
					'mensaje'=>'La factura fue firmada, enviada y autorizada por el SRI.',
					'factura'=>$facturaActualizada
				];
			}

			$etapa = $resultado['etapa'] ?? 'error';
			$mensaje = $this->extraerMensajesSri($resultado['resultado'] ?? $resultado);
			if(in_array($etapa, ['en_procesamiento', 'timeout', 'consulta'], true)){
				$estadoPendiente = !empty($resultado['recepcion']['exito']) ? 'RECIBIDA' : 'ENVIADA';
				$this->actualizarFacturaSri($conexion, $facturaId, [
					'estado_sri'=>$estadoPendiente,
					'mensaje_error'=>$resultado['mensaje'] ?? $mensaje
				]);
				$facturaActualizada = $this->obtenerFacturaElectronicaPorId($conexion, $facturaId);
				return [
					'exito'=>true,
					'estado'=>$estadoPendiente,
					'titulo'=>'Factura enviada',
					'mensaje'=>$resultado['mensaje'] ?? 'La factura esta en procesamiento en el SRI. Consulte nuevamente en unos minutos.',
					'factura'=>$facturaActualizada
				];
			}

			$estadoError = ($etapa === 'no_autorizado') ? 'NO_AUTORIZADO' : (($resultado['resultado']['estado'] ?? '') === 'DEVUELTA' ? 'DEVUELTA' : 'ERROR');
			$this->actualizarFacturaSri($conexion, $facturaId, [
				'estado_sri'=>$estadoError,
				'mensaje_error'=>$mensaje
			]);
			$facturaActualizada = $this->obtenerFacturaElectronicaPorId($conexion, $facturaId);
			return [
				'exito'=>false,
				'estado'=>$estadoError,
				'titulo'=>'Factura no autorizada',
				'mensaje'=>$mensaje,
				'factura'=>$facturaActualizada
			];
		}

		public function emitirFacturaElectronicaSri(){
			$facturaId = (int)($_POST['factura_id'] ?? 0);
			if($facturaId <= 0){
				return $this->respuestaJson('simple', 'Factura no valida', 'No fue posible identificar la factura a emitir.', 'error');
			}
			try{
				$resultado = $this->emitirFacturaSriLocal($facturaId, false);
				$factura = $resultado['factura'] ?? [];
				$numero = !empty($factura) ? (($factura['establecimiento'] ?? '').'-'.($factura['punto_emision'] ?? '').'-'.($factura['secuencial'] ?? '')) : '';
				$tipo = !empty($resultado['exito']) ? (($resultado['estado'] === 'AUTORIZADO') ? 'factura_autorizada' : 'factura_enviada') : 'factura_error_sri';
				return $this->respuestaJson($tipo, $resultado['titulo'], $resultado['mensaje'], !empty($resultado['exito']) ? 'success' : 'error', [
					'factura_id'=>$facturaId,
					'numero'=>$numero,
					'clave_acceso'=>$factura['clave_acceso'] ?? '',
					'estado_sri'=>$resultado['estado'],
					'numero_autorizacion'=>$factura['numero_autorizacion'] ?? '',
					'xml_url'=>APP_URL.'app/ajax/facturasAjax.php?modulo_facturas=DESCARGAR_XML&factura_id='.$facturaId,
					'ride_url'=>APP_URL.'app/ajax/facturasAjax.php?modulo_facturas=VER_RIDE&factura_id='.$facturaId
				]);
			}catch(\Throwable $e){
				return $this->respuestaJson('simple', 'No fue posible emitir', $e->getMessage(), 'error');
			}
		}

		public function consultarFacturaElectronicaSri(){
			$facturaId = (int)($_POST['factura_id'] ?? 0);
			if($facturaId <= 0){
				return $this->respuestaJson('simple', 'Factura no valida', 'No fue posible identificar la factura a consultar.', 'error');
			}
			try{
				$resultado = $this->emitirFacturaSriLocal($facturaId, true);
				$factura = $resultado['factura'] ?? [];
				$numero = !empty($factura) ? (($factura['establecimiento'] ?? '').'-'.($factura['punto_emision'] ?? '').'-'.($factura['secuencial'] ?? '')) : '';
				$tipo = !empty($resultado['exito']) ? (($resultado['estado'] === 'AUTORIZADO') ? 'factura_autorizada' : 'factura_enviada') : 'factura_error_sri';
				return $this->respuestaJson($tipo, $resultado['titulo'], $resultado['mensaje'], !empty($resultado['exito']) ? 'success' : 'warning', [
					'factura_id'=>$facturaId,
					'numero'=>$numero,
					'clave_acceso'=>$factura['clave_acceso'] ?? '',
					'estado_sri'=>$resultado['estado'],
					'numero_autorizacion'=>$factura['numero_autorizacion'] ?? '',
					'xml_url'=>APP_URL.'app/ajax/facturasAjax.php?modulo_facturas=DESCARGAR_XML&factura_id='.$facturaId,
					'ride_url'=>APP_URL.'app/ajax/facturasAjax.php?modulo_facturas=VER_RIDE&factura_id='.$facturaId
				]);
			}catch(\Throwable $e){
				return $this->respuestaJson('simple', 'No fue posible consultar', $e->getMessage(), 'error');
			}
		}

		private function limpiarHeaderCorreo($valor){
			return trim(preg_replace('/[\r\n]+/', ' ', (string)$valor));
		}

		private function nombreAdjuntoSeguro($nombre){
			$nombre = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string)$nombre);
			return trim($nombre, '._') ?: 'adjunto';
		}

		private function extraerEmailCorreo($correo){
			$correo = trim((string)$correo);
			if(preg_match('/<([^>]+)>/', $correo, $m)){
				return trim($m[1]);
			}
			return $correo;
		}

		private function cabeceraCorreoUtf8($valor){
			$valor = $this->limpiarHeaderCorreo($valor);
			return preg_match('/^[\x20-\x7E]*$/', $valor) ? $valor : '=?UTF-8?B?'.base64_encode($valor).'?=';
		}

		private function formatearRemitenteCorreo($email, $nombre=''){
			$email = $this->extraerEmailCorreo($email);
			$nombre = trim((string)$nombre);
			return $nombre !== '' ? $this->cabeceraCorreoUtf8($nombre).' <'.$email.'>' : '<'.$email.'>';
		}

		private function construirMensajeSmtpMime($destinatario, $asunto, $mensaje, $from, $fromName, array $adjuntos){
			$boundary = '=_CDJG_'.md5(uniqid((string)time(), true));
			$headers = [
				'Date: '.date('r'),
				'From: '.$this->formatearRemitenteCorreo($from, $fromName),
				'To: <'.$this->extraerEmailCorreo($destinatario).'>',
				'Subject: '.$this->cabeceraCorreoUtf8($asunto),
				'MIME-Version: 1.0',
				'Content-Type: multipart/mixed; boundary="'.$boundary.'"',
			];

			$body = "--".$boundary."\r\n";
			$body .= "Content-Type: text/plain; charset=UTF-8\r\n";
			$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
			$body .= chunk_split(base64_encode((string)$mensaje))."\r\n";

			foreach($adjuntos as $adjunto){
				$path = $adjunto['path'] ?? '';
				if($path === '' || !is_file($path)){
					throw new \RuntimeException('No se encontro el adjunto de la factura.');
				}
				$filename = $this->nombreAdjuntoSeguro($adjunto['filename'] ?? basename($path));
				$contentType = $this->limpiarHeaderCorreo($adjunto['content_type'] ?? 'application/octet-stream');
				$body .= "--".$boundary."\r\n";
				$body .= "Content-Type: ".$contentType."; name=\"".$filename."\"\r\n";
				$body .= "Content-Transfer-Encoding: base64\r\n";
				$body .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
				$body .= chunk_split(base64_encode(file_get_contents($path)))."\r\n";
			}

			$body .= "--".$boundary."--";
			return implode("\r\n", $headers)."\r\n\r\n".$body;
		}

		private function smtpLeerRespuesta($socket){
			$respuesta = '';
			while(!feof($socket)){
				$linea = fgets($socket, 515);
				if($linea === false){
					break;
				}
				$respuesta .= $linea;
				if(strlen($linea) >= 4 && $linea[3] === ' '){
					break;
				}
			}
			if($respuesta === ''){
				throw new \RuntimeException('SMTP no devolvio respuesta.');
			}
			return [(int)substr($respuesta, 0, 3), trim($respuesta)];
		}

		private function smtpEnviarComando($socket, $comando, array $esperados, $etiqueta=''){
			if($comando !== null){
				fwrite($socket, $comando."\r\n");
			}
			[$codigo, $respuesta] = $this->smtpLeerRespuesta($socket);
			if(!in_array($codigo, $esperados, true)){
				$accion = $etiqueta !== '' ? $etiqueta : (is_string($comando) ? strtok($comando, ' ') : 'SMTP');
				throw new \RuntimeException('SMTP '.$accion.' fallo: '.$respuesta);
			}
			return $respuesta;
		}

		private function enviarCorreoSmtpConAdjuntos($destinatario, $asunto, $mensaje, $from, $fromName, array $adjuntos, array $smtp){
			$host = trim((string)($smtp['host'] ?? ''));
			$port = max(1, (int)($smtp['port'] ?? 587));
			$seguridad = in_array((string)($smtp['seguridad'] ?? 'tls'), ['tls', 'ssl', 'ninguna'], true) ? (string)$smtp['seguridad'] : 'tls';
			$usuario = trim((string)($smtp['usuario'] ?? ''));
			$clave = (string)($smtp['clave'] ?? '');
			$timeout = max(5, (int)($smtp['timeout'] ?? 30));

			if($host === ''){
				throw new \RuntimeException('Servidor SMTP no configurado.');
			}
			if(!function_exists('stream_socket_client')){
				throw new \RuntimeException('PHP no tiene disponible stream_socket_client para SMTP.');
			}

			$destinatario = $this->extraerEmailCorreo($destinatario);
			$fromEmail = $this->extraerEmailCorreo($from);
			if(!filter_var($destinatario, FILTER_VALIDATE_EMAIL) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)){
				throw new \RuntimeException('Correo remitente o destinatario no valido.');
			}

			$transporte = $seguridad === 'ssl' ? 'ssl' : 'tcp';
			$socket = @stream_socket_client($transporte.'://'.$host.':'.$port, $errno, $errstr, $timeout);
			if(!$socket){
				throw new \RuntimeException('No fue posible conectar al SMTP '.$host.':'.$port.'. '.$errstr);
			}

			try{
				stream_set_timeout($socket, $timeout);
				$ehlo = preg_replace('/[^A-Za-z0-9.-]+/', '', (string)(gethostname() ?: 'localhost')) ?: 'localhost';
				$this->smtpEnviarComando($socket, null, [220], 'conexion');
				$this->smtpEnviarComando($socket, 'EHLO '.$ehlo, [250], 'EHLO');

				if($seguridad === 'tls'){
					$this->smtpEnviarComando($socket, 'STARTTLS', [220], 'STARTTLS');
					if(!function_exists('stream_socket_enable_crypto') || !@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)){
						throw new \RuntimeException('No fue posible iniciar TLS con el servidor SMTP.');
					}
					$this->smtpEnviarComando($socket, 'EHLO '.$ehlo, [250], 'EHLO TLS');
				}

				if($usuario !== ''){
					$this->smtpEnviarComando($socket, 'AUTH LOGIN', [334], 'AUTH');
					$this->smtpEnviarComando($socket, base64_encode($usuario), [334], 'usuario');
					$this->smtpEnviarComando($socket, base64_encode($clave), [235], 'clave');
				}

				$this->smtpEnviarComando($socket, 'MAIL FROM:<'.$fromEmail.'>', [250], 'MAIL FROM');
				$this->smtpEnviarComando($socket, 'RCPT TO:<'.$destinatario.'>', [250, 251], 'RCPT TO');
				$this->smtpEnviarComando($socket, 'DATA', [354], 'DATA');

				$mensajeMime = $this->construirMensajeSmtpMime($destinatario, $asunto, $mensaje, $fromEmail, $fromName, $adjuntos);
				$mensajeMime = preg_replace('/^\./m', '..', $mensajeMime);
				fwrite($socket, $mensajeMime."\r\n.\r\n");
				$this->smtpEnviarComando($socket, null, [250], 'envio');
				@fwrite($socket, "QUIT\r\n");
			}finally{
				fclose($socket);
			}

			return true;
		}

		private function enviarCorreoConAdjuntos($destinatario, $asunto, $mensaje, $from, array $adjuntos, array $correoConfig=[]){
			$smtp = $correoConfig['smtp'] ?? [];
			if(($correoConfig['mailer'] ?? '') === 'smtp' || !empty($smtp['activo'])){
				return $this->enviarCorreoSmtpConAdjuntos(
					$destinatario,
					$asunto,
					$mensaje,
					$from,
					$correoConfig['from_name'] ?? APP_NAME,
					$adjuntos,
					$smtp
				);
			}

			if(!function_exists('mail')){
				throw new \RuntimeException('La funcion mail() no esta disponible en este servidor.');
			}

			// Mismo esquema que "Enviar recibo" (pagosReciboEnvio), que si entrega:
			// mail() con asunto y cuerpo en ISO-8859-1 y parte de texto 7bit.
			// El esquema previo (UTF-8 / 8bit / asunto RFC2047) era rechazado por
			// el MTA del servidor, por eso no llegaban los correos de facturas.
			$destinatario = $this->limpiarHeaderCorreo($destinatario);
			$from = $this->limpiarHeaderCorreo($from);
			$asunto = mb_convert_encoding($this->limpiarHeaderCorreo($asunto), 'ISO-8859-1', 'UTF-8');
			$mensaje = mb_convert_encoding($mensaje, 'ISO-8859-1', 'UTF-8');

			$uid = md5(uniqid((string)time()));
			$headers = "From: ".$from;
			$headers .= "\r\nMIME-Version: 1.0\r\n";
			$headers .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";

			$body = "--".$uid."\r\n";
			$body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
			$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
			$body .= $mensaje."\r\n\r\n";

			foreach($adjuntos as $adjunto){
				$path = $adjunto['path'] ?? '';
				if($path === '' || !is_file($path)){
					throw new \RuntimeException('No se encontro el adjunto de la factura.');
				}
				$filename = $this->nombreAdjuntoSeguro($adjunto['filename'] ?? basename($path));
				$contentType = $this->limpiarHeaderCorreo($adjunto['content_type'] ?? 'application/octet-stream');
				$contenido = chunk_split(base64_encode(file_get_contents($path)));
				$body .= "--".$uid."\r\n";
				$body .= "Content-Type: ".$contentType."; name=\"".$filename."\"\r\n";
				$body .= "Content-Transfer-Encoding: base64\r\n";
				$body .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
				$body .= $contenido."\r\n\r\n";
			}

			$body .= "--".$uid."--";

			$warningMail = '';
			set_error_handler(function($errno, $errstr) use (&$warningMail){
				$warningMail = trim((string)$errstr);
				return true;
			});
			try{
				$enviado = mail($destinatario, $asunto, $body, $headers);
			}finally{
				restore_error_handler();
			}

			if(!$enviado){
				$detalle = $warningMail !== '' ? ' Detalle tecnico: '.$warningMail : '';
				throw new \RuntimeException('El servidor no confirmo el envio del correo (mail()). Verifique la configuracion del correo saliente del servidor.'.$detalle);
			}

			return true;
		}

		private function enviarCorreoFacturaSriLocal($facturaId){
			$config = $this->sriConfig();
			$conexion = $this->conectar();
			$factura = $this->obtenerFacturaElectronicaPorId($conexion, $facturaId);
			if(($factura['estado_sri'] ?? '') !== 'AUTORIZADO'){
				throw new \RuntimeException('Solo se pueden enviar facturas autorizadas por el SRI.');
			}

			$email = trim((string)($factura['cliente_email'] ?? ''));
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
				throw new \RuntimeException('El correo del cliente no es valido.');
			}

			$xmlPath = $factura['xml_autorizado'] ?? '';
			if($xmlPath === '' || !is_file($xmlPath)){
				throw new \RuntimeException('No se encontro el XML autorizado para adjuntar.');
			}

			$ridePath = $this->actualizarRideFacturaSri($conexion, $factura, $config);

			$numero = $factura['establecimiento'].'-'.$factura['punto_emision'].'-'.$factura['secuencial'];

			// RIDE en PDF (legible) para adjuntar al correo
			$detallesRide = $this->obtenerDetallesRideFactura($conexion, $factura['id']);
			$ridePdfPath = rtrim($config['storage']['ride'], '/\\').DIRECTORY_SEPARATOR.$factura['clave_acceso'].'.pdf';
			$this->crearRidePdf([
				'numero'=>$numero,
				'alumno_id'=>$factura['alumno_id'] ?? null,
				'clave_acceso'=>$factura['clave_acceso'],
				'estado_sri'=>$factura['estado_sri'],
				'ambiente'=>$factura['ambiente'],
				'cliente_razon_social'=>$factura['cliente_razon_social'],
				'cliente_identificacion'=>$factura['cliente_identificacion'],
				'cliente_direccion'=>$factura['cliente_direccion'],
				'cliente_email'=>$factura['cliente_email'],
				'numero_autorizacion'=>$factura['numero_autorizacion'] ?? '',
				'fecha_autorizacion'=>$factura['fecha_autorizacion'] ?? '',
				'subtotal_iva'=>$factura['subtotal_iva'],
				'subtotal_0'=>$factura['subtotal_0'],
				'iva'=>$factura['iva'],
				'total'=>$factura['total'],
			], $detallesRide, $config, $ridePdfPath);

			$emisor = $config['emisor'] ?? [];
			$nombreEmisor = trim((string)($emisor['nombre_comercial'] ?? ''));
			if($nombreEmisor === ''){
				$nombreEmisor = trim((string)($emisor['razon_social'] ?? APP_NAME));
			}
			$correoConfig = $config['correo'] ?? [];
			$from = $correoConfig['from'] ?? (getenv('CDJG_FACTURACION_FROM') ?: 'facturacion@cdjg.local');
			if(empty($correoConfig['from_name'])){
				$correoConfig['from_name'] = $nombreEmisor;
			}
			$cliente = $this->limpiarTextoFactura($factura['cliente_razon_social'] ?? 'cliente');
			$mensaje = "Estimado/a ".$cliente.",\n\n";
			$mensaje .= "Adjuntamos la factura electronica ".$numero." autorizada por el SRI.\n\n";
			$mensaje .= "Clave de acceso: ".$factura['clave_acceso']."\n";
			$mensaje .= "Numero de autorizacion: ".($factura['numero_autorizacion'] ?? $factura['clave_acceso'])."\n";
			$mensaje .= "Valor total: $".number_format((float)$factura['total'], 2, '.', '')."\n\n";
			$mensaje .= $nombreEmisor;

			$adjuntos = [
				[
					'path'=>$ridePdfPath,
					'filename'=>'RIDE-'.$numero.'.pdf',
					'content_type'=>'application/pdf'
				],
				[
					'path'=>$xmlPath,
					'filename'=>$factura['clave_acceso'].'.xml',
					'content_type'=>'application/xml; charset=UTF-8'
				]
			];

			if(!$this->enviarCorreoConAdjuntos($email, 'Factura electronica '.$numero.' - '.$nombreEmisor, $mensaje, $from, $adjuntos, $correoConfig)){
				throw new \RuntimeException('El servidor no confirmo el envio del correo. Revise la configuracion mail() de PHP o SMTP local.');
			}

			return ['email'=>$email, 'numero'=>$numero];
		}

		public function enviarFacturaElectronicaCorreo(){
			$facturaId = (int)($_POST['factura_id'] ?? 0);
			if($facturaId <= 0){
				return $this->respuestaJson('simple', 'Factura no valida', 'No fue posible identificar la factura a enviar.', 'error');
			}
			try{
				$resultado = $this->enviarCorreoFacturaSriLocal($facturaId);
				return $this->respuestaJson('simple', 'Factura enviada', 'Se envio la factura '.$resultado['numero'].' a '.$resultado['email'].'.', 'success');
			}catch(\Throwable $e){
				return $this->respuestaJson('simple', 'No fue posible enviar', $e->getMessage(), 'error');
			}
		}

		public function generarFacturaElectronica(){
			if(!$this->facturaElectronicaInstalada()){
				return $this->respuestaJson('simple', 'Facturacion no configurada', 'Ejecute la migracion de facturacion electronica antes de generar comprobantes.', 'error');
			}
			$config = $this->sriConfig();
			$emisor = $config['emisor'] ?? [];
			if($this->leerClaveFirmaSri() === ''){
				return $this->respuestaJson('simple', 'Firma electronica pendiente', 'Cargue o actualice la clave de la firma electronica antes de generar y emitir facturas.', 'warning');
			}
			$alumnoid = (int)($_POST['alumno'] ?? 0);
			$formasPagoSri = $config['formas_pago'] ?? [];
			$formaPago = (string)($_POST['forma_pago'] ?? '');
			if($formaPago === '' || !isset($formasPagoSri[$formaPago])){
				$formaPago = (string)($config['forma_pago_default'] ?? '20');
			}
			$pagosIds = $_POST['pagos'] ?? ($_POST['pagos_seleccionados'] ?? []);
			if(!is_array($pagosIds)){ $pagosIds = [$pagosIds]; }
			if($alumnoid <= 0){ return $this->respuestaJson('simple', 'Alumno no valido', 'No fue posible identificar el alumno para la factura.', 'error'); }
			if(empty($pagosIds)){ return $this->respuestaJson('simple', 'Seleccione pagos', 'Seleccione al menos un pago pendiente para generar la factura.', 'warning'); }
			$ruc = preg_replace('/\D+/', '', (string)($emisor['ruc'] ?? ''));
			if(strlen($ruc) !== 13){ return $this->respuestaJson('simple', 'RUC emisor no valido', 'Configure un RUC de 13 digitos en config/sri.php o en las variables CDJG_SRI_*.', 'error'); }
			$conexion = $this->conectar();
			try{
				$conexion->beginTransaction();
				$representante = $this->obtenerRepresentanteFactura($conexion, $alumnoid);
				$clienteOrigen = (string)($representante['cliente_origen'] ?? 'REPRESENTANTE');
				$clienteTexto = $clienteOrigen === 'CONYUGE' ? 'conyuge' : 'representante';
				$clienteTipoIdentificacion = (string)($representante['cliente_tipoidentificacion'] ?? '');
				$clienteIdentificacion = trim((string)($representante['cliente_identificacion'] ?? ''));
				$clienteRazonSocial = trim((string)($representante['cliente_razon_social'] ?? ''));
				$clienteDireccion = trim((string)($representante['cliente_direccion'] ?? ''));
				$clienteCorreo = trim((string)($representante['cliente_correo'] ?? ''));
				$clienteCelular = trim((string)($representante['cliente_celular'] ?? ''));
				if($clienteIdentificacion === '' || $clienteRazonSocial === '' || $clienteDireccion === '' || $clienteCorreo === '' || $clienteCelular === ''){
					throw new \RuntimeException('Complete identificacion, nombres, direccion, correo y telefono del '.$clienteTexto.' antes de facturar.');
				}
				$tipoIdentificacion = $this->codigoSriTipoIdentificacion($clienteTipoIdentificacion, $clienteIdentificacion);
				if(!$this->validarIdentificacionSri($clienteIdentificacion, $clienteTipoIdentificacion)){ throw new \RuntimeException('La identificacion del '.$clienteTexto.' no es valida para facturacion SRI.'); }
				if(!filter_var($clienteCorreo, FILTER_VALIDATE_EMAIL)){ throw new \RuntimeException('El correo del '.$clienteTexto.' no es valido.'); }
				$pagos = $this->obtenerPagosFacturaElectronica($conexion, $representante['repre_id'], $pagosIds);
				$service = new FacturaElectronicaService($config);
				$tipoComprobante = '01';
				$establecimiento = str_pad((string)($emisor['codigo_establecimiento'] ?? '001'), 3, '0', STR_PAD_LEFT);
				$puntoEmision = str_pad((string)($emisor['punto_emision'] ?? '001'), 3, '0', STR_PAD_LEFT);
				$secuencial = $this->reservarSecuencialFactura($conexion, $tipoComprobante, $establecimiento, $puntoEmision, (int)($config['secuencial_inicio'] ?? 1));
				$codigoNumerico = $service->generarCodigoNumerico();
				$fechaEmision = date('Y-m-d');
				$claveAcceso = $service->generarClaveAcceso(date('dmY'), $tipoComprobante, $ruc, (string)($config['ambiente'] ?? '1'), $establecimiento.$puntoEmision, $secuencial, $codigoNumerico, (string)($config['tipo_emision'] ?? '1'));
				$ivaInfo = $this->tarifaIvaSri((float)($config['iva_tarifa_default'] ?? 0));
				$valoresIncluyenIva = !empty($config['valores_incluyen_iva']);
				$detalles = [];
				$subtotalIva = 0.00; $subtotal0 = 0.00; $ivaTotal = 0.00; $total = 0.00;
				foreach($pagos as $pago){
					$totalLinea = round((float)$pago['pago_valor'], 2);
					if($ivaInfo['porcentaje'] > 0){
						if($valoresIncluyenIva){ $base = round($totalLinea / (1 + ($ivaInfo['porcentaje'] / 100)), 2); $ivaLinea = round($totalLinea - $base, 2); }
						else{ $base = $totalLinea; $ivaLinea = round($base * ($ivaInfo['porcentaje'] / 100), 2); $totalLinea = round($base + $ivaLinea, 2); }
						$subtotalIva += $base;
					}else{
						$base = $totalLinea; $ivaLinea = 0.00; $subtotal0 += $base;
					}
					$total += $totalLinea; $ivaTotal += $ivaLinea;
					$detalles[] = [
						'pago_id'=>(int)$pago['pago_id'], 'codigo'=>(string)$pago['codigo'],
						'descripcion'=>$this->normalizarTextoSri('Pago '.$pago['detalle'].' del alumno '.$pago['alumno'], 300),
						'cantidad'=>1.000000, 'precio_unitario'=>$base, 'descuento'=>0.00,
						'precio_total_sin_impuesto'=>$base, 'iva_tarifa'=>$ivaInfo['porcentaje'],
						'iva_codigo_porcentaje'=>$ivaInfo['codigo_porcentaje'], 'iva_valor'=>$ivaLinea,
						'impuestos'=>[['codigo'=>$ivaInfo['codigo'], 'codigo_porcentaje'=>$ivaInfo['codigo_porcentaje'], 'tarifa'=>$ivaInfo['porcentaje'], 'base_imponible'=>$base, 'valor'=>$ivaLinea]]
					];
				}
				$subtotal = round($subtotalIva + $subtotal0, 2); $ivaTotal = round($ivaTotal, 2); $total = round($total, 2);
				$datosFactura = [
					'clave_acceso'=>$claveAcceso, 'establecimiento'=>$establecimiento, 'punto_emision'=>$puntoEmision, 'secuencial'=>$secuencial, 'fecha_emision'=>date('d/m/Y'),
					'cliente'=>['tipo_identificacion'=>$tipoIdentificacion, 'identificacion'=>$this->normalizarTextoSri($clienteIdentificacion, 20), 'razon_social'=>$this->normalizarTextoSri($clienteRazonSocial, 300), 'direccion'=>$this->normalizarTextoSri($clienteDireccion, 300)],
					'totales'=>['subtotal'=>$subtotal, 'descuento'=>0.00, 'total'=>$total, 'impuestos'=>[['codigo'=>$ivaInfo['codigo'], 'codigo_porcentaje'=>$ivaInfo['codigo_porcentaje'], 'base_imponible'=>$subtotal, 'tarifa'=>$ivaInfo['porcentaje'], 'valor'=>$ivaTotal]]],
					'detalles'=>$detalles, 'pagos'=>[['forma_pago'=>$formaPago, 'total'=>$total]],
					'info_adicional'=>['Email'=>$clienteCorreo, 'Telefono'=>$clienteCelular, 'Generado por'=>$_SESSION['usuario'] ?? 'Sistema']
				];
				$xml = $service->generarXMLFactura($datosFactura);
				$validacionXml = $service->validarXML($xml);
				if(empty($validacionXml['valido'])){ throw new \RuntimeException('El XML generado no es valido: '.implode('; ', $validacionXml['errores'] ?? [])); }
				$xmlPath = $service->guardarXML($xml, $claveAcceso, 'generados');
				$numero = $establecimiento.'-'.$puntoEmision.'-'.$secuencial;
				$insertFactura = $conexion->prepare("INSERT INTO facturas_electronicas (alumno_id, representante_id, clave_acceso, tipo_comprobante, establecimiento, punto_emision, secuencial, fecha_emision, ambiente, tipo_emision, cliente_tipo_identificacion, cliente_identificacion, cliente_razon_social, cliente_direccion, cliente_email, cliente_telefono, subtotal_iva, subtotal_0, subtotal_no_objeto, subtotal_exento, subtotal, descuento, iva, total, estado_sri, xml_generado, created_by) VALUES (:alumno_id, :representante_id, :clave_acceso, :tipo_comprobante, :establecimiento, :punto_emision, :secuencial, :fecha_emision, :ambiente, :tipo_emision, :cliente_tipo_identificacion, :cliente_identificacion, :cliente_razon_social, :cliente_direccion, :cliente_email, :cliente_telefono, :subtotal_iva, :subtotal_0, 0, 0, :subtotal, 0, :iva, :total, 'GENERADA', :xml_generado, :created_by)");
				$insertFactura->execute([':alumno_id'=>$alumnoid, ':representante_id'=>(int)$representante['repre_id'], ':clave_acceso'=>$claveAcceso, ':tipo_comprobante'=>$tipoComprobante, ':establecimiento'=>$establecimiento, ':punto_emision'=>$puntoEmision, ':secuencial'=>$secuencial, ':fecha_emision'=>$fechaEmision, ':ambiente'=>(string)($config['ambiente'] ?? '1'), ':tipo_emision'=>(string)($config['tipo_emision'] ?? '1'), ':cliente_tipo_identificacion'=>$tipoIdentificacion, ':cliente_identificacion'=>$this->normalizarTextoSri($clienteIdentificacion, 20), ':cliente_razon_social'=>$this->normalizarTextoSri($clienteRazonSocial, 300), ':cliente_direccion'=>$this->normalizarTextoSri($clienteDireccion, 300), ':cliente_email'=>$clienteCorreo, ':cliente_telefono'=>$clienteCelular, ':subtotal_iva'=>round($subtotalIva, 2), ':subtotal_0'=>round($subtotal0, 2), ':subtotal'=>$subtotal, ':iva'=>$ivaTotal, ':total'=>$total, ':xml_generado'=>$xmlPath, ':created_by'=>$_SESSION['id'] ?? null]);
				$facturaId = (int)$conexion->lastInsertId();
				$insertDetalle = $conexion->prepare("INSERT INTO facturas_electronicas_detalle (factura_electronica_id, pago_id, codigo_principal, descripcion, cantidad, precio_unitario, descuento, precio_total_sin_impuesto, iva_tarifa, iva_codigo_porcentaje, iva_valor) VALUES (:factura_id, :pago_id, :codigo, :descripcion, :cantidad, :precio_unitario, :descuento, :precio_total_sin_impuesto, :iva_tarifa, :iva_codigo_porcentaje, :iva_valor)");
				foreach($detalles as $detalle){
					$insertDetalle->execute([':factura_id'=>$facturaId, ':pago_id'=>$detalle['pago_id'], ':codigo'=>$detalle['codigo'], ':descripcion'=>$detalle['descripcion'], ':cantidad'=>$detalle['cantidad'], ':precio_unitario'=>$detalle['precio_unitario'], ':descuento'=>$detalle['descuento'], ':precio_total_sin_impuesto'=>$detalle['precio_total_sin_impuesto'], ':iva_tarifa'=>$detalle['iva_tarifa'], ':iva_codigo_porcentaje'=>$detalle['iva_codigo_porcentaje'], ':iva_valor'=>$detalle['iva_valor']]);
				}
				$insertPago = $conexion->prepare("INSERT INTO facturas_electronicas_pagos (factura_electronica_id, forma_pago, total) VALUES (:factura_id, :forma_pago, :total)");
				$insertPago->execute([':factura_id'=>$facturaId, ':forma_pago'=>$formaPago, ':total'=>$total]);
				$ridePath = rtrim($config['storage']['ride'], '/\\').DIRECTORY_SEPARATOR.$claveAcceso.'.html';
				$ridePath = $this->crearRideHtml(['numero'=>$numero, 'alumno_id'=>$alumnoid, 'clave_acceso'=>$claveAcceso, 'estado_sri'=>'GENERADA', 'ambiente'=>(string)($config['ambiente'] ?? '1'), 'cliente_razon_social'=>$this->normalizarTextoSri($clienteRazonSocial, 300), 'cliente_identificacion'=>$this->normalizarTextoSri($clienteIdentificacion, 20), 'cliente_direccion'=>$this->normalizarTextoSri($clienteDireccion, 300), 'cliente_email'=>$clienteCorreo, 'subtotal_iva'=>round($subtotalIva, 2), 'subtotal_0'=>round($subtotal0, 2), 'iva'=>$ivaTotal, 'total'=>$total], $detalles, $config, $ridePath);
				$updateRide = $conexion->prepare("UPDATE facturas_electronicas SET ride_html = :ride WHERE id = :id");
				$updateRide->execute([':ride'=>$ridePath, ':id'=>$facturaId]);
				$conexion->commit();
				try{
					$emision = $this->emitirFacturaSriLocal($facturaId, false);
					$tipoRespuesta = !empty($emision['exito']) ? (($emision['estado'] === 'AUTORIZADO') ? 'factura_autorizada' : 'factura_enviada') : 'factura_error_sri';
					$icono = !empty($emision['exito']) ? 'success' : 'error';
					return $this->respuestaJson($tipoRespuesta, $emision['titulo'], $emision['mensaje'], $icono, ['factura_id'=>$facturaId, 'numero'=>$numero, 'clave_acceso'=>$claveAcceso, 'estado_sri'=>$emision['estado'], 'numero_autorizacion'=>$emision['factura']['numero_autorizacion'] ?? '', 'xml_url'=>APP_URL.'app/ajax/facturasAjax.php?modulo_facturas=DESCARGAR_XML&factura_id='.$facturaId, 'ride_url'=>APP_URL.'app/ajax/facturasAjax.php?modulo_facturas=VER_RIDE&factura_id='.$facturaId]);
				}catch(\Throwable $emisionError){
					$this->actualizarFacturaSri($this->conectar(), $facturaId, ['estado_sri'=>'ERROR', 'mensaje_error'=>$emisionError->getMessage()]);
					return $this->respuestaJson('factura_error_sri', 'Factura generada sin autorizar', 'La factura '.$numero.' fue generada, pero no se pudo completar la emision SRI: '.$emisionError->getMessage(), 'error', ['factura_id'=>$facturaId, 'numero'=>$numero, 'clave_acceso'=>$claveAcceso, 'estado_sri'=>'ERROR', 'xml_url'=>APP_URL.'app/ajax/facturasAjax.php?modulo_facturas=DESCARGAR_XML&factura_id='.$facturaId, 'ride_url'=>APP_URL.'app/ajax/facturasAjax.php?modulo_facturas=VER_RIDE&factura_id='.$facturaId]);
				}
			}catch(\Throwable $e){
				if($conexion->inTransaction()){ $conexion->rollBack(); }
				return $this->respuestaJson('simple', 'No fue posible generar la factura', $e->getMessage(), 'error');
			}
		}

		public function obtenerArchivoFacturaElectronica($facturaId, $tipo='xml'){
			$config = $this->sriConfig();
			$facturaId = (int)$facturaId;
			$stmt = $this->ejecutarConsulta("SELECT id, clave_acceso, xml_generado, xml_firmado, xml_autorizado, ride_html FROM facturas_electronicas WHERE id = ".$facturaId);
			if($stmt->rowCount()<=0){ return null; }
			$factura = $stmt->fetch();
			$tipo = strtolower((string)$tipo);
			$archivo = '';
			$storageKey = 'xml_generados';
			if($tipo === 'ride'){
				$archivo = $factura['ride_html'] ?? '';
				$storageKey = 'ride';
			}elseif($tipo === 'autorizado'){
				$archivo = $factura['xml_autorizado'] ?? '';
				$storageKey = 'xml_autorizados';
			}elseif($tipo === 'firmado'){
				$archivo = $factura['xml_firmado'] ?? '';
				$storageKey = 'xml_firmados';
			}elseif($tipo === 'generado'){
				$archivo = $factura['xml_generado'] ?? '';
				$storageKey = 'xml_generados';
			}else{
				if(!empty($factura['xml_autorizado'])){ $archivo = $factura['xml_autorizado']; $storageKey = 'xml_autorizados'; }
				elseif(!empty($factura['xml_firmado'])){ $archivo = $factura['xml_firmado']; $storageKey = 'xml_firmados'; }
				else{ $archivo = $factura['xml_generado'] ?? ''; $storageKey = 'xml_generados'; }
			}
			if($archivo==='' || !is_file($archivo)){ return null; }
			$raiz = realpath($config['storage'][$storageKey] ?? dirname($archivo));
			$real = realpath($archivo);
			if(!$real || !$raiz || strpos($real, $raiz) !== 0){ return null; }
			return ['path'=>$real, 'filename'=>$factura['clave_acceso'].($tipo === 'ride' ? '.html' : '.xml'), 'content_type'=>$tipo === 'ride' ? 'text/html; charset=UTF-8' : 'application/xml; charset=UTF-8'];
		}


		/*  fin funciones para el manejo de facturas */


		/*----------  Controlador registrar alumno  ----------*/
		public function registrarAlumnoControlador(){
			/*---------------Variables para el registro del tab del alumno----------------*/
			$alumno_repreid 			= $this->limpiarCadena($_POST['alumno_repreid']);
			$alumno_identificacion 		= $this->limpiarCadena($_POST['alumno_identificacion']);
			$alumno_apellidopaterno 	= $this->limpiarCadena($_POST['alumno_apellido1']);
			$alumno_apellidomaterno 	= $this->limpiarCadena($_POST['alumno_apellido2']);
			$alumno_tipoidentificacion 	= $this->limpiarCadena($_POST['alumno_tipoidentificacion']);
			$alumno_primernombre 		= $this->limpiarCadena($_POST['alumno_nombre1']);
			$alumno_segundonombre 		= $this->limpiarCadena($_POST['alumno_nombre2']);
			$alumno_nacionalidadid		= $this->limpiarCadena($_POST['alumno_nacionalidadid']);
			$alumno_fechanacimiento 	= $this->limpiarCadena($_POST['alumno_fechanacimiento']);
			$alumno_direccion 			= $this->limpiarCadena($_POST['alumno_direccion']);
			$alumno_fechaingreso		= $this->limpiarCadena($_POST['alumno_fechaingreso']);
			$alumno_sedeid 				= $this->limpiarCadena($_POST['alumno_sedeid']);
			$alumno_nombrecorto 		= ""; //$this->limpiarCadena($_POST['alumno_nombrecorto']);
			$alumno_posicionid			= ""; //$this->limpiarCadena($_POST['alumno_posicionid']);
			$alumno_numcamiseta 		= $_POST['alumno_numcamiseta'];
			$alumno_estado 				= "A";
			$alumno_genero 				= "";
			$alumno_hermanos 			= "";

			if ($alumno_numcamiseta == "" ){$alumno_numcamiseta = 0;}

			if (isset($_POST['alumno_genero']) && isset($_POST['alumno_hermanos'])) {
				$alumno_genero 				= $_POST['alumno_genero'];
				$alumno_hermanos 			= $_POST['alumno_hermanos'];

			}else{
			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"No ha completado los campos obligatorios del alumno",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

		    # Verificando campos obligatorios #
		    if($alumno_identificacion=="" || $alumno_primernombre=="" || $alumno_apellidopaterno=="" || $alumno_fechanacimiento==""){
			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"No ha completado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

		    # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-ZÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚ÂºÃƒÆ’Ã†â€™Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â±ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“]{3,40}",$alumno_primernombre)){
			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"El nombre ingresado no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

            # Verificando identificacion #
		    $check_alumno=$this->ejecutarConsulta("SELECT alumno_identificacion FROM sujeto_alumno WHERE alumno_identificacion='$alumno_identificacion'");
		    if($check_alumno->rowCount()>0){
			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"La identificaciÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³n ingresada ya se encuentra registrada, por favor verificar",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

		    # Directorio de imagenes #
		$img_dir="../views/imagenes/fotos/alumno/";
			$codigo=rand(0,100);

		# Comprobar si se selecciono una imagen #
		if($_FILES['alumno_foto']['name']!="" && $_FILES['alumno_foto']['size']>0){

			# Creando directorio #
		        if(!file_exists($img_dir)){
		            if(!mkdir($img_dir,0777)){
			$alerta=[
							"tipo"=>"simple",
							"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
							"texto"=>"No fue posible crear el directorio",
							"icono"=>"error"
						];
						return json_encode($alerta);
		            }
		        }

		        # Verificando formato de imagenes #
		        if(mime_content_type($_FILES['alumno_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['alumno_foto']['tmp_name'])!="image/png"){
			$alerta=[
						"tipo"=>"simple",
						"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
		        }

		        # Verificando peso de imagen #
		        if(($_FILES['alumno_foto']['size']/1024)>4000){
			$alerta=[
						"tipo"=>"simple",
						"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido 4MB",
						"icono"=>"error"
					];
					return json_encode($alerta);
		        }

		        # Nombre de la foto #
		        $foto=str_ireplace(" ","_",$alumno_identificacion);
		        $foto=$foto."_".$codigo;

		        # Extension de la imagen #
		        switch(mime_content_type($_FILES['alumno_foto']['tmp_name'])){
		            case 'image/jpeg':
		                $foto=$foto.".jpg";
		            break;
		            case 'image/png':
		                $foto=$foto.".png";
		            break;
		        }
				$maxWidth = 800;
			$maxHeight = 600;

				chmod($img_dir,0777);
				$inputFile = ($_FILES['alumno_foto']['tmp_name']);
			$outputFile = $img_dir.$foto;

				# Moviendo imagen al directorio #
				//if(!move_uploaded_file($_FILES['alumno_foto']['tmp_name'],$img_dir.$foto)){
				if ($this->resizeImageGD($inputFile, $maxWidth, $maxHeight, $outputFile)) {

				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
						"texto"=>"No es posible subir la imagen al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

		}else{
			$foto="";
		}

			/*---------------Registro del tab Cedula del alumno----------------*/

			# Directorio de imagenes #
			$img_cedula="../views/imagenes/cedulas/";

			# Comprobar si seleccionÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ el Anverso de la cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dula #
			if($_FILES['alumno_cedulaA']['name']!="" && $_FILES['alumno_cedulaA']['size']>0){

				# Creando directorio #
				if(!file_exists($img_cedula)){
					if(!mkdir($img_cedula,0777)){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
							"texto"=>"No fue posible crear el directorio para almacenar las imÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡genes de la cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dula",
							"icono"=>"error"
						];
						return json_encode($alerta);
					}
				}

				# Verificando formato de imagenes #
				if(mime_content_type($_FILES['alumno_cedulaA']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['alumno_cedulaA']['tmp_name'])!="image/png"){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Verificando peso de imagen #
				if(($_FILES['alumno_cedulaA']['size']/1024)>4000){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido 4MB",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Nombre de la foto #
				$cedulaA=str_ireplace(" ","_",$alumno_identificacion);
				$cedulaA=$cedulaA."_A".$codigo;

				# Extension de la imagen #
				switch(mime_content_type($_FILES['alumno_cedulaA']['tmp_name'])){
					case 'image/jpeg':
						$cedulaA=$cedulaA.".jpg";
					break;
					case 'image/png':
						$cedulaA=$cedulaA.".png";
					break;
				}
				$maxWidth = 800;
				$maxHeight = 600;

				chmod($img_cedula,0777);
				$inputFile = ($_FILES['alumno_cedulaA']['tmp_name']);
				$outputFile = $img_cedula.$cedulaA;

				# Moviendo imagen al directorio #
				//if(!move_uploaded_file($_FILES['alumno_foto']['tmp_name'],$img_dir.$foto)){
				if ($this->resizeImageGD($inputFile, $maxWidth, $maxHeight, $outputFile)) {

				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
						"texto"=>"No es posible subir la imagen al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

			}else{
				$cedulaA="";
			}

			# Comprobar si seleccionÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ el reverso de la cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dula #
			if($_FILES['alumno_cedulaR']['name']!="" && $_FILES['alumno_cedulaR']['size']>0){

				# Creando directorio #
				if(!file_exists($img_cedula)){
					if(!mkdir($img_cedula,0777)){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
							"texto"=>"No fue posible crear el directorio para almacenar las imÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡genes de la cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dula",
							"icono"=>"error"
						];
						return json_encode($alerta);
					}
				}

				# Verificando formato de imagenes #
				if(mime_content_type($_FILES['alumno_cedulaR']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['alumno_cedulaR']['tmp_name'])!="image/png"){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Verificando peso de imagen #
				if(($_FILES['alumno_cedulaR']['size']/1024)>4000){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido 4MB",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Nombre de la foto #
				$cedulaR=str_ireplace(" ","_",$alumno_identificacion);
				$cedulaR=$cedulaR."_R".$codigo;

				# Extension de la imagen #
				switch(mime_content_type($_FILES['alumno_cedulaR']['tmp_name'])){
					case 'image/jpeg':
						$cedulaR=$cedulaR.".jpg";
					break;
					case 'image/png':
						$cedulaR=$cedulaR.".png";
					break;
				}
				$maxWidth = 800;
				$maxHeight = 600;

				chmod($img_cedula,0777);
				$inputFile = ($_FILES['alumno_cedulaR']['tmp_name']);
				$outputFile = $img_cedula.$cedulaR;

				# Moviendo imagen al directorio #
				//if(!move_uploaded_file($_FILES['alumno_foto']['tmp_name'],$img_dir.$foto)){
				if ($this->resizeImageGD($inputFile, $maxWidth, $maxHeight, $outputFile)) {

				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
						"texto"=>"No es posible subir la imagen al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

			}else{
				$cedulaR="";
			}

		    $alumno_datos_reg=[
				[
					"campo_nombre"=>"alumno_repreid",
					"campo_marcador"=>":Repreid",
					"campo_valor"=>$alumno_repreid
				],
				[
					"campo_nombre"=>"alumno_sedeid",
					"campo_marcador"=>":Sedeid",
					"campo_valor"=>$alumno_sedeid
				],
				[
					"campo_nombre"=>"alumno_posicionid",
					"campo_marcador"=>":Posicionid",
					"campo_valor"=>$alumno_posicionid
				],
				[
					"campo_nombre"=>"alumno_nacionalidadid",
					"campo_marcador"=>":Nacionalidadid",
					"campo_valor"=>$alumno_nacionalidadid
				],
				[
					"campo_nombre"=>"alumno_tipoidentificacion",
					"campo_marcador"=>":Tipoidentificacion",
					"campo_valor"=>$alumno_tipoidentificacion
				],
				[
					"campo_nombre"=>"alumno_identificacion",
					"campo_marcador"=>":Identificacion",
					"campo_valor"=>$alumno_identificacion
				],
				[
					"campo_nombre"=>"alumno_primernombre",
					"campo_marcador"=>":Primernombre",
					"campo_valor"=>$alumno_primernombre
				],
				[
					"campo_nombre"=>"alumno_segundonombre",
					"campo_marcador"=>":Segundonombre",
					"campo_valor"=>$alumno_segundonombre
				],
				[
					"campo_nombre"=>"alumno_apellidopaterno",
					"campo_marcador"=>":Apellidopaterno",
					"campo_valor"=>$alumno_apellidopaterno
				],
				[
					"campo_nombre"=>"alumno_apellidomaterno",
					"campo_marcador"=>":Apellidomaterno",
					"campo_valor"=>$alumno_apellidomaterno
				],
				[
					"campo_nombre"=>"alumno_nombrecorto",
					"campo_marcador"=>":Nombrecorto",
					"campo_valor"=>$alumno_nombrecorto
				],
				[
					"campo_nombre"=>"alumno_direccion",
					"campo_marcador"=>":Direccion",
					"campo_valor"=>$alumno_direccion
				],
				[
					"campo_nombre"=>"alumno_fechanacimiento",
					"campo_marcador"=>":Fechanacimiento",
					"campo_valor"=>$alumno_fechanacimiento
				],
				[
					"campo_nombre"=>"alumno_fechaingreso",
					"campo_marcador"=>":Fechaingreso",
					"campo_valor"=>$alumno_fechaingreso
				],
				[
					"campo_nombre"=>"alumno_genero",
					"campo_marcador"=>":Genero",
					"campo_valor"=>$alumno_genero
				],
				[
					"campo_nombre"=>"alumno_hermanos",
					"campo_marcador"=>":Hermanos",
					"campo_valor"=>$alumno_hermanos
				],
				[
					"campo_nombre"=>"alumno_estado",
					"campo_marcador"=>":Activo",
					"campo_valor"=>$alumno_estado
				],
				[
					"campo_nombre"=>"alumno_imagen",
					"campo_marcador"=>":Foto",
					"campo_valor"=>$foto
				],
				[
					"campo_nombre"=>"alumno_numcamiseta",
					"campo_marcador"=>":Camiseta",
					"campo_valor"=>$alumno_numcamiseta
				],
				[
					"campo_nombre"=>"alumno_cedulaA",
					"campo_marcador"=>":CedulaA",
					"campo_valor"=>$cedulaA
				],
				[
					"campo_nombre"=>"alumno_cedulaR",
					"campo_marcador"=>":CedulaR",
					"campo_valor"=>$cedulaR
				]
			];

			$registrar_alumno=$this->guardarDatos("sujeto_alumno",$alumno_datos_reg);

			/*---------------Inicio de registro de InformaciÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³n de los tabs*/
			if($registrar_alumno->rowCount()==1){
				$alerta=[
					"tipo"=>"limpiar",
					"titulo"=>"Alumno registrado",
					"texto"=>"El alumno ".$alumno_identificacion." | ".$alumno_primernombre." ".$alumno_apellidopaterno." se registrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ correctamente",
					"icono"=>"success"
				];

				$infomedic_tiposangre 	= $this->limpiarCadena($_POST['infomedic_tiposangre']);
				$infomedic_peso		  	= $this->limpiarCadena($_POST['infomedic_peso']);
				$infomedic_talla 	  	= $this->limpiarCadena($_POST['infomedic_talla']);
				$infomedic_enfermedad 	= $this->limpiarCadena($_POST['infomedic_enfermedad']);
				$infomedic_medicamentos = $this->limpiarCadena($_POST['infomedic_medicamentos']);
				$infomedic_alergia1 	= $this->limpiarCadena($_POST['infomedic_alergia1']);
				$infomedic_alergia2 	= $this->limpiarCadena($_POST['infomedic_alergia2']);
				$infomedic_cirugias 	= $this->limpiarCadena($_POST['infomedic_cirugias']);
				$infomedic_observacion	= $this->limpiarCadena($_POST['infomedic_observacion']);

				if ($infomedic_peso ==""){$infomedic_peso = 0;}
				if ($infomedic_talla ==""){$infomedic_talla = 0;}

				if(isset($_POST['infomedic_covid'])){ $infomedic_covid  = $_POST['infomedic_covid']; }else {$infomedic_covid="";}
				if(isset($_POST['infomedic_vacunas'])){ $infomedic_vacunas  = $_POST['infomedic_vacunas']; }else {$infomedic_vacunas="";}

				/*---------------Obtengo campo alumnoid para todas las tablas*/
				$check_alumno=$this->ejecutarConsulta("SELECT alumno_id FROM sujeto_alumno WHERE alumno_identificacion='$alumno_identificacion'");

				if($check_alumno->rowCount()==1){
					$alumno=$check_alumno->fetchAll();
					foreach( $alumno as $rows ){
						$alumnoid = $rows['alumno_id'];
					}
				}

				/*---------------Registro del tab InformaciÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³n MÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dica del alumno*/
				if($infomedic_tiposangre!="" || $infomedic_peso>0 || $infomedic_talla>0 || $infomedic_enfermedad!=""||
					$infomedic_medicamentos!="" || $infomedic_alergia1!="" || $infomedic_alergia2!="" || $infomedic_cirugias!="" ||
					$infomedic_observacion!=""){

					$infomedic_reg=[
						[
							"campo_nombre"=>"infomedic_alumnoid",
							"campo_marcador"=>":Alumnoid",
							"campo_valor"=>$alumnoid
						],
						[
							"campo_nombre"=>"infomedic_fecha",
							"campo_marcador"=>":Fechacreacion",
							"campo_valor"=>date("Y-m-d H:i:s")
						],
						[
							"campo_nombre"=>"infomedic_tiposangre",
							"campo_marcador"=>":Tiposangre",
							"campo_valor"=>$infomedic_tiposangre
						],
						[
							"campo_nombre"=>"infomedic_peso",
							"campo_marcador"=>":Peso",
							"campo_valor"=>$infomedic_peso
						],
						[
							"campo_nombre"=>"infomedic_talla",
							"campo_marcador"=>":Talla",
							"campo_valor"=>$infomedic_talla
						],
						[
							"campo_nombre"=>"infomedic_enfermedad",
							"campo_marcador"=>":Enfermedad",
							"campo_valor"=>$infomedic_enfermedad
						],
						[
							"campo_nombre"=>"infomedic_medicamentos",
							"campo_marcador"=>":Medicamentos",
							"campo_valor"=>$infomedic_medicamentos
						],
						[
							"campo_nombre"=>"infomedic_alergia1",
							"campo_marcador"=>":AlergiaMedicamentos",
							"campo_valor"=>$infomedic_alergia1
						],
						[
							"campo_nombre"=>"infomedic_alergia2",
							"campo_marcador"=>":AlergiaObjetos",
							"campo_valor"=>$infomedic_alergia2
						],
						[
							"campo_nombre"=>"infomedic_cirugias",
							"campo_marcador"=>":Cirugias",
							"campo_valor"=>$infomedic_cirugias
						],
						[
							"campo_nombre"=>"infomedic_observacion",
							"campo_marcador"=>":Observacion",
							"campo_valor"=>$infomedic_observacion
						],
						[
							"campo_nombre"=>"infomedic_covid",
							"campo_marcador"=>":VacunasCovid",
							"campo_valor"=>$infomedic_covid
						],
						[
							"campo_nombre"=>"infomedic_vacunas",
							"campo_marcador"=>":Vacunas",
							"campo_valor"=>$infomedic_vacunas
						]
					];

					$this->guardarDatos("alumno_infomedic",$infomedic_reg);
				}

				/*---------------Fin de registro del tab InformaciÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³n MÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dica del alumno*/

				/*---------------Registro del tab Contacto Emergencia del alumno------------*/
				$cemer_nombre 		= $this->limpiarCadena($_POST['cemer_nombre']);
				$cemer_celular 		= $this->limpiarCadena($_POST['cemer_celular']);
				$cemer_parentesco	= $this->limpiarCadena($_POST['cemer_parentesco']);

				if($cemer_nombre!="" || $cemer_celular!=""){
					$cemergencia_reg=[
						[
							"campo_nombre"=>"cemer_alumnoid",
							"campo_marcador"=>":Alumnoid",
							"campo_valor"=>$alumnoid
						],
						[
							"campo_nombre"=>"cemer_nombre",
							"campo_marcador"=>":NombreContactoEmer",
							"campo_valor"=>$cemer_nombre
						],
						[
							"campo_nombre"=>"cemer_celular",
							"campo_marcador"=>":CelularContactoEmer",
							"campo_valor"=>$cemer_celular
						],
						[
							"campo_nombre"=>"cemer_parentesco",
							"campo_marcador"=>":ParentescoContactoEmer",
							"campo_valor"=>$cemer_parentesco
						]
					];

					$this->guardarDatos("alumno_cemergencia",$cemergencia_reg);
				}
				/*---------------Fin de registro del tab Contacto Emergencia del alumno------*/

				/*---------------Actulizar horario de entrenamiento---------------------*/
				$horario_id = $this->limpiarCadena($_POST['horarioid']);

				if($horario_id!="" || $alumnoid!=""){

					$asignacion_horario_reg = [
						[
							"campo_nombre" => "asignahorario_horarioid",
							"campo_marcador" => ":Horarioid",
							"campo_valor" => $horario_id
						],
						[
							"campo_nombre" => "asignahorario_alumnoid",
							"campo_marcador" => ":Alumnoid",
							"campo_valor" => $alumnoid
						]
					];

					$this->guardarDatos("asistencia_asignahorario",$asignacion_horario_reg);
				}


				/*---------------Fin de actualizacion de horariop de entrenamiento------*/
			}else{
				if(is_file($img_dir.$foto)){
					chmod($img_dir.$foto,0777);
					unlink($img_dir.$foto);
				}

				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"No se pudo registrar la informaciÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³n del alumno, por favor intente nuevamente",
					"icono"=>"error"
				];
			}
			return json_encode($alerta);
		}

		/*----------  Matriz de alumnos con opciones Ver, Actualizar, Eliminar  ----------*/


		/*----------  Obtener el tipo de documento guardado  ----------*/
		public function listarOptionTipoIdentificacion($tipoidentificacion){
			$option="";

			$consulta_datos="SELECT C.catalogo_valor, C.catalogo_descripcion
								FROM general_tabla_catalogo C
								INNER JOIN general_tabla T on T.tabla_id = C.catalogo_tablaid
								WHERE T.tabla_nombre = 'tipo_documento'";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				if($tipoidentificacion == $rows['catalogo_valor']){
					$option.='<option value='.$rows['catalogo_valor'].' selected="selected">'.$rows['catalogo_descripcion'].'</option>';
				}else{
					$option.='<option value='.$rows['catalogo_valor'].'>'.$rows['catalogo_descripcion'].'</option>';
				}
			}
			return $option;
		}

		/*----------  Obtener la nacionalidad guardada  ----------*/
		public function listarOptionNacionalidad($alumno_nacionalidadid){
			$option="";

			$consulta_datos="SELECT C.catalogo_valor, C.catalogo_descripcion
								FROM general_tabla_catalogo C
								INNER JOIN general_tabla T on T.tabla_id = C.catalogo_tablaid
								WHERE T.tabla_nombre = 'nacionalidad'";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				if($alumno_nacionalidadid == $rows['catalogo_valor']){
					$option.='<option value='.$rows['catalogo_valor'].' selected="selected">'.$rows['catalogo_descripcion'].'</option>';
				}else{
					$option.='<option value='.$rows['catalogo_valor'].'>'.$rows['catalogo_descripcion'].'</option>';
				}
			}
			return $option;
		}

		/*----------  Obtener la sede guardada  ----------*/
		public function listarSedeAlumno($alumno_sedeid){
			$option="";

			$consulta_datos="SELECT sede_id, sede_nombre FROM general_sede";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				if($alumno_sedeid == $rows['sede_id']){
					$option.='<option value='.$rows['sede_id'].' selected="selected">'.$rows['sede_nombre'].'</option>';
				}else{
					$option.='<option value='.$rows['sede_id'].'>'.$rows['sede_nombre'].'</option>';
				}
			}
			return $option;
		}

		/*----------  Obtener la posiciÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³n de juego guardada  ----------*/
		public function listarAlumnosPDF($categoriaid,$sedeid){
			$consulta_datos=("SELECT sede_nombre, alumno_identificacion, alumno_primernombre, alumno_segundonombre,
									alumno_apellidopaterno, alumno_apellidomaterno, alumno_fechanacimiento
								FROM sujeto_alumno, general_sede
								WHERE alumno_estado = 'A'
									AND alumno_sedeid = sede_id");

			if($categoriaid!=0){
				$consulta_datos .= " and YEAR(alumno_fechanacimiento) = ".$categoriaid;
			}

			if($sedeid!=0){
				$consulta_datos .= " and alumno_sedeid = ".$sedeid;
			}

			$consulta_datos.= " ORDER BY alumno_fechanacimiento";

			$datos = $this->ejecutarConsulta($consulta_datos);
			return $datos;
		}

		public function listarOptionParentesco($cemer_parentesco){
			$option="";

			$consulta_datos="SELECT C.catalogo_valor, C.catalogo_descripcion
								FROM general_tabla_catalogo C
								INNER JOIN general_tabla T on T.tabla_id = C.catalogo_tablaid
								WHERE T.tabla_nombre = 'parentesco'";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				if($cemer_parentesco == $rows['catalogo_valor']){
					$option.='<option value='.$rows['catalogo_valor'].' selected="selected">'.$rows['catalogo_descripcion'].'</option>';
				}else{
					$option.='<option value='.$rows['catalogo_valor'].'>'.$rows['catalogo_descripcion'].'</option>';
				}
			}
			return $option;
		}

		/*----------  Controlador eliminar alumno  ----------*/
		public function actualizarEstadoAlumnoControlador(){

			$alumno_id=$this->limpiarCadena($_POST['alumno_id']);

			# Verificando usuario #
		    $datos=$this->ejecutarConsulta("SELECT * FROM sujeto_alumno WHERE alumno_id='$alumno_id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"El alumno no se encuentra en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
			$datos=$datos->fetch();
		    }
			if($datos['alumno_estado']=='A'){
				$estadoA = 'I';
			}else{
				$estadoA = 'A';
			}
            $alumno_datos_up=[
				[
					"campo_nombre"=>"alumno_estado",
					"campo_marcador"=>":Estado",
					"campo_valor"=> $estadoA
				]
			];
			$condicion=[
				"condicion_campo"=>"alumno_id",
				"condicion_marcador"=>":Alumnoid",
				"condicion_valor"=>$alumno_id
			];

			if($this->actualizarDatos("sujeto_alumno",$alumno_datos_up,$condicion)){

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Estado actualizado correctamente",
					"texto"=>"El estado del alumno ".$datos['alumno_primernombre']." | ".$datos['alumno_apellidopaterno']." fue actualizado correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error inesperado",
					"texto"=>"No hemos podido actualizar el estado del alumno ".$datos['alumno_primernombre']." ".$datos['alumno_apellidopaterno'].", por favor intente nuevamente",
					"icono"=>"error"
				];
			}
			return json_encode($alerta);
		}

		/*----------  Controlador eliminar alumno  ----------*/
		public function eliminarAlumnoControlador(){

			$alumno_id=$this->limpiarCadena($_POST['alumno_id']);

			# Verificando usuario #
		    $datos=$this->ejecutarConsulta("SELECT * FROM sujeto_alumno WHERE alumno_id='$alumno_id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"El alumno no se encuentra en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
			$datos=$datos->fetch();
		    }
			if($datos['alumno_estado']=='A' || $datos['alumno_estado']=='I'){
				$estadoA = 'E';
			}else{
				$estadoA = 'X';
			}
            $alumno_datos_up=[
				[
					"campo_nombre"=>"alumno_estado",
					"campo_marcador"=>":Estado",
					"campo_valor"=> $estadoA
				]
			];
			$condicion=[
				"condicion_campo"=>"alumno_id",
				"condicion_marcador"=>":Alumnoid",
				"condicion_valor"=>$alumno_id
			];

			if($this->actualizarDatos("sujeto_alumno",$alumno_datos_up,$condicion)){

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"El alumno fue eliminado correctamente",
					"texto"=>"El alumno ".$datos['alumno_primernombre']." | ".$datos['alumno_apellidopaterno']." fue eliminado correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error inesperado",
					"texto"=>"No hemos podido eliminar el alumno ".$datos['alumno_primernombre']." ".$datos['alumno_apellidopaterno'].", por favor intente nuevamente",
					"icono"=>"error"
				];
			}
			return json_encode($alerta);
		}

		/*----------  Controlador actualizar alumno  ----------*/
		public function actualizarAlumnoControlador(){

			$alumnoid=$this->limpiarCadena($_POST['alumno_id']);

			# Verificando usuario #
		    $datos=$this->ejecutarConsulta("SELECT * FROM sujeto_alumno WHERE alumno_id ='$alumnoid'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"El alumno no se encuentra en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
			$datos=$datos->fetch();
		    }

			/*---------------Variables para el registro del tab del alumno----------------*/
			$alumno_identificacion 		= $this->limpiarCadena($_POST['alumno_identificacion']);
			$alumno_apellidopaterno 	= $this->limpiarCadena($_POST['alumno_apellido1']);
			$alumno_apellidomaterno 	= $this->limpiarCadena($_POST['alumno_apellido2']);
			$alumno_tipoidentificacion 	= $this->limpiarCadena($_POST['alumno_tipoidentificacion']);
			$alumno_primernombre 		= $this->limpiarCadena($_POST['alumno_nombre1']);
			$alumno_segundonombre 		= $this->limpiarCadena($_POST['alumno_nombre2']);
			$alumno_nacionalidadid		= $this->limpiarCadena($_POST['alumno_nacionalidadid']);
			$alumno_fechanacimiento 	= $this->limpiarCadena($_POST['alumno_fechanacimiento']);
			$alumno_direccion 			= $this->limpiarCadena($_POST['alumno_direccion']);
			$alumno_fechaingreso		= $this->limpiarCadena($_POST['alumno_fechaingreso']);
			$alumno_sedeid 				= $this->limpiarCadena($_POST['alumno_sedeid']);
			$alumno_nombrecorto 		= ""; //$this->limpiarCadena($_POST['alumno_nombrecorto']);
			$alumno_posicionid			= ""; //$this->limpiarCadena($_POST['alumno_posicionid']);
			$alumno_numcamiseta 		= $_POST['alumno_numcamiseta'];
			$alumno_genero 				= "";
			$alumno_hermanos 			= "";

			if ($alumno_numcamiseta == ""){$alumno_numcamiseta = 0;}

			if (isset($_POST['alumno_genero']) && isset($_POST['alumno_hermanos'])) {
				$alumno_genero 				= $_POST['alumno_genero'];
				$alumno_hermanos 			= $_POST['alumno_hermanos'];

			}else{
			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"No ha completado los campos obligatorios del alumno",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

		    # Verificando campos obligatorios #
		    if($alumno_identificacion=="" || $alumno_primernombre=="" || $alumno_apellidopaterno=="" || $alumno_fechanacimiento==""){
			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"No ha completado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

		    # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-ZÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚ÂºÃƒÆ’Ã†â€™Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â±ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“]{3,40}",$alumno_primernombre)){
			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"El campo nombre no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

			$alumno_datos_reg=[
				[
					"campo_nombre"=>"alumno_sedeid",
					"campo_marcador"=>":Sedeid",
					"campo_valor"=>$alumno_sedeid
				],
				[
					"campo_nombre"=>"alumno_posicionid",
					"campo_marcador"=>":Posicionid",
					"campo_valor"=>$alumno_posicionid
				],
				[
					"campo_nombre"=>"alumno_nacionalidadid",
					"campo_marcador"=>":Nacionalidadid",
					"campo_valor"=>$alumno_nacionalidadid
				],
				[
					"campo_nombre"=>"alumno_tipoidentificacion",
					"campo_marcador"=>":Tipoidentificacion",
					"campo_valor"=>$alumno_tipoidentificacion
				],
				[
					"campo_nombre"=>"alumno_identificacion",
					"campo_marcador"=>":Identificacion",
					"campo_valor"=>$alumno_identificacion
				],
				[
					"campo_nombre"=>"alumno_primernombre",
					"campo_marcador"=>":Primernombre",
					"campo_valor"=>$alumno_primernombre
				],
				[
					"campo_nombre"=>"alumno_segundonombre",
					"campo_marcador"=>":Segundonombre",
					"campo_valor"=>$alumno_segundonombre
				],
				[
					"campo_nombre"=>"alumno_apellidopaterno",
					"campo_marcador"=>":Apellidopaterno",
					"campo_valor"=>$alumno_apellidopaterno
				],
				[
					"campo_nombre"=>"alumno_apellidomaterno",
					"campo_marcador"=>":Apellidomaterno",
					"campo_valor"=>$alumno_apellidomaterno
				],
				[
					"campo_nombre"=>"alumno_nombrecorto",
					"campo_marcador"=>":Nombrecorto",
					"campo_valor"=>$alumno_nombrecorto
				],
				[
					"campo_nombre"=>"alumno_direccion",
					"campo_marcador"=>":Direccion",
					"campo_valor"=>$alumno_direccion
				],
				[
					"campo_nombre"=>"alumno_fechanacimiento",
					"campo_marcador"=>":Fechanacimiento",
					"campo_valor"=>$alumno_fechanacimiento
				],
				[
					"campo_nombre"=>"alumno_fechaingreso",
					"campo_marcador"=>":Fechaingreso",
					"campo_valor"=>$alumno_fechaingreso
				],
				[
					"campo_nombre"=>"alumno_genero",
					"campo_marcador"=>":Genero",
					"campo_valor"=>$alumno_genero
				],
				[
					"campo_nombre"=>"alumno_hermanos",
					"campo_marcador"=>":Hermanos",
					"campo_valor"=>$alumno_hermanos
				],
				[
					"campo_nombre"=>"alumno_numcamiseta",
					"campo_marcador"=>":Camiseta",
					"campo_valor"=>$alumno_numcamiseta
				]
			];

			# Directorio de fotos #
			$codigorand=rand(0,100);
			$img_dir="../views/imagenes/fotos/alumno/";

			# Directorio de imagenes cedula#
			$dir_cedula="../views/imagenes/cedulas/";

		# Comprobar si se selecciono una imagen #
		if($_FILES['alumno_foto']['name']!="" && $_FILES['alumno_foto']['size']>0){

				# Creando directorio #
				if(!file_exists($img_dir)){
					if(!mkdir($img_dir,0777)){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Error",
							"texto"=>"No se creÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ el directorio",
							"icono"=>"error"
						];
						return json_encode($alerta);
						//exit();
					}
				}

				# Verificando formato de imagenes #
				if(mime_content_type($_FILES['alumno_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['alumno_foto']['tmp_name'])!="image/png"){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Error",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido ",
						"icono"=>"error"
					];
					return json_encode($alerta);
					//exit();
				}

				# Verificando peso de imagen #
				if(($_FILES['alumno_foto']['size']/1024)>4000){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Error",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido 4MB",
						"icono"=>"error"
					];
					return json_encode($alerta);
					//exit();
				}

				#nombre de la foto
				$foto=str_ireplace(" ","_",$alumno_identificacion);
				$foto=$foto."_".$codigorand;


				# Extension de la imagen #
				switch(mime_content_type($_FILES['alumno_foto']['tmp_name'])){
					case 'image/jpeg':
						$foto=$foto.".jpg";
					break;
					case 'image/png':
						$foto=$foto.".png";
					break;
				}
				$maxWidth = 800;
			$maxHeight = 600;

				chmod($img_dir,0777);
				$inputFile = ($_FILES['alumno_foto']['tmp_name']);
			$outputFile = $img_dir.$foto;

				# Moviendo imagen al directorio #
				//if(!move_uploaded_file($_FILES['alumno_foto']['tmp_name'],$img_dir.$foto)){
				if ($this->resizeImageGD($inputFile, $maxWidth, $maxHeight, $outputFile)) {

				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Error",
						"texto"=>"No es posible subir la imagen al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Eliminando imagen anterior #
				if(is_file($img_dir.$datos['alumno_imagen']) && $datos['alumno_imagen']!=$foto){
					chmod($img_dir.$datos['alumno_imagen'], 0777);
					unlink($img_dir.$datos['alumno_imagen']);
				}

				$alumno_datos_reg[] = [
					"campo_nombre" => "alumno_imagen",
					"campo_marcador" => ":Foto",
					"campo_valor" => $foto
				];
			}

			if($_FILES['alumno_cedulaA']['name']!="" && $_FILES['alumno_cedulaA']['size']>0){

				# Creando directorio #
				if(!file_exists($dir_cedula)){
					if(!mkdir($dir_cedula,0777)){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Error",
							"texto"=>"No se creÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ el directorio",
							"icono"=>"error"
						];
						return json_encode($alerta);
					}
				}

				# Verificando formato de imagenes #
				if(mime_content_type($_FILES['alumno_cedulaA']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['alumno_cedulaA']['tmp_name'])!="image/png"){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Error",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido ",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Verificando peso de imagen #
				if(($_FILES['alumno_cedulaA']['size']/1024)>4000){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Error",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido 4MB",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				#nombre de la imagen cedula
				$CedulaA=str_ireplace(" ","_",$alumno_identificacion);
				$CedulaA=$CedulaA."_A".$codigorand=rand(0,100);

				# Extension de la imagen #
				switch(mime_content_type($_FILES['alumno_cedulaA']['tmp_name'])){
					case 'image/jpeg':
						$CedulaA=$CedulaA.".jpg";
					break;
					case 'image/png':
						$CedulaA=$CedulaA.".png";
					break;
				}
				$maxWidth = 800;
			$maxHeight = 600;

				chmod($img_dir,0777);
				$inputFile = ($_FILES['alumno_cedulaA']['tmp_name']);
			$outputFile = $dir_cedula.$CedulaA;

				# Moviendo imagen al directorio #
				//if(!move_uploaded_file($_FILES['alumno_foto']['tmp_name'],$img_dir.$foto)){
				if ($this->resizeImageGD($inputFile, $maxWidth, $maxHeight, $outputFile)) {

				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Error",
						"texto"=>"No es posible subir la imagen de la cedula al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Eliminando imagen anterior #
				if(is_file($dir_cedula.$datos['alumno_cedulaA']) && $datos['alumno_cedulaA']!=$CedulaA){
					chmod($dir_cedula.$datos['alumno_cedulaA'], 0777);
					unlink($dir_cedula.$datos['alumno_cedulaA']);
				}

				$alumno_datos_reg[] = [
					"campo_nombre" => "alumno_cedulaA",
					"campo_marcador" => ":CedulaA",
					"campo_valor" => $CedulaA
				];
			}

			if($_FILES['alumno_cedulaR']['name']!="" && $_FILES['alumno_cedulaR']['size']>0){

				# Creando directorio #
				if(!file_exists($dir_cedula)){
					if(!mkdir($dir_cedula,0777)){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Error",
							"texto"=>"No se creÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ el directorio",
							"icono"=>"error"
						];
						return json_encode($alerta);
					}
				}

				# Verificando formato de imagenes #
				if(mime_content_type($_FILES['alumno_cedulaR']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['alumno_cedulaR']['tmp_name'])!="image/png"){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Error",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido ",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Verificando peso de imagen #
				if(($_FILES['alumno_cedulaR']['size']/1024)>4000){
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Error",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido 4MB",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				#nombre imagen cedula reverso
				$CedulaR=str_ireplace(" ","_",$alumno_identificacion);
				$CedulaR=$CedulaR."_R".$codigorand;

				# Extension de la imagen #
				switch(mime_content_type($_FILES['alumno_cedulaR']['tmp_name'])){
					case 'image/jpeg':
						$CedulaR=$CedulaR.".jpg";
					break;
					case 'image/png':
						$CedulaR=$CedulaR.".png";
					break;
				}
				$maxWidth = 800;
			$maxHeight = 600;

				chmod($img_dir,0777);
				$inputFile = ($_FILES['alumno_cedulaR']['tmp_name']);
			$outputFile = $dir_cedula.$CedulaR;

				# Moviendo imagen al directorio #
				//if(!move_uploaded_file($_FILES['alumno_foto']['tmp_name'],$img_dir.$foto)){
				if ($this->resizeImageGD($inputFile, $maxWidth, $maxHeight, $outputFile)) {

				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Error",
						"texto"=>"No es posible subir la imagen de la cedula al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}

				# Eliminando imagen anterior #
				if(is_file($dir_cedula.$datos['alumno_cedulaR']) && $datos['alumno_cedulaR']!=$CedulaR){
					chmod($dir_cedula.$datos['alumno_cedulaR'], 0777);
					unlink($dir_cedula.$datos['alumno_cedulaR']);
				}

				$alumno_datos_reg[] = [
					"campo_nombre" => "alumno_cedulaR",
					"campo_marcador" => ":CedulaR",
					"campo_valor" => $CedulaR
				];
			}

			$condicion=[
				"condicion_campo"=>"alumno_id",
				"condicion_marcador"=>":Alumnoid",
				"condicion_valor"=>$alumnoid
			];

			if($this->actualizarDatos("sujeto_alumno",$alumno_datos_reg,$condicion)){

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Alumno actualizado",
					"texto"=>"El alumno ".$alumno_identificacion." | ".$alumno_primernombre." ".$alumno_apellidopaterno." se actualizÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ correctamente",
					"icono"=>"success"
				];

				/*---------------Inicio de registro de InformaciÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³n de los tabs*/
				$infomedic_tiposangre 	= $this->limpiarCadena($_POST['infomedic_tiposangre']);
				$infomedic_peso		  	= $this->limpiarCadena($_POST['infomedic_peso']);
				$infomedic_talla 	  	= $this->limpiarCadena($_POST['infomedic_talla']);
				$infomedic_enfermedad 	= $this->limpiarCadena($_POST['infomedic_enfermedad']);
				$infomedic_medicamentos = $this->limpiarCadena($_POST['infomedic_medicamentos']);
				$infomedic_alergia1 	= $this->limpiarCadena($_POST['infomedic_alergia1']);
				$infomedic_alergia2 	= $this->limpiarCadena($_POST['infomedic_alergia2']);
				$infomedic_cirugias 	= $this->limpiarCadena($_POST['infomedic_cirugias']);
				$infomedic_observacion	= $this->limpiarCadena($_POST['infomedic_observacion']);

				if(isset($_POST['infomedic_covid'])){ $infomedic_covid  = $_POST['infomedic_covid']; }else {$infomedic_covid="";}
				if(isset($_POST['infomedic_vacunas'])){ $infomedic_vacunas  = $_POST['infomedic_vacunas']; }else {$infomedic_vacunas="";}


	if ($infomedic_peso ==""){$infomedic_peso = 0;}
				if ($infomedic_talla ==""){$infomedic_talla = 0;}

				$infomedic=$this->ejecutarConsulta("SELECT * FROM alumno_infomedic WHERE infomedic_alumnoid='$alumnoid'");
				if($infomedic->rowCount()>0){


					$infomedic_reg=[
						[
							"campo_nombre"=>"infomedic_alumnoid",
							"campo_marcador"=>":Alumnoid",
							"campo_valor"=>$alumnoid
						],
						[
							"campo_nombre"=>"infomedic_fecha",
							"campo_marcador"=>":Fechacreacion",
							"campo_valor"=>date("Y-m-d H:i:s")
						],
						[
							"campo_nombre"=>"infomedic_tiposangre",
							"campo_marcador"=>":Tiposangre",
							"campo_valor"=>$infomedic_tiposangre
						],
						[
							"campo_nombre"=>"infomedic_peso",
							"campo_marcador"=>":Peso",
							"campo_valor"=>$infomedic_peso
						],
						[
							"campo_nombre"=>"infomedic_talla",
							"campo_marcador"=>":Talla",
							"campo_valor"=>$infomedic_talla
						],
						[
							"campo_nombre"=>"infomedic_enfermedad",
							"campo_marcador"=>":Enfermedad",
							"campo_valor"=>$infomedic_enfermedad
						],
						[
							"campo_nombre"=>"infomedic_medicamentos",
							"campo_marcador"=>":Medicamentos",
							"campo_valor"=>$infomedic_medicamentos
						],
						[
							"campo_nombre"=>"infomedic_alergia1",
							"campo_marcador"=>":AlergiaMedicamentos",
							"campo_valor"=>$infomedic_alergia1
						],
						[
							"campo_nombre"=>"infomedic_alergia2",
							"campo_marcador"=>":AlergiaObjetos",
							"campo_valor"=>$infomedic_alergia2
						],
						[
							"campo_nombre"=>"infomedic_cirugias",
							"campo_marcador"=>":Cirugias",
							"campo_valor"=>$infomedic_cirugias
						],
						[
							"campo_nombre"=>"infomedic_observacion",
							"campo_marcador"=>":Observacion",
							"campo_valor"=>$infomedic_observacion
						],
						[
							"campo_nombre"=>"infomedic_covid",
							"campo_marcador"=>":VacunasCovid",
							"campo_valor"=>$infomedic_covid
						],
						[
							"campo_nombre"=>"infomedic_vacunas",
							"campo_marcador"=>":Vacunas",
							"campo_valor"=>$infomedic_vacunas
						]
					];

					$condicion=[
						"condicion_campo"=>"infomedic_alumnoid",
						"condicion_marcador"=>":Alumnoid",
						"condicion_valor"=>$alumnoid
					];

					$this->actualizarDatos("alumno_infomedic",$infomedic_reg,$condicion);

				}else{
					if($infomedic_tiposangre!="" || $infomedic_peso>0 || $infomedic_talla>0 || $infomedic_enfermedad!=""||
					$infomedic_medicamentos!="" || $infomedic_alergia1!="" || $infomedic_alergia2!="" || $infomedic_cirugias!="" ||
					$infomedic_observacion!=""){
						//if (!is_int($infomedic_peso) && !is_float($infomedic_peso)){$infomedic_peso = 0;}
						//if (!is_int($infomedic_talla) && !is_float($infomedic_talla)){$infomedic_talla = 0;}

						$infomedic_reg=[
							[
								"campo_nombre"=>"infomedic_alumnoid",
								"campo_marcador"=>":Alumnoid",
								"campo_valor"=>$alumnoid
							],
							[
								"campo_nombre"=>"infomedic_fecha",
								"campo_marcador"=>":Fechacreacion",
								"campo_valor"=>date("Y-m-d H:i:s")
							],
							[
								"campo_nombre"=>"infomedic_tiposangre",
								"campo_marcador"=>":Tiposangre",
								"campo_valor"=>$infomedic_tiposangre
							],
							[
								"campo_nombre"=>"infomedic_peso",
								"campo_marcador"=>":Peso",
								"campo_valor"=>$infomedic_peso
							],
							[
								"campo_nombre"=>"infomedic_talla",
								"campo_marcador"=>":Talla",
								"campo_valor"=>$infomedic_talla
							],
							[
								"campo_nombre"=>"infomedic_enfermedad",
								"campo_marcador"=>":Enfermedad",
								"campo_valor"=>$infomedic_enfermedad
							],
							[
								"campo_nombre"=>"infomedic_medicamentos",
								"campo_marcador"=>":Medicamentos",
								"campo_valor"=>$infomedic_medicamentos
							],
							[
								"campo_nombre"=>"infomedic_alergia1",
								"campo_marcador"=>":AlergiaMedicamentos",
								"campo_valor"=>$infomedic_alergia1
							],
							[
								"campo_nombre"=>"infomedic_alergia2",
								"campo_marcador"=>":AlergiaObjetos",
								"campo_valor"=>$infomedic_alergia2
							],
							[
								"campo_nombre"=>"infomedic_cirugias",
								"campo_marcador"=>":Cirugias",
								"campo_valor"=>$infomedic_cirugias
							],
							[
								"campo_nombre"=>"infomedic_observacion",
								"campo_marcador"=>":Observacion",
								"campo_valor"=>$infomedic_observacion
							],
							[
								"campo_nombre"=>"infomedic_covid",
								"campo_marcador"=>":VacunasCovid",
								"campo_valor"=>$infomedic_covid
							],
							[
								"campo_nombre"=>"infomedic_vacunas",
								"campo_marcador"=>":Vacunas",
								"campo_valor"=>$infomedic_vacunas
							]
						];

						$this->guardarDatos("alumno_infomedic",$infomedic_reg);
					}

				}
				/*---------------Fin de registro del tab InformaciÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³n MÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dica del alumno*/


				/*---------------Registro del tab Contacto Emergencia del alumno------------*/
				$cemer_nombre 		= $this->limpiarCadena($_POST['cemer_nombre']);
				$cemer_celular 		= $this->limpiarCadena($_POST['cemer_celular']);
				$cemer_parentesco	= $this->limpiarCadena($_POST['cemer_parentesco']);

				$cmer=$this->ejecutarConsulta("SELECT * FROM alumno_cemergencia WHERE cemer_alumnoid='$alumnoid'");
				if($cmer->rowCount()>0){

					$cemergencia_reg=[
						[
							"campo_nombre"=>"cemer_alumnoid",
							"campo_marcador"=>":Alumnoid",
							"campo_valor"=>$alumnoid
						],
						[
							"campo_nombre"=>"cemer_nombre",
							"campo_marcador"=>":NombreContactoEmer",
							"campo_valor"=>$cemer_nombre
						],
						[
							"campo_nombre"=>"cemer_celular",
							"campo_marcador"=>":CelularContactoEmer",
							"campo_valor"=>$cemer_celular
						],
						[
							"campo_nombre"=>"cemer_parentesco",
							"campo_marcador"=>":ParentescoContactoEmer",
							"campo_valor"=>$cemer_parentesco
						]
					];

					$condicion=[
						"condicion_campo"=>"cemer_alumnoid",
						"condicion_marcador"=>":Alumnoid",
						"condicion_valor"=>$alumnoid
					];

					$this->actualizarDatos("alumno_cemergencia",$cemergencia_reg,$condicion);

				}else{
					if($cemer_nombre!="" || $cemer_celular!=""){

						$cemergencia_reg=[
							[
								"campo_nombre"=>"cemer_alumnoid",
								"campo_marcador"=>":Alumnoid",
								"campo_valor"=>$alumnoid
							],
							[
								"campo_nombre"=>"cemer_nombre",
								"campo_marcador"=>":NombreContactoEmer",
								"campo_valor"=>$cemer_nombre
							],
							[
								"campo_nombre"=>"cemer_celular",
								"campo_marcador"=>":CelularContactoEmer",
								"campo_valor"=>$cemer_celular
							],
							[
								"campo_nombre"=>"cemer_parentesco",
								"campo_marcador"=>":ParentescoContactoEmer",
								"campo_valor"=>$cemer_parentesco
							]
						];

						$condicion=[
							"condicion_campo"=>"cemer_alumnoid",
							"condicion_marcador"=>":Alumnoid",
							"condicion_valor"=>$alumnoid
						];
						$this->guardarDatos("alumno_cemergencia",$cemergencia_reg);
					}

				}
				/*---------------Fin de registro del tab Contacto Emergencia del alumno------*/

				/*---------------Actulizar horario de entrenamiento---------------------*/
				$horario_id = $this->limpiarCadena($_POST['horarioid']);

				$cmer=$this->ejecutarConsulta("SELECT * FROM asistencia_asignahorario WHERE asignahorario_alumnoid = '$alumnoid'");
				if($cmer->rowCount()>0){

					$asignacion_horario_reg = [
						[
							"campo_nombre" => "asignahorario_horarioid",
							"campo_marcador" => ":Horarioid",
							"campo_valor" => $horario_id
						],
						[
							"campo_nombre" => "asignahorario_alumnoid",
							"campo_marcador" => ":Alumnoid",
							"campo_valor" => $alumnoid
						]
					];

					$condicion=[
						"condicion_campo"=>"asignahorario_alumnoid",
						"condicion_marcador"=>":Alumnoid",
						"condicion_valor"=>$alumnoid
					];

					$this->actualizarDatos("asistencia_asignahorario",$asignacion_horario_reg,$condicion);

				}else{
					if($horario_id!="" || $alumnoid!=""){

						$asignacion_horario_reg = [
							[
								"campo_nombre" => "asignahorario_horarioid",
								"campo_marcador" => ":Horarioid",
								"campo_valor" => $horario_id
							],
							[
								"campo_nombre" => "asignahorario_alumnoid",
								"campo_marcador" => ":Alumnoid",
								"campo_valor" => $alumnoid
							]
						];

						$this->guardarDatos("asistencia_asignahorario",$asignacion_horario_reg);
					}

				}
				/*---------------Fin de actualizacion de horariop de entrenamiento------*/

			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Alumno no actualizado",
					"texto"=>"No fue posible actualizar los datos del alumno ".$alumno_identificacion." | ".$alumno_primernombre." ".$alumno_apellidopaterno.", por favor intente nuevamente",
					"icono"=>"success"
				];
			}
			return json_encode($alerta);
		}

		/*----------  Controlador eliminar foto alumno  ----------*/
		public function eliminarFotoAlumnoControlador(){

			$id=$this->limpiarCadena($_POST['usuario_id']);

			# Verificando usuario #
		    $datos=$this->ejecutarConsulta("SELECT * FROM usuario WHERE usuario_id='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"El usuario no se encuentra en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        //exit();
		    }else{
			$datos=$datos->fetch();
		    }

		    # Directorio de imagenes #
		$img_dir="../views/imagenes/fotos/";

		chmod($img_dir,0777);

		if(is_file($img_dir.$datos['usuario_foto'])){

		        chmod($img_dir.$datos['usuario_foto'],0777);

		        if(!unlink($img_dir.$datos['usuario_foto'])){
		            $alerta=[
						"tipo"=>"simple",
						"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
						"texto"=>"Error al intentar eliminar la foto del usuario, por favor intente nuevamente",
						"icono"=>"error"
					];
					return json_encode($alerta);
			//exit();
		        }
		    }else{
			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"No se encuentra la foto del usuario en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        //exit();
		    }

		    $usuario_datos_up=[
				[
					"campo_nombre"=>"usuario_foto",
					"campo_marcador"=>":Foto",
					"campo_valor"=>""
				],
				[
					"campo_nombre"=>"usuario_actualizado",
					"campo_marcador"=>":Actualizado",
					"campo_valor"=>date("Y-m-d H:i:s")
				]
			];

			$condicion=[
				"condicion_campo"=>"usuario_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("usuario",$usuario_datos_up,$condicion)){

				if($id==$_SESSION['id']){
					$_SESSION['foto']="";
				}

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto eliminada",
					"texto"=>"La foto del usuario ".$datos['usuario_nombre']." ".$datos['usuario_apellido']." se elimino correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto eliminada",
					"texto"=>"No fue posible actualizar algunos datos del usuario ".$datos['usuario_nombre']." ".$datos['usuario_apellido'].", sin embargo la foto ha sido eliminada correctamente",
					"icono"=>"warning"
				];
			}

			return json_encode($alerta);
		}

		/*----------  Controlador actualizar foto alumno  ----------*/
		public function actualizarFotoAlumnoControlador(){

			$id=$this->limpiarCadena($_POST['usuario_id']);

			# Verificando usuario #
		    $datos=$this->ejecutarConsulta("SELECT * FROM usuario WHERE usuario_id='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"No hemos encontrado el usuario en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        //exit();
		    }else{
			$datos=$datos->fetch();
		    }

		    # Directorio de imagenes #
		$img_dir="../views/imagenes/fotos/";

		# Comprobar si se selecciono una imagen #
		if($_FILES['usuario_foto']['name']=="" && $_FILES['usuario_foto']['size']<=0){
			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"No ha seleccionado una foto para el usuario",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        //exit();
		}

		# Creando directorio #
	        if(!file_exists($img_dir)){
	            if(!mkdir($img_dir,0777)){
	                $alerta=[
						"tipo"=>"simple",
						"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error inesperado",
						"texto"=>"No se creÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ el directorio",
						"icono"=>"error"
					];
					return json_encode($alerta);
	                //exit();
	            }
	        }

	        # Verificando formato de imagenes #
	        if(mime_content_type($_FILES['usuario_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['usuario_foto']['tmp_name'])!="image/png"){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
					"icono"=>"error"
				];
				return json_encode($alerta);
	            //exit();
	        }

	        # Verificando peso de imagen #
	        if(($_FILES['usuario_foto']['size']/1024)>250){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"La imagen que ha seleccionado supera el peso permitido",
					"icono"=>"error"
				];
				return json_encode($alerta);
	            //exit();
	        }

	        # Nombre de la foto #
	        if($datos['usuario_foto']!=""){
		        $foto=explode(".", $datos['usuario_foto']);
		        $foto=$foto[0];
	        }else{
		$foto=str_ireplace(" ","_",$datos['usuario_nombre']);
		$foto=$foto."_".rand(0,100);
	        }


	        # Extension de la imagen #
	        switch(mime_content_type($_FILES['usuario_foto']['tmp_name'])){
	            case 'image/jpeg':
	                $foto=$foto.".jpg";
	            break;
	            case 'image/png':
	                $foto=$foto.".png";
	            break;
	        }

	        chmod($img_dir,0777);

	        # Moviendo imagen al directorio #
	        if(!move_uploaded_file($_FILES['usuario_foto']['tmp_name'],$img_dir.$foto)){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"OcurriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ un error",
					"texto"=>"No podemos subir la imagen al sistema en este momento",
					"icono"=>"error"
				];
				return json_encode($alerta);
	            //exit();
	        }

	        # Eliminando imagen anterior #
	        if(is_file($img_dir.$datos['usuario_foto']) && $datos['usuario_foto']!=$foto){
		        chmod($img_dir.$datos['usuario_foto'], 0777);
		        unlink($img_dir.$datos['usuario_foto']);
		    }

		    $usuario_datos_up=[
				[
					"campo_nombre"=>"usuario_foto",
					"campo_marcador"=>":Foto",
					"campo_valor"=>$foto
				],
				[
					"campo_nombre"=>"usuario_actualizado",
					"campo_marcador"=>":Actualizado",
					"campo_valor"=>date("Y-m-d H:i:s")
				]
			];

			$condicion=[
				"condicion_campo"=>"usuario_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("usuario",$usuario_datos_up,$condicion)){

				if($id==$_SESSION['id']){
					$_SESSION['foto']=$foto;
				}

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto actualizada",
					"texto"=>"La foto del usuario ".$datos['usuario_nombre']." ".$datos['usuario_apellido']." se actualizo correctamente",
					"icono"=>"success"
				];
			}else{

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto actualizada",
					"texto"=>"No hemos podido actualizar algunos datos del usuario ".$datos['usuario_nombre']." ".$datos['usuario_apellido']." , sin embargo la foto ha sido actualizada",
					"icono"=>"warning"
				];
			}

			return json_encode($alerta);
		}


		# Consultar datos del representante para la vista vincular alumno
		public function datosRepresentante($alumnoid){
			$consulta_repre = "SELECT repre_identificacion IDENTIFICACION,
									concat(repre_primernombre, ' ', repre_segundonombre, ' ', repre_apellidopaterno, ' ', repre_apellidomaterno) AS REPRESENTANTE,
									catalogo_descripcion PARENTESCO, repre_direccion, repre_correo, repre_celular, repre_factura
									FROM sujeto_alumno, alumno_representante, general_tabla, general_tabla_catalogo
									WHERE alumno_repreid = repre_id
										and tabla_id = catalogo_tablaid
										and repre_parentesco = catalogo_valor
										and alumno_id =  ".$alumnoid;
			$datos = $this->ejecutarConsulta($consulta_repre);
			return $datos;
		}

		/* ==================================== Roles ==================================== */

		public function listarOptionSede($rolid = null, $usuario = null ){
			$option="";

			if($rolid != 1 && $rolid != 2){
				$consulta_datos="SELECT S.sede_id, S.sede_nombre
									FROM general_sede S
									INNER JOIN seguridad_usuario_sede US ON US.usuariosede_sedeid = S.sede_id
									INNER JOIN seguridad_usuario U ON U.usuario_id = US.usuariosede_usuarioid
									WHERE U.usuario_usuario  = '".$usuario."'";
			}else{
				$consulta_datos="SELECT sede_id, sede_nombre FROM general_sede";
			}

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$option.='<option value='.$rows['sede_id'].'>'.$rows['sede_nombre'].'</option>';
			}
			return $option;
		}



		public function listarCatalogoTipoDocumento(){
			$option="";

			$consulta_datos="SELECT C.catalogo_valor, C.catalogo_descripcion
								FROM general_tabla_catalogo C
								INNER JOIN general_tabla T on T.tabla_id = C.catalogo_tablaid
								WHERE T.tabla_nombre = 'tipo_documento'";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$option.='<option value='.$rows['catalogo_valor'].'>'.$rows['catalogo_descripcion'].'</option>';
			}
			return $option;
		}

		public function listarCatalogoNacionalidad(){
			$option="";

			$consulta_datos="SELECT C.catalogo_valor, C.catalogo_descripcion
								FROM general_tabla_catalogo C
								INNER JOIN general_tabla T on T.tabla_id = C.catalogo_tablaid
								WHERE T.tabla_nombre = 'nacionalidad'";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$option.='<option value='.$rows['catalogo_valor'].'>'.$rows['catalogo_descripcion'].'</option>';
			}
			return $option;
		}

		public function listarCatalogoParentesco(){
			$option="";

			$consulta_datos="SELECT C.catalogo_valor, C.catalogo_descripcion
								FROM general_tabla_catalogo C
								INNER JOIN general_tabla T on T.tabla_id = C.catalogo_tablaid
								WHERE T.tabla_nombre = 'parentesco'";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$option.='<option value='.$rows['catalogo_valor'].'>'.$rows['catalogo_descripcion'].'</option>';
			}
			return $option;
		}

		public function informacionSede($sedeid){
			$consulta_datos="SELECT * FROM general_sede WHERE sede_id  = $sedeid";
			$datos = $this->ejecutarConsulta($consulta_datos);
			return $datos;
		}

		//horarios

		public function generarHorarioProfile($horario_id = null){
			$tabla="";
			$consulta_datos = "SELECT
								'Horario' AS Categoria,
								MAX(CASE WHEN detalle_dia = 1 THEN CONCAT(hora_inicio, ' - ', hora_fin) END) AS Lunes,
								MAX(CASE WHEN detalle_dia = 2 THEN CONCAT(hora_inicio, ' - ', hora_fin) END) AS Martes,
								MAX(CASE WHEN detalle_dia = 3 THEN CONCAT(hora_inicio, ' - ', hora_fin) END) AS Miercoles,
								MAX(CASE WHEN detalle_dia = 4 THEN CONCAT(hora_inicio, ' - ', hora_fin) END) AS Jueves,
								MAX(CASE WHEN detalle_dia = 5 THEN CONCAT(hora_inicio, ' - ', hora_fin) END) AS Viernes
							FROM asistencia_horario
							INNER JOIN asistencia_horario_detalle ON detalle_horarioid = horario_id
							LEFT JOIN asistencia_hora ON hora_id = detalle_horaid
							WHERE horario_id = ".$horario_id."
							GROUP BY Categoria

							UNION ALL

							SELECT
								'Cancha' AS Categoria,
								MAX(CASE WHEN detalle_dia = 1 THEN lugar_nombre END) AS Lunes,
								MAX(CASE WHEN detalle_dia = 2 THEN lugar_nombre END) AS Martes,
								MAX(CASE WHEN detalle_dia = 3 THEN lugar_nombre END) AS Miercoles,
								MAX(CASE WHEN detalle_dia = 4 THEN lugar_nombre END) AS Jueves,
								MAX(CASE WHEN detalle_dia = 5 THEN lugar_nombre END) AS Viernes
							FROM asistencia_horario
							INNER JOIN asistencia_horario_detalle ON detalle_horarioid = horario_id
							LEFT JOIN asistencia_lugar ON lugar_id = detalle_lugarid
							WHERE horario_id = ".$horario_id."
							GROUP BY Categoria

							UNION ALL

							SELECT
								'Profesor' AS Categoria,
								MAX(CASE WHEN detalle_dia = 1 THEN empleado_nombre END) AS Lunes,
								MAX(CASE WHEN detalle_dia = 2 THEN empleado_nombre END) AS Martes,
								MAX(CASE WHEN detalle_dia = 3 THEN empleado_nombre END) AS Miercoles,
								MAX(CASE WHEN detalle_dia = 4 THEN empleado_nombre END) AS Jueves,
								MAX(CASE WHEN detalle_dia = 5 THEN empleado_nombre END) AS Viernes
							FROM asistencia_horario
							INNER JOIN asistencia_horario_detalle ON detalle_horarioid = horario_id
							LEFT JOIN sujeto_empleado ON empleado_id = detalle_profesorid
							WHERE horario_id = ".$horario_id."
							GROUP BY Categoria";


			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$tabla.="	<tr style='font-size: 14px'>
								<th>".$rows['Categoria']."</th>
								<td>".$rows['Lunes']."</td>
								<td>".$rows['Martes']."</td>
								<td>".$rows['Miercoles']."</td>
								<td>".$rows['Jueves']."</td>
								<td>".$rows['Viernes']."</td>
							</tr>";
			}
			return $tabla;
		}

		public function HorarioID($alumnoid){
			$consulta_datos="SELECT asignahorario_horarioid FROM asistencia_asignahorario WHERE asignahorario_alumnoid = $alumnoid";
			$datos = $this->ejecutarConsulta($consulta_datos);
			return $datos;
		}

		public function listarhorariosProfile($horarioid = null, $sedeid = null){
			$option="";

			$consulta_datos="SELECT AH.horario_id, CONCAT(AH.horario_detalle, ' | ',HORA.hora_inicio, ' - ', HORA.hora_fin ) AS HORARIO
								FROM asistencia_horario AH
									INNER JOIN(
										SELECT detalle_horarioid, detalle_horaid, H.hora_inicio, H.hora_fin
										FROM asistencia_horario_detalle D
										INNER JOIN asistencia_hora H on H.hora_id = D.detalle_horaid
										GROUP BY detalle_horarioid, detalle_horaid, H.hora_inicio, H.hora_fin
									)HORA ON HORA.detalle_horarioid = AH.horario_id

								WHERE AH.horario_estado = 'A' AND AH.horario_sedeid = $sedeid";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				if($horarioid == $rows['horario_id']){
					$option.='<option value='.$rows['horario_id'].' selected="selected">'.$rows['HORARIO'].'</option>';
				}else{
					$option.='<option value='.$rows['horario_id'].'>'.$rows['HORARIO'].'</option>';
				}
			}
			return $option;
		}

		public function listarhorarios(){
			$option="";

			$consulta_datos="SELECT AH.horario_id, CONCAT(AH.horario_detalle, ' | ',HORA.hora_inicio, ' - ', HORA.hora_fin ) AS HORARIO
								FROM asistencia_horario AH
									INNER JOIN(
										SELECT detalle_horarioid, detalle_horaid, H.hora_inicio, H.hora_fin
										FROM asistencia_horario_detalle D
										INNER JOIN asistencia_hora H on H.hora_id = D.detalle_horaid
										GROUP BY detalle_horarioid, detalle_horaid, H.hora_inicio, H.hora_fin
									)HORA ON HORA.detalle_horarioid = AH.horario_id

								WHERE AH.horario_estado = 'A'";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$detallePago = htmlspecialchars($this->limpiarTextoFactura($rows['detalle'] ?? ''), ENT_QUOTES, 'UTF-8');
				$alumnoPago = htmlspecialchars($this->limpiarTextoFactura($rows['alumno'] ?? ''), ENT_QUOTES, 'UTF-8');
				$codigoPago = htmlspecialchars((string)($rows['codigo'] ?? ''), ENT_QUOTES, 'UTF-8');
				$fechaPago = htmlspecialchars((string)($rows['pago_fecharegistro'] ?? ''), ENT_QUOTES, 'UTF-8');
				$valorPago = number_format((float)($rows['pago_valor'] ?? 0), 2, '.', '');
				$option.='<option value='.$rows['horario_id'].'>'.$rows['HORARIO'].'</option>';
			}
			return $option;
		}
	}
