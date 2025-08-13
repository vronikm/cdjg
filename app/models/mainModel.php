<?php
	
	namespace app\models;

	use PDO;
	use PDOException;

	if(file_exists(__DIR__."/../../config/server.php")){
		require_once __DIR__."/../../config/server.php";
	}

class mainModel
{
    private $server = DB_SERVER;
    private $db     = DB_NAME;
    private $user   = DB_USER;
    private $pass   = DB_PASS;

    protected function conectar()
    {
        try {
            $conexion = new PDO("mysql:host={$this->server};dbname={$this->db};charset=utf8", $this->user, $this->pass);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    protected function ejecutarConsulta($consulta, $parametros = [])
    {
        $sql = $this->conectar()->prepare($consulta);
        $sql->execute($parametros);
        return $sql;
    }

    public function limpiarCadena($cadena)
    {
        if (!isset($cadena)) return '';

        // Filtra caracteres HTML peligrosos
        $cadena = htmlspecialchars(trim($cadena), ENT_QUOTES, 'UTF-8');

        // Puedes aplicar expresiones para eliminar scripts, pero es mejor no depender solo de listas negras
        $cadena = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $cadena);

        return $cadena;
    }

    protected function verificarDatos($filtro, $cadena)
    {
        return !preg_match("/^$filtro$/", $cadena);
    }

    protected function guardarDatos($tabla, $datos)
    {
        $campos   = implode(',', array_column($datos, 'campo_nombre'));
        $marcas   = implode(',', array_column($datos, 'campo_marcador'));
        $query    = "INSERT INTO $tabla ($campos) VALUES ($marcas)";
        $sql      = $this->conectar()->prepare($query);

        foreach ($datos as $campo) {
            $sql->bindParam($campo["campo_marcador"], $campo["campo_valor"]);
        }

        $sql->execute();
        return $sql;
    }

    public function seleccionarDatos($tipo, $tabla, $campo, $id = null)
    {
        $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla); // evitar inyección por nombre de tabla

        if ($tipo === "Unico") {
            $sql = $this->conectar()->prepare("SELECT * FROM $tabla WHERE $campo = :ID");
            $sql->bindParam(":ID", $id);
        } elseif ($tipo === "Normal") {
            $sql = $this->conectar()->prepare("SELECT $campo FROM $tabla");
        } else {
            throw new \Exception("Tipo de consulta no válido.");
        }

        $sql->execute();
        return $sql;
    }

    protected function actualizarDatos($tabla, $datos, $condicion)
    {
        $set = [];
        foreach ($datos as $campo) {
            $set[] = "{$campo['campo_nombre']} = {$campo['campo_marcador']}";
        }

        $query = "UPDATE $tabla SET " . implode(", ", $set) . " WHERE {$condicion['condicion_campo']} = {$condicion['condicion_marcador']}";
        $sql   = $this->conectar()->prepare($query);

        foreach ($datos as $campo) {
            $sql->bindParam($campo['campo_marcador'], $campo['campo_valor']);
        }

        $sql->bindParam($condicion['condicion_marcador'], $condicion['condicion_valor']);
        $sql->execute();
        return $sql;
    }

    protected function eliminarRegistro($tabla, $campo, $id)
    {
        $sql = $this->conectar()->prepare("DELETE FROM $tabla WHERE $campo = :id");
        $sql->bindParam(":id", $id);
        $sql->execute();
        return $sql;
    }

    public function resizeImageGD($file, $maxWidth, $maxHeight, $outputFile)
    {
        [$originalWidth, $originalHeight, $imageType] = getimagesize($file);

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($file);
                break;
            default:
                return false;
        }

        $aspectRatio = $originalWidth / $originalHeight;
        if ($maxWidth / $maxHeight > $aspectRatio) {
            $newWidth = round($maxHeight * $aspectRatio);
            $newHeight = $maxHeight;
        } else {
            $newHeight = round($maxWidth / $aspectRatio);
            $newWidth = $maxWidth;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $outputFile, 80);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $outputFile, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $outputFile);
                break;
        }

        imagedestroy($image);
        imagedestroy($newImage);

        return true;
    }

}