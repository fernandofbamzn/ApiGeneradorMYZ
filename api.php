<?php
header("Content-Type: application/json");

// Incluye manualmente los archivos de firebase/php-jwt
require_once 'libraries/php-jwt/src/BeforeValidException.php';
require_once 'libraries/php-jwt/src/ExpiredException.php';
require_once 'libraries/php-jwt/src/SignatureInvalidException.php';
require_once 'libraries/php-jwt/src/JWT.php';
require_once 'config.php';
// Incluye los archivos de controladores
$controllersDirectory = 'controladores/';

foreach (glob($controllersDirectory . "*.php") as $filename) {
    require_once $filename;
}

define('MetodoIncorrecto', 'Método HTTP incorrecto');

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
$mysqli->set_charset("utf8");

if ($mysqli->connect_error) {
    die(json_encode(["error" => "Error al conectar a la base de datos: " . $mysqli->connect_error], JSON_UNESCAPED_UNICODE));
}

$method = $_SERVER["REQUEST_METHOD"];
$request_uri = $_SERVER["REQUEST_URI"];

function getallheaders()
{
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) === 'HTTP_') {
            $header_key = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header_key] = $value;
        }
    }
    return $headers;
}

// Comprueba si el parámetro 'get' está presente en la URL. En tal caso, llama a la funcion correspondiente.

$controlador = $_GET['controlador'];

if (!isset($controlador)) {
    echo json_encode(['error' => 'Controlador no especificado']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$autController = new AutentificarController();
if ($controlador != 'autentificar') { //Comprobamos que el token sea valido.
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    if (!$autController->ValidateToken($token)) {
        echo json_encode(['error' => 'Token no válido'], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else { //Intentamos generar el token para autentificar el usuario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = isset($data['username']) ? $data['username'] : '';
        $password = isset($data['password']) ? $data['password'] : '';
        if ($username && $password) {
            $autController->AuthenticateUser($mysqli, $username, $password);
            exit;
        } else {
            echo json_encode(['error' => 'Faltan los parámetros username y/o password']);
        }
    } else {
        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
    }
}

switch ($controlador) {
    case 'personajes':
        $personajesController = new PersonajesController();
        $result = $personajesController->routeRequest($mysqli, $data);
        break;
    case 'archivos':
        $archivosController = new ArchivosController();
        $result = $archivosController->routeRequest($mysqli, $data);
        break;
    case 'imagenes':
        $imagenesController = new ImagenesController();
        $result = $imagenesController->routeRequest($mysqli, $data);
        break;
    case 'metadatos':
        $metadatosController = new MetadatosController();
        $result = $metadatosController->routeRequest($mysqli, $data);
        break;
    case 'stlMeta':
        $stlMetadatosController = new StlMetadatosController();
        $result = $stlMetadatosController->routeRequest($mysqli, $data);
        break;
    default:
        echo json_encode(['error' => 'Controlador no encontrado'], JSON_UNESCAPED_UNICODE);
        break;
}

$mysqli->close();