<?php
// imagenes.php
require_once 'clases/DirectoryHelper.php'; // Cambiar por la ruta a tu archivo DirectoryHelper.php

class ImagenesController
{
    // Definir las consultas SQL como constantes de clase
    const SQL_GET_IMAGENES = 'SELECT id, archivo_id, ruta_imagen FROM imagenes ';
    const SQL_CREAR_IMAGEN = 'INSERT INTO imagenes (archivo_id, ruta_imagen) VALUES (?, ?)';
    const SQL_EDITAR_IMAGEN = 'UPDATE imagenes SET archivo_id = ?, ruta_imagen = ? WHERE id = ?';
    const SQL_ELIMINAR_IMAGEN = 'DELETE FROM imagenes WHERE id = ?';

    public $MostrarEchos = true;
    public $MostrarEchosErrores = false;

    public function routeRequest($mysqli, $data)
    {
        $action = $_GET['metodo'];
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($action) {
            case 'getAll':
                if ($method == 'GET') {
                    self::getImagenes($mysqli);
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'get':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    if (isset($id)) {
                        self::getImagen($mysqli, $id);
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
                if ($method == 'POST') {
                    if (isset($data['archivo_id']) && isset($data['ruta_imagen'])) {
                        self::crearImagen($mysqli, $data['archivo_id'], $data['ruta_imagen']);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Faltan los parámetros archivo_id y/o ruta_imagen']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'editar':
                if ($method == 'PUT') {
                    if (isset($data['id']) && isset($data['archivo_id']) && isset($data['ruta_imagen'])) {
                        self::editarImagen($mysqli, $data['id'], $data['archivo_id'], $data['ruta_imagen']);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Faltan los parámetros id, archivo_id y/o ruta_imagen']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'eliminar':
                if ($method == 'DELETE') {
                    if (isset($data['id'])) {
                        self::eliminarImagen($mysqli, $data['id']);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'huerfanos':
                if ($method == 'GET') {

                    self::eliminarRegistrosHuerfanos($mysqli);

                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => 'Método incorrecto'], JSON_UNESCAPED_UNICODE);
                }
                break;
            default:
                if ($this->MostrarEchosErrores)
                    echo json_encode(['error' => 'Acción no encontrada'], JSON_UNESCAPED_UNICODE);
                return false;
        }
        return true;
    }

    private function getImagenes($mysqli)
    {
        $result = $mysqli->query(self::SQL_GET_IMAGENES);

        if ($result->num_rows > 0) {

            $imagenes = [];
            while ($row = $result->fetch_assoc()) {
                $imagenes[] = $row;
            }
            if ($this->MostrarEchos)
                echo json_encode($imagenes, JSON_UNESCAPED_UNICODE);
            return $imagenes;
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'No se encontraron imágenes'], JSON_UNESCAPED_UNICODE);
        }
    }

    private function getImagen($mysqli, $id)
    {
        $stmt = $mysqli->prepare(self::SQL_GET_IMAGENES . "WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $imagen = $result->fetch_assoc();
            if ($this->MostrarEchos)
                echo json_encode($imagen, JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Imagen no encontrada'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function crearImagen($mysqli, $archivo_id, $ruta_imagen)
    {
        $stmt = $mysqli->prepare(self::SQL_CREAR_IMAGEN);
        $stmt->bind_param("is", $archivo_id, $ruta_imagen);

        if ($stmt->execute()) {
            // Obtiene el ID de la imagen creada
            $imagenId = $mysqli->insert_id;
            if ($this->MostrarEchos)
                echo json_encode(['success' => 'Imagen creada correctamente'], JSON_UNESCAPED_UNICODE);
            // Devuelve el ID de la imagen creada
            return $imagenId;
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Error al crear la imagen: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
            // Devuelve null si hubo un error al crear la imagen
            return null;
        }
    }

    private function editarImagen($mysqli, $id, $archivo_id, $ruta_imagen)
    {
        $stmt = $mysqli->prepare(self::SQL_EDITAR_IMAGEN);
        $stmt->bind_param("isi", $archivo_id, $ruta_imagen, $id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos)
                echo json_encode(['success' => 'Imagen actualizada correctamente'], JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Error al actualizar la imagen: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }

    private function eliminarImagen($mysqli, $id)
    {
        $stmt = $mysqli->prepare(self::SQL_ELIMINAR_IMAGEN);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos)
                echo json_encode(['success' => 'Imagen eliminada correctamente'], JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Error al eliminar la imagen: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }

    public function eliminarRegistrosHuerfanos($mysqli)
    {
        // 1. Obtener todos los registros de imágenes de la base de datos
        // 2. Usar DirectoryHelper para obtener todas las imágenes en el directorio
        // 3. Comparar la lista de imágenes de la base de datos con la lista de imágenes del directorio
        // 4. Eliminar los registros de imágenes de la base de datos que no se encuentren en el directorio
        $directoryHelper = new DirectoryHelper();
        $this->MostrarEchos = false;
        $allImagesInDB = $this->getImagenes($mysqli);
        $allImagesInDir = $directoryHelper->getImages();

        $orphans = [];

        foreach ($allImagesInDB as $imageInDB) {
            $found = false;
            foreach ($allImagesInDir as $imageInDir) {
                if ($imageInDB['ruta_imagen'] === $imageInDir) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $orphans[] = $imageInDB['id'];
            }
        }

        $deletedCount = 0;
        foreach ($orphans as $orphanId) {
            if ($this->eliminarImagen($mysqli, $orphanId)) {
                $deletedCount++;
            }
        }
        $this->MostrarEchos = true;
        return [
            'success' => 'Se han eliminado ' . $deletedCount . ' registros huérfanos'
        ];
    }
    public function imagenExiste($mysqli, $rutaImagen)
    {
        $query = "SELECT * FROM imagenes WHERE ruta_imagen = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('s', $rutaImagen);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
    
}