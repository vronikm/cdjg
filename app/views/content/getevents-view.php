<?php
	header('Content-Type: application/json');
    // Conexión a la base de datos
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "digitech_soccereasy";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }else {
        echo "Conexión exitosa";
    }

    // Consulta para obtener los eventos
    $sql = "SELECT asistencia_alumnoid AS id, asistencia_D28 AS title, 
                    concat(Anio, '-', Mes, '-', Dia) AS start, 
                    concat(Anio, '-', Mes, '-', Dia) AS end
                FROM (
                SELECT asistencia_alumnoid, asistencia_D28, substring(asistencia_aniomes, 1, 4) Anio, substring(asistencia_aniomes, 5, 2) Mes, 28 Dia
                    FROM asistencia_asistencia
                    WHERE asistencia_D28 IS NOT null
                        and asistencia_alumnoid =302
				) as FECHA";
    $result = $conn->query($sql);

    $eventos = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $eventos[] = $row;
        }
    }

    header('Content-Type: application/json');
    // Convertir a JSON y enviar la respuesta
    echo json_encode($eventos, JSON_PRETTY_PRINT);


    $conn->close();

