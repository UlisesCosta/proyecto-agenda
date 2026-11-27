<?php
/**
 * Página principal: lista todos los contactos de la agenda.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mensajes de sesión: éxito (mensaje) y error (error)
$mensaje = $_SESSION['mensaje'] ?? '';
$flashError = $_SESSION['error'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['error']);
$mensaje = is_string($mensaje) ? $mensaje : '';
$flashError = is_string($flashError) ? $flashError : '';

$titulo = 'Contactos';

// Búsqueda por nombre, apellido, teléfono o email.
// La consulta usa prepare/execute porque ahora recibe un parámetro.
$busqueda = trim((string) ($_GET['q'] ?? ''));
if (mb_strlen($busqueda) > 80) {
    $busqueda = mb_substr($busqueda, 0, 80);
}

$contactos = [];
$error = false;

try {
    $sql = 'SELECT * FROM contactos';
    $parametros = [];

    if ($busqueda !== '') {
        $sql .= ' WHERE nombre LIKE ?
                     OR apellido LIKE ?
                     OR telefono LIKE ?
                     OR email LIKE ?';
        $parametros = array_fill(0, 4, '%' . $busqueda . '%');
    }

    $sql .= ' ORDER BY apellido, nombre';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $contactos = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error al listar contactos: ' . $e->getMessage());
    $error = true;
}

$totalContactos = count($contactos);
if ($busqueda === '') {
    $descripcion = $totalContactos === 1
        ? '1 contacto en tu agenda'
        : $totalContactos . ' contactos en tu agenda';
} else {
    $descripcion = $totalContactos === 1
        ? '1 resultado para «' . $busqueda . '»'
        : $totalContactos . ' resultados para «' . $busqueda . '»';
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <header class="page-header">
        <div>
            <h1 class="page-title">Contactos</h1>
            <p class="page-description"><?php echo e($descripcion); ?></p>
        </div>
        <a href="crear.php" class="btn btn-primary">Nuevo contacto</a>
    </header>

    <?php if ($mensaje !== ''): ?>
        <div class="alert alert-success" role="status">
            <?php echo e($mensaje); ?>
        </div>
    <?php endif; ?>

    <?php if ($flashError !== ''): ?>
        <div class="alert" role="alert">
            <?php echo e($flashError); ?>
        </div>
    <?php endif; ?>

    <form class="buscador" method="get" action="index.php" role="search">
        <label class="sr-only" for="busqueda">Buscar contactos</label>
        <input class="form-control" id="busqueda" name="q" type="search" maxlength="80"
               placeholder="Buscar por nombre, teléfono o email"
               value="<?php echo e($busqueda); ?>">
        <button class="btn btn-ghost" type="submit">Buscar</button>
        <?php if ($busqueda !== ''): ?>
            <a class="btn btn-ghost" href="index.php">Limpiar</a>
        <?php endif; ?>
    </form>

    <?php if ($error): ?>
        <div class="alert" role="alert">
            No se pudo cargar la lista de contactos. Inténtalo más tarde.
        </div>
    <?php elseif (empty($contactos)): ?>
        <?php if ($busqueda !== ''): ?>
            <div class="empty-state">
                <h2>Sin resultados</h2>
                <p>No se encontraron contactos para «<?php echo e($busqueda); ?>».</p>
                <a href="index.php" class="btn btn-ghost">Ver todos los contactos</a>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>No hay contactos todavía</h2>
                <p>Agrega tu primer contacto para empezar.</p>
                <a href="crear.php" class="btn btn-primary">Agregar contacto</a>
            </div>
        <?php endif; ?>
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
                           href="eliminar.php?id=<?php echo (int) $contacto['id']; ?>">Eliminar</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
