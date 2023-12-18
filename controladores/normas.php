<?php
//normas.php
class NormasController
{
    public $MostrarEchos = true;
    public $MostrarEchosErrores = true;
    // Definición de consultas SQL para operaciones con personajes

    const SQL_GET_MUTACIONES = "SELECT Mutacion.*, NotaTabla.nota FROM Mutacion 
        LEFT JOIN NotaTablaRegistro ntr on Mutacion.id_mutacion = ntr.id_relacionado and ntr.tabla_relacionada = 'Mutacion'
        LEFT JOIN NotaTabla ON ntr.id_nota = NotaTabla.id_nota";
    const SQL_GET_TALENTOS = "SELECT Talento.*, Rol.nombre_rol FROM Talento LEFT JOIN Rol ON Talento.id_rol = Rol.id_rol";
    const SQL_GET_ARMAS = "SELECT Arma.*, TipoArma.tipo FROM Arma INNER JOIN TipoArma ON Arma.id_tipo = TipoArma.id_tipo";
    const SQL_GET_ARTESFACTOS = "SELECT * FROM Artefacto";
    const SQL_GET_CARACTERISTICAS = "SELECT * FROM Caracteristica";
    const SQL_GET_CHATARRAS = "SELECT * FROM Chatarra";
    const SQL_GET_HABILIDADES = "SELECT Habilidad.id_habilidad, Habilidad.habilidad, Caracteristica.id_caracteristica, Caracteristica.caracteristica , HabilidadUtilidad.id_habilidad_utilidad, HabilidadUtilidad.descripcion, HabilidadUtilidad.exito, HabilidadUtilidad.fallo, HabilidadUtilidadProeza.id_proeza, HabilidadUtilidadProeza.descripcion as descripcion_proeza 
    FROM Habilidad LEFT JOIN HabilidadUtilidad ON Habilidad.id_habilidad = HabilidadUtilidad.id_habilidad 
    LEFT JOIN HabilidadUtilidadProeza ON HabilidadUtilidad.id_habilidad_utilidad = HabilidadUtilidadProeza.id_habilidad_utilidad
    INNER JOIN Caracteristica ON Habilidad.id_caracteristica = Caracteristica.id_caracteristica";
    const SQL_GET_HERIDAS_CRITICAS = "SELECT * FROM HeridaCritica";
    // ... otras consultas SQL como INSERT, UPDATE, DELETE

