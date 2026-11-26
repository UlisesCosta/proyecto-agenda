<?php
/**
 * Página principal: lista todos los contactos de la agenda.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/funciones.php';

$titulo = 'Contactos';

// Obtener todos los contactos ordenados por apellido y nombre.
// Esta consulta no recibe datos del usuario, por eso usa query() sin
// parámetros. Las sentencias preparadas (prepare/execute) se usan en las
// páginas que reciben datos: guardar.php, actualizar.php y eliminar.php.
$contactos = [];
$error = false;

try {
    $stmt = $pdo->query('SELECT * FROM contactos ORDER BY apellido, nombre');
    $contactos = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error al listar contactos: ' . $e->getMessage());
    $error = true;
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <header class="page-header">
        <div>
            <h1 class="page-title">Contactos</h1>
            <p class="page-description"><?php echo count($contactos); ?> contactos en tu agenda</p>
        </div>
        <a href="crear.php" class="btn btn-primary">Nuevo contacto</a>
    </header>

    <?php if ($error): ?>
        <div class="alert" role="alert">
            No se pudo cargar la lista de contactos. Inténtalo más tarde.
        </div>
    <?php elseif (empty($contactos)): ?>
        <div class="empty-state">
            <h2>No hay contactos todavía</h2>
            <p>Agrega tu primer contacto para empezar.</p>
            <a href="crear.php" class="btn btn-primary">Agregar contacto</a>
        </div>
    <?php else: ?>
        <div class="lista-contactos">
            <?php foreach ($contactos as $contacto): ?>
                <?php
                    // Usar la foto del contacto o la por defecto si el archivo no existe
                    $foto = (!empty($contacto['foto']) && file_exists(RUTA_ARCHIVOS . $contacto['foto']))
                        ? $contacto['foto']
                        : FOTO_POR_DEFECTO;

                    $nombreCompleto = trim($contacto['apellido'] . ' ' . $contacto['nombre']);
                ?>
                <article class="contacto">
                    <img class="contacto-foto"
                         src="archivos/<?php echo e($foto); ?>"
                         alt="Foto de <?php echo e($nombreCompleto); ?>">

                    <div class="contacto-info">
                        <a class="contacto-nombre" href="ver.php?id=<?php echo (int) $contacto['id']; ?>">
                            <?php echo e($nombreCompleto); ?>
                        </a>
                        <span class="contacto-detalle">
                            <?php echo e($contacto['telefono']); ?>
                            <?php if (!empty($contacto['email'])): ?>
                                · <?php echo e($contacto['email']); ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="contacto-acciones">
                        <a class="accion" href="editar.php?id=<?php echo (int) $contacto['id']; ?>">Editar</a>
                        <a class="accion accion-peligro"
                           href="eliminar.php?id=<?php echo (int) $contacto['id']; ?>"
                           onclick="return confirm('¿Seguro que quieres eliminar este contacto?');">Eliminar</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
