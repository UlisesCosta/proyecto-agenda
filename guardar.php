<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/funciones.php';

$campos = ['nombre', 'apellido', 'telefono', 'email', 'direccion', 'notas'];
$datos = [];
foreach ($campos as $campo) {
    $valor = $_POST[$campo] ?? '';
    $datos[$campo] = is_scalar($valor) ? trim((string) $valor) : '';
}

$errores = validarContacto($datos);
if ($datos['telefono'] !== '' && !preg_match('/^\+?[0-9\s().-]{7,20}$/', $datos['telefono'])) {
    $errores['telefono'] = 'Ingresa un teléfono válido.';
}

$archivoFoto = $_FILES['foto'] ?? [];
$usarFotoPredeterminada = false;
if (!is_array($archivoFoto) || (isset($archivoFoto['error']) && is_array($archivoFoto['error']))) {
    $errores['foto'] = 'La foto no es válida o no se pudo subir.';
} elseif (!isset($archivoFoto['error']) || $archivoFoto['error'] === UPLOAD_ERR_NO_FILE) {
    $usarFotoPredeterminada = true;
} elseif ($archivoFoto['error'] !== UPLOAD_ERR_OK) {
    $errores['foto'] = 'La foto no es válida o no se pudo subir.';
} elseif (
    !isset($archivoFoto['size'], $archivoFoto['tmp_name'])
    || !is_scalar($archivoFoto['size'])
    || !is_scalar($archivoFoto['tmp_name'])
) {
    $errores['foto'] = 'La foto no es válida o no se pudo subir.';
}

if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    $_SESSION['old'] = $datos;
    header('Location: crear.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT 1 FROM contactos WHERE telefono = :telefono LIMIT 1');
    $stmt->execute([':telefono' => $datos['telefono']]);

    if ($stmt->fetchColumn() !== false) {
        $_SESSION['errores'] = ['telefono' => 'Este número de teléfono ya está registrado.'];
        $_SESSION['old'] = $datos;
        header('Location: crear.php');
        exit;
    }
} catch (PDOException $e) {
    error_log('Error al verificar teléfono duplicado: ' . $e->getMessage());
    $_SESSION['errores'] = ['_general' => 'No se pudo validar el contacto. Inténtalo más tarde.'];
    $_SESSION['old'] = $datos;
    header('Location: crear.php');
    exit;
}

$nombreFoto = FOTO_POR_DEFECTO;
if (!$usarFotoPredeterminada) {
    $nombreFoto = subirFoto($archivoFoto);
    if ($nombreFoto === false) {
        $_SESSION['errores'] = ['foto' => 'La foto no es válida o no se pudo subir. Usa JPG, PNG o WebP de hasta 2 MB.'];
        $_SESSION['old'] = $datos;
        header('Location: crear.php');
        exit;
    }
}

$valorOpcional = static function ($valor) {
    return $valor === '' ? null : $valor;
};

try {
    $stmt = $pdo->prepare(
        'INSERT INTO contactos (nombre, apellido, telefono, foto, email, direccion, notas)
         VALUES (:nombre, :apellido, :telefono, :foto, :email, :direccion, :notas)'
    );
    $stmt->execute([
        ':nombre' => $datos['nombre'],
        ':apellido' => $datos['apellido'],
        ':telefono' => $datos['telefono'],
        ':foto' => $nombreFoto,
        ':email' => $valorOpcional($datos['email']),
        ':direccion' => $valorOpcional($datos['direccion']),
        ':notas' => $valorOpcional($datos['notas']),
    ]);
} catch (PDOException $e) {
    borrarFoto($nombreFoto);
    error_log('Error al crear contacto: ' . $e->getMessage());
    $_SESSION['errores'] = ['_general' => 'No se pudo guardar el contacto. Inténtalo más tarde.'];
    $_SESSION['old'] = $datos;
    header('Location: crear.php');
    exit;
}

unset($_SESSION['errores'], $_SESSION['old']);
$_SESSION['mensaje'] = 'El contacto se creó correctamente.';
header('Location: index.php');
exit;
