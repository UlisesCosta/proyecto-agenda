<?php
/**
 * Cabecera común a todas las páginas.
 *
 * - Abre el documento HTML.
 * - Define el título (la página puede pasar $titulo antes de incluirla).
 * - Enlaza la hoja de estilos y el menú de navegación.
 * - Incluye funciones.php para que e() esté siempre disponible.
 *
 * Uso desde una página (en la raíz del proyecto):
 *     $titulo = 'Nuevo contacto';
 *     require_once __DIR__ . '/../includes/header.php';
 */

require_once __DIR__ . '/funciones.php';

// Título por defecto si la página no define el suyo
if (!isset($titulo)) {
    $titulo = 'Agenda de Contactos';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($titulo); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php require_once __DIR__ . '/menu.php'; ?>
