<?php
// stlmetadatos.php
class StlMetadatosController
{
    // Definir las consultas SQL como constantes de clase
    const SQL_GET_STL_METADATOS = 'SELECT sm.id, sm.stl_id, sm.metadato_id, mv.valor FROM stl_metadatos sm INNER JOIN metadato_valor mv ON sm.valor = mv.id ';
    const SQL_CREAR_STL_METADATO = 'INSERT INTO stl_metadatos (stl_id, metadato_id, valor_id) VALUES (?, ?, ?)';
    const SQL_GET_STL_METADATO_VALOR = 'SELECT id FROM metadato_valor WHERE LOWER(valor) = LOWER(?)';
    const SQL_CREAR_STL_METADATO_VALOR = 'INSERT INTO metadato_valor (valor) VALUES (?)';
    const SQL_EDITAR_STL_METADATO = 'UPDATE stl_metadatos SET stl_id = ?, metadato_id = ?, valor_id = ? WHERE id = ?';
    const SQL_ELIMINAR_STL_METADATO = 'DELETE FROM stl_metadatos WHERE id = ?';
    const SQL_ELIMINAR_STL_METADATO_FOR_STL = 'DELETE FROM stl_metadatos WHERE stl_id = ? and metadato_id = ?';
    const SQL_GET_ALL_FOR_STL = 'SELECT m.id, m.nombre_metadato, m.descripcion, mv.valor FROM metadatos m LEFT JOIN stl_metadatos sm ON m.id = sm.metadato_id INNER JOIN metadato_valor mv ON sm.valor_id = mv.id WHERE sm.stl_id = ?';

    public $MostrarEchos = true;
    public $MostrarEchosErrores = false;

    public function routeRequest($mysqli, $data)
    {
        $action = $_GET['metodo'];
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($action) {
            case 'getAll':
                if ($method == 'GET') {
                    self::getStlMetadatos($mysqli);
                } else {
                    if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getAllForStl':
                if ($method == 'GET') {
                    $stl_id = $_GET['stl_id'];
                    if (isset($stl_id)) {
                        self::getAllForStl($mysqli, $stl_id);
                    } else {
                        if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'Falta el parámetro stl_id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)  echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'get':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    if (isset($id)) {
                        self::getStlMetadato($mysqli, $id);
                    } else {
                        if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'crear':
                if ($method == 'POST') {
                    // Asegúrate de validar que los datos necesarios están presentes
                    self::crearStlMetadato($mysqli, $data['stl_id'], $data['metadato_id'], $data['valor']);
                } else {
                    if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'editar':
                if ($method == 'PUT') {
                    // Asegúrate de validar que los datos necesarios están presentes
                    self::editarStlMetadato($mysqli, $data['id'], $data['stl_id'], $data['metadato_id'], $data['valor']);
                } else {
                    if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'eliminar':
                if ($method == 'DELETE') {
                    if (isset($data['id'])) {
                        self::eliminarStlMetadato($mysqli, $data['id']);
                    } else {
                        if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)  echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            default:
                if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'Acción no encontrada'], JSON_UNESCAPED_UNICODE);
                return false;
        }
        return true;
    }

    // Obtener todos los registros de stl_metadatos
    private function getStlMetadatos($mysqli)
    {
        $result = $mysqli->query(self::SQL_GET_STL_METADATOS);

        if ($result->num_rows > 0) {
            $stl_metadatos = [];
            while ($row = $result->fetch_assoc()) {
                $stl_metadatos[] = $row;
            }
            if ($this->MostrarEchos)  echo json_encode($stl_metadatos, JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'No se encontraron registros en stl_metadatos'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getAllForStl($mysqli, $stl_id) {
        $stmt = $mysqli->prepare(self::SQL_GET_ALL_FOR_STL);
        $stmt->bind_param("i", $stl_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $metadatos = [];
            while ($row = $result->fetch_assoc()) {
                $metadatos[] = $row;
            }
            if ($this->MostrarEchos)  echo json_encode($metadatos, JSON_UNESCAPED_UNICODE);
            return $metadatos;
        } else {
            if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'No se encontraron metadatos para el STL'], JSON_UNESCAPED_UNICODE);
        }
    }

    // Obtener un registro de stl_metadatos por su ID
    private function getStlMetadato($mysqli, $id)
    {
        $stmt = $mysqli->prepare(self::SQL_GET_STL_METADATOS . "WHERE sm.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stl_metadato = $result->fetch_assoc();
            if ($this->MostrarEchos)  echo json_encode($stl_metadato, JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'Registro no encontrado'], JSON_UNESCAPED_UNICODE);
        }
    }

    // Crear un registro en stl_metadatos
    public function crearStlMetadato($mysqli, $stl_id, $metadato_id, $valor)
    {
        $valor_id = $this->buscarOInsertarValor($mysqli, $valor);
        if ($valor_id === null) {
            return false;
        }
        $stmt = $mysqli->prepare(self::SQL_CREAR_STL_METADATO);
        $stmt->bind_param("iii", $stl_id, $metadato_id, $valor_id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos)  echo json_encode(['success' => 'Registro creado correctamente'], JSON_UNESCAPED_UNICODE);
            return true;
        } else {
            if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'Error al crear el registro: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
        return false;
    }
    private function buscarOInsertarValor($mysqli, $valor) {
        $valor = trim($valor);
    
        $stmt = $mysqli->prepare(self::SQL_GET_STL_METADATO_VALOR);
        $stmt->bind_param('s', $valor);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        } else {
            $stmt = $mysqli->prepare(self::SQL_CREAR_STL_METADATO_VALOR);
            $stmt->bind_param('s', $valor);
            if ($stmt->execute()) {
                return $mysqli->insert_id;
            } else {
                if ($this->MostrarEchosErrores) {
                    echo json_encode(['error' => 'Error al insertar el valor: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
                }
                return null;
            }
        }
    }
    

    // Editar un registro en stl_metadatos
    private function editarStlMetadato($mysqli, $id, $stl_id, $metadato_id, $valor)
    {
        $valor_id = $this->buscarOInsertarValor($mysqli, $valor);
        if ($valor_id === null) {
            return;
        }

        $stmt = $mysqli->prepare(self::SQL_EDITAR_STL_METADATO);
        $stmt->bind_param("iiii", $stl_id, $metadato_id, $valor_id, $id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos)  echo json_encode(['success' => 'Registro actualizado correctamente'], JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)  echo json_encode(['error' => 'Error al actualizar el registro: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }

    // Eliminar un registro en stl_metadatos
    public function eliminarStlMetadato($mysqli, $id)
    {
        $stmt = $mysqli->prepare(self::SQL_ELIMINAR_STL_METADATO);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos)  echo json_encode(['success' => 'Registro eliminado correctamente. ' . $id], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['error' => 'Error al eliminar el registro: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }
    public function eliminarStlMetadatoArchivo($mysqli, $idStl, $idMeta)
    {
        $stmt = $mysqli->prepare(self::SQL_ELIMINAR_STL_METADATO_FOR_STL);
        $stmt->bind_param("ii", $idStl, $idMeta);

        if ($stmt->execute()) {
            if ($this->MostrarEchos)  echo json_encode(['success' => 'Registro eliminado correctamente. ' . $idStl .'-' . $idMeta], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['error' => 'Error al eliminar el registro: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }

}