    public function routeRequest($mysqli, $data)
    {
        $action = $_GET['metodo'];
        $method = $_SERVER['REQUEST_METHOD'];
        $parms = isset($_GET['parms']) ? $_GET['parms'] : '';

        switch ($action) {
            case 'getMutacionAll':
                if ($method == 'GET') {
                    self::getAll($mysqli, self::SQL_GET_MUTACIONES . ' ORDER BY Mutacion.id_mutacion');
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
            case 'getTalentoAll':
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
            case 'getArmaAll':
                if ($method == 'GET') {
                    self::getAll($mysqli, self::SQL_GET_ARMAS);
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getArma':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    $nom = $_GET['nom'];
                    if (isset($id) || isset($nom)) {
                        self::getOne($mysqli, $id, $nom, self::SQL_GET_ARMAS, 'id_arma', 'arma');
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getArtefactoAll':
                if ($method == 'GET') {
                    self::getAll($mysqli, self::SQL_GET_ARTESFACTOS);
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getArtefacto':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    $nom = $_GET['nom'];
                    if (isset($id) || isset($nom)) {
                        self::getOne($mysqli, $id, $nom, self::SQL_GET_ARTESFACTOS, 'id_artefacto', 'artefacto');
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getCaracteristicaAll':
                if ($method == 'GET') {
                    self::getAll($mysqli, self::SQL_GET_CARACTERISTICAS);
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getCaracteristica':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    $nom = $_GET['nom'];
                    if (isset($id) || isset($nom)) {
                        self::getOne($mysqli, $id, $nom, self::SQL_GET_CARACTERISTICAS, 'id_caracteristica', 'caracteristica');
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getChatarraAll':
                if ($method == 'GET') {
                    self::getAll($mysqli, self::SQL_GET_CHATARRAS);
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getChatarra':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    $nom = $_GET['nom'];
                    if (isset($id) || isset($nom)) {
                        self::getOne($mysqli, $id, $nom, self::SQL_GET_CHATARRAS, 'id_chatarra', 'chatarra');
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getHabilidadAll':

                if ($method == 'GET') {
                    //Almaceno el valor de $MostrarEchos en una variable temporal y la pongo a false para que getAll no recupere los datos
                    $temp = $this->MostrarEchos;
                    $this->MostrarEchos = false;
                    $habilidadesTabla = self::getAll($mysqli, self::SQL_GET_HABILIDADES);
                    //devuelvo $MostrarEchos a su valor original
                    $this->MostrarEchos = $temp;
                    $habilidadesTabla = self::formateaHabilidades($habilidadesTabla);
                    if ($this->MostrarEchos) {
                        echo json_encode(array_values($habilidadesTabla), JSON_UNESCAPED_UNICODE);
                    }

                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getHabilidad':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    $nom = $_GET['nom'];
                    if (isset($id) || isset($nom)) {
                        //Almaceno el valor de $MostrarEchos en una variable temporal y la pongo a false para que getAll no recupere los datos
                        $temp = $this->MostrarEchos;
                        $this->MostrarEchos = false;
                        $habilidadesTabla = self::getOne($mysqli, $id, $nom, self::SQL_GET_HABILIDADES, 'Habilidad.id_habilidad', 'Habilidad.habilidad', false);
                        //devuelvo $MostrarEchos a su valor original
                        $this->MostrarEchos = $temp;
                        $habilidadesTabla = self::formateaHabilidades($habilidadesTabla);
                        if ($this->MostrarEchos) {
                            echo json_encode(array_values($habilidadesTabla), JSON_UNESCAPED_UNICODE);
                        }
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getHeridaCriticaAll':
                if ($method == 'GET') {
                    self::getAll($mysqli, self::SQL_GET_HERIDAS_CRITICAS);
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'getHeridaCritica':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    $nom = $_GET['nom'];
                    if (isset($id) || isset($nom)) {
                        self::getOne($mysqli, $id, $nom, self::SQL_GET_HERIDAS_CRITICAS, 'id_heridacritica', 'herida');
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

                break;
            case 'editar':

                break;
            case 'eliminar':

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

            $elementos = [];
            while ($row = $result->fetch_assoc()) {
                $elementos[] = $row;
            }
            if ($this->MostrarEchos)
                echo json_encode($elementos, JSON_UNESCAPED_UNICODE);
            return $elementos;
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'No se encontraron.'], JSON_UNESCAPED_UNICODE);
        }
    }
    private function getOne($mysqli, $id, $nom, $sql, $campoId, $campoNom, $getOnlyFirst = true)
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
            if ($getOnlyFirst) {
                $elemento = $result->fetch_assoc();
                if ($this->MostrarEchos)
                    echo json_encode($elemento, JSON_UNESCAPED_UNICODE);
                return $elemento;
            } else {
                $elementos = [];
                while ($row = $result->fetch_assoc()) {
                    $elementos[] = $row;
                }
                if ($this->MostrarEchos)
                    echo json_encode($elementos, JSON_UNESCAPED_UNICODE);
                return $elementos;
            }
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => $campoNom + ' no encontrado'], JSON_UNESCAPED_UNICODE);
        }
    }
    private function formateaHabilidades($habilidadesTabla)
    {
        $habilidades = array();

        foreach ($habilidadesTabla as $fila) {
            $habilidadId = $fila['id_habilidad'];
            $utilidadId = $fila['id_habilidad_utilidad'];
            $proezaId = $fila['id_proeza'];

            if (!isset($habilidades[$habilidadId])) {
                $habilidades[$habilidadId] = array(
                    'id_habilidad' => $fila['id_habilidad'],
                    'habilidad' => $fila['habilidad'],
                    'id_caracteristica' => $fila['id_caracteristica'],
                    'caracteristica' => $fila['caracteristica'],
                    'utilidades' => array());
            }

            if (!isset($habilidades[$habilidadId]['utilidades'][$utilidadId])) {
                $habilidades[$habilidadId]['utilidades'][$utilidadId] = array(
                    'id_habilidad_utilidad' => $fila['id_habilidad_utilidad'],
                    'descripcion' => $fila['descripcion'],
                    'exito' => $fila['exito'],
                    'fallo' => $fila['fallo'],
                    'proezas' => array());
            }
            if ($proezaId != null) {
                $habilidades[$habilidadId]['utilidades'][$utilidadId]['proezas'][$proezaId] = array(
                    'id_proeza' => $fila['id_proeza'],
                    'descripcion_proeza' => $fila['descripcion_proeza']);
            }
        }
        return $habilidades;
    }
}
