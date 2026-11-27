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

// Foto actual (solo al editar), para mostrarla junto al campo de subida
$fotoActual = '';
if ($contacto !== null && !empty($contacto['foto'])) {
    $nombreFotoActual = basename((string) $contacto['foto']);
    if (file_exists(RUTA_ARCHIVOS . $nombreFotoActual)) {
        $fotoActual = $nombreFotoActual;
    }
}
$nombreCompletoForm = $contacto !== null
    ? trim(($contacto['nombre'] ?? '') . ' ' . ($contacto['apellido'] ?? ''))
    : '';
?>
<form class="formulario" action="<?php echo e($urlDestino); ?>" method="post" enctype="multipart/form-data">
    <?php echo csrfCampo(); ?>
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

    <div class="form-grid">
        <div class="form-field">
            <label class="form-label" for="nombre">Nombre <span class="form-required" aria-hidden="true">*</span></label>
            <input class="form-control" id="nombre" name="nombre" type="text" maxlength="80" required
                    value="<?php echo e($valores['nombre']); ?>">
        </div>

        <div class="form-field">
            <label class="form-label" for="apellido">Apellido <span class="form-required" aria-hidden="true">*</span></label>
            <input class="form-control" id="apellido" name="apellido" type="text" maxlength="80" required
                    value="<?php echo e($valores['apellido']); ?>">
        </div>

        <div class="form-field">
            <label class="form-label" for="telefono">Teléfono <span class="form-required" aria-hidden="true">*</span></label>
            <input class="form-control" id="telefono" name="telefono" type="tel" maxlength="12"
                    pattern="[0-9 ]{10,12}"
                    title="Ingresa 10 dígitos; puedes separarlos con espacios, por ejemplo 000 000 0000."
                    placeholder="000 000 0000" required
                    value="<?php echo e($valores['telefono']); ?>">
            <p class="form-hint">10 dígitos. Puedes separarlos con espacios.</p>
        </div>

        <div class="form-field">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" id="email" name="email" type="email" maxlength="120"
                    placeholder="nombre@ejemplo.com"
                    value="<?php echo e($valores['email']); ?>">
        </div>

        <div class="form-field form-field--full">
            <label class="form-label" for="foto">Foto (opcional)</label>
            <?php if ($fotoActual !== ''): ?>
                <div class="foto-actual">
                    <img src="archivos/<?php echo e($fotoActual); ?>"
                            alt="Foto actual de <?php echo e($nombreCompletoForm); ?>">
                    <span>Sube otra imagen para reemplazar la foto actual.</span>
                </div>
            <?php endif; ?>
            <input class="form-control" id="foto" name="foto" type="file" accept="image/*">
            <p class="form-hint">JPG, PNG o WebP, hasta 2 MB.</p>
        </div>

        <div class="form-field form-field--full">
            <label class="form-label" for="direccion">Dirección</label>
            <input class="form-control" id="direccion" name="direccion" type="text" maxlength="255"
                    value="<?php echo e($valores['direccion']); ?>">
        </div>

        <div class="form-field form-field--full">
            <label class="form-label" for="notas">Notas</label>
            <textarea class="form-control" id="notas" name="notas"><?php echo e($valores['notas']); ?></textarea>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Guardar contacto</button>
        <a class="btn btn-ghost" href="index.php">Cancelar</a>
    </div>
</form>
