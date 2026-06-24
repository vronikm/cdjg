<?php
require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\carnetController;

if(isset($_POST['modulo_carnet'])) {
    
    $insCarnet = new carnetController();
    
    try {
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
            
        case 'imprimir_carnetspendientes':
            $sede_id = isset($_POST['sede_id']) ? (int)$_POST['sede_id'] : 0;
            // Obtener carnets pendientes
            $resultado = $insCarnet->carnetPendientesImpresion($sede_id);
            
            // Retornar JSON con el total
            if(isset($resultado[0]['total'])) {
                echo json_encode([
                    "tipo" => "success",
                    "total" => (int)$resultado[0]['total']
                ]);
            } else {
                echo json_encode([
                    "tipo" => "error",
                    "total" => 0,
                    "mensaje" => "No se pudo obtener el total de carnets"
                ]);
            }
            break;

        case 'preparar_impresion_mensual':
            $sede_id = isset($_POST['sede_id']) ? (int)$_POST['sede_id'] : 0;
            echo $insCarnet->prepararImpresionMensual($sede_id);
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
    } catch(Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "tipo" => "error",
            "titulo" => "Error",
            "texto" => "No se pudo procesar la solicitud de carnets. " . $e->getMessage(),
            "icono" => "error"
        ]);
    }
    
} else {
    session_destroy();
    header("Location: ".APP_URL."login/");
}
?>
