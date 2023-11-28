<?php
// autentificar.php
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\JWT;

class AutentificarController {
const SQL_GET_USUARIO = 'SELECT id, usuario, contrasena, nombre, apellido, rol FROM usuarios WHERE usuario = ?';

public $MostrarEchos = true;
    public $MostrarEchosErrores = false;

public function AuthenticateUser($mysqli, $username, $password) {
    $stmt = $mysqli->prepare(self::SQL_GET_USUARIO);
    if (!$stmt) {
        die(json_encode(["error" => "Error al preparar la consulta: " . $mysqli->error], JSON_UNESCAPED_UNICODE));
    }
    $stmt->bind_param("s", $username);
    
    $stmt->execute();
    $result = $stmt->get_result();    

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Después de $stmt->bind_param("s", $username);
        if (password_verify($password, $user['contrasena'])) {
            unset($user['contrasena']);
            
            $payload = [
                'iat' => time(),
                'exp' => time() + (24 * 60 * 60), // Expira en 24 horas
                'user' => $user
            ];

            $jwt = JWT::encode($payload, JWT_SECRET);            
            if ($this->MostrarEchos) echo json_encode(['token' => $jwt]);
        } else {
            if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Contraseña incorrecta']);
        }
    } else {
        if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Usuario no encontrado']);
    }
}

public function ValidateToken($token) {
    $token = str_replace("Bearer ", "", $token);
    try {       
        $decoded = JWT::decode($token, JWT_SECRET, ['HS256']);

        // Verificar si el token tiene una fecha de caducidad (campo 'exp')
        if (!isset($decoded->exp)) {
            return false;
        }

        return true;
    } catch (DomainException $e) {
        // Aquí puedes imprimir información adicional sobre el error
        if ($this->MostrarEchosErrores) echo "DomainException: " . $e->getMessage();
        return false;
    } catch (Exception $e) {
        //echo json_encode(['Exception' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        return false;
    }
}
}