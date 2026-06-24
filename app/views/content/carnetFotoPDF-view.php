<?php
use app\controllers\carnetController;

include 'app/lib/barcode.php';
include 'app/lib/alphapdf.php';

$insCarnet = new carnetController();

$generator  = new barcode_generator();
$symbology  = "qr";
$optionsQR  = array('sx'=>4, 'sy'=>4, 'p'=>-12);
$tempDir    = "app/views/dist/img/temp/";
if(!is_dir($tempDir)){
    @mkdir($tempDir, 0775, true);
}

if(!function_exists('carnetTipoImagenPDF')){
    function carnetTipoImagenPDF($ruta){
        if(!is_file($ruta)){
            return '';
        }

        $info = @getimagesize($ruta);
        if(!$info){
            return '';
        }

        switch($info[2]){
            case IMAGETYPE_JPEG:
                return 'JPG';
            case IMAGETYPE_PNG:
                return 'PNG';
            case IMAGETYPE_GIF:
                return 'GIF';
            default:
                return '';
        }
    }
}

if(!function_exists('carnetImagenCompatiblePDF')){
    function carnetImagenCompatiblePDF($ruta, $tempDir){
        $tipo = carnetTipoImagenPDF($ruta);
        if($tipo === ''){
            return ['ruta' => '', 'tipo' => '', 'temporal' => false];
        }

        if($tipo === 'JPG'){
            return ['ruta' => $ruta, 'tipo' => 'JPG', 'temporal' => false];
        }

        $creador = null;
        if($tipo === 'PNG' && function_exists('imagecreatefrompng')){
            $creador = 'imagecreatefrompng';
        } elseif($tipo === 'GIF' && function_exists('imagecreatefromgif')){
            $creador = 'imagecreatefromgif';
        }

        if($creador === null || !function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')){
            return ['ruta' => '', 'tipo' => '', 'temporal' => false];
        }

        $origen = @$creador($ruta);
        if(!$origen){
            return ['ruta' => '', 'tipo' => '', 'temporal' => false];
        }

        if(function_exists('imagepalettetotruecolor')){
            @imagepalettetotruecolor($origen);
        }

        $ancho = imagesx($origen);
        $alto = imagesy($origen);
        if($ancho <= 0 || $alto <= 0){
            imagedestroy($origen);
            return ['ruta' => '', 'tipo' => '', 'temporal' => false];
        }

        $destino = imagecreatetruecolor($ancho, $alto);
        if(!$destino){
            imagedestroy($origen);
            return ['ruta' => '', 'tipo' => '', 'temporal' => false];
        }

        imagealphablending($destino, true);
        $blanco = imagecolorallocate($destino, 255, 255, 255);
        imagefilledrectangle($destino, 0, 0, $ancho, $alto, $blanco);
        imagecopy($destino, $origen, 0, 0, 0, 0, $ancho, $alto);

        if(!is_dir($tempDir)){
            @mkdir($tempDir, 0775, true);
        }

        $rutaTemporal = rtrim($tempDir, '/\\') . DIRECTORY_SEPARATOR . 'pdf_img_' . uniqid('', true) . '.jpg';
        $convertida = @imagejpeg($destino, $rutaTemporal, 90);

        imagedestroy($origen);
        imagedestroy($destino);

        if(!$convertida || !is_file($rutaTemporal)){
            if(is_file($rutaTemporal)){
                @unlink($rutaTemporal);
            }
            return ['ruta' => '', 'tipo' => '', 'temporal' => false];
        }

        return ['ruta' => $rutaTemporal, 'tipo' => 'JPG', 'temporal' => true];
    }
}

if(!function_exists('carnetLimpiarImagenTemporalPDF')){
    function carnetLimpiarImagenTemporalPDF($imagen){
        if(!empty($imagen['temporal']) && !empty($imagen['ruta']) && is_file($imagen['ruta'])){
            @unlink($imagen['ruta']);
        }
    }
}

