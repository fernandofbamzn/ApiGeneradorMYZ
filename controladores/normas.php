<?php
//normas.php
class NormasController
{
    public $MostrarEchos = true;
    public $MostrarEchosErrores = true;
    // Definición de consultas SQL para operaciones con personajes

    const SQL_GET_MUTACIONES = "SELECT * FROM Mutacion";
    const SQL_GET_TALENTOS = "SELECT * FROM Talento";

    // ... otras consultas SQL como INSERT, UPDATE, DELETE

    public function routeRequest($mysqli, $data)
    {
        $action = $_GET['metodo'];
        $method = $_SERVER['REQUEST_METHOD'];
        $parms = isset($_GET['parms']) ? $_GET['parms'] : '';

        switch ($action) {
            case 'getMutacionesAll':
                if ($method == 'GET') {
                    self::getAll($mysqli, self::SQL_GET_MUTACIONES);
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getMutacion':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    $nom = $_GET['nom'];
                    if (isset($id) || isset($nom)) {
                        self::getOne($mysqli, $id, $nom, self::SQL_GET_MUTACIONES, 'id_mutacion', 'mutacion');
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
                case 'getTalentosAll':
                    if ($method == 'GET') {
                        self::getAll($mysqli, self::SQL_GET_TALENTOS);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                    }
                    break;
                case 'getTalento':
                    if ($method == 'GET') {
                        $id = $_GET['id'];
                        $nom = $_GET['nom'];
                        if (isset($id) || isset($nom)) {
                            self::getOne($mysqli, $id, $nom, self::SQL_GET_TALENTOS, 'id_talento', 'talento');
                        } else {
                            if ($this->MostrarEchosErrores)
                                echo json_encode(['error' => 'Falta el parámetro id']);
                        }
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                    }
                    break;
            case 'crear':
                // Lógica para crear un nuevo personaje
                break;
            case 'editar':
                // Lógica para editar un personaje existente
                break;
            case 'eliminar':
                // Lógica para eliminar un personaje
                break;
            // ... otros casos según sea necesario
            default:
                echo json_encode(['error' => 'Acción no encontrada'], JSON_UNESCAPED_UNICODE);
                return false;
        }
        return true;
    }

    // Implementación de los métodos específicos (getAll, get, crear, editar, eliminar)
    // ...
    private function getAll($mysqli, $sqlGet)
    {
        
        $result = $mysqli->query($sqlGet);

        if ($result->num_rows > 0) {

            $mutaciones = [];
            while ($row = $result->fetch_assoc()) {
                $mutaciones[] = $row;
            }
            if ($this->MostrarEchos)
                echo json_encode($mutaciones, JSON_UNESCAPED_UNICODE);
            return $mutaciones;
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'No se encontraron.'], JSON_UNESCAPED_UNICODE);
        }
    }
    private function getOne($mysqli, $id, $nom, $sql, $campoId, $campoNom)
    {               
        $tipoParametros = ""; // Almacena los tipos de los parámetros
        $parametros = []; // Almacena los valores de los parámetros

        // Verificar si se recibió el parámetro 'id'
        if (isset($id) && $id !== null) {
            $sql .= " WHERE {$campoId} = ?";
            $tipoParametros .= "i"; // Tipo 'integer'
            $parametros[] = &$id;
        }

        // Verificar si se recibió el parámetro 'nom'
        if (isset($nom) && $nom !== null) {
            // Si ya se ha agregado una condición, usar AND para agregar otra
            if (!empty($tipoParametros)) {
                $sql .= " AND";
            } else {
                $sql .= " WHERE";
            }
            $sql .= " {$campoNom} LIKE CONCAT('%', ?, '%')";
            $tipoParametros .= "s"; // Tipo 'string'
            $parametros[] = &$nom;
        }

        $stmt = $mysqli->prepare($sql);

        // Vincular los parámetros si es necesario
        if (!empty($tipoParametros)) {
            $stmt->bind_param($tipoParametros, ...$parametros);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $mutacion = $result->fetch_assoc();
            if ($this->MostrarEchos)
                echo json_encode($mutacion, JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Mutación no encontrada'], JSON_UNESCAPED_UNICODE);
        }
    }
}
