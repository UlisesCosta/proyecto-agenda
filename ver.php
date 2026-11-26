<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    $_SESSION['mensaje'] = 'El contacto solicitado no existe.';
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
    error_log('Error al consultar contacto: ' . $e->getMessage());
    $_SESSION['mensaje'] = 'No se pudo cargar el contacto. Inténtalo más tarde.';
    header('Location: index.php');
    exit;
}

if (!$contacto) {
    $_SESSION['mensaje'] = 'El contacto solicitado no existe.';
    header('Location: index.php');
    exit;
}

$titulo = 'Detalle del contacto';
$nombreCompleto = trim($contacto['nombre'] . ' ' . $contacto['apellido']);
$foto = (!empty($contacto['foto']) && file_exists(RUTA_ARCHIVOS . basename($contacto['foto'])))
    ? basename($contacto['foto'])
    : FOTO_POR_DEFECTO;

require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <header class="page-header">
        <div>
            <h1 class="page-title"><?php echo e($nombreCompleto); ?></h1>
            <p class="page-description">Detalle del contacto</p>
        </div>
    </header>

    <article class="contacto">
        <img class="contacto-foto"
             src="archivos/<?php echo e($foto); ?>"
             alt="Foto de <?php echo e($nombreCompleto); ?>">
        <div class="contacto-info">
            <span class="contacto-detalle"><?php echo e($contacto['telefono']); ?></span>
            <?php foreach (['email' => 'Email', 'direccion' => 'Dirección', 'notas' => 'Notas'] as $campo => $etiqueta): ?>
                <?php if (trim((string) ($contacto[$campo] ?? '')) !== ''): ?>
                    <span class="contacto-detalle"><?php echo e($etiqueta); ?>: <?php echo e($contacto[$campo]); ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </article>

    <p class="contacto-acciones">
        <a class="btn btn-primary" href="editar.php?id=<?php echo (int) $contacto['id']; ?>">Editar</a>
        <a class="accion accion-peligro" href="eliminar.php?id=<?php echo (int) $contacto['id']; ?>">Eliminar</a>
        <a class="btn btn-ghost" href="index.php">Volver al listado</a>
    </p>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
