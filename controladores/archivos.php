<?php
// archivos.php
require_once 'clases/DirectoryHelper.php'; // Cambiar por la ruta a tu archivo DirectoryHelper.php

class ArchivosController
{
    public $MostrarEchos = true;
    public $MostrarEchosErrores = true;

    // Definir las consultas SQL como constantes de clase
    const SQL_GET_ARCHIVOS = 'SELECT id, nombre, extension, ruta_fichero, fecha_creacion, fecha_modificacion FROM archivos ';
    const SQL_CREAR_ARCHIVO = 'INSERT INTO archivos (nombre, extension, ruta_fichero, fecha_creacion, fecha_modificacion) VALUES (?, ?, ?, NOW(), NOW())';
    const SQL_EDITAR_ARCHIVO = 'UPDATE archivos SET nombre = ?, extension = ?, ruta_fichero = ?, fecha_modificacion = NOW() WHERE id = ?';
    const SQL_ELIMINAR_ARCHIVO = 'DELETE FROM archivos WHERE id = ?';
    const SQL_BUSCAR = "SELECT a.id, a.ruta_fichero, a.nombre, a.extension,  a.fecha_creacion, a.fecha_modificacion, i.ruta_imagen, m.nombre_metadato, m.descripcion as descripcion_metadato, mv.valor
    FROM archivos a
    LEFT JOIN imagenes i ON a.id = i.archivo_id
    LEFT JOIN stl_metadatos sm ON a.id = sm.stl_id
    LEFT JOIN metadatos m ON sm.metadato_id = m.id
    LEFT JOIN metadato_valor mv on sm.valor_id = mv.id
    WHERE a.nombre LIKE CONCAT('%', ?, '%')
    OR a.ruta_fichero LIKE CONCAT('%', ?, '%')
    OR i.ruta_imagen LIKE CONCAT('%', ?, '%')
    OR m.nombre_metadato LIKE CONCAT('%', ?, '%')
    OR mv.valor LIKE CONCAT('%', ?, '%')";
    public function routeRequest($mysqli, $data)
    {
        $action = $_GET['metodo'];
        $method = $_SERVER['REQUEST_METHOD'];
        $parms = isset($_GET['parms']) ? $_GET['parms'] : '';
        switch ($action) {
            case 'getAll':
                if ($method == 'GET') {
                    self::getArchivos($mysqli);
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'get':
                if ($method == 'GET') {
                    $id = $_GET['id'];
                    if (isset($id)) {
                        self::getArchivo($mysqli, $id);
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
                    if (isset($data['nombre']) && isset($data['extension']) && isset($data['ruta_fichero'])) {
                        self::crearArchivo($mysqli, $data['nombre'], $data['extension'], $data['ruta_fichero']);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Faltan los parámetros nombre, tipo y/o url']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'editar':
                if ($method == 'PUT') {
                    if (isset($data['id']) && isset($data['nombre']) && isset($data['extension']) && isset($data['ruta_fichero'])) {
                        self::editarArchivo($mysqli, $data['id'], $data['nombre'], $data['extension'], $data['ruta_fichero']);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Faltan los parámetros id, nombre, tipo y/o url']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;

            case 'eliminar':
                if ($method == 'DELETE') {
                    if (isset($data['id'])) {
                        self::eliminarArchivo($mysqli, $data['id']);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro id']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'buscar':
                if ($method == 'GET') {
                    $busqueda = $_GET['busqueda'];
                    if (isset($busqueda)) {
                        self::buscar($mysqli, $busqueda);
                    } else {
                        if ($this->MostrarEchosErrores)
                            echo json_encode(['error' => 'Falta el parámetro busqueda']);
                    }
                } else {
                    if ($this->MostrarEchosErrores)
                        echo json_encode(['error' => MetodoIncorrecto], JSON_UNESCAPED_UNICODE);
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
            case 'sincronizar':
                if ($method == 'GET') {
                    $this->MostrarEchos = false;
                    if (empty($parms) || strpos($parms, 'H') !== false) {
                        self::sincronizarHuerfanos($mysqli);
                    }
                    if (empty($parms) || strpos($parms, 'D') !== false) {
                        self::sincronizarDirectorio($mysqli);
                    }
                    if (empty($parms) || strpos($parms, 'M') !== false) {
                        self::sincronizarMetadatos($mysqli);
                    }
                    if (empty($parms) || strpos($parms, 'I') !== false) {
                        self::sincronizarImagenes($mysqli);
                    }
                    $this->MostrarEchos = true;
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

    // Obtener todos los archivos
    private function getArchivos($mysqli)
    {
        $result = $mysqli->query(self::SQL_GET_ARCHIVOS);

        if ($result->num_rows > 0) {
            $archivos = [];
            while ($row = $result->fetch_assoc()) {
                $archivos[] = $row;
            }
            if ($this->MostrarEchos)
                echo json_encode($archivos, JSON_UNESCAPED_UNICODE);
            return $archivos;
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'No se encontraron archivos'], JSON_UNESCAPED_UNICODE);
        }
    }

    // Obtener un archivo por su ID
    private function getArchivo($mysqli, $id)
    {
        $stmt = $mysqli->prepare(self::SQL_GET_ARCHIVOS . "WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $archivo = $result->fetch_assoc();
            if ($this->MostrarEchos)
                echo json_encode($archivo, JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Archivo no encontrado'], JSON_UNESCAPED_UNICODE);
        }
    }

    private function buscar($mysqli, $busqueda)
    {
        $stmt = $mysqli->prepare(self::SQL_BUSCAR);
        if ($stmt === false) {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Error al preparar la consulta: ' . $mysqli->error]);
            return [];
        }

        $stmt->bind_param("sssss", $busqueda, $busqueda, $busqueda, $busqueda, $busqueda);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $archivos = [];
            while ($row = $result->fetch_assoc()) {
                $archivos[] = $row;
            }
            if ($this->MostrarEchos)
                echo json_encode($archivos, JSON_UNESCAPED_UNICODE);
            return $archivos;
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'No se encontraron archivos para la búsqueda'], JSON_UNESCAPED_UNICODE);
            return [];
        }
    }

    // Crear un archivo
    private function crearArchivo($mysqli, $nombre, $tipo, $ruta)
    {
        $stmt = $mysqli->prepare(self::SQL_CREAR_ARCHIVO);
        $stmt->bind_param("sss", $nombre, $tipo, $ruta);

        if ($stmt->execute()) {
            // Obtiene el ID del archivo creado
            $archivoId = $mysqli->insert_id;
            if ($this->MostrarEchos) {
                echo json_encode(['success' => 'Archivo creado correctamente: '] . $nombre, JSON_UNESCAPED_UNICODE);
            }
            // Devuelve el ID del archivo creado
            return $archivoId;
        } else {
            if ($this->MostrarEchosErrores) {
                echo json_encode(['error' => 'Error al crear el archivo: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
            }
            return null;
        }
    }

    // Editar un archivo
    private function editarArchivo($mysqli, $id, $nombre, $ruta, $tipo)
    {
        $stmt = $mysqli->prepare(self::SQL_EDITAR_ARCHIVO);
        $stmt->bind_param("sssi", $nombre, $tipo, $ruta, $id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos)
                echo json_encode(['success' => 'Archivo actualizado correctamente'], JSON_UNESCAPED_UNICODE);
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Error al actualizar el archivo: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        }
    }

    // Eliminar un archivo
    private function eliminarArchivo($mysqli, $id)
    {
        // Crear una instancia de StlMetadatosController
        $stlMetadatosController = new StlMetadatosController();
        $stlMetadatosController->MostrarEchos = $this->MostrarEchos;
        $stlMetadatosController->MostrarEchosErrores = $this->MostrarEchosErrores;

        // Obtener registros asociados en la tabla stl_metadatos
        $metadatos = $stlMetadatosController->getAllForStl($mysqli, $id);

        // Eliminar registros asociados en la tabla stl_metadatos
        foreach ($metadatos as $metadato) {
            $stlMetadatosController->eliminarStlMetadatoArchivo($mysqli, $id, $metadato['id']);
        }

        $stmt = $mysqli->prepare(self::SQL_ELIMINAR_ARCHIVO);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if ($this->MostrarEchos)
                echo json_encode(['success' => 'Archivo eliminado correctamente'], JSON_UNESCAPED_UNICODE);
            return true;
        } else {
            if ($this->MostrarEchosErrores)
                echo json_encode(['error' => 'Error al eliminar el archivo: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
            return false;
        }
    }

    public function eliminarRegistrosHuerfanos($mysqli)
    {
        // 1. Obtener todos los registros de archivos de la base de datos
        // 2. Usar DirectoryHelper para obtener todos los archivos en el directorio
        // 3. Comparar la lista de archivos de la base de datos con la lista de archivos del directorio
        // 4. Eliminar los registros de archivos de la base de datos que no se encuentren en el directorio
        $directoryHelper = new DirectoryHelper();
        $this->MostrarEchos = false;
        $allFilesInDB = $this->getArchivos($mysqli);
        $allFilesInDir = $directoryHelper->getFiles();

        $orphans = [];

        foreach ($allFilesInDB as $fileInDB) {
            $found = false;
            foreach ($allFilesInDir as $fileInDir) {
                if ($fileInDB['ruta_fichero'] === $fileInDir) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $orphans[] = $fileInDB['id'];
            }
        }

        $deletedCount = 0;
        foreach ($orphans as $orphanId) {
            if ($this->eliminarArchivo($mysqli, $orphanId)) {
                $deletedCount++;
            }
        }
        $this->MostrarEchos = true;
        $resultado = 'Se han eliminado ' . $deletedCount . ' registros huérfanos';
        echo json_encode(['success' => $resultado]);

        return [
            'success' => $resultado
        ];
    }

    public function archivoExiste($mysqli, $ruta)
    {
        $query = "SELECT * FROM archivos WHERE ruta_fichero = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('s', $ruta);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }


    public function sincronizarHuerfanos($mysqli)
    {
        /**/
        if ($this->MostrarEchos)
            echo json_encode(['debug' => 'Hacemos una primera limpieza de los registros huerfanos en la BD.']);
        $this->eliminarRegistrosHuerfanos($mysqli);
        $limpiaImagenes = new ImagenesController();
        $limpiaImagenes->eliminarRegistrosHuerfanos($mysqli);
    }

    // Método para sincronizar el directorio
    public function sincronizarDirectorio($mysqli)
    {
        $directoryHelper = new DirectoryHelper();
        $countAdd = 0;
        if ($this->MostrarEchos)
            echo json_encode(['debug' => '***ARCHIVOS***']);

        // Obtén todos los archivos que no son imágenes
        $archivos = $directoryHelper->getFiles(['stl', 'zip', 'rar', '7z', 'tar']);

        // Inserta archivos en la base de datos y crea metadatos
        foreach ($archivos as $rutaArchivo) {
            $pathInfo = pathinfo($rutaArchivo);
            $nombre = $pathInfo['filename'];
            $extension = $pathInfo['extension'];
            //if ($this->MostrarEchos)
            //    echo json_encode(['debug' => '--Analizando: ' . $nombre . '.' . $extension . ' -> ' . $rutaArchivo]);
            // Verifica si el archivo ya existe en la base de datos
            if (!$this->archivoExiste($mysqli, $rutaArchivo)) {
                $archivoId = $this->crearArchivo($mysqli, $nombre, $extension, $rutaArchivo);
                $countAdd++;
                if ($this->MostrarEchos)
                    echo json_encode(['debug' => '   No existia en bd, y lo hemos creado: ' . $archivoId]);
            }

        }
        echo json_encode(['info' => '****Ficheros añadidos: ' . $countAdd]);
    }
    public function sincronizarMetadatos($mysqli)
    {
        $countAdd = 0;
        if ($this->MostrarEchos)
            echo json_encode(['debug' => '***Metadatos***']);

        $this->MostrarEchos = false;
        // Obtén todos los archivos que no son imágenes
        $archivos = $this->getArchivos($mysqli);

        // Inserta archivos en la base de datos y crea metadatos
        foreach ($archivos as $rutaArchivo) {

            $ruta = $rutaArchivo['ruta_fichero'];

            // Verifica si el archivo ya existe en la base de datos
            $metadatosController = new MetadatosController();
            $metadatos = $metadatosController->extraerMetadatos($mysqli, $ruta);
            if ($this->MostrarEchos)
                echo json_encode(['debug' => '   Le añadimos estos metadatos ' . json_encode($metadatos)]);
            $stlMetadatosController = new StlMetadatosController();
            foreach ($metadatos as $metadatoId => $valor) {
                if ($stlMetadatosController->crearStlMetadato($mysqli, $rutaArchivo['id'], $metadatoId, $valor)) {
                    $countAdd++;
                }
            }
        }
        $this->MostrarEchos = true;
        echo json_encode(['info' => '****STL_Metadatos añadidos: ' . $countAdd]);
    }
    public function sincronizarImagenes($mysqli)
    {
        $countAdd = 0;
        $directoryHelper = new DirectoryHelper();
        if ($this->MostrarEchos)
            echo json_encode(['debug' => '***IMAGENES***']);
        // Obtén todas las imágenes
        $imagenes = $directoryHelper->getImages();

        // Inserta imágenes en la base de datos y relaciona con archivos
        $imagenesController = new ImagenesController();
        foreach ($imagenes as $rutaImagen) {
            //if ($this->MostrarEchos)
            //    echo json_encode(['debug' => '--Analizando: ' . $rutaImagen]);
            // Verifica si la imagen ya existe en la base de datos
            if (!$imagenesController->imagenExiste($mysqli, $rutaImagen)) {
                $archivoId = $this->obtenerArchivoIdPorImagen($mysqli, $rutaImagen);
                //if ($archivoId !== null) {
                if ($this->MostrarEchos)
                    echo json_encode(['debug' => '   Lo asociamos a : ' . $archivoId]);
                // Crea la imagen y obtiene su ID (la imagen ya se relaciona con el archivo al llamar a crearImagen)
                $imagenesController->crearImagen($mysqli, $archivoId, $rutaImagen);
                $countAdd++;
                //}
            }
        }
        echo json_encode(['info' => '****Imagenes añadidos: ' . $countAdd]);
    }

    private function obtenerArchivoIdPorImagen($mysqli, $rutaImagen)
    {
        $this->MostrarEchos = false;
        // Extrae el nombre del archivo de la ruta de la imagen
        $nombreArchivo = pathinfo($rutaImagen, PATHINFO_FILENAME);
        // Extrae la ruta del directorio de la imagen
        $rutaDirectorio = pathinfo($rutaImagen, PATHINFO_DIRNAME);

        // Busca archivos en la misma ruta en la base de datos
        $archivosEncontrados = $this->buscar($mysqli, $rutaDirectorio);
        $this->MostrarEchos = true;
        if (count($archivosEncontrados) === 1) {
            return $archivosEncontrados[0]['id'];
        } else {
            foreach ($archivosEncontrados as $archivo) {
                // Verifica si el archivo tiene el mismo nombre
                if ($nombreArchivo === $archivo['nombre']) {
                    // Si se encuentra un archivo que coincide, devuelve su ID
                    return $archivo['id'];
                }
            }
        }

        // Si no se encuentra ningún archivo coincidente, devuelve null
        return null;
    }

}