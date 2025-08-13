<?php

	namespace app\controllers;
	use app\models\mainModel;
	use DateTime;

	class reporteController extends mainModel{	
		public function listarPagos($fecha_inicio, $fecha_fin, $sede_id){
			$tabla="";
			$VALOR_PAGADO = 0;
			$VALOR_PENDIENTE = 0;
			$consulta_datos="SELECT sede_nombre SEDE, A.alumno_identificacion IDENTIFICACION,
								concat(A.alumno_primernombre, ' ', A.alumno_segundonombre, ' ', A.alumno_apellidopaterno, ' ', A.alumno_apellidomaterno) ALUMNO,
								pago_fecha FECHA_PAGO, 
								pago_fecharegistro FECHA_REG_SISTEMA, 
								pago_periodo PERIODO,  
								R.catalogo_descripcion RUBRO,  
								F.catalogo_descripcion FORMA_PAGO, 
								((P.pago_saldo + P.pago_valor) - (IFNULL(PT.transaccion_valorcalculado, P.pago_saldo)))VALOR_PAGADO, 
								IFNULL(PT.transaccion_valorcalculado, P.pago_saldo) VALOR_PENDIENTE,
								case IFNULL(PT.transaccion_valorcalculado, P.pago_saldo) when 0 then 'Cancelado' else 'Pendiente' end ESTADO_PAGO
							from alumno_pago P
								inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid 
								inner join general_tabla_catalogo F ON F.catalogo_valor = P.pago_formapagoid
								inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid 
								inner join general_sede S on S.sede_id = alumno_sedeid
								LEFT JOIN(SELECT COUNT(1) total, PT.transaccion_pagoid, MIN(PT.transaccion_id) IDT
								FROM alumno_pago_transaccion PT
								WHERE PT.transaccion_estado = 'C'
								GROUP BY PT.transaccion_pagoid)T ON T.transaccion_pagoid = P.pago_id
								LEFT JOIN alumno_pago_transaccion PT ON PT.transaccion_id  = T.IDT
							where pago_estado <> 'E'
								and alumno_sedeid = ".$sede_id."
								and pago_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
							
							union all 
														
							SELECT sede_nombre SEDE, A.alumno_identificacion IDENTIFICACION,
								concat(A.alumno_primernombre, ' ', A.alumno_segundonombre, ' ', A.alumno_apellidopaterno, ' ', A.alumno_apellidomaterno) ALUMNO,
								transaccion_fecha FECHA_PAGO, 
								transaccion_fecharegistro FECHA_REG_SISTEMA, 
								transaccion_periodo PERIODO,  
								R.catalogo_descripcion RUBRO,  
								F.catalogo_descripcion FORMA_PAGO, 								
								transaccion_valor VALOR_PAGADO, 
								transaccion_valorcalculado - transaccion_valor VALOR_PENDIENTE,
								case (transaccion_valorcalculado - transaccion_valor) when 0 then 'Cancelado' else 'Pendiente' end ESTADO_PAGO
							from alumno_pago P
								inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid
								inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid 
								inner join alumno_pago_transaccion T on T.transaccion_pagoid = P.pago_id 
								inner join general_tabla_catalogo F ON F.catalogo_valor = T.transaccion_formapagoid 								
								inner join general_sede S on S.sede_id = alumno_sedeid
							where transaccion_estado <> 'E'
								and alumno_sedeid = ".$sede_id."
								and transaccion_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
								ORDER BY FECHA_PAGO DESC";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$VALOR_PAGADO += $rows['VALOR_PAGADO'];
				$VALOR_PENDIENTE += $rows['VALOR_PENDIENTE'];
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['IDENTIFICACION'].'</td>
						<td>'.$rows['ALUMNO'].'</td>
						<td>'.$rows['FECHA_PAGO'].'</td>
						<td>'.$rows['FECHA_REG_SISTEMA'].'</td>
						<td>'.$rows['PERIODO'].'</td>
						<td>'.$rows['RUBRO'].'</td>
						<td>'.$rows['FORMA_PAGO'].'</td>
						<td style="text-align: right">'.$rows['VALOR_PAGADO'].'</td>
						<td style="text-align: right">'.$rows['VALOR_PENDIENTE'].'</td>
						<td>'.$rows['ESTADO_PAGO'].'</td>
					</tr>';	
			}
			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td ></td>
					<td ></td>
					<td ></td>
					<td ></td>
					<td ></td>
					<td ></td>
					<td ></td>			
				</tr>';	

			return $tabla;			
		}

		public function listarPagosConsolidado($fecha_inicio, $fecha_fin){
			$tabla="";
			$VALOR_PAGADO = 0;
			$VALOR_PENDIENTE = 0;
			$consulta_datos="SELECT sede_nombre SEDE, A.alumno_identificacion IDENTIFICACION,
								concat(A.alumno_primernombre, ' ', A.alumno_segundonombre, ' ', A.alumno_apellidopaterno, ' ', A.alumno_apellidomaterno) ALUMNO,
								pago_fecha FECHA_PAGO, 
								pago_fecharegistro FECHA_REG_SISTEMA, 
								pago_periodo PERIODO,  
								R.catalogo_descripcion RUBRO,  
								F.catalogo_descripcion FORMA_PAGO, 
								pago_concepto CONCEPTO,
								((P.pago_saldo + P.pago_valor) - (IFNULL(PT.transaccion_valorcalculado, P.pago_saldo)))VALOR_PAGADO, 
								IFNULL(PT.transaccion_valorcalculado, P.pago_saldo) VALOR_PENDIENTE,
								case IFNULL(PT.transaccion_valorcalculado, P.pago_saldo) when 0 then 'Cancelado' else 'Pendiente' end ESTADO_PAGO
							from alumno_pago P
								inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid 
								inner join general_tabla_catalogo F ON F.catalogo_valor = P.pago_formapagoid
								inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid
								inner join general_sede S on S.sede_id = alumno_sedeid 
								LEFT JOIN(SELECT COUNT(1) total, PT.transaccion_pagoid, MIN(PT.transaccion_id) IDT
								FROM alumno_pago_transaccion PT
								WHERE PT.transaccion_estado = 'C'
								GROUP BY PT.transaccion_pagoid)T ON T.transaccion_pagoid = P.pago_id
								LEFT JOIN alumno_pago_transaccion PT ON PT.transaccion_id  = T.IDT
							where pago_estado <> 'E'
								and pago_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
							
							union all 
														
							SELECT sede_nombre SEDE, A.alumno_identificacion IDENTIFICACION,
								concat(A.alumno_primernombre, ' ', A.alumno_segundonombre, ' ', A.alumno_apellidopaterno, ' ', A.alumno_apellidomaterno) ALUMNO,
								transaccion_fecha FECHA_PAGO, 
								transaccion_fecharegistro FECHA_REG_SISTEMA, 
								transaccion_periodo PERIODO,  
								R.catalogo_descripcion RUBRO,  
								F.catalogo_descripcion FORMA_PAGO, 
								transaccion_concepto CONCEPTO,
								transaccion_valor VALOR_PAGADO, 
								transaccion_valorcalculado - transaccion_valor VALOR_PENDIENTE,
								case (transaccion_valorcalculado - transaccion_valor) when 0 then 'Cancelado' else 'Pendiente' end ESTADO_PAGO
							from alumno_pago P
								inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid 							
								inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid 
								inner join alumno_pago_transaccion T on T.transaccion_pagoid = P.pago_id								
								inner join general_tabla_catalogo F ON F.catalogo_valor = T.transaccion_formapagoid 
								inner join general_sede S on S.sede_id = alumno_sedeid
							where transaccion_estado <> 'E'
								and transaccion_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
								ORDER BY FECHA_PAGO DESC";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$VALOR_PAGADO += $rows['VALOR_PAGADO'];
				$VALOR_PENDIENTE += $rows['VALOR_PENDIENTE'];
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['IDENTIFICACION'].'</td>
						<td>'.$rows['ALUMNO'].'</td>
						<td>'.$rows['FECHA_PAGO'].'</td>
						<td>'.$rows['FECHA_REG_SISTEMA'].'</td>
						<td>'.$rows['PERIODO'].'</td>
						<td>'.$rows['RUBRO'].'</td>
						<td>'.$rows['FORMA_PAGO'].'</td>
						<td style="text-align: right">'.$rows['VALOR_PAGADO'].'</td>
						<td style="text-align: right">'.$rows['VALOR_PENDIENTE'].'</td>
						<td>'.$rows['CONCEPTO'].'</td>
						<td>'.$rows['ESTADO_PAGO'].'</td>
					</tr>';	
			}
			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td ></td>
					<td ></td>
					<td ></td>
					<td ></td>
					<td ></td>
					<td ></td>
					<td ></td>
					<td >TOTAL</td>
					<td style="text-align: right">'.number_format($VALOR_PAGADO, 2, '.',',').'</td>
					<td style="text-align: right">'.number_format($VALOR_PENDIENTE, 2, '.',',').'</td>
					<td> </td>				
				</tr>';	
			return $tabla;			
		}

		public function fechaPagosReceptados($sede_id){		
			$consulta_fecham="SELECT max(pago_fecharegistro) AS FECHA_MAXIMA
								FROM alumno_pago, sujeto_alumno
								WHERE pago_alumnoid = alumno_id
									AND alumno_sedeid = ".$sede_id."
								ORDER BY pago_fecharegistro";
			$fecha_maxima = $this->ejecutarConsulta($consulta_fecham);		
			return $fecha_maxima;
		}

		public function valoresPendientes($sedeid){		
			$tabla="";
			$NUM_SALDO = 0;
			$SALDO = 0;
			$NUM_PENSION = 0;
			$PENSION = 0;
			$consulta_datos="SELECT 
									alumno_id, 
									alumno_identificacion, 
									alumno_fechaingreso,
									CONCAT_WS(' ', alumno_primernombre, alumno_segundonombre, alumno_apellidopaterno, alumno_apellidomaterno) AS NOMBRES,  
									IFNULL(P.TOTAL,0) AS NUM_SALDO, 
									IFNULL(P.SALDO,0) AS SALDO, 
									IFNULL(PEN.PENSIONES,0) AS NUM_PENSION, 
									IFNULL(PEN.TOTAL,0) AS PENSION, 
									PEN.FECHA
								FROM sujeto_alumno A
								LEFT JOIN (
									SELECT 
									pago_alumnoid, 
									COUNT(pago_saldo) AS TOTAL, 
									SUM(pago_saldo) AS SALDO
									FROM alumno_pago
										INNER JOIN sujeto_alumno ON alumno_id = pago_alumnoid
									WHERE pago_estado = 'P' AND pago_saldo > 0 AND alumno_sedeid = ".$sedeid." 
									GROUP BY pago_alumnoid
								) P ON P.pago_alumnoid = A.alumno_id
								LEFT JOIN (
									SELECT 
									BASE.FECHA,
									BASE.pago_alumnoid,
									CASE WHEN BASE.FECHA > CURDATE() THEN 0 ELSE
										GREATEST(0, TIMESTAMPDIFF(MONTH, BASE.FECHA, CURDATE()) + (DAY(CURDATE()) < DAY(BASE.FECHA))) END AS PENSIONES,
									CASE WHEN BASE.FECHA > CURDATE() THEN 0 ELSE
										GREATEST(0, TIMESTAMPDIFF(MONTH, BASE.FECHA, CURDATE()) + (DAY(CURDATE()) < DAY(BASE.FECHA))) * COALESCE(BASE.descuento_valor, BASE.sede_pension) END AS TOTAL
									FROM (
									SELECT 
										MAX(pago_fecha) AS FECHA, 
										pago_alumnoid, 
										MAX(descuento_valor) AS descuento_valor, 
										MAX(sede_pension) AS sede_pension  
									FROM 
										sujeto_alumno
										LEFT JOIN alumno_pago ON pago_alumnoid = alumno_id 
										LEFT JOIN alumno_pago_descuento ON descuento_alumnoid = alumno_id AND descuento_estado = 'S'
										LEFT JOIN general_sede ON sede_id = alumno_sedeid
									WHERE pago_rubroid = 'RPE' AND alumno_estado <> 'I' AND alumno_sedeid = ".$sedeid." 
									GROUP BY 
										pago_alumnoid
									) BASE
								) PEN ON PEN.pago_alumnoid = A.alumno_id
								WHERE A.alumno_estado <> 'E'
									AND PEN.TOTAL > 0 OR P.SALDO > 0
								ORDER BY PEN.PENSIONES DESC";
			
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){	
				$NUM_SALDO += $rows['NUM_SALDO'];
				$SALDO += $rows['SALDO'];
				$NUM_PENSION += $rows['NUM_PENSION'];
				$PENSION += $rows['PENSION'];

				$tabla.='
					<tr data-widget="expandable-table" aria-expanded="false">
						<td>'.$rows['alumno_identificacion'].'</td>
						<td>'.$rows['NOMBRES'].'</td>
						<td>'.$rows['alumno_fechaingreso'].'</td>
						<td>'.$rows['NUM_SALDO'].'</td>
						<td>'.$rows['SALDO'].'</td>
						<td>'.$rows['NUM_PENSION'].'</td>
						<td>'.$rows['PENSION'].'</td>						
					</tr>';							
			}			
			return $tabla;
		}		
		public function listarOptionRubro($rubro){
			$option ='<option value=0> Seleccione el rubro</option>';
			$consulta_datos="SELECT C.catalogo_valor, C.catalogo_descripcion 
								FROM general_tabla_catalogo C
								INNER JOIN general_tabla T on T.tabla_id = C.catalogo_tablaid
								WHERE T.tabla_nombre = 'rubros'
									AND T.tabla_estado = 'A'
									AND C.catalogo_estado = 'A'";	
					
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				if($rubro == $rows['catalogo_valor']){	
					$option.='<option value='.$rows['catalogo_valor'].' selected="selected">'.$rows['catalogo_descripcion'].'</option>';
				}else{		
				$option.='<option value='.$rows['catalogo_valor'].'>'.$rows['catalogo_descripcion'].'</option>';	
                }						
			}
			return $option;
		}

		public function listarAlumnosRubro($sede_id, $rubro){
			$tabla="";
			$consulta_datos="SELECT concat_ws(' ', alumno_primernombre, alumno_segundonombre, alumno_apellidopaterno, alumno_apellidomaterno) ALUMNO,
								pago_concepto KIT, 
								year(alumno_fechanacimiento) ANIO_NACIMIENTO, 
								alumno_numcamiseta NUMCAMISETA, 
								catalogo_descripcion TALLA,
								sede_nombre SEDE
								FROM sujeto_alumno 
								INNER JOIN alumno_pago ON alumno_id = pago_alumnoid
								LEFT JOIN general_tabla_catalogo on pago_talla = catalogo_valor
								INNER JOIN general_sede on alumno_sedeid = sede_id
								WHERE pago_estado <> 'E'
									AND alumno_sedeid = ".$sede_id."
									AND pago_rubroid = '".$rubro."'";								

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['ALUMNO'].'</td>
						<td>'.$rows['KIT'].'</td>
						<td>'.$rows['ANIO_NACIMIENTO'].'</td>
						<td>'.$rows['NUMCAMISETA'].'</td>
						<td>'.$rows['TALLA'].'</td>
					</tr>';	
			}
			return $tabla;			
		}

		public function listarAlumnosRubroConsolidado($rubro){
			$tabla="";
			$consulta_datos="SELECT concat_ws(' ', alumno_primernombre, alumno_segundonombre, alumno_apellidopaterno, alumno_apellidomaterno) ALUMNO,
								pago_concepto KIT, 
								year(alumno_fechanacimiento) ANIO_NACIMIENTO, 
								alumno_numcamiseta NUMCAMISETA, 
								catalogo_descripcion TALLA,
								sede_nombre SEDE
								FROM sujeto_alumno 
								INNER JOIN alumno_pago ON alumno_id = pago_alumnoid
								INNER JOIN general_sede on alumno_sedeid = sede_id
								LEFT JOIN general_tabla_catalogo on pago_talla = catalogo_valor
								WHERE pago_estado <> 'E'
									AND pago_rubroid = '".$rubro."'";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['ALUMNO'].'</td>
						<td>'.$rows['KIT'].'</td>
						<td>'.$rows['ANIO_NACIMIENTO'].'</td>
						<td>'.$rows['NUMCAMISETA'].'</td>
						<td>'.$rows['TALLA'].'</td>
					</tr>';	
			}
			return $tabla;			
		}
		public function listarSedebusqueda($sedeid){
			$option ="0";
			$consulta_datos="SELECT sede_id, sede_nombre FROM general_sede";	
					
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
		public function resumenPagos($fecha_inicio, $fecha_fin, $sede_id){
			$tabla="";
			$VALOR_PAGADO = 0;
			$PAGOS = 0;
			$consulta_datos="SELECT sede_nombre SEDE,
								pago_fecha FECHA_PAGO, 
								R.catalogo_descripcion RUBRO,  
								F.catalogo_descripcion FORMA_PAGO,
								count(*) PAGOS, 
								SUM(((P.pago_saldo + P.pago_valor) - (IFNULL(PT.transaccion_valorcalculado, P.pago_saldo))))VALOR_PAGADO
							FROM alumno_pago P
								inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid 
								inner join general_tabla_catalogo F ON F.catalogo_valor = P.pago_formapagoid
								inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid 
								inner join general_sede S on S.sede_id = alumno_sedeid
								LEFT JOIN(SELECT COUNT(1) total, PT.transaccion_pagoid, MIN(PT.transaccion_id) IDT
								FROM alumno_pago_transaccion PT
								WHERE PT.transaccion_estado = 'C'
								GROUP BY PT.transaccion_pagoid)T ON T.transaccion_pagoid = P.pago_id
								LEFT JOIN alumno_pago_transaccion PT ON PT.transaccion_id  = T.IDT
							WHERE pago_estado <> 'E'
								and alumno_sedeid = ".$sede_id."
								and pago_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
							GROUP BY SEDE, FECHA_PAGO, RUBRO, FORMA_PAGO							
							
							union all 
														
							SELECT sede_nombre SEDE,
								transaccion_fecha FECHA_PAGO,  
								CONCAT_WS(' ', R.catalogo_descripcion, ' - Abono') RUBRO,  
								F.catalogo_descripcion FORMA_PAGO, 
								count(*) PAGOS,
								SUM(transaccion_valor) VALOR_PAGADO
							FROM alumno_pago P
								inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid
								inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid 
								inner join alumno_pago_transaccion T on T.transaccion_pagoid = P.pago_id 
								inner join general_tabla_catalogo F ON F.catalogo_valor = T.transaccion_formapagoid 								
								inner join general_sede S on S.sede_id = alumno_sedeid
							WHERE transaccion_estado <> 'E'
								and alumno_sedeid = ".$sede_id."
								and transaccion_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
							GROUP BY SEDE, FECHA_PAGO, RUBRO, FORMA_PAGO
							ORDER BY SEDE, FECHA_PAGO, RUBRO";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$VALOR_PAGADO += $rows['VALOR_PAGADO'];
				$PAGOS += $rows['PAGOS'];
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['FECHA_PAGO'].'</td>
						<td>'.$rows['RUBRO'].'</td>
						<td>'.$rows['FORMA_PAGO'].'</td>
						<td style="text-align: right">'.$rows['PAGOS'].'</td>
						<td style="text-align: right">'.$rows['VALOR_PAGADO'].'</td>
					</tr>';	
			}
			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td ></td>
					<td ></td>
					<td ></td>
					<td >TOTAL</td>
					<td style="text-align: right">'.number_format($PAGOS, 0, '.',',').'</td>
					<td style="text-align: right">'.number_format($VALOR_PAGADO, 2, '.',',').'</td>				
				</tr>';
			return $tabla;			
		}
		public function resumenPagosConsolidado($fecha_inicio, $fecha_fin){
			$tabla="";
			$VALOR_PAGADO = 0;
			$consulta_datos="SELECT sede_nombre SEDE,
								pago_fecha FECHA_PAGO, 
								R.catalogo_descripcion RUBRO,   
								F.catalogo_descripcion FORMA_PAGO,
								count(*) PAGOS, 
								SUM(((P.pago_saldo + P.pago_valor) - (IFNULL(PT.transaccion_valorcalculado, P.pago_saldo))))VALOR_PAGADO
							from alumno_pago P
								inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid 
								inner join general_tabla_catalogo F ON F.catalogo_valor = P.pago_formapagoid
								inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid 
								inner join general_sede S on S.sede_id = alumno_sedeid
								LEFT JOIN(SELECT COUNT(1) total, PT.transaccion_pagoid, MIN(PT.transaccion_id) IDT
								FROM alumno_pago_transaccion PT
								WHERE PT.transaccion_estado = 'C'
								GROUP BY PT.transaccion_pagoid)T ON T.transaccion_pagoid = P.pago_id
								LEFT JOIN alumno_pago_transaccion PT ON PT.transaccion_id  = T.IDT
							where pago_estado <> 'E'
								and pago_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
							GROUP BY SEDE, FECHA_PAGO, RUBRO, FORMA_PAGO
							
							union all 
														
							SELECT sede_nombre SEDE,
								transaccion_fecha FECHA_PAGO,  
								CONCAT_WS(' ', R.catalogo_descripcion, ' - Abono') RUBRO, 
								F.catalogo_descripcion FORMA_PAGO, 
								count(*) PAGOS,
								SUM(transaccion_valor) VALOR_PAGADO
							from alumno_pago P
								inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid
								inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid 
								inner join alumno_pago_transaccion T on T.transaccion_pagoid = P.pago_id 
								inner join general_tabla_catalogo F ON F.catalogo_valor = T.transaccion_formapagoid 								
								inner join general_sede S on S.sede_id = alumno_sedeid
							where transaccion_estado <> 'E'
								and transaccion_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
							GROUP BY SEDE, FECHA_PAGO, RUBRO, FORMA_PAGO
							ORDER BY SEDE, RUBRO";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$VALOR_PAGADO += $rows['VALOR_PAGADO'];
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['FECHA_PAGO'].'</td>
						<td>'.$rows['RUBRO'].'</td>
						<td>'.$rows['FORMA_PAGO'].'</td>
						<td>'.$rows['PAGOS'].'</td>
						<td style="text-align: right">'.$rows['VALOR_PAGADO'].'</td>
					</tr>';	
			}
			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td colspan="5">TOTAL</td>
					<td style="text-align: right">'.number_format($VALOR_PAGADO, 2, '.',',').'</td>			
				</tr>';	

			return $tabla;			
		}
		public function fechaPagosResumen(){		
			$consulta_fecham="SELECT max(pago_fecharegistro) AS FECHA_MAXIMA
								FROM alumno_pago, sujeto_alumno
								WHERE pago_alumnoid = alumno_id
								ORDER BY pago_fecharegistro";
			$fecha_maxima = $this->ejecutarConsulta($consulta_fecham);		
			return $fecha_maxima;
		}
		public function fechaPagosCompletos(){		
			$consulta_fecham="SELECT max(pago_fecharegistro) AS FECHA_MAXIMA
								FROM alumno_pago, sujeto_alumno
								WHERE pago_alumnoid = alumno_id
									AND pago_estado = 'C'
								ORDER BY pago_fecharegistro";
			$fecha_maxima = $this->ejecutarConsulta($consulta_fecham);		
			return $fecha_maxima;
		}
		public function  pagosFacturacion($fecha_inicio, $fecha_fin){
			$tabla="";
			$consulta_datos="SELECT sede_nombre AS SEDE, repre_identificacion AS IDENTIFICACION, 
								concat_ws(' ', repre_primernombre, repre_segundonombre, repre_apellidopaterno, repre_apellidomaterno) as REPRESENTANTE,
								repre_direccion DIRECCION, repre_correo CORREO, repre_celular CELULAR,
								concat_ws(' ', alumno_primernombre, alumno_segundonombre, alumno_apellidopaterno, alumno_apellidomaterno) AS ALUMNO,
								catalogo_descripcion AS RUBRO, pago_valor AS VALOR, pago_fecharegistro AS FECHA_REGISTRO
							from alumno_representante, sujeto_alumno, alumno_pago, general_tabla_catalogo, general_sede
							where alumno_repreid = repre_id
								and alumno_id = pago_alumnoid 
								and pago_rubroid = catalogo_valor
								and alumno_sedeid = sede_id
								and repre_factura = 'S'
								and pago_estado = 'C'
								and pago_fecharegistro between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['IDENTIFICACION'].'</td>
						<td>'.$rows['REPRESENTANTE'].'</td>
						<td>'.$rows['DIRECCION'].'</td>
						<td>'.$rows['CORREO'].'</td>
						<td>'.$rows['CELULAR'].'</td>
						<td>'.$rows['ALUMNO'].'</td>
						<td>'.$rows['RUBRO'].'</td>
						<td>'.$rows['VALOR'].'</td>
						<td>'.$rows['FECHA_REGISTRO'].'</td>
					</tr>';	
			}
			return $tabla;
		}
		public function resumenPagosForma($fecha_inicio, $fecha_fin, $sede_id){
			$tabla="";
			$VALOR_PAGADO = 0;
			$PAGOS = 0;
			$consulta_datos="SELECT SEDE, FECHA_PAGO, FORMA_PAGO, SUM(PAGOS) PAGOS, SUM(VALOR_PAGADO) VALOR_PAGADO
								FROM(
									SELECT sede_nombre SEDE,
									pago_fecha FECHA_PAGO,
									F.catalogo_descripcion FORMA_PAGO,
									count(*) PAGOS, 
									SUM(((P.pago_saldo + P.pago_valor) - (IFNULL(PT.transaccion_valorcalculado, P.pago_saldo))))VALOR_PAGADO
								FROM alumno_pago P
									inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid 
									inner join general_tabla_catalogo F ON F.catalogo_valor = P.pago_formapagoid
									inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid 
									inner join general_sede S on S.sede_id = alumno_sedeid
									LEFT JOIN(SELECT COUNT(1) total, PT.transaccion_pagoid, MIN(PT.transaccion_id) IDT
									FROM alumno_pago_transaccion PT
									WHERE PT.transaccion_estado = 'C'
									GROUP BY PT.transaccion_pagoid)T ON T.transaccion_pagoid = P.pago_id
									LEFT JOIN alumno_pago_transaccion PT ON PT.transaccion_id  = T.IDT
								WHERE pago_estado <> 'E'
									and alumno_sedeid = ".$sede_id."
									and pago_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
								GROUP BY SEDE, FECHA_PAGO, FORMA_PAGO
								
								union all 
															
								SELECT sede_nombre SEDE,
									transaccion_fecha FECHA_PAGO,
									F.catalogo_descripcion FORMA_PAGO, 
									count(*) PAGOS,
									SUM(transaccion_valor) VALOR_PAGADO
								FROM alumno_pago P
									inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid
									inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid 
									inner join alumno_pago_transaccion T on T.transaccion_pagoid = P.pago_id 
									inner join general_tabla_catalogo F ON F.catalogo_valor = T.transaccion_formapagoid 								
									inner join general_sede S on S.sede_id = alumno_sedeid
								WHERE transaccion_estado <> 'E'
									and alumno_sedeid = ".$sede_id."
									and transaccion_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
								GROUP BY SEDE, FECHA_PAGO, FORMA_PAGO
								) FORMAPAGO
								group by SEDE, FECHA_PAGO, FORMA_PAGO
								order by SEDE, FECHA_PAGO";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$VALOR_PAGADO += $rows['VALOR_PAGADO'];
				$PAGOS += $rows['PAGOS'];
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['FECHA_PAGO'].'</td>
						<td>'.$rows['FORMA_PAGO'].'</td>
						<td style="text-align: right">'.$rows['PAGOS'].'</td>
						<td style="text-align: right">'.$rows['VALOR_PAGADO'].'</td>
					</tr>';	
			}
			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td ></td>
					<td ></td>
					<td >TOTAL</td>
					<td style="text-align: right">'.number_format($PAGOS, 0, '.',',').'</td>
					<td style="text-align: right">'.number_format($VALOR_PAGADO, 2, '.',',').'</td>				
				</tr>';
			return $tabla;			
		}
		public function resumenPagosFormaConsolidado($fecha_inicio, $fecha_fin){
			$tabla="";
			$VALOR_PAGADO = 0;
			$consulta_datos="SELECT sede_nombre SEDE,
								pago_fecha FECHA_PAGO, 
								R.catalogo_descripcion RUBRO,   
								F.catalogo_descripcion FORMA_PAGO,
								count(*) PAGOS, 
								SUM(((P.pago_saldo + P.pago_valor) - (IFNULL(PT.transaccion_valorcalculado, P.pago_saldo))))VALOR_PAGADO
							from alumno_pago P
								inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid 
								inner join general_tabla_catalogo F ON F.catalogo_valor = P.pago_formapagoid
								inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid 
								inner join general_sede S on S.sede_id = alumno_sedeid
								LEFT JOIN(SELECT COUNT(1) total, PT.transaccion_pagoid, MIN(PT.transaccion_id) IDT
								FROM alumno_pago_transaccion PT
								WHERE PT.transaccion_estado = 'C'
								GROUP BY PT.transaccion_pagoid)T ON T.transaccion_pagoid = P.pago_id
								LEFT JOIN alumno_pago_transaccion PT ON PT.transaccion_id  = T.IDT
							where pago_estado <> 'E'
								and pago_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
							GROUP BY SEDE, FECHA_PAGO, RUBRO, FORMA_PAGO
							
							union all 
														
							SELECT sede_nombre SEDE,
								transaccion_fecha FECHA_PAGO,  
								CONCAT_WS(' ', R.catalogo_descripcion, ' - Abono') RUBRO, 
								F.catalogo_descripcion FORMA_PAGO, 
								count(*) PAGOS,
								SUM(transaccion_valor) VALOR_PAGADO
							from alumno_pago P
								inner join general_tabla_catalogo R ON R.catalogo_valor = P.pago_rubroid
								inner join sujeto_alumno A on A.alumno_id = P.pago_alumnoid 
								inner join alumno_pago_transaccion T on T.transaccion_pagoid = P.pago_id 
								inner join general_tabla_catalogo F ON F.catalogo_valor = T.transaccion_formapagoid 								
								inner join general_sede S on S.sede_id = alumno_sedeid
							where transaccion_estado <> 'E'
								and transaccion_fecha between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
							GROUP BY SEDE, FECHA_PAGO, RUBRO, FORMA_PAGO
							ORDER BY SEDE, RUBRO";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$VALOR_PAGADO += $rows['VALOR_PAGADO'];
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['FECHA_PAGO'].'</td>
						<td>'.$rows['RUBRO'].'</td>
						<td>'.$rows['FORMA_PAGO'].'</td>
						<td>'.$rows['PAGOS'].'</td>
						<td style="text-align: right">'.$rows['VALOR_PAGADO'].'</td>
					</tr>';	
			}
			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td colspan="5">TOTAL</td>
					<td style="text-align: right">'.number_format($VALOR_PAGADO, 2, '.',',').'</td>			
				</tr>';	

			return $tabla;			
		}
		public function balanceResultado($fecha_inicio, $fecha_fin, $sede_id){
			$tabla="";
			$TOTAL_INGRESOS = 0;
			$MONTO_TOTAL_INGRESO = 0;
			$consulta_ingresos="SELECT sede_nombre SEDE, catalogo_descripcion RUBRO, count(*) TOTAL_INGRESOS, SUM(pago_valor) MONTO_TOTAL_INGRESO 
								FROM sujeto_alumno 
								INNER JOIN alumno_pago ON alumno_id = pago_alumnoid
								LEFT JOIN general_tabla_catalogo on pago_rubroid = catalogo_valor
								INNER JOIN general_sede on alumno_sedeid = sede_id
								WHERE pago_estado <> 'E'
									AND alumno_sedeid =".$sede_id."
									and pago_fecharegistro between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
								GROUP BY sede_nombre, catalogo_descripcion
							
							UNION ALL

							SELECT sede_nombre SEDE, concat_ws(' ', catalogo_descripcion, '-', 'ABONO') RUBRO, 
									count(*) TOTAL_INGRESOS, SUM(transaccion_valor) MONTO_TOTAL_INGRESO 
								from sujeto_alumno
								INNER JOIN alumno_pago ON alumno_id = pago_alumnoid
								INNER JOIN alumno_pago_transaccion ON pago_id = transaccion_pagoid
								LEFT JOIN general_tabla_catalogo on pago_rubroid = catalogo_valor
								INNER JOIN general_sede on alumno_sedeid = sede_id
								WHERE transaccion_estado <> 'E'
									AND alumno_sedeid =".$sede_id."
									and transaccion_fecharegistro between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
									AND alumno_sedeid =1
								GROUP BY sede_nombre, catalogo_descripcion
							
							UNION ALL
							
							SELECT sede_nombre SEDE, concat_ws(' ', catalogo_descripcion, '-', empleado_nombre) RUBRO, 
									count(*) TOTAL_INGRESOS, SUM(egreso_valor) MONTO_TOTAL_INGRESO 
								FROM empleado_egreso
								INNER JOIN sujeto_empleado on empleado_id = egreso_empleadoid 
								LEFT JOIN general_tabla_catalogo on egreso_tipoid = catalogo_valor
								INNER JOIN general_sede on empleado_sedeid = sede_id
								WHERE egreso_estado <> 'E'
									AND empleado_sedeid = ".$sede_id."
									AND egreso_fechaegreso between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
								GROUP BY sede_nombre, catalogo_descripcion, empleado_nombre
								
							UNION ALL
							
							SELECT sede_nombre SEDE, concat_ws(' ', catalogo_descripcion, '-', ingreso_empresa) RUBRO, 
									count(*) TOTAL_INGRESOS, SUM(ingreso_monto) MONTO_TOTAL_INGRESO 
								FROM balance_ingreso
								LEFT JOIN general_tabla_catalogo on ingreso_concepto = catalogo_valor
								INNER JOIN general_sede on ingreso_sedeid = sede_id
								WHERE ingreso_estado <> 'E'
									AND ingreso_sedeid = ".$sede_id."
									and ingreso_fecharecepcion between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
								GROUP BY sede_nombre, catalogo_descripcion, ingreso_empresa";

			$datos = $this->ejecutarConsulta($consulta_ingresos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$TOTAL_INGRESOS += $rows['TOTAL_INGRESOS'];
				$MONTO_TOTAL_INGRESO += $rows['MONTO_TOTAL_INGRESO'];
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['RUBRO'].'</td>
						<td style="text-align: right">'.number_format($rows['TOTAL_INGRESOS'], 0, '.',',').'</td>
						<td style="text-align: right">'.number_format($rows['MONTO_TOTAL_INGRESO'], 2, '.',',').'</td>
						<td></td>
					</tr>';	
			}
			
			$TOTAL_EGRESOS = 0;
			$MONTO_TOTAL_EGRESO = 0;
			$consulta_egresos="SELECT sede_nombre SEDE, catalogo_descripcion RUBRO, count(*) TOTAL_EGRESOS, SUM(ingreso_valor) MONTO_TOTAL_EGRESO 
									FROM empleado_ingreso
									INNER JOIN sujeto_empleado on empleado_id = ingreso_empleadoid 
									LEFT JOIN general_tabla_catalogo on ingreso_tipoingresoid = catalogo_valor
									INNER JOIN general_sede on empleado_sedeid = sede_id
									WHERE ingreso_estado <> 'E'
										AND empleado_sedeid = ".$sede_id."
										AND ingreso_fechapago BETWEEN ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
									GROUP BY sede_nombre, catalogo_descripcion

								UNION ALL

								SELECT sede_nombre SEDE, concat_ws(' ', catalogo_descripcion, '-', egreso_empresa) RUBRO, count(*) TOTAL_EGRESOS, SUM(egreso_monto) MONTO_TOTAL_EGRESO 
									FROM balance_egreso
									LEFT JOIN general_tabla_catalogo on egreso_concepto = catalogo_valor
									INNER JOIN general_sede on egreso_sedeid = sede_id
									WHERE egreso_estado <> 'E'
										AND egreso_sedeid = ".$sede_id."
										AND egreso_fechapago between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
									GROUP BY sede_nombre, catalogo_descripcion, egreso_empresa";

			$datos = $this->ejecutarConsulta($consulta_egresos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$TOTAL_EGRESOS += $rows['TOTAL_EGRESOS'];
				$MONTO_TOTAL_EGRESO += $rows['MONTO_TOTAL_EGRESO'];
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['RUBRO'].'</td>						
						<td style="text-align: right">'.number_format($rows['TOTAL_EGRESOS'], 0, '.',',').'</td>
						<td></td>
						<td style="text-align: right">'.number_format($rows['MONTO_TOTAL_EGRESO'], 2, '.',',').'</td>
					</tr>';	
			}
			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td >SUBTOTAL</td>
					<td ></td>
					<td ></td>
					<td style="text-align: right">'.number_format($MONTO_TOTAL_INGRESO, 2, '.',',').'</td>
					<td style="text-align: right">'.number_format($MONTO_TOTAL_EGRESO, 2, '.',',').'</td>
				</tr>';
			
			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="true">
					<td style="text-align: center; font-weight: bold;">TOTAL = Ingresos - Egresos</td>
					<td ></td>
					<td ></td>
					<td style="text-align: right; font-weight: bold;">'.number_format($MONTO_TOTAL_INGRESO - $MONTO_TOTAL_EGRESO, 2, '.',',').'</td>
					<td ></td>
				</tr>';
			return $tabla;			
		}
		public function balanceResultadosConsolidado($fecha_inicio, $fecha_fin){
			$tabla="";
			$TOTAL_INGRESOS = 0;
			$MONTO_TOTAL_INGRESO = 0;
			$consulta_ingresos="SELECT sede_nombre SEDE, catalogo_descripcion RUBRO, count(*) TOTAL_INGRESOS, SUM(pago_valor) MONTO_TOTAL_INGRESO 
								FROM sujeto_alumno 
								INNER JOIN alumno_pago ON alumno_id = pago_alumnoid
								LEFT JOIN general_tabla_catalogo on pago_rubroid = catalogo_valor
								INNER JOIN general_sede on alumno_sedeid = sede_id
								WHERE pago_estado <> 'E'
									and pago_fecharegistro between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
								GROUP BY sede_nombre, catalogo_descripcion
							
							UNION ALL

							SELECT sede_nombre SEDE, concat_ws(' ', catalogo_descripcion, '-', 'ABONO') RUBRO, 
									count(*) TOTAL_INGRESOS, SUM(transaccion_valor) MONTO_TOTAL_INGRESO 
								from sujeto_alumno
								INNER JOIN alumno_pago ON alumno_id = pago_alumnoid
								INNER JOIN alumno_pago_transaccion ON pago_id = transaccion_pagoid
								LEFT JOIN general_tabla_catalogo on pago_rubroid = catalogo_valor
								INNER JOIN general_sede on alumno_sedeid = sede_id
								WHERE transaccion_estado <> 'E'
									and transaccion_fecharegistro between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
									AND alumno_sedeid =1
								GROUP BY sede_nombre, catalogo_descripcion
							
							UNION ALL
							
							SELECT sede_nombre SEDE, concat_ws(' ', catalogo_descripcion, '-', empleado_nombre) RUBRO, 
									count(*) TOTAL_INGRESOS, SUM(egreso_valor) MONTO_TOTAL_INGRESO 
								FROM empleado_egreso
								INNER JOIN sujeto_empleado on empleado_id = egreso_empleadoid 
								LEFT JOIN general_tabla_catalogo on egreso_tipoid = catalogo_valor
								INNER JOIN general_sede on empleado_sedeid = sede_id
								WHERE egreso_estado <> 'E'
									AND egreso_fechaegreso between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
								GROUP BY sede_nombre, catalogo_descripcion, empleado_nombre
								
							UNION ALL
							
							SELECT sede_nombre SEDE, concat_ws(' ', catalogo_descripcion, '-', ingreso_empresa) RUBRO, 
									count(*) TOTAL_INGRESOS, SUM(ingreso_monto) MONTO_TOTAL_INGRESO 
								FROM balance_ingreso
								LEFT JOIN general_tabla_catalogo on ingreso_concepto = catalogo_valor
								INNER JOIN general_sede on ingreso_sedeid = sede_id
								WHERE ingreso_estado <> 'E'
									and ingreso_fecharecepcion between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
								GROUP BY sede_nombre, catalogo_descripcion, ingreso_empresa";

			$datos = $this->ejecutarConsulta($consulta_ingresos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$TOTAL_INGRESOS += $rows['TOTAL_INGRESOS'];
				$MONTO_TOTAL_INGRESO += $rows['MONTO_TOTAL_INGRESO'];
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['RUBRO'].'</td>
						<td style="text-align: right">'.number_format($rows['TOTAL_INGRESOS'], 0, '.',',').'</td>
						<td style="text-align: right">'.number_format($rows['MONTO_TOTAL_INGRESO'], 2, '.',',').'</td>
						<td></td>
					</tr>';	
			}
			
			$TOTAL_EGRESOS = 0;
			$MONTO_TOTAL_EGRESO = 0;
			$consulta_egresos="SELECT sede_nombre SEDE, catalogo_descripcion RUBRO, count(*) TOTAL_EGRESOS, SUM(ingreso_valor) MONTO_TOTAL_EGRESO 
									FROM empleado_ingreso
									INNER JOIN sujeto_empleado on empleado_id = ingreso_empleadoid 
									LEFT JOIN general_tabla_catalogo on ingreso_tipoingresoid = catalogo_valor
									INNER JOIN general_sede on empleado_sedeid = sede_id
									WHERE ingreso_estado <> 'E'
										AND ingreso_fechapago BETWEEN ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
									GROUP BY sede_nombre, catalogo_descripcion

								UNION ALL

								SELECT sede_nombre SEDE, concat_ws(' ', catalogo_descripcion, '-', egreso_empresa) RUBRO, count(*) TOTAL_EGRESOS, SUM(egreso_monto) MONTO_TOTAL_EGRESO 
									FROM balance_egreso
									LEFT JOIN general_tabla_catalogo on egreso_concepto = catalogo_valor
									INNER JOIN general_sede on egreso_sedeid = sede_id
									WHERE egreso_estado <> 'E'
										AND egreso_fechapago between ' ".$fecha_inicio." ' and ' ".$fecha_fin."'
									GROUP BY sede_nombre, catalogo_descripcion, egreso_empresa";

			$datos = $this->ejecutarConsulta($consulta_egresos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$TOTAL_EGRESOS += $rows['TOTAL_EGRESOS'];
				$MONTO_TOTAL_EGRESO += $rows['MONTO_TOTAL_EGRESO'];
				$tabla.='
					<tr>
						<td>'.$rows['SEDE'].'</td>
						<td>'.$rows['RUBRO'].'</td>						
						<td style="text-align: right">'.number_format($rows['TOTAL_EGRESOS'], 0, '.',',').'</td>
						<td></td>
						<td style="text-align: right">'.number_format($rows['MONTO_TOTAL_EGRESO'], 2, '.',',').'</td>
					</tr>';	
			}
			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="false">
					<td >SUBTOTAL</td>
					<td ></td>
					<td ></td>
					<td style="text-align: right">'.number_format($MONTO_TOTAL_INGRESO, 2, '.',',').'</td>
					<td style="text-align: right">'.number_format($MONTO_TOTAL_EGRESO, 2, '.',',').'</td>
				</tr>';
			
			$tabla.='
				<tr data-widget="expandable-table" aria-expanded="true">
					<td style="text-align: center; font-weight: bold;">TOTAL = Ingresos - Egresos</td>
					<td ></td>
					<td ></td>
					<td style="text-align: right; font-weight: bold;">'.number_format($MONTO_TOTAL_INGRESO - $MONTO_TOTAL_EGRESO, 2, '.',',').'</td>
					<td ></td>
					</tr>';
			return $tabla;		
		}

		/*----------  Matriz de alumnos con opciones Ver, Actualizar, Eliminar  ----------*/
		public function listarAlumnos($apellidopaterno, $primernombre, $anio, $sede){
			if($primernombre!=""){
				$primernombre .= '%';
			} 
			if($apellidopaterno!=""){
				$apellidopaterno .= '%';
			} 					

			$tabla="";
			$consulta_datos="SELECT distinct alumno_id, alumno_identificacion, alumno_primernombre, alumno_segundonombre, 
									alumno_apellidopaterno, alumno_apellidomaterno, alumno_fechanacimiento
								FROM sujeto_alumno
								INNER JOIN asistencia_asistencia ON asistencia_alumnoid = alumno_id
								WHERE (alumno_primernombre LIKE '".$primernombre."' 
										OR alumno_apellidopaterno LIKE '".$apellidopaterno."') ";			
			if($anio!=""){
				$consulta_datos .= " and YEAR(alumno_fechanacimiento) = '".$anio."'"; 
			}

			if($primernombre=="" && $apellidopaterno==""){
				$consulta_datos="SELECT distinct alumno_id, alumno_identificacion, alumno_primernombre, alumno_segundonombre, 
										alumno_apellidopaterno, alumno_apellidomaterno, alumno_fechanacimiento
									FROM sujeto_alumno
									INNER JOIN asistencia_asistencia ON asistencia_alumnoid = alumno_id
									WHERE YEAR(alumno_fechanacimiento) = '".$anio."'";
			}
			
			if($primernombre=="" && $apellidopaterno=="" && $anio == ""){
				$consulta_datos = "SELECT distinct alumno_id, alumno_identificacion, alumno_primernombre, alumno_segundonombre, 
											alumno_apellidopaterno, alumno_apellidomaterno, alumno_fechanacimiento
										FROM sujeto_alumno
										INNER JOIN asistencia_asistencia ON asistencia_alumnoid = alumno_id
										WHERE alumno_primernombre <> '' ";
			}

			if($sede!=""){
				if($sede == 0){
					$consulta_datos .= " and alumno_sedeid <> '".$sede."'"; 
				}else{
					$consulta_datos .= " and alumno_sedeid = '".$sede."'"; 
				}
			}else{
				$consulta_datos = "SELECT distinct alumno_id, alumno_identificacion, alumno_primernombre, alumno_segundonombre, 
											alumno_apellidopaterno, alumno_apellidomaterno, alumno_fechanacimiento
										FROM sujeto_alumno
										INNER JOIN asistencia_asistencia ON asistencia_alumnoid = alumno_id 
										WHERE alumno_primernombre = ''";
			}			

			$consulta_datos .= " AND alumno_estado <> 'E'"; 
			
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$tabla.='
					<tr>
						<td>'.$rows['alumno_identificacion'].'</td>
						<td>'.$rows['alumno_primernombre'].' '.$rows['alumno_segundonombre'].'</td>
						<td>'.$rows['alumno_apellidopaterno'].' '.$rows['alumno_apellidomaterno'].'</td>
						<td>'.$rows['alumno_fechanacimiento'].'</td>
						<td>
							<a href="'.APP_URL.'buscarAsistencia/'.$rows['alumno_id'].'/" target="_blank" class="btn float-left btn-ver btn-xs">Ver</a>
						</td>
					</tr>';	
			}
			return $tabla;			
		}
		public function listarEmpleados($empleado_nombre, $fecha_inicio, $fecha_fin){
			$tabla="";
			$consulta_datos="SELECT distinct empleado_id, empleado_identificacion, empleado_nombre
								FROM sujeto_empleado
								INNER JOIN empleado_asistencia ON asistencia_empleadoid = empleado_id
								WHERE (empleado_nombre LIKE '%".$empleado_nombre."%')
									OR asistencia_hora between '".$fecha_inicio."' and '".$fecha_fin."'
									AND empleado_estado <> 'E'";
			
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$tabla.='
					<tr>
						<td>'.$rows['empleado_identificacion'].'</td>
						<td>'.$rows['empleado_nombre'].'</td>
						<td>							
							<form action="'.APP_URL.'empleadoAsistenciasDetalle/" method="POST" autocomplete="off" target="_blank" >
								<input type="hidden" name="empleado_id" value="'.$rows['empleado_id'].'">
								<input type="hidden" name="fecha_inicio" value="'.$fecha_inicio.'">		
								<input type="hidden" name="fecha_fin" value="'.$fecha_fin.'">					
								<button type="submit" class="btn float-right btn-ver btn-xs" style="margin-right: 5px;" >Detalle ver</button>
							</form>						
						</td>
					</tr>';	
			}
			return $tabla;			
		}

		public function fechaMarcacion(){		
			$consulta_fecham="SELECT max(asistencia_hora) AS FECHA_MAXIMA FROM empleado_asistencia";
			$fecha_maxima = $this->ejecutarConsulta($consulta_fecham);		
			return $fecha_maxima;
		}

		public function listarMarcacionesEmpleado($empleado_id, $fecha_inicio, $fecha_fin){
			$tabla="";
			$consulta_datos="SELECT distinct empleado_nombre, DATE(asistencia_hora) AS fecha, TIME(asistencia_hora) AS hora, 
									asistencia_tipo, asistencia_ubicacion 
							FROM empleado_asistencia
							INNER JOIN sujeto_empleado ON empleado_id=asistencia_empleadoid
							WHERE empleado_id = $empleado_id
								AND asistencia_hora between '".$fecha_inicio."' and '".$fecha_fin."'";	
					
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				if($rows['asistencia_tipo']=='E'){
					$asistencia_tipo = "Entrada";
				}else{
					$asistencia_tipo = "Salida";
				}	

				$tabla.='
					<tr>
						<td>'.$rows['empleado_nombre'].'</td>
						<td>'.$rows['fecha'].'</td>						
						<td>'.$rows['hora'].'</td>
						<td>'.$asistencia_tipo.'</td>
						<td><a href="'.$rows['asistencia_ubicacion'].'" target="_blank"> Lugar de marcación</a></td>													
					</tr>';	
			}
			return $tabla;
		}

		public function pensionAlumno(){
			$tabla="";
			$consulta_datos="SELECT sede_nombre, alumno_id, alumno_identificacion, CONCAT(alumno_apellidopaterno,' ',alumno_apellidomaterno) APELLIDOS, 
									CONCAT(alumno_primernombre,' ', alumno_segundonombre) NOMBRES, IFNULL(descuento_valor,35) PENSION, descuento_detalle DETALLE, descuento_fecha
								FROM sujeto_alumno
								LEFT JOIN (SELECT descuento_id, descuento_alumnoid, descuento_valor, descuento_detalle, descuento_fecha 
												FROM alumno_pago_descuento WHERE descuento_estado = 'S') AS Descuento ON descuento_alumnoid = alumno_id
								INNER JOIN general_sede ON sede_id = alumno_sedeid
								WHERE alumno_estado = 'A'
								ORDER BY descuento_valor DESC, descuento_detalle DESC";

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$tabla.='
					<tr>
						<td>'.$rows['sede_nombre'].'</td>
						<td>'.$rows['alumno_identificacion'].'</td>
						<td>'.$rows['APELLIDOS'].'</td>
						<td>'.$rows['NOMBRES'].'</td>
						<td>'.$rows['PENSION'].'</td>
						<td>'.$rows['DETALLE'].'</td>
						<td>'.$rows['descuento_fecha'].'</td>
					</tr>';	
			}
			return $tabla;			
		}
	}
			
											