<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$titulo = 'Nuevo contacto';
$contacto = null;
$urlDestino = 'guardar.php';

require_once __DIR__ . '/includes/header.php';
?>

<main class="container">
    <header class="page-header">
        <div>
            <h1 class="page-title">Nuevo contacto</h1>
            <p class="page-description">Completa los datos para agregarlo a tu agenda.</p>
        </div>
    </header>

    <?php require __DIR__ . '/includes/form_contacto.php'; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
