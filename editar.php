<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idRecibido = $_GET['id'] ?? null;
$id = is_scalar($idRecibido) ? filter_var($idRecibido, FILTER_VALIDATE_INT) : false;
if ($id === false || $id < 1) {
    $_SESSION['error'] = 'El contacto solicitado no existe.';
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/funciones.php';

try {
    $stmt = $pdo->prepare('SELECT * FROM contactos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $contacto = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Error al buscar contacto para editar: ' . $e->getMessage());
    $_SESSION['error'] = 'No se pudo cargar el contacto. Inténtalo más tarde.';
    header('Location: index.php');
    exit;
}

if (!$contacto) {
    $_SESSION['error'] = 'El contacto solicitado no existe.';
    header('Location: index.php');
    exit;
}

$titulo = 'Editar contacto';
$urlDestino = 'actualizar.php';
require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <header class="page-header">
        <div>
            <h1 class="page-title">Editar contacto</h1>
            <p class="page-description">Actualiza los datos de <?php echo e(trim($contacto['nombre'] . ' ' . $contacto['apellido'])); ?>.</p>
        </div>
    </header>

    <?php require __DIR__ . '/includes/form_contacto.php'; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
