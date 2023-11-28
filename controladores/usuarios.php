<?php
// usuarios.php

class UsuariosController {
    // Definir las consultas SQL como constantes de clase
    const SQL_GET_USUARIO = 'SELECT id, usuario, nombre, apellido, rol FROM usuarios ';
    const SQL_CREAR_USUARIO = 'INSERT INTO usuarios (usuario, contrasena, nombre, apellido, rol) VALUES (?, ?, ?, ?, ?)';
    const SQL_EDITAR_USUARIO = 'UPDATE usuarios SET usuario = ?, nombre = ?, apellido = ?, rol = ? WHERE id = ?';
    const SQL_ELIMINAR_USUARIO = 'DELETE FROM usuarios WHERE id = ?';
    const SQL_CAMBIAR_CONTRASENA = 'UPDATE usuarios SET contrasena = ? WHERE id = ?';

    public $MostrarEchos = true;
    public $MostrarEchosErrores = false;

    /**
     * Summary of usuarios\routeRequest     
     * @param mixed $mysqli
     * @param mixed $data
     * @return bool
     */
    public function routeRequest($mysqli, $data){
        $action = $_GET['metodo'];
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($action) {
            case 'getAll':
                if ($method == 'GET') {
                    self::getUsuarios($mysqli);
                } else {
                    if ($this->MostrarEchosErrores) echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'get':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    $user = $_GET['user'];
                    if (isset($id)) {
                        self::getUsuario($mysqli, $id);
                    } elseif (isset($user)) {
                        self::getUsuarioFromUser($mysqli, $user);
                    } else {
                        if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Falta el parámetro id o user']);
                    }
                } else {
                    if ($this->MostrarEchosErrores) echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'crear':
                if ($method == 'POST') {
                    if (isset($data['usuario']) && isset($data['contrasena']) && isset($data['nombre']) && isset($data['apellido']) && isset($data['rol'])) {
                        self::crearUsuario($mysqli, $data['usuario'], $data['contrasena'], $data['nombre'], $data['apellido'], $data['rol']);
                    } else {
                        if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Faltan los parámetros usuario, contrasena, nombre, apellido y/o rol']);
                    }
                } else {
                    if ($this->MostrarEchosErrores) echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'editar':
                if ($method == 'PUT') {
                    if (isset($data['id']) && isset($data['usuario']) && isset($data['nombre']) && isset($data['apellido']) && isset($data['rol'])) {
                        self::editarUsuario($mysqli, $data['id'], $data['usuario'], $data['nombre'], $data['apellido'], $data['rol']);
                    } else {
                        if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Faltan los parámetros id, usuario, nombre, apellido y/o rol']);
                    }
                } else {
                    if ($this->MostrarEchosErrores) echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'cambiarContrasena':
                if ($method == 'PUT') {
                    if (isset($data['id']) && isset($data['nueva_contrasena'])) {
                        self::cambiarContrasena($mysqli, $data['id'], $data['nueva_contrasena']);
                    } else {
                        if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Faltan los parámetros id, contrasena_actual y/o nueva_contrasena']);
                    }
                } else {
                    if ($this->MostrarEchosErrores) echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'eliminar':
                if ($method == 'DELETE') {
                    if (isset($data['id'])) {
                        self::eliminarUsuario($mysqli, $data['id']);
                    } else {
                        if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores) echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            default:
                if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Acción no encontrada'], JSON_UNESCAPED_UNICODE);
                return false;
        }
        return true;
    }

    private function getUsuarios($mysqli)
    {
        // La función getUsuarios ya implementada aquí        
        $result = $mysqli->query(self::SQL_GET_USUARIO);

        if ($result->num_rows > 0) {
            $usuarios = [];
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
            if ($this->MostrarEchos) echo json_encode($usuarios, JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores) echo json_encode(['error' => 'No se encontraron usuarios'], JSON_UNESCAPED_UNICODE);
        }
    }

    // Obtener un usuario por su ID
    private function getUsuario($mysqli, $id)
    {
        $stmt = $mysqli->prepare(self::SQL_GET_USUARIO . "WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($this->MostrarEchos) echo json_encode($user, JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Usuario no encontrado'], JSON_UNESCAPED_UNICODE);
        }
    }
    // Obtener un usuario por su USER
    private function getUsuarioFromUser($mysqli, $user)
    {
        $stmt = $mysqli->prepare(self::SQL_GET_USUARIO . "WHERE usuario = ?");
        $stmt->bind_param("i", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($this->MostrarEchos) echo json_encode($user, JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Usuario no encontrado'], JSON_UNESCAPED_UNICODE);
        }
    }

    // Crear un usuario
    private function crearUsuario($mysqli, $usuario, $password, $nombre, $apellido, $rol)
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare(self::SQL_CREAR_USUARIO);
        $stmt->bind_param("sssss", $usuario, $hashed_password, $nombre, $apellido, $rol);

        if ($stmt->execute()) {
            if ($this->MostrarEchos) echo json_encode(['success' => 'Usuario creado correctamente'], JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Error al crear el usuario: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }

    // Editar un usuario
    private function editarUsuario($mysqli, $id, $usuario, $nombre, $apellido, $rol)
    {
        $stmt = $mysqli->prepare(self::SQL_EDITAR_USUARIO);
        $stmt->bind_param("ssssi", $usuario, $nombre, $apellido, $rol, $id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos) echo json_encode(['success' => 'Usuario actualizado correctamente'], JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Error al actualizar el usuario: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }

    // Eliminar un usuario
    private function eliminarUsuario($mysqli, $id)
    {
        $stmt = $mysqli->prepare(self::SQL_ELIMINAR_USUARIO);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos) echo json_encode(['success' => 'Usuario eliminado correctamente'], JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Error al eliminar el usuario: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }

    private function cambiarContrasena($mysqli, $id, $new_password)
    {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare(self::SQL_CAMBIAR_CONTRASENA);
        $stmt->bind_param("si", $hashed_password, $id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos) echo json_encode(['success' => 'Contraseña actualizada correctamente'], JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores) echo json_encode(['error' => 'Error al actualizar la contraseña: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }
}