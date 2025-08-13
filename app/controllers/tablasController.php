<?php
	namespace app\controllers;
	use app\models\mainModel;

	class tablasController extends mainModel{		
		public function registrarTablaControlador(){
			# Almacenando datos#
			$tabla_nombre 	= $this->limpiarCadena($_POST['tabla_nombre']);
			
			# Verificando campos obligatorios #
		    if($tabla_nombre==""){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No ha completado todos los campos obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);       
		    }		

			$tabla_datos_reg=[
				[
					"campo_nombre"=>"tabla_nombre",
					"campo_marcador"=>":Tablanombre",
					"campo_valor"=>$tabla_nombre
				],			
				[
					"campo_nombre"=>"tabla_estado",
					"campo_marcador"=>":Estado",
					"campo_valor"=>'A'
				]
			];		

			$registrar_tabla=$this->guardarDatos("general_tabla",$tabla_datos_reg);

			if($registrar_tabla->rowCount()>0){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Registro de tabla",
					"texto"=>"La tabla se registró correctamente",
					"icono"=>"success"
				];				
			
			}else{				

				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No se pudo registrar la tabla, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);

		}

		public function listarTablas(){ 			
			$tabla="";
		
			$consulta_datos="SELECT tabla_id, 
									tabla_nombre, 
									CASE tabla_estado when 'A' then 'Activa' when 'I' then 'Inactiva' else tabla_estado end as ESTADO_TABLA
									FROM general_tabla G 
									where G.tabla_estado = 'A'
									ORDER BY tabla_id ASC";	

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
			
				$tabla.='
					<tr>
						<td>'.$rows['tabla_id'].'</td>
						<td>'.$rows['tabla_nombre'].'</td>
						<td>'.$rows['ESTADO_TABLA'].'</td>
						<td>
							<form class="FormularioAjax" action="'.APP_URL.'app/ajax/tablasAjax.php" method="POST" autocomplete="off" >
								<input type="hidden" name="modulo_tablas" value="eliminar">
								<input type="hidden" name="tabla_id" value="'.$rows['tabla_id'].'">						
								<button type="submit" class="btn float-right btn-danger btn-sm" style="margin-right: 5px;">Eliminar</button>
							</form>							
							
							<a href="'.APP_URL.'tablasNew/'.$rows['tabla_id'].'/" class="btn float-right btn-success btn-sm" style="margin-right: 5px;" >Editar</a>

						</td>
					</tr>';	
			}
			return $tabla;			
		}

		public function actualizarTablaControlador(){	
			# Almacenando datos#
			$tablaid		= $this->limpiarCadena($_POST['tabla_id']);
			$tabla_nombre 	= $this->limpiarCadena($_POST['tabla_nombre']);			

			# Verificando pago #
			$datos = $this->ejecutarConsulta("SELECT * FROM general_tabla WHERE tabla_id = '$tablaid'");			
			if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No hemos encontrado la tabla en el sistema: ".$tablaid,
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos=$datos->fetch();				
		    }			
			# Verificando campos obligatorios #
		    if($tabla_nombre==""){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No ha completado todos los campos obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);		        
		    }			

			$tabla_datos_reg=[
				[
					"campo_nombre"=>"tabla_nombre",
					"campo_marcador"=>":Tablanombre",
					"campo_valor"=>$tabla_nombre
				]
			];			
		
			$condicion=[
				"condicion_campo"=>"tabla_id",
				"condicion_marcador"=>":tablaid",
				"condicion_valor"=>$tablaid
			];			

			if($this->actualizarDatos("general_tabla",$tabla_datos_reg,$condicion)){				
				
				$alerta=[
					"tipo"=>"redireccionar",			
					"url"=>APP_URL.'tablasNew/',					
					"titulo"=>"Tabla actualizada",
					"texto"=>"Los datos de la tabla ".$tabla_nombre." se actualizaron correctamente",
					"icono"=>"success"	
				];								

			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No hemos podido actualizar los datos de la tabla ".$tabla_nombre.", por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

		public function BuscarTabla($tablaid){		
			$consulta_datos="SELECT G.* 
					FROM general_tabla G										
					WHERE tabla_id = ".$tablaid;	

			$datos = $this->ejecutarConsulta($consulta_datos);		
			return $datos;
		}

		public function eliminarTablaControlador(){			
			$tablaid=$this->limpiarCadena($_POST['tabla_id']);

			$tabla_datos=[
				[
					"campo_nombre"=>"tabla_estado",
					"campo_marcador"=>":Estado",
					"campo_valor"=> 'I'
				]
			];

			$condicion=[
				"condicion_campo"=>"tabla_id",
				"condicion_marcador"=>":tablaid",
				"condicion_valor"=>$tablaid
			];

			if($this->actualizarDatos("general_tabla", $tabla_datos, $condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Tabla eliminada",
					"texto"=>"La tabla fue eliminada correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No fue posible eliminar la tabla, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

		/*----------  Controlador para catálogos  ----------*/

		public function registrarCatalogoControlador(){
			# Almacenando datos#
			$catalogo_valor 	  = $this->limpiarCadena($_POST['catalogo_valor']);
			$catalogo_tablaid	  = $this->limpiarCadena($_POST['catalogo_tablaid']);
			$catalogo_descripcion = $this->limpiarCadena($_POST['catalogo_descripcion']);
			
			# Verificando campos obligatorios #
		    if($catalogo_valor=="" || $catalogo_descripcion==""){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No ha completado todos los campos obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);       
		    }
			$catalogo_datos_reg=[
				[
					"campo_nombre"=>"catalogo_valor",
					"campo_marcador"=>":ValorCatalogo",
					"campo_valor"=>$catalogo_valor
				],			
				[
					"campo_nombre"=>"catalogo_tablaid",
					"campo_marcador"=>":Tablaid",
					"campo_valor"=>$catalogo_tablaid
				],			
				[
					"campo_nombre"=>"catalogo_descripcion",
					"campo_marcador"=>":Descripcion",
					"campo_valor"=>$catalogo_descripcion
				],			
				[
					"campo_nombre"=>"catalogo_estado",
					"campo_marcador"=>":Estado",
					"campo_valor"=>'A'
				]				
			];	
			$registrar_catalogo=$this->guardarDatos("general_tabla_catalogo",$catalogo_datos_reg);

			if($registrar_catalogo->rowCount()>0){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Registro de catálogo",
					"texto"=>"El catálogo se registró correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No se pudo registrar el catálogo, por favor intente nuevamente",
					"icono"=>"error"
				];
			}
			return json_encode($alerta);
		}

		public function listarCatalogos(){ 			
			$tabla="";
			$estado = "";
			$texto = "";
			$boton = "";

			$consulta_datos="SELECT tabla_id, tabla_nombre, catalogo_valor, catalogo_descripcion, 
								case catalogo_estado when 'A' then 'Activo' when 'I' then 'Inactivo' else catalogo_estado end AS ESTADO_CATALOGO
								FROM general_tabla, general_tabla_catalogo
								WHERE tabla_id = catalogo_tablaid
									and catalogo_estado in ('A','I')";	

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				if($rows['ESTADO_CATALOGO']=='Activo'){
					$estado = "Activo";
					$texto = "Inactivar";
					$boton = "btn-secondary";
				}else{
					$estado = "Inactivo";
					$texto = "Activar";
					$boton = "btn-info";				
				}
				$tabla.='
					<tr>
						<td>'.$rows['tabla_id'].'</td>
						<td>'.$rows['tabla_nombre'].'</td>
						<td>'.$rows['catalogo_valor'].'</td>
						<td>'.$rows['catalogo_descripcion'].'</td>
						<td>'.$rows['ESTADO_CATALOGO'].'</td>
						<td>
							<form class="FormularioAjax" action="'.APP_URL.'app/ajax/tablasAjax.php" method="POST" autocomplete="off" >
								<input type="hidden" name="modulo_catalogos" value="eliminar">
								<input type="hidden" name="catalogo_valor" value="'.$rows['catalogo_valor'].'">						
								<button type="submit" class="btn float-right btn-danger btn-xs" style="margin-right: 5px;">Eliminar</button>
							</form>			
							
							<form class="FormularioAjax" action="'.APP_URL.'app/ajax/tablasAjax.php" method="POST" autocomplete="off" >
								<input type="hidden" name="modulo_catalogos" value="actualizarestado">
								<input type="hidden" name="catalogo_valor" value="'.$rows['catalogo_valor'].'">						
								<input type="hidden" name="catalogo_estado" value="'.$rows['ESTADO_CATALOGO'].'">
								<button type="submit" class="btn float-right '.$boton.' btn-xs" style="margin-right: 5px;""> '.$texto.' </button>
							</form>

							<a href="'.APP_URL.'catalogosNew/'.$rows['catalogo_valor'].'/" class="btn float-right btn-success btn-xs" style="margin-right: 5px;" >Editar</a>
							
						</td>
					</tr>';	
			}
			return $tabla;			
		}

		public function actualizarCatalogoControlador(){
			# Almacenando datos#		
			$catalogo_valor 	  = $this->limpiarCadena($_POST['catalogo_valor']);
			$catalogo_tablaid	  = $this->limpiarCadena($_POST['catalogo_tablaid']);
			$catalogo_descripcion = $this->limpiarCadena($_POST['catalogo_descripcion']);
			$catalogo_estado	  = "";
			$codigo_catalogo 	  = $this->limpiarCadena($_POST['codigo_catalogo']);

			# Verificando catálogo #
			$datos = $this->ejecutarConsulta("SELECT * FROM general_tabla_catalogo 
												WHERE catalogo_valor = '$codigo_catalogo'");
			if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No hemos encontrado el catálogo en el sistema: ".$codigo_catalogo,
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos=$datos->fetch();
				$catalogo_estado = $datos['catalogo_estado'];
		    }		
			# Verificando campos obligatorios #
		    if($catalogo_valor=="" || $catalogo_descripcion==""){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No ha completado todos los campos obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);		        
		    }			

			$catalogo_datos_reg=[
				[
					"campo_nombre"=>"catalogo_valor",
					"campo_marcador"=>":ValorCatalogo",
					"campo_valor"=>$catalogo_valor
				],			
				[
					"campo_nombre"=>"catalogo_tablaid",
					"campo_marcador"=>":Tablaid",
					"campo_valor"=>$catalogo_tablaid
				],			
				[
					"campo_nombre"=>"catalogo_descripcion",
					"campo_marcador"=>":Descripcion",
					"campo_valor"=>$catalogo_descripcion
				],			
				[
					"campo_nombre"=>"catalogo_estado",
					"campo_marcador"=>":Estado",
					"campo_valor"=>$catalogo_estado
				]				
			];	
		
			$condicion=[
					"condicion_campo"=>"catalogo_valor",
					"condicion_marcador"=>":CatalogoValor",
					"condicion_valor"=>$codigo_catalogo
			];			
			if($this->actualizarDatos("general_tabla_catalogo",$catalogo_datos_reg,$condicion)){				
				$alerta=[
					"tipo"=>"redireccionar",			
					"url"=>APP_URL.'catalogosNew/',					
					"titulo"=>"Catálogo actualizado",
					"texto"=>"Los datos del catálogo ".$catalogo_valor." se actualizaron correctamente",
					"icono"=>"success"	
				];								

			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error",
					"texto"=>"No hemos podido actualizar los datos del catálogo ".$catalogo_valor.", por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

		public function BuscarCatalogo($catalogo_valor){		
			$consulta_datos=("SELECT catalogo_valor, tabla_id, tabla_nombre, catalogo_descripcion, 
								CASE WHEN catalogo_estado ='A' THEN 'Activo' 
									 WHEN catalogo_estado = 'I' THEN 'Inactivo' 
									 ELSE catalogo_estado 
								END AS ESTADO 
								FROM general_tabla, general_tabla_catalogo 
								WHERE catalogo_tablaid = tabla_id
									and catalogo_valor = '$catalogo_valor'");	

			$datos = $this->ejecutarConsulta($consulta_datos);		
			return $datos;
		}

		public function eliminarCatalogoControlador(){			
			$catalogo_valor=$this->limpiarCadena($_POST['catalogo_valor']);

			$catalogo_datos=[
				[
					"campo_nombre"=>"catalogo_estado",
					"campo_marcador"=>":Estado",
					"campo_valor"=> 'E'
				]
			];

			$condicion=[
				"condicion_campo"=>"catalogo_valor",
				"condicion_marcador"=>":Catalogo",
				"condicion_valor"=>$catalogo_valor
			];

			if($this->actualizarDatos("general_tabla_catalogo", $catalogo_datos, $condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Catálogo eliminado",
					"texto"=>"El catálogo fue eliminado correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No fue posible eliminar el catálogo, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

		public function listarCatalogoTablas($tablaid){ 			
			$option="";
		
			$consulta_datos="SELECT tabla_id, tabla_nombre 									
									FROM general_tabla 
									where tabla_estado = 'A'";	
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){				
				if($tablaid==$rows['tabla_id']){
					$option.='<option value='.$rows['tabla_id'].' selected>'.$rows['tabla_nombre'].'</option>';	
				}else{
					$option.='<option value='.$rows['tabla_id'].'>'.$rows['tabla_nombre'].'</option>';	
				}
			}
			return $option;			
		}
		public function actualizarCatalogoEstadoControlador(){			

			$estadoA 		= '';		
			$catalogo_valor = $this->limpiarCadena($_POST['catalogo_valor']);

		    $datos=$this->ejecutarConsulta("SELECT * FROM general_tabla_catalogo WHERE catalogo_valor ='$catalogo_valor'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el catálogo en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);

		    }else{
		    	$datos=$datos->fetch();
		    }
				
			if($datos['catalogo_estado']=='A'){
				$estadoA = 'I';
			}else{
				$estadoA = 'A';
			}

            $catalogo_datos_up=[
				[
					"campo_nombre"=>"catalogo_estado",
					"campo_marcador"=>":Estado",
					"campo_valor"=> $estadoA
				]
			];

			$condicion=[
				"condicion_campo"=>"catalogo_valor",
				"condicion_marcador"=>":CatalogoValor",
				"condicion_valor"=>$catalogo_valor
			];

			if($this->actualizarDatos("general_tabla_catalogo",$catalogo_datos_up,$condicion)){

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Catálogo actualizado",
					"texto"=>"El estado del catálogo ".$datos['catalogo_valor']." se actualizó correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido actualizar los datos del catálogo ".$datos['catalogo_valor'].", por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}
	}
			