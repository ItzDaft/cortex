<?php

class ArchivoController {

    /**
     * Sirve de forma segura un archivo de la carpeta de uploads.
     * @param string $tipo El subdirectorio (ej. 'pagos', 'resumenes').
     * @param string $nombreArchivo El nombre del archivo.
     */
    public function ver($tipo, $nombreArchivo) {
        if (!isset($_SESSION['usuario_id'])) {
            die('Acceso denegado.');
        }

        $tiposPermitidos = ['pagos', 'resumenes'];
        if (!in_array($tipo, $tiposPermitidos)) {
            die('Tipo de archivo no válido.');
        }
        
        $rutaCompleta = BACKEND_ROOT . "/uploads/{$tipo}/" . basename($nombreArchivo);

        if (file_exists($rutaCompleta)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $rutaCompleta);
            finfo_close($finfo);

            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: inline; filename="' . basename($nombreArchivo) . '"');
            readfile($rutaCompleta);
            exit;
        } else {
            http_response_code(404);
            die('Archivo no encontrado.');
        }
    }
}