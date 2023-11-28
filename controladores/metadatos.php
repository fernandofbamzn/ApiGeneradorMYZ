<?php
// metadatos.php
class MetadatosController
{
    // Definir las consultas SQL como constantes de clase
    const SQL_GET_METADATOS = 'SELECT id, nombre_metadato, descripcion FROM metadatos ';
    const SQL_CREAR_METADATO = 'INSERT INTO metadatos (nombre_metadato, descripcion) VALUES (?, ?)';
    const SQL_EDITAR_METADATO = 'UPDATE metadatos SET nombre_metadato = ?, descripcion = ? WHERE id = ?';
    const SQL_ELIMINAR_METADATO = 'DELETE FROM metadatos WHERE id = ?';

    public $MostrarEchos = true;
    public $MostrarEchosErrores = false;

    public function routeRequest($mysqli, $data)
    {
        $action = $_GET['metodo'];
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($action) {
            case 'getAll':
                if ($method == 'GET') {
                    self::getMetadatos($mysqli);
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'get':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    if (isset($id)) {
                        self::getMetadato($mysqli, $id);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'crear':
                if ($method == 'POST') {
                    if (isset($data['nombre_metadato'])) {
                        $descripcion = isset($data['descripcion']) ? $data['descripcion'] : null;
                        self::crearMetadato($mysqli, $data['nombre_metadato'], $descripcion);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro nombre_metadato']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'editar':
                if ($method == 'PUT') {
                    if (isset($data['id']) && isset($data['nombre_metadato'])) {
                        $descripcion = isset($data['descripcion']) ? $data['descripcion'] : null;
                        self::editarMetadato($mysqli, $data['id'], $data['nombre_metadato'], $descripcion);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Faltan los parámetros id y/o nombre_metadato']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'eliminar':
                if ($method == 'DELETE') {
                    if (isset($data['id'])) {
                        self::eliminarMetadato($mysqli, $data['id']);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            default:
                if ($this->MostrarEchosErrores)
                    echo json_encode(['error' => 'Acción no encontrada'], JSON_UNESCAPED_UNICODE);
                return false;
        }
        return true;
    }


    // Obtener todos los metadatos
    private function getMetadatos($mysqli)
    {
        $result = $mysqli->query(self::SQL_GET_METADATOS);

        if ($result->num_rows > 0) {
            $metadatos = [];
            while ($row = $result->fetch_assoc()) {
                $metadatos[] = $row;
            }
            if ($this->MostrarEchos)
                echo json_encode($metadatos, JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'No se encontraron metadatos'], JSON_UNESCAPED_UNICODE);
        }
    }

    // Obtener un metadato por su ID
    private function getMetadato($mysqli, $id)
    {
        $stmt = $mysqli->prepare(self::SQL_GET_METADATOS . "WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $metadato = $result->fetch_assoc();
            if ($this->MostrarEchos)
                echo json_encode($metadato, JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Metadato no encontrado'], JSON_UNESCAPED_UNICODE);
        }
    }
    public function buscarMetadatoPorNombre($mysqli, $nombre_metadato)
{
    $stmt = $mysqli->prepare(self::SQL_GET_METADATOS . "WHERE nombre_metadato = ?");
    $stmt->bind_param("s", $nombre_metadato);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

    // Crear un metadato
    private function crearMetadato($mysqli, $nombre_metadato, $descripcion)
    {
        $stmt = $mysqli->prepare(self::SQL_CREAR_METADATO);
        $stmt->bind_param("ss", $nombre_metadato, $descripcion);

        if ($stmt->execute()) {
            if ($this->MostrarEchos) echo json_encode(['success' => 'Metadato creado correctamente'], JSON_UNESCAPED_UNICODE);
            return $mysqli->insert_id;
        } else {
            if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Error al crear el metadato: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
            return null;
        }
    }

    // Editar un metadato
    private function editarMetadato($mysqli, $id, $nombre_metadato, $descripcion)
    {
        $stmt = $mysqli->prepare(self::SQL_EDITAR_METADATO);
        $stmt->bind_param("ssi", $nombre_metadato, $descripcion, $id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos)
                echo json_encode(['success' => 'Metadato actualizado correctamente'], JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Error al actualizar el metadato: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }

    // Eliminar un metadato
    private function eliminarMetadato($mysqli, $id)
    {
        $stmt = $mysqli->prepare(self::SQL_ELIMINAR_METADATO);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos)
                echo json_encode(['success' => 'Metadato eliminado correctamente'], JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Error al eliminar el metadato: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }
    public function extraerMetadatos($mysqli, $rutaArchivo)
{
    //echo json_encode(['ruta' => $rutaArchivo], JSON_UNESCAPED_UNICODE);
    $partesRuta = explode("/", $rutaArchivo);

    $tematica = isset($partesRuta[1]) ? $partesRuta[1] : null;
    $unidades = isset($partesRuta[2]) ? $partesRuta[2] : null;
    $faccion = (strtolower($tematica) == 'starwars' && strtolower($unidades) == 'unidades' && isset($partesRuta[3])) ? $partesRuta[3] : null;
    

    $metadatos = [
        'Temática' => $tematica,
    ];
    $metadatos['Tipo'] = $unidades;
    if ($faccion !== null) {
        $metadatos['Facción'] = $faccion;
    }

    $metadatosIdValor = [];

    foreach ($metadatos as $nombre => $valor) {
        $metadato = $this->buscarMetadatoPorNombre($mysqli, $nombre);

        if (!$metadato) {
            $metadato = $this->crearMetadato($mysqli, $nombre, null);            
        }

        $metadatosIdValor[$metadato['id']] = $valor;
    }

    return $metadatosIdValor;
    
}


}