if(!function_exists('carnetImagePDF')){
    function carnetImagePDF($pdf, $ruta, $tempDir, $x, $y, $w = 0, $h = 0){
        $imagen = carnetImagenCompatiblePDF($ruta, $tempDir);
        if($imagen['tipo'] === ''){
            return false;
        }

        try {
            $pdf->Image($imagen['ruta'], $x, $y, $w, $h, $imagen['tipo']);
            return true;
        } catch(Exception $e) {
            error_log('[carnetPDF] Imagen omitida: ' . $ruta . ' - ' . $e->getMessage());
            return false;
        } finally {
            carnetLimpiarImagenTemporalPDF($imagen);
        }
    }
}

if(!function_exists('carnetCellAjustadaPDF')){
    function carnetCellAjustadaPDF($pdf, $x, $y, $w, $h, $texto, $font = 'Arial', $style = 'B', $maxSize = 8, $minSize = 5){
        $texto = (string)$texto;
        $size = $maxSize;

        do {
            $pdf->SetFont($font, $style, $size);
            if($pdf->GetStringWidth($texto) <= $w){
                break;
            }
            $size -= 0.25;
        } while($size >= $minSize);

        if($pdf->GetStringWidth($texto) > $w){
            $suffix = '...';
            while($texto !== '' && $pdf->GetStringWidth($texto.$suffix) > $w){
                $texto = substr($texto, 0, -1);
            }
            $texto = rtrim($texto).$suffix;
        }

        $pdf->SetXY($x, $y);
        $pdf->Cell($w, $h, $texto, 0, 0, 'L');
    }
}

// ============================================
// OBTENER DATOS DEL ALUMNO
// ============================================
$alumnoid = $insCarnet->limpiarCadena($url[1]);
$mesSolicitado = isset($url[2]) ? (int)$insCarnet->limpiarCadena($url[2]) : 0;
$anioSolicitado = isset($url[3]) ? (int)$insCarnet->limpiarCadena($url[3]) : 0;
if($mesSolicitado < 1 || $mesSolicitado > 12 || $anioSolicitado < 2000){
    $mesSolicitado = 0;
    $anioSolicitado = 0;
}

$datosRaw = $insCarnet->infoAlumnoCarnet($alumnoid);

