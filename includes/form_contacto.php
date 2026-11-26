<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$contacto = is_array($contacto ?? null) ? $contacto : null;
$urlDestino = (string) ($urlDestino ?? '');
$errores = $_SESSION['errores'] ?? [];
$old = $_SESSION['old'] ?? [];

if (!is_array($errores)) {
    $errores = ['_general' => (string) $errores];
}
if (!is_array($old)) {
    $old = [];
}

unset($_SESSION['errores'], $_SESSION['old']);

$campos = ['nombre', 'apellido', 'telefono', 'email', 'direccion', 'notas'];
$valores = [];
foreach ($campos as $campo) {
    $fuente = array_key_exists($campo, $old) ? $old : ($contacto ?? []);
    $valor = $fuente[$campo] ?? '';
    $valores[$campo] = is_scalar($valor) ? (string) $valor : '';
}
?>
<form action="<?php echo e($urlDestino); ?>" method="post" enctype="multipart/form-data">
    <?php if ($contacto !== null): ?>
        <input type="hidden" name="id" value="<?php echo e($contacto['id'] ?? ''); ?>">
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div class="alert" role="alert">
            <?php foreach ($errores as $error): ?>
                <p><?php echo e($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p>
        <label for="nombre">Nombre</label><br>
        <input id="nombre" name="nombre" type="text" maxlength="80" required
               value="<?php echo e($valores['nombre']); ?>">
    </p>
    <p>
        <label for="apellido">Apellido</label><br>
        <input id="apellido" name="apellido" type="text" maxlength="80" required
               value="<?php echo e($valores['apellido']); ?>">
    </p>
    <p>
        <label for="telefono">Teléfono</label><br>
        <input id="telefono" name="telefono" type="tel" maxlength="12" pattern="[0-9 ]{10,12}"
               title="Ingresa 10 dígitos; puedes separarlos con espacios, por ejemplo 000 000 0000."
               placeholder="000 000 0000" required
               value="<?php echo e($valores['telefono']); ?>">
    </p>
    <p>
        <label for="foto">Foto (opcional)</label><br>
        <input id="foto" name="foto" type="file" accept="image/*">
    </p>
    <p>
        <label for="email">Email</label><br>
        <input id="email" name="email" type="email" maxlength="120"
               value="<?php echo e($valores['email']); ?>">
    </p>
    <p>
        <label for="direccion">Dirección</label><br>
        <input id="direccion" name="direccion" type="text" maxlength="255"
               value="<?php echo e($valores['direccion']); ?>">
    </p>
    <p>
        <label for="notas">Notas</label><br>
        <textarea id="notas" name="notas"><?php echo e($valores['notas']); ?></textarea>
    </p>
    <button class="btn btn-primary" type="submit">Guardar contacto</button>
</form>
