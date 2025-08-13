<?php

	namespace app\controllers;
	use app\models\mainModel;

	class menuController extends mainModel{
        public function listarMenu(){
			$tabla="";

			$consulta_datos="SELECT *, 
							CASE WHEN menu_estado = 'A' THEN 'Activo' ELSE 'Inactivo' END AS estado,
							CASE WHEN menu_hijo = 'S' THEN 'Si' ELSE 'No' END AS menu_hijo 
							FROM seguridad_menu
							WHERE menu_estado != 'E'";
					
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$tabla.='
					<tr>
						<td>'.$rows['menu_id'].'</td>
						<td>'.$rows['menu_orden'].'</td>
						<td>'.$rows['menu_padreid'].'</td>
						<td>'.$rows['menu_hijo'].'</td>
						<td>'.$rows['menu_nombre'].'</td>
						<td>'.$rows['menu_vista'].'</td>
						<td>'.$rows['menu_icono'].'</td>
						<td>'.$rows['estado'].'</td>
						<td>
							<form class="FormularioAjax" action="'.APP_URL.'app/ajax/menuAjax.php" method="POST" autocomplete="off" >
								<input type="hidden" name="modulo_menu" value="eliminarMenu">
								<input type="hidden" name="menu_id" value="'.$rows['menu_id'].'">						
								<button type="submit" class="btn float-right btn-danger btn-xs" style="margin-right: 5px;">Eliminar</button>
							</form>							

							<a href="'.APP_URL.'userMenu/'.$rows['menu_id'].'/" class="btn float-right btn-success btn-xs" style="margin-right: 5px;" >Editar</a>
							
						</td>
					</tr>';	
			}
			return $tabla;
		}

		public function BuscarMenu($menuid){
		
			$consulta_datos="SELECT M.* 
					FROM seguridad_menu M										
					WHERE M.menu_id = ".$menuid;	

			$datos = $this->ejecutarConsulta($consulta_datos);		
			return $datos;
		}

		public function crearMenu(){		
			
			# Almacenando datos#
			$menu_nombre  	= $this->limpiarCadena($_POST['menu_nombre']);
			$menu_vista		= $this->limpiarCadena($_POST['menu_vista']);
			$menu_icono		= $this->limpiarCadena($_POST['menu_icono']);
			$menu_orden  	= $this->limpiarCadena($_POST['menu_orden']);
			$menu_padreid	= $this->limpiarCadena($_POST['menu_idpadre']);
			$menu_hijo		= $this->limpiarCadena($_POST['menu_hijo']);
			$menu_estado  	= $this->limpiarCadena($_POST['menu_estado']);
			
			# Verificando campos obligatorios #
			if($menu_nombre=="" || $menu_vista=="" || $menu_icono=="" || $menu_orden=="" || $menu_padreid=="" || $menu_hijo=="" || $menu_estado=="" ){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"No has llenado los campos obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
				
			}			
									
			$menu_datos_reg=[
				[
					"campo_nombre"=>"menu_nombre",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$menu_nombre
				],
				[
					"campo_nombre"=>"menu_orden",
					"campo_marcador"=>":Orden",
					"campo_valor"=>$menu_orden
				],
				[
					"campo_nombre"=>"menu_padreid",
					"campo_marcador"=>":Padreid",
					"campo_valor"=>$menu_padreid
				],
				[
					"campo_nombre"=>"menu_hijo",
					"campo_marcador"=>":Hijo",
					"campo_valor"=>$menu_hijo
				],	
				[
					"campo_nombre"=>"menu_vista",
					"campo_marcador"=>":Vista",
					"campo_valor"=>$menu_vista
				],	
				[
					"campo_nombre"=>"menu_icono",
					"campo_marcador"=>":Icono",
					"campo_valor"=>$menu_icono
				],	
				[
					"campo_nombre"=>"menu_estado",
					"campo_marcador"=>":Estado",
					"campo_valor"=>'A'
				]
			];		

			$registrar_rol=$this->guardarDatos("seguridad_menu",$menu_datos_reg);

			if($registrar_rol->rowCount()>0){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Menu guardado",
					"texto"=>"Menu registrado correctamente",
					"icono"=>"success"
				];				
			
			}else{				

				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"No se pudo registrar el Menu, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}		

		public function actualizarMenu(){
			
			$menuid = $this->limpiarCadena($_POST['menu_id']);

			# Verificando pago #
			$datos = $this->ejecutarConsulta("SELECT menu_id FROM seguridad_menu WHERE menu_id = '$menuid '");			
			if($datos->rowCount()<=0){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurró un error inesperado",
					"texto"=>"No hemos encontrado el Rol en el sistema: ".$menuid,
					"icono"=>"error"
				];
				return json_encode($alerta);
			}else{
				$datos=$datos->fetch();				
			}				

			# Almacenando datos#
			$menu_nombre  	= $this->limpiarCadena($_POST['menu_nombre']);
			$menu_vista		= $this->limpiarCadena($_POST['menu_vista']);
			$menu_icono		= $this->limpiarCadena($_POST['menu_icono']);
			$menu_orden  	= $this->limpiarCadena($_POST['menu_orden']);
			$menu_padreid	= $this->limpiarCadena($_POST['menu_idpadre']);
			$menu_hijo		= $this->limpiarCadena($_POST['menu_hijo']);
			$menu_estado  	= $this->limpiarCadena($_POST['menu_estado']);
			
			# Verificando campos obligatorios #
			if($menu_nombre=="" || $menu_vista=="" || $menu_icono=="" || $menu_orden=="" || $menu_padreid=="" || $menu_hijo=="" || $menu_estado=="" ){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"No has llenado los campos obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);				
			}				

			$menu_datos_reg=[
				[
					"campo_nombre"=>"menu_nombre",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$menu_nombre
				],
				[
					"campo_nombre"=>"menu_orden",
					"campo_marcador"=>":Orden",
					"campo_valor"=>$menu_orden
				],
				[
					"campo_nombre"=>"menu_padreid",
					"campo_marcador"=>":Padreid",
					"campo_valor"=>$menu_padreid
				],
				[
					"campo_nombre"=>"menu_hijo",
					"campo_marcador"=>":Hijo",
					"campo_valor"=>$menu_hijo
				],	
				[
					"campo_nombre"=>"menu_vista",
					"campo_marcador"=>":Vista",
					"campo_valor"=>$menu_vista
				],	
				[
					"campo_nombre"=>"menu_icono",
					"campo_marcador"=>":Icono",
					"campo_valor"=>$menu_icono
				],	
				[
					"campo_nombre"=>"menu_estado",
					"campo_marcador"=>":Estado",
					"campo_valor"=>$menu_estado
				]
			];			
		
			$condicion=[
				"condicion_campo"=>"menu_id",
				"condicion_marcador"=>":Menuid",
				"condicion_valor"=>$menuid
			];			

			if($this->actualizarDatos("seguridad_menu",$menu_datos_reg,$condicion)){				
				
				$alerta=[
					"tipo"=>"redireccionar",			
					"url"=>APP_URL.'userMenu/',					
					"titulo"=>"Menu actualizado",
					"texto"=> "EL menu $menu_nombre se actualizo correctamente",
					"icono"=>"success"	
				];								

			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=> "No hemos podido actualizar el menu $menuid, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

        public function eliminarMenu(){	
			# Almacenando datos
			$menu_id = $_POST['menu_id'];
					
			# Verificando campos obligatorios #
			if($menu_id == ""){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"El menú no se encuentra asignado",
					"icono"=>"error"
				];
				return json_encode($alerta);       
		    }				
			
			$eliminar_menu = $this->eliminarRegistro("seguridad_menu","menu_id",$menu_id);
			
			if($eliminar_menu->rowCount()>0){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Permiso eliminado",
					"texto"=> "El menú fue eliminado correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"No fue posible eliminar el menú",
					"icono"=>"error"
				];
			}
			return json_encode($alerta);
		}  

        public function BuscarRol($rolid){
		
			$consulta_rol="SELECT * FROM seguridad_rol WHERE rol_id = ".$rolid;	

			$rol = $this->ejecutarConsulta($consulta_rol);		
			return $rol;
		}

        public function listarPermiso($rol_id){			
			$tabla="";

			$consulta_datos = "SELECT permiso_id, rol_nombre, CASE WHEN M.menu_padreid = 0 THEN M.menu_nombre ELSE concat(MP.menu_nombre, ' | ', M.menu_nombre) END menu_nombre, permiso_estado
                                    FROM seguridad_permiso P
                                    LEFT JOIN seguridad_menu M ON M.menu_id = P.permiso_menuid 
									LEFT JOIN seguridad_menu MP ON MP.menu_id = M.menu_padreid 
                                    LEFT JOIN seguridad_rol R ON R.rol_id = P.permiso_rolid 
                                    WHERE permiso_estado = 'A'
                                        AND permiso_rolid = $rol_id";
			
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$tabla.='					
					<tr>
						<form class="FormularioAjax" action="'.APP_URL.'app/ajax/menuAjax.php" method="POST" autocomplete="off" >
                            <td>'.$rows['permiso_id'].'</td>
                            <td>'.$rows['rol_nombre'].'</td>
                            <td>'.$rows['menu_nombre'].'</td>
                            <td>'.$rows['permiso_estado'].'</td>
                            <td>												
                                <input type="hidden" name="modulo_menu" value="eliminarpermiso">
                                <input type="hidden" name="permiso_id" value="'.$rows['permiso_id'].'">										
                                <button type="submit" class="btn float-right btn-danger btn-xs" style="margin-right: 5px;">Eliminar</button>					
                            </td>
						</form>
					</tr>
				';
			}
			return $tabla;
		}

        public function MenuPermiso($rol_id){
			$tabla="";

			$consulta_datos="SELECT M.menu_id, CASE WHEN M.menu_padreid = 0 THEN M.menu_nombre ELSE concat(P.menu_nombre, ' | ', M.menu_nombre) END MENU,
                                    M.menu_vista, M.menu_icono
                                FROM seguridad_menu M
                                LEFT JOIN seguridad_menu P ON P.menu_id = M.menu_padreid 
                                WHERE M.menu_hijo <> 'S'
                                    AND M.menu_estado = 'A'
                                    AND M.menu_id NOT IN (select permiso_menuid from seguridad_permiso where permiso_rolid = $rol_id)";
					
			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			foreach($datos as $rows){
				$tabla.='
					<tr>
                        <form class="FormularioAjax" action="'.APP_URL.'app/ajax/menuAjax.php" method="POST" autocomplete="off" >
                            <td>'.$rows['menu_id'].'</td>
                            <td>'.$rows['MENU'].'</td>
                            <td>'.$rows['menu_vista'].'</td>
                            <td>'.$rows['menu_icono'].'</td>
                            <td>	
                                <input type="hidden" name="modulo_menu" value="asignarpermiso">
                                <input type="hidden" name="menu_id" value="'.$rows['menu_id'].'">
                                <input type="hidden" name="rolid" value="'.$rol_id.'">                          
                                <button type="submit" class="btn float-right btn-actualizar btn-xs" style="margin-right: 5px;"">Asignar</button>                             
                            </td>
                        </form>
					</tr>';	
			}
			return $tabla;
		}

        public function registrarPermiso(){	
			# Almacenando datos
            $permiso_menuid = intval($_POST['menu_id']);		
			$permiso_rolid  = $_POST['rolid'];
						
			$permiso_reg = [
				[
					"campo_nombre" => "permiso_rolid",
					"campo_marcador" => ":Rolid",
					"campo_valor" => $permiso_rolid
				],
				[
					"campo_nombre" => "permiso_menuid",
					"campo_marcador" => ":Menuid",
					"campo_valor" => $permiso_menuid
				],
				[
					"campo_nombre" => "permiso_estado",
					"campo_marcador" => ":Estado",
					"campo_valor" => 'A'
				]
			];
			
			$asignar_permiso=$this->guardarDatos("seguridad_permiso",$permiso_reg);
			
			if($asignar_permiso->rowCount()==1){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Permiso agregado",
					"texto"=>"El permiso fue agregado correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"No fue posible agregar el permiso",
					"icono"=>"error"
				];
			}
			return json_encode($alerta);			
		}

        public function eliminarPermiso(){	
			# Almacenando datos
			$permiso_id = $_POST['permiso_id'];
					
			# Verificando campos obligatorios #
			if($permiso_id == ""){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"El permiso no se encuentra asignado",
					"icono"=>"error"
				];
				return json_encode($alerta);       
		    }				
			
			$eliminar_permiso = $this->eliminarRegistro("seguridad_permiso","permiso_id",$permiso_id);
			
			if($eliminar_permiso->rowCount()>0){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Permiso eliminado",
					"texto"=> "El permiso fue eliminado correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Error",
					"texto"=>"No fue posible eliminar el permiso",
					"icono"=>"error"
				];
			}
			return json_encode($alerta);
		}  

		public function BuscarMenuPadre(){
			$consulta_menupadre="SELECT M.* FROM seguridad_menu M WHERE M.menu_padreid = 0 AND M.menu_estado = 'A' ORDER BY M.menu_orden";
			$menupadre = $this->ejecutarConsulta($consulta_menupadre);		
			return $menupadre;
		}

		public function PermisoMenu($rolid){
			$consulta_permisomenu="SELECT *	FROM seguridad_permiso WHERE permiso_rolid = $rolid";
			$permisomenu = $this->ejecutarConsulta($consulta_permisomenu);		
			return $permisomenu;
		}

		public function BuscarMenuHijo($menupadre){
			$consulta_menuhijo="SELECT M.* FROM seguridad_menu M WHERE M.menu_padreid = $menupadre AND M.menu_estado = 'A' ORDER BY M.menu_orden";
			$menuhijo = $this->ejecutarConsulta($consulta_menuhijo);		
			return $menuhijo;
		}

		public function ObtenerMenu($usuario){
			$consulta_menu="SELECT usuario_id, usuario_usuario, usuario_rolid, permiso_menuid, M.*, A.menu_nombre AS padre
							FROM seguridad_usuario U
							LEFT JOIN seguridad_permiso P ON P.permiso_rolid = U.usuario_rolid
							LEFT JOIN seguridad_menu M ON M.menu_id = P.permiso_menuid 
							LEFT JOIN seguridad_menu A ON A.menu_id = M.menu_padreid
							WHERE M.menu_estado = 'A'
								AND usuario_usuario = '".$usuario."'
							 ORDER BY M.menu_padreid, M.menu_orden";	

			$menu = $this->ejecutarConsulta($consulta_menu);		

			$menus = [];
			while ($row = $menu->fetch()) {
				$menus[] = $row;
			}
			return $menus;
		}

		public function ConstruirMenu($menus){
			$html = '';
			$padreActual = null; // Variable para rastrear el padre actual
		
			if (count($menus) > 0) {
				foreach ($menus as $menu) {
					if ($menu['menu_padreid'] == 0 && $menu['menu_hijo'] == 'N') {
						// Si había un bloque de padre abierto, ciérralo antes
						if (!is_null($padreActual)) {
							$html .= '</ul>';
							$html .= '</li>';
							$padreActual = null; // Reinicia el rastreador de padre
						}
		
						// Menú principal sin hijos
						$html .= '<li class="nav-item">';
						$html .= '<a href="' . APP_URL . $menu['menu_vista'] . '/" class="nav-link">';
						$html .= '<i class="'.$menu['menu_icono'].'"></i> <p>' . $menu['menu_nombre'] . '</p>';
						$html .= '</a>';
						$html .= '</li>';
					} else {
						// Menú con padre y posiblemente hijos
						if ($padreActual !== $menu['padre']) {
							// Si hay un padre diferente, cierra el bloque anterior
							if (!is_null($padreActual)) {
								$html .= '</ul>';
								$html .= '</li>';
							}
		
							// Agrega el nuevo padre
							$html .= '<li class="nav-header">' . $menu['padre'] . '</li>';
							$html .= '<li class="nav-item">';
							$html .= '<a href="#" class="nav-link">';
							$html .= '<i class="'.$menu['menu_icono'].'"></i>';
							$html .= '<p>' . $menu['padre'] . '<i class="fas fa-angle-left right"></i></p>';
							$html .= '</a>';
							$html .= '<ul class="nav nav-treeview">';
							$padreActual = $menu['padre']; // Actualiza el rastreador de padre actual
						}
		
						// Agrega los hijos al menú
						$html .= '<li class="nav-item">';
						$html .= '<a href="' . APP_URL . $menu['menu_vista'] . '/" class="nav-link">';
						$html .= '<i class="nav-icon far fa-circle text-info"></i>';
						$html .= '<p>' . $menu['menu_nombre'] . '</p>';
						$html .= '</a>';
						$html .= '</li>';
					}
				}
		
				// Cierra cualquier bloque abierto al final
				if (!is_null($padreActual)) {
					$html .= '</ul>';
					$html .= '</li>';
				}				
			}

			$html .= '<li class="nav-header">Salir</li>';
			$html .= '	<li class="nav-item">';
			$html .= '	  <a href="'.APP_URL.'logOut/" class="nav-link" id="btn_exit">';
			$html .= '		<i class="nav-icon far fa-circle text-danger"></i>';
			$html .= '		<p class="text">Salir</p>';
			$html .= '	  </a>';
			$html .= '	</li>';
			
		
			return $html;
		}
		
		
		
    }