if(is_string($datosRaw)){
    $alerta = json_decode($datosRaw, true);
    die('<div style="font-family:sans-serif;padding:30px;color:#c0392b;">
            <h2>⚠️ ' . htmlspecialchars($alerta['titulo']) . '</h2>
            <p>' . htmlspecialchars($alerta['texto']) . '</p>
            <a href="javascript:history.back()">← Volver</a>
         </div>');
}

if($datosRaw->rowCount() != 1){
    die('<div style="font-family:sans-serif;padding:30px;color:#c0392b;">
            <h2>⚠️ Alumno no encontrado</h2>
            <a href="javascript:history.back()">← Volver</a>
         </div>');
}
$datos = $datosRaw->fetch();

// Estado de pensión y mes
$estadoAlumno = $insCarnet->EstadoAlumno($alumnoid, $mesSolicitado, $anioSolicitado);
if($estadoAlumno->rowCount() == 1){
    $estadoAlumno    = $estadoAlumno->fetch();
    $condicion       = $estadoAlumno['Condicion'];
    $fechaUltPension = $estadoAlumno['FechaUltPension'];
    $mesActual       = $mesSolicitado > 0 ? $mesSolicitado : (int)date('n', strtotime($fechaUltPension));
} else {
    $condicion       = 'Pendiente';
    $fechaUltPension = ($mesSolicitado > 0 && $anioSolicitado > 0)
        ? sprintf('%04d-%02d-01', $anioSolicitado, $mesSolicitado)
        : date('Y-m-d');
    $mesActual       = $mesSolicitado > 0 ? $mesSolicitado : (int)date('n');
}

// Color del mes
$colorMes = $insCarnet->BuscarColorPorMes($mesActual);
$colorHex = '#FF69B4'; // rosa por defecto (mes sin color configurado)
if($colorMes && $colorMes->rowCount() == 1){
    $colorMes = $colorMes->fetch();
    $colorHex = $colorMes['color_hex'];
}
list($r, $g, $b) = sscanf($colorHex, "#%02x%02x%02x");

// Información de sede
$sede = $insCarnet->informacionSede($datos['alumno_sedeid']);
if($sede->rowCount() != 1){
    die('<div style="font-family:sans-serif;padding:30px;color:#c0392b;">
            <h2>⚠️ Sede no encontrada</h2>
            <a href="javascript:history.back()">← Volver</a>
         </div>');
}
$sede = $sede->fetch();

// Nombre del mes
$nombresMeses = [
    1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
    5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
    9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
];
$mesNombre = strtoupper($nombresMeses[$mesActual] ?? 'N/A');

// Edad
$edad = date_diff(date_create($datos['alumno_fechanacimiento']), date_create('today'))->y;

// Nombre completo
$nombreCompleto = mb_convert_encoding(
    strtoupper($datos['Nombres'] . ' ' . $datos['Apellidos']),
    'ISO-8859-1', 'UTF-8'
);

// ============================================
// CONFIGURACIÓN PDF — tamaño exacto del carnet
// ============================================
$carnetWidth  = 85.6;   // mm (tarjeta de crédito estándar)
$carnetHeight = 53.98;  // mm

$pdf = new AlphaPDF('L', 'mm', array($carnetWidth, $carnetHeight));
$pdf->SetAutoPagebreak(false);
$pdf->SetMargins(0, 0, 0);
$pdf->AddPage();

$x = 0;
$y = 0;

// ============================================
// FONDO BLANCO + BORDE
// ============================================
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect($x, $y, $carnetWidth, $carnetHeight, 'F');
$pdf->SetLineWidth(0.3);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Rect($x, $y, $carnetWidth, $carnetHeight);

// ============================================
// IMAGEN DECORATIVA IZQUIERDA (vertical_fondo)
// ============================================
$imgFondo = "./app/views/imagenes/carnet/" . trim((string)$sede['escuela_verticalfondo']);
carnetImagePDF($pdf, $imgFondo, $tempDir, $x, $y, 20, $carnetHeight);

// Overlay de color del mes sobre la imagen izquierda
$pdf->SetAlpha(0.5);
$pdf->SetFillColor($r, $g, $b);
$pdf->Rect($x, $y, 20, $carnetHeight, 'F');
$pdf->SetAlpha(1);

// Línea decorativa vertical (vertical_principal)
$imgDerecha = "./app/views/imagenes/carnet/" . trim((string)$sede['escuela_verticalprincipal']);
carnetImagePDF($pdf, $imgDerecha, $tempDir, $x + $carnetWidth - 65, $y, 1, $carnetHeight);

// ============================================
// HEADER: LOGO Y QR
// ============================================
$logoBasePath = "./app/views/imagenes/fotos/sedes/";
$logoPath = $logoBasePath . trim((string)$sede['sede_foto']);
if(!is_file($logoPath)){
    $logoPath = $logoBasePath . "default_sede.jpg";
}
carnetImagePDF($pdf, $logoPath, $tempDir, $x + 40, $y + 2, 17, 19);

// Código QR
$qrData = "Estado pension: " . $condicion . "\n" .
          "Fecha ultimo pago: " . $fechaUltPension . "\n" .
          "Sede: " . $sede['sede_nombre'] . "\n" .
          $sede['sede_telefono'] . "\n" .
          $sede['sede_email'];

$qrFile = $tempDir . "qr_" . $alumnoid . "_" . time() . "_" . rand(1000,9999) . ".jpeg";
$image  = $generator->render_image($symbology, $qrData, $optionsQR);
imagejpeg($image, $qrFile);
imagedestroy($image);

carnetImagePDF($pdf, $qrFile, $tempDir, $x + $carnetWidth - 15, $y + 2, 12, 12);
@unlink($qrFile);

// ============================================
// FOTO DEL ALUMNO
// ============================================
$fotoPath = "./app/views/imagenes/fotos/alumno/" . $datos['alumno_imagen'];
if(!is_file($fotoPath) || empty($datos['alumno_imagen'])){
    $fotoPath = "./app/views/imagenes/fotos/alumno/alumno.jpg";
}

$fotoX      = $x + $carnetWidth - 23;
$fotoY      = $y + 20;
$fotoWidth  = 20;
$fotoHeight = 25;

carnetImagePDF($pdf, $fotoPath, $tempDir, $fotoX, $fotoY, $fotoWidth, $fotoHeight);
$pdf->SetLineWidth(0.3);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Rect($fotoX, $fotoY, $fotoWidth, $fotoHeight);

// ============================================
// INFORMACIÓN DEL ALUMNO
// ============================================
$infoX = $x + 22;
$infoY = $y + 17;

$pdf->SetFont('Arial', 'B', 6);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($infoX, $infoY);
$pdf->Cell(40, 3, 'DEPORTISTA', 0, 0, 'L');

$infoY += 4;
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY($infoX, $infoY);
if(strlen(mb_convert_encoding($datos['Nombres'] . ' ' . $datos['Apellidos'], 'ISO-8859-1', 'UTF-8')) > 21){
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
$pdf->Cell(32, 2.5, $datos['alumno_identificacion'], 0, 0, 'L');

$infoY += 3;
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY($infoX, $infoY);
$pdf->Cell(15, 2.5, 'Horario:', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($infoX + 12, $infoY);
$pdf->Cell(28, 2.5,
    mb_convert_encoding($datos['horario_nombre'], 'ISO-8859-1', 'UTF-8'),
    0, 0, 'L');

$infoY += 3;
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY($infoX, $infoY);
$pdf->Cell(15, 2.5, 'Edad:', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 7.5);
$pdf->SetXY($infoX + 10, $infoY);
$pdf->Cell(30, 2.5,
    $edad . ' ' . mb_convert_encoding('años', 'ISO-8859-1', 'UTF-8'),
    0, 0, 'L');

$infoY += 3;
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($infoX, $infoY);
$pdf->Cell(15, 2.5, 'Mes vigencia:', 0, 0, 'L');

$pdf->SetFillColor($r, $g, $b);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY($infoX + 18, $infoY - 0.5);
$pdf->Cell(20, 3,
    mb_convert_encoding($mesNombre, 'ISO-8859-1', 'UTF-8'),
    0, 0, 'C', true);

$infoY += 3;
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY($infoX, $infoY);
$pdf->Cell(15, 2.5, 'Sede:', 0, 0, 'L');
$sedeTextoX = $infoX + 10;
$sedeTextoW = max(18, $fotoX - $sedeTextoX - 1.5);
carnetCellAjustadaPDF(
    $pdf,
    $sedeTextoX,
    $infoY,
    $sedeTextoW,
    2.5,
    mb_convert_encoding($sede['sede_nombre'], 'ISO-8859-1', 'UTF-8'),
    'Arial',
    'B',
    7.2,
    4.8
);

$infoY += 3.5;
$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY($infoX, $infoY);
$pdf->Cell(40, 2, 'clubjorgeguzman@gmail.com', 0, 0, 'L');
$infoY += 3;
$pdf->SetXY($infoX, $infoY);
$pdf->Cell(40, 2, '0983779393', 0, 0, 'L');

// ============================================
// FOOTER
// ============================================
$pdf->SetDrawColor(220, 220, 220);
$pdf->Line($x + 22, $y + $carnetHeight - 2, $x + $carnetWidth - 3, $y + $carnetHeight - 2);

$pdf->SetFont('Arial', '', 5);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetXY($x + $carnetWidth - 25, $y + $carnetHeight - 4.5);
$pdf->Cell(22, 2, 'Impreso: ' . date('d/m/Y'), 0, 0, 'R');

// ============================================
// SALIDA DEL PDF
// ============================================
$nombreArchivo = 'carnet_'
    . preg_replace('/\s+/', '_', mb_convert_encoding($datos['Nombres'], 'ISO-8859-1', 'UTF-8'))
    . '_'
    . preg_replace('/\s+/', '_', mb_convert_encoding($datos['Apellidos'], 'ISO-8859-1', 'UTF-8'))
    . '.pdf';

$pdf->Output($nombreArchivo, 'I');
?>
