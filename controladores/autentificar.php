<?php
// autentificar.php
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\JWT;

class AutentificarController {
public $MostrarEchos = true;
    public $MostrarEchosErrores = false;

public function AuthenticateUser($mysqli, $username, $password) {
         
    if ($username == "Fernando" && $password == "dwKd31aw8wui44dluV7") {
        
            
            $payload = [
                'iat' => time(),                
                'user' => $username
            ];//'exp' => time() + (24 * 60 * 60), // Expira en 24 horas

            $jwt = JWT::encode($payload, JWT_SECRET);            
            if ($this->MostrarEchos) echo json_encode(['token' => $jwt]);
        
    } else {
        if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Usuario no encontrado']);
    }
}

public function ValidateToken($token) {
    $token = str_replace("Bearer ", "", $token);
    try {       
        $decoded = JWT::decode($token, JWT_SECRET, ['HS256']);
        return true;
        // Verificar si el token tiene una fecha de caducidad (campo 'exp')
        if (!isset($decoded->exp)) {
            return false;
        }        
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