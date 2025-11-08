<?php
require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\carnetController;

if(isset($_POST['modulo_carnet'])) {
    
    $insCarnet = new carnetController();
    
    switch($_POST['modulo_carnet']) {
        
        case 'actualizar_colores':
            echo $insCarnet->actualizarColoresMeses();
            break;
            
        case 'obtener_color':
            // Para obtener el color hexadecimal via AJAX
            if(isset($_POST['color_id'])) {
                $color_id = $_POST['color_id'];
                $color_hex = $insCarnet->obtenerColorHex($color_id);
                echo json_encode(['color_hex' => $color_hex]);
            }
            break;

        case 'procesar_reimpresion':
            echo $insCarnet->procesarReimpresion();
            break;
            
        default:
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Error",
                "texto" => "Módulo no reconocido",
                "icono" => "error"
            ];
            echo json_encode($alerta);
            break;
    }
    
} else {
    session_destroy();
    header("Location: ".APP_URL."login/");
}
?>