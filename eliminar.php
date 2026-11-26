<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$metodo = $_SERVER['REQUEST_METHOD'];
if ($metodo !== 'GET' && $metodo !== 'POST') {
    header('Location: index.php');
    exit;
}

$idRecibido = $metodo === 'GET' ? ($_GET['id'] ?? null) : ($_POST['id'] ?? null);
$id = is_scalar($idRecibido) ? filter_var($idRecibido, FILTER_VALIDATE_INT) : false;
if ($id === false || $id < 1) {
    $_SESSION['mensaje'] = 'El contacto solicitado no existe.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/funciones.php';

try {
    $stmt = $pdo->prepare('SELECT id, nombre, apellido, foto FROM contactos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $contacto = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Error al buscar contacto para eliminar: ' . $e->getMessage());
    $_SESSION['mensaje'] = 'No se pudo procesar la solicitud. Inténtalo más tarde.';
    header('Location: index.php');
    exit;
}

if (!$contacto) {
    $_SESSION['mensaje'] = 'El contacto solicitado no existe.';
    header('Location: index.php');
    exit;
}

if ($metodo === 'POST') {
    try {
        $stmt = $pdo->prepare('DELETE FROM contactos WHERE id = :id');
        $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log('Error al eliminar contacto: ' . $e->getMessage());
        $_SESSION['mensaje'] = 'No se pudo eliminar el contacto. Inténtalo más tarde.';
        header('Location: index.php');
        exit;
    }

    if ($stmt->rowCount() !== 1) {
        $_SESSION['mensaje'] = 'El contacto solicitado ya no existe.';
        header('Location: index.php');
        exit;
    }

    borrarFoto($contacto['foto']);
    $_SESSION['mensaje'] = 'El contacto se eliminó correctamente.';
    header('Location: index.php');
    exit;
}

$titulo = 'Confirmar eliminación';
$nombreCompleto = trim($contacto['nombre'] . ' ' . $contacto['apellido']);
require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <header class="page-header">
        <div>
            <h1 class="page-title">Confirmar eliminación</h1>
            <p class="page-description">¿Eliminar a <?php echo e($nombreCompleto); ?>?</p>
        </div>
    </header>

    <form method="post" action="eliminar.php">
        <input type="hidden" name="id" value="<?php echo (int) $contacto['id']; ?>">
        <button class="btn btn-primary" type="submit">Eliminar</button>
        <a class="btn btn-ghost" href="index.php">Cancelar</a>
    </form>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
