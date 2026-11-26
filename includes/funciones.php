<?php
/**
 * Funciones auxiliares compartidas por todas las páginas.
 *
 * Uso desde una página:
 *     require_once __DIR__ . '/../includes/funciones.php';
 */

// Carpeta donde se guardan las fotos subidas
define('RUTA_ARCHIVOS', __DIR__ . '/../archivos/');

// Foto que se asigna por defecto cuando el contacto no tiene foto propia.
// Ajusta el nombre para que coincida con el archivo que subas a archivos/.
define('FOTO_POR_DEFECTO', 'default.png');

/**
 * Escapa un texto para mostrarlo de forma segura en HTML.
 *
 * @param string $texto Texto a escapar.
 * @return string Texto escapado.
 */
function e($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Valida los datos de un contacto recibidos por POST.
 *
 * Longitudes máximas según la tabla `contactos`:
 *   - nombre, apellido: 80 caracteres
 *   - telefono: 20 caracteres
 *   - email: 120 caracteres
 *   - direccion: 255 caracteres
 *
 * @param array $datos Arreglo con los campos del formulario.
 * @return array Arreglo de errores indexado por campo (vacío si es válido).
 */
function validarContacto(array $datos) {
    $errores = [];

    // Nombre (obligatorio)
    $nombre = trim((string) ($datos['nombre'] ?? ''));
    if ($nombre === '') {
        $errores['nombre'] = 'El nombre es obligatorio.';
    } elseif (mb_strlen($nombre) > 80) {
        $errores['nombre'] = 'El nombre no puede superar los 80 caracteres.';
    }

    // Apellido (obligatorio)
    $apellido = trim((string) ($datos['apellido'] ?? ''));
    if ($apellido === '') {
        $errores['apellido'] = 'El apellido es obligatorio.';
    } elseif (mb_strlen($apellido) > 80) {
        $errores['apellido'] = 'El apellido no puede superar los 80 caracteres.';
    }

    // Teléfono (obligatorio)
    $telefono = trim((string) ($datos['telefono'] ?? ''));
    if ($telefono === '') {
        $errores['telefono'] = 'El teléfono es obligatorio.';
    } elseif (mb_strlen($telefono) > 20) {
        $errores['telefono'] = 'El teléfono no puede superar los 20 caracteres.';
    }

    // Email (opcional, pero si viene debe tener formato válido)
    $email = trim((string) ($datos['email'] ?? ''));
    if ($email !== '') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El email no tiene un formato válido.';
        } elseif (mb_strlen($email) > 120) {
            $errores['email'] = 'El email no puede superar los 120 caracteres.';
        }
    }

    // Dirección (opcional)
    $direccion = trim((string) ($datos['direccion'] ?? ''));
    if ($direccion !== '' && mb_strlen($direccion) > 255) {
        $errores['direccion'] = 'La dirección no puede superar los 255 caracteres.';
    }

    return $errores;
}

/**
 * Valida y guarda una foto subida por un formulario.
 *
 * @param array $archivo Entrada de $_FILES (por ejemplo $_FILES['foto']).
 * @return string|false Nombre único del archivo guardado, o false si falla.
 */
function subirFoto(array $archivo) {
    // Tipos MIME reales permitidos y su extensión
    $permitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    // Tamaño máximo: 2 MB
    $tamanoMaximo = 2 * 1024 * 1024;

    // Verificar que la subida no traiga errores
    if (!isset($archivo['error']) || is_array($archivo['error'])) {
        return false;
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Verificar tamaño
    if ($archivo['size'] > $tamanoMaximo) {
        return false;
    }

    // Verificar el tipo MIME real del archivo (no confiar en la extensión)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);

    if (!array_key_exists($mime, $permitidos)) {
        return false;
    }

    // Generar un nombre único para evitar sobrescribir fotos existentes
    $extension = $permitidos[$mime];
    $nombre = uniqid('foto_', true) . '.' . $extension;

    // Mover el archivo temporal a la carpeta de fotos
    $destino = RUTA_ARCHIVOS . $nombre;
    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        error_log('No se pudo mover la foto subida a: ' . $destino);
        return false;
    }

    return $nombre;
}

/**
 * Elimina una foto de la carpeta archivos/ si existe y no es la por defecto.
 *
 * @param string $nombre Nombre del archivo (solo el nombre, no la ruta).
 * @return void
 */
function borrarFoto($nombre) {
    // Sanitizar: usar solo el nombre del archivo (evita rutas maliciosas)
    $nombre = basename((string) $nombre);

    if ($nombre === '' || $nombre === FOTO_POR_DEFECTO) {
        return;
    }

    $ruta = RUTA_ARCHIVOS . $nombre;

    if (file_exists($ruta)) {
        unlink($ruta);
    }
}
