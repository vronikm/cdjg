<?php
use app\controllers\carnetController;

include 'app/lib/barcode.php';
include 'app/lib/alphapdf.php';

$insCarnet = new carnetController();

// Generador de código QR
$generator = new barcode_generator();
$symbology = "qr";
$optionsQR = array('sx'=>4, 'sy'=>4, 'p'=>-12);
$tempDir = "app/views/dist/img/temp/";

// ============================================
// OBTENER CARNETS UNIFICADOS
// ============================================
// Si hay IDs en sesión, son para reimpresión
$alumno_ids_reimpresion = '';

// ✅ LEER IDS DESDE SESIÓN
if(isset($_SESSION['carnet_reimpresion_ids']) && !empty($_SESSION['carnet_reimpresion_ids'])) {
    $alumno_ids_reimpresion = $_SESSION['carnet_reimpresion_ids'];
    // Limpiar la sesión después de leer
    unset($_SESSION['carnet_reimpresion_ids']);
}

// Obtener TODOS los carnets (nuevos + reimpresiones)
$carnetsData = $insCarnet->obtenerCarnetsTodosUnificados($alumno_ids_reimpresion);

if(empty($carnetsData)) {
    echo "<script>
        alert('No hay carnets para imprimir');
        window.history.back();
    </script>";
    exit;
}

// Obtener resumen de impresión
$resumen = $insCarnet->obtenerResumenImpresion($carnetsData);

// Obtener información de la escuela
$sede = $insCarnet->informacionSede(1);
if($sede->rowCount() == 1) {
    $sede = $sede->fetch();
} else {
    echo "<script>
        alert('Error: No se encontró información de la escuela');
        window.close();
    </script>";
    exit;
}

// ============================================
// CONFIGURACIÓN PDF
// ============================================
$pdf = new AlphaPDF('P', 'mm', 'A4');
$pdf->SetAutoPagebreak(false);
$pdf->SetMargins(0, 0, 0);

// Dimensiones del carnet (tamaño tarjeta de crédito estándar)
$carnetWidth = 85.6;   // mm
$carnetHeight = 53.98; // mm
$carnetsPerRow = 2;
$carnetsPerCol = 5;
$carnetsPerPage = 10;

// Dimensiones de la página A4
$pageWidth = 210;  // mm
$pageHeight = 297; // mm

// Espaciado entre carnets
$espacioX = 0;
$espacioY = 0;

// Calcular márgenes para centrar
$totalWidth = ($carnetsPerRow * $carnetWidth) + (($carnetsPerRow - 1) * $espacioX);
$totalHeight = ($carnetsPerCol * $carnetHeight) + (($carnetsPerCol - 1) * $espacioY);
$margenX = ($pageWidth - $totalWidth) / 2;
$margenY = ($pageHeight - $totalHeight) / 2;

$totalCarnets = count($carnetsData);
$carnetCounter = 0;

// Nombres de meses
$nombresMeses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// ============================================
// GENERAR CARNETS
// ============================================
foreach($carnetsData as $carnet) {
    // Nueva página cada 10 carnets
    if($carnetCounter % $carnetsPerPage == 0) {
        $pdf->AddPage();
        
        // ============================================
        // ENCABEZADO DE PÁGINA CON RESUMEN
        // ============================================
        if($carnetCounter == 0) {
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(10, 5);
            $pdf->Cell(0, 5, 'IMPRESION DE CARNETS - ' . strtoupper($nombresMeses[date('n')]) . ' ' . date('Y'), 0, 0, 'L');
            
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetXY(10, 10);
            $resumenTexto = 'Total: ' . $resumen['total'] . ' carnets';
            if($resumen['nuevos'] > 0) {
                $resumenTexto .= ' | Nuevos: ' . $resumen['nuevos'];
            }
            if($resumen['reimpresiones'] > 0) {
                $resumenTexto .= ' | Reimpresiones: ' . $resumen['reimpresiones'];
            }
            $pdf->Cell(0, 4, $resumenTexto, 0, 0, 'L');
        }
    }
    
    // Calcular posición del carnet
    $posEnPagina = $carnetCounter % $carnetsPerPage;
    $fila = floor($posEnPagina / $carnetsPerRow);
    $columna = $posEnPagina % $carnetsPerRow;
    
    $x = $margenX + ($columna * ($carnetWidth + $espacioX));
    $y = $margenY + ($fila * ($carnetHeight + $espacioY));
    
    // Determinar si es reimpresión
    $esReimpresion = ($carnet['es_reimpresion'] == 1);
    
    // Color del mes
    $colorHex = $carnet['color_hex'];
    list($r, $g, $b) = sscanf($colorHex, "#%02x%02x%02x");
    
    // ====================
    // FONDO DEL CARNET
    // ====================
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect($x, $y, $carnetWidth, $carnetHeight, 'F');
    
    $pdf->SetLineWidth(0.3);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Rect($x, $y, $carnetWidth, $carnetHeight);
    
    // ====================
    // IMÁGENES DECORATIVAS
    // ====================
    $imgFondo = "./app/views/imagenes/carnet/" . $sede['escuela_verticalfondo'];
    if(file_exists($imgFondo)) {
        $pdf->Image($imgFondo, $x, $y, 20, $carnetHeight);
    }
    
    $pdf->SetAlpha(0.5);
    $pdf->SetFillColor($r, $g, $b);
    $pdf->Rect($x, $y, 20, $carnetHeight, 'F');
    $pdf->SetAlpha(1);

    $imgDerecha = "./app/views/imagenes/carnet/" . $sede['escuela_verticalprincipal'];
    if(file_exists($imgDerecha)) {
        $pdf->Image($imgDerecha, $x + $carnetWidth - 65, $y, 1, $carnetHeight);
    }
    
    // ====================
    // HEADER: LOGO Y QR
    // ====================
    $logoPath = "./app/views/imagenes/fotos/sedes/" . $sede['sede_foto'];
    if(file_exists($logoPath)) {
        $pdf->Image($logoPath, $x + 40, $y + 2, 12, 17);
    }
    
    // Código QR
    $estadoAlumno = $insCarnet->EstadoAlumno($carnet['alumno_id']);
    if($estadoAlumno->rowCount() == 1) {
        $estadoAlumno = $estadoAlumno->fetch();
        $condicion = $estadoAlumno['Condicion'];
        $fechaUltPension = $estadoAlumno['FechaUltPension'];
    } else {
        $condicion = 'Pendiente';
        $fechaUltPension = date('Y-m-d');
    }
    
    $marcaReimpresion = $esReimpresion ? "\nREIMPRESION: SI" : "";
    $qrData = "Estado pension: " . $condicion . "\n" .
              "Fecha ultimo pago: " . $fechaUltPension . "\n" .
              "Sede: " . $sede['sede_nombre'] . "\n" .
              $sede['sede_telefono'] . $marcaReimpresion;
    
    $qrFile = $tempDir . "qr_" . $carnet['alumno_id'] . "_" . time() . "_" . rand(1000,9999) . ".jpeg";
    $image = $generator->render_image($symbology, $qrData, $optionsQR);
    imagejpeg($image, $qrFile);
    imagedestroy($image);

    if(file_exists($qrFile)) {
        $pdf->Image($qrFile, $x + $carnetWidth - 15, $y + 2, 12, 12);
        @unlink($qrFile);
    }
    
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($x + $carnetWidth - 15, $y + 14);
    $pdf->Cell(12, 3, $carnet['alumno_carnet'], 0, 0, 'C');

    // ====================
    // FOTO DEL ALUMNO
    // ====================
    $fotoPath = "./app/views/imagenes/fotos/alumno/" . $carnet['alumno_imagen'];
    if(!file_exists($fotoPath) || empty($carnet['alumno_imagen'])) {
        $fotoPath = "./app/views/imagenes/fotos/alumno/koki.jpg";
    }
    
    $fotoX = $x + $carnetWidth - 23;
    $fotoY = $y + 20;
    $fotoWidth = 20;
    $fotoHeight = 25;
    
    if(file_exists($fotoPath)) {
        $pdf->Image($fotoPath, $fotoX, $fotoY, $fotoWidth, $fotoHeight);
    }

    $pdf->SetLineWidth(0.3);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Rect($fotoX, $fotoY, $fotoWidth, $fotoHeight);
    
    // ====================
    // INFORMACIÓN DEL ALUMNO
    // ====================
    $infoX = $x + 22;
    $infoY = $y + 17;

    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(40, 3, 'DEPORTISTA', 0, 0, 'L');
    
    $infoY += 4;
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY($infoX, $infoY);
    $nombreCompleto = mb_convert_encoding(
        strtoupper($carnet['alumno_nombre']), 
        'ISO-8859-1', 'UTF-8'
    );
    
    if(strlen($carnet['alumno_nombre']) > 21) {
        $pdf->MultiCell(42, 3, $nombreCompleto, 0, 'L');
        $infoY = $pdf->GetY();
    } else {
        $pdf->Cell(40, 3, $nombreCompleto, 0, 0, 'L');
        $infoY += 3;
    }
    
    $infoY += 1;
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(15, 2.5, 'C.I.:', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($infoX + 8, $infoY);
    $pdf->Cell(32, 2.5, $carnet['alumno_identificacion'], 0, 0, 'L');
    
    $infoY += 3;
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(15, 2.5, 'Horario:', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($infoX + 12, $infoY);
    $pdf->Cell(28, 2.5, 
        mb_convert_encoding($carnet['horario_nombre'], 'ISO-8859-1', 'UTF-8'), 
        0, 0, 'L');
    
    $infoAlumno = $insCarnet->infoAlumnoCarnet($carnet['alumno_id']);
    if($infoAlumno->rowCount() == 1) {
        $infoAlumno = $infoAlumno->fetch();
        $edad = date_diff(
            date_create($infoAlumno['alumno_fechanacimiento']), 
            date_create('today')
        )->y;
    } else {
        $edad = 0;
    }
    
    $infoY += 3;
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(15, 2.5, 'Edad:', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 7.5);
    $pdf->SetXY($infoX + 10, $infoY);
    $pdf->Cell(30, 2.5, $edad . ' ' . mb_convert_encoding('años', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
    
    $infoY += 3;
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(15, 2.5, 'Mes vigencia:', 0, 0, 'L');
    
    $pdf->SetFillColor($r, $g, $b);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY($infoX + 18, $infoY - 0.5);
    $mesNombre = strtoupper($nombresMeses[$carnet['carnet_mes']] ?? 'N/A');
    $pdf->Cell(20, 3, 
        mb_convert_encoding($mesNombre, 'ISO-8859-1', 'UTF-8'), 
        0, 0, 'C', true);
    
    $infoY += 3;
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(15, 2.5, 'Sede:', 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($infoX + 10, $infoY);
    $pdf->Cell(30, 2.5, 
        mb_convert_encoding($sede['sede_nombre'], 'ISO-8859-1', 'UTF-8'), 
        0, 0, 'L');
    
    $infoY += 3.5;
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(40, 2, 'clubjorgeguzman@gmail.com', 0, 0, 'L');
    $infoY += 3;
    $pdf->SetXY($infoX, $infoY);
    $pdf->Cell(40, 2, '0983779393', 0, 0, 'L');
    
    // ====================
    // MARCA DE REIMPRESIÓN
    // ====================
    if($esReimpresion) {
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->SetXY($x + 22, $y + $carnetHeight - 4);
        $pdf->Cell(30, 2, mb_convert_encoding('REIMPRESION', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        
        $pdf->SetFont('Arial', '', 4.5);
        $pdf->SetXY($x + $carnetWidth - 25, $y + $carnetHeight - 4.5);
        $pdf->Cell(22, 2, 'Reimp: ' . date('d/m/Y'), 0, 0, 'R');
    } else {
        $pdf->SetFont('Arial', '', 5);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetXY($x + $carnetWidth - 25, $y + $carnetHeight - 4.5);
        $pdf->Cell(22, 2, 'Impreso: ' . date('d/m/Y'), 0, 0, 'R');
    }
    
    // ====================
    // FOOTER
    // ====================
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->Line($x + 22, $y + $carnetHeight - 2, $x + $carnetWidth - 3, $y + $carnetHeight - 2);
    
    $carnetCounter++;
}

// Registrar impresión solo de carnets NUEVOS
$carnet_ids_nuevos = [];
foreach($carnetsData as $carnet) {
    if($carnet['es_reimpresion'] == 0 && !empty($carnet['carnet_id'])) {
        $carnet_ids_nuevos[] = $carnet['carnet_id'];
    }
}

if(!empty($carnet_ids_nuevos)) {
    $insCarnet->registrarImpresion($carnet_ids_nuevos);
}

// Nombre del archivo
$nombreArchivo = "Carnets_" . date('Y-m-d_His') . ".pdf";
if($resumen['reimpresiones'] > 0 && $resumen['nuevos'] == 0) {
    $nombreArchivo = "Carnets_Reimpresion_" . date('Y-m-d_His') . ".pdf";
} elseif($resumen['reimpresiones'] > 0 && $resumen['nuevos'] > 0) {
    $nombreArchivo = "Carnets_Mixto_" . date('Y-m-d_His') . ".pdf";
}

// Salida del PDF
$pdf->Output($nombreArchivo, "I", "T");
?>