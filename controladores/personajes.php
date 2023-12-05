<?php
//personajes.php
class PersonajesController
{
    public $MostrarEchos = true;
    public $MostrarEchosErrores = true;
    // Definición de consultas SQL para operaciones con personajes

    const SQL_Proc_Habilidades = "CALL GetPjHabilidades(NULL, TRUE);";
    const SQL_Proc_Caracteristicas = "CALL GetPjCaracteristicaTrauma(NULL, TRUE);";
    const SQL_Proc_Relaciones = "CALL GetPjRelaciones(NULL, TRUE);";
    const SQL_GET_PERSONAJES = "SELECT * FROM DetallePersonaje P
    INNER JOIN PersonajeHabilidadPivot PH ON P.id_personaje=PH.id_personaje
INNER JOIN PersonajeCaracteristicaTraumaPivot PC ON P.id_personaje=PC.id_personaje
INNER JOIN RelacionPersonajePivot RP ON P.id_personaje=RP.id_personaje";

    // ... otras consultas SQL como INSERT, UPDATE, DELETE

    public function routeRequest($mysqli, $data)
    {
        $action = $_GET['metodo'];
        $method = $_SERVER['REQUEST_METHOD'];
        $parms = isset($_GET['parms']) ? $_GET['parms'] : '';

        switch ($action) {
            case 'getAll':
                if ($method == 'GET') {
                    self::getPersonajes($mysqli);
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'get':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    $nom = $_GET['nom'];
                    if (isset($id) || isset($nom)) {
                        self::getPersonaje($mysqli, $id, $nom);
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
    private function getPersonajes($mysqli)
    {
        $mysqli->query(self::SQL_Proc_Habilidades);
        $mysqli->query(self::SQL_Proc_Caracteristicas);
        $mysqli->query(self::SQL_Proc_Relaciones);
        $result = $mysqli->query(self::SQL_GET_PERSONAJES);

        if ($result->num_rows > 0) {

            $personajes = [];
            while ($row = $result->fetch_assoc()) {
                $personajes[] = $row;
            }
            if ($this->MostrarEchos)
                echo json_encode($personajes, JSON_UNESCAPED_UNICODE);
            return $personajes;
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'No se encontraron personajes'], JSON_UNESCAPED_UNICODE);
        }
    }
    private function getPersonaje($mysqli, $id, $nom)
    {
        $mysqli->query(self::SQL_Proc_Habilidades);
        $mysqli->query(self::SQL_Proc_Caracteristicas);
        $mysqli->query(self::SQL_Proc_Relaciones);
        $sql = self::SQL_GET_PERSONAJES;
        $tipoParametros = ""; // Almacena los tipos de los parámetros
        $parametros = []; // Almacena los valores de los parámetros

        // Verificar si se recibió el parámetro 'id'
        if (isset($id) && $id !== null) {
            $sql .= " WHERE P.id_personaje = ?";
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
            $sql .= " P.nombre LIKE CONCAT('%', ?, '%')";
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
            $imagen = $result->fetch_assoc();
            if ($this->MostrarEchos)
                echo json_encode($imagen, JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Personaje no encontrado'], JSON_UNESCAPED_UNICODE);
        }
    }
}
