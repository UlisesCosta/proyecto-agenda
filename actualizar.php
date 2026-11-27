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

if (!csrfValidar($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Tu sesión expiró. Vuelve a intentarlo.';
    header('Location: index.php');
    exit;
}

$idRecibido = $_POST['id'] ?? null;
$id = is_scalar($idRecibido) ? filter_var($idRecibido, FILTER_VALIDATE_INT) : false;

if ($id === false || $id < 1) {
    $_SESSION['error'] = 'No se pudo actualizar el contacto.';
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT foto FROM contactos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $contacto = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Error al buscar contacto para actualizar: ' . $e->getMessage());
    $_SESSION['error'] = 'No se pudo actualizar el contacto. Inténtalo más tarde.';
    header('Location: index.php');
    exit;
}

if (!$contacto) {
    $_SESSION['error'] = 'No se encontró el contacto que quieres actualizar.';
    header('Location: index.php');
    exit;
}

$campos = ['nombre', 'apellido', 'telefono', 'email', 'direccion', 'notas'];
$datos = [];
foreach ($campos as $campo) {
    $valor = $_POST[$campo] ?? '';
    $datos[$campo] = is_scalar($valor) ? trim((string) $valor) : '';
}

$resultadoTelefono = validarTelefono($datos['telefono']);
$datos['telefono'] = $resultadoTelefono['telefono'];

$errores = validarContacto($datos);
if ($resultadoTelefono['error'] !== null) {
    $errores['telefono'] = $resultadoTelefono['error'];
}

if (empty($errores)) {
    try {
        $stmt = $pdo->prepare(
            "SELECT 1 FROM contactos
             WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefono, ' ', ''), '-', ''), '(', ''), ')', ''), '.', ''), '+', '') = :telefono
             AND id <> :id
             LIMIT 1"
        );
        $stmt->execute([
            ':telefono' => $datos['telefono'],
            ':id' => $id,
        ]);

        if ($stmt->fetchColumn() !== false) {
            $errores['telefono'] = 'Este número de teléfono ya está registrado.';
        }
    } catch (PDOException $e) {
        error_log('Error al verificar teléfono duplicado: ' . $e->getMessage());
        $_SESSION['errores'] = ['_general' => 'No se pudo validar el contacto. Inténtalo más tarde.'];
        $_SESSION['old'] = $datos;
        header('Location: editar.php?id=' . $id);
        exit;
    }
}

$archivoFoto = $_FILES['foto'] ?? [];
$subirFotoNueva = false;
if (!is_array($archivoFoto) || (isset($archivoFoto['error']) && is_array($archivoFoto['error']))) {
    $errores['foto'] = 'La foto no es válida o no se pudo subir.';
} elseif (!isset($archivoFoto['error']) || $archivoFoto['error'] === UPLOAD_ERR_NO_FILE) {
    $subirFotoNueva = false;
} elseif ($archivoFoto['error'] !== UPLOAD_ERR_OK) {
    $errores['foto'] = 'La foto no es válida o no se pudo subir.';
} elseif (
    !isset($archivoFoto['size'], $archivoFoto['tmp_name'])
    || !is_scalar($archivoFoto['size'])
    || !is_scalar($archivoFoto['tmp_name'])
) {
    $errores['foto'] = 'La foto no es válida o no se pudo subir.';
} else {
    $subirFotoNueva = true;
}

if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    $_SESSION['old'] = $datos;
    header('Location: editar.php?id=' . $id);
    exit;
}

$foto = $contacto['foto'];
if ($subirFotoNueva) {
    $fotoNueva = subirFoto($archivoFoto);
    if ($fotoNueva === false) {
        $_SESSION['errores'] = [
            'foto' => 'La foto no es válida o no se pudo subir. Usa JPG, PNG o WebP de hasta 2 MB.',
        ];
        $_SESSION['old'] = $datos;
        header('Location: editar.php?id=' . $id);
        exit;
    }
    $foto = $fotoNueva;
}

$valorOpcional = static function ($valor) {
    return $valor === '' ? null : $valor;
};

try {
    $stmt = $pdo->prepare(
        'UPDATE contactos
         SET nombre = :nombre,
             apellido = :apellido,
             telefono = :telefono,
             foto = :foto,
             email = :email,
             direccion = :direccion,
             notas = :notas
         WHERE id = :id'
    );
    $stmt->execute([
        ':nombre' => $datos['nombre'],
        ':apellido' => $datos['apellido'],
        ':telefono' => $datos['telefono'],
        ':foto' => $foto,
        ':email' => $valorOpcional($datos['email']),
        ':direccion' => $valorOpcional($datos['direccion']),
        ':notas' => $valorOpcional($datos['notas']),
        ':id' => $id,
    ]);
} catch (PDOException $e) {
    if ($subirFotoNueva) {
        borrarFoto($foto);
    }
    error_log('Error al actualizar contacto: ' . $e->getMessage());
    $_SESSION['errores'] = ['_general' => 'No se pudo actualizar el contacto. Inténtalo más tarde.'];
    $_SESSION['old'] = $datos;
    header('Location: editar.php?id=' . $id);
    exit;
}

if ($subirFotoNueva) {
    borrarFoto($contacto['foto']);
}

unset($_SESSION['errores'], $_SESSION['old']);
$_SESSION['mensaje'] = 'El contacto se actualizó correctamente.';
header('Location: index.php');
exit;
