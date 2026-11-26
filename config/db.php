<?php
/**
 * Conexión a la base de datos con PDO.
 *
 * Lee las credenciales desde el archivo .env (en la raíz del proyecto)
 * y expone la variable global $pdo para que las demás páginas la usen.
 *
 * El archivo .env NO se sube al repositorio (está en .gitignore).
 * Cada integrante debe copiar .env.example a .env y completar sus datos.
 *
 * Uso desde una página:
 *     require_once __DIR__ . '/../config/db.php';
 *     // a partir de acá puedes usar $pdo
 */

/**
 * Lee un archivo .env (líneas CLAVE=VALOR) y agrega las variables a $_ENV.
 *
 * - Las líneas que empiezan con # se tratan como comentarios.
 * - Los valores pueden ir entre comillas simples o dobles.
 *
 * @param string $ruta Ruta completa al archivo .env.
 * @return bool true si el archivo se leyó, false si no existe o no se pudo leer.
 */
function cargarEnv($ruta) {
    if (!file_exists($ruta)) {
        return false;
    }

    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lineas === false) {
        return false;
    }

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        // Ignorar líneas vacías y comentarios
        if ($linea === '' || strpos($linea, '#') === 0) {
            continue;
        }

        // Separar en CLAVE=VALOR (solo el primer =, para no romper valores con =)
        $partes = explode('=', $linea, 2);
        if (count($partes) !== 2) {
            continue;
        }

        $clave = trim($partes[0]);
        $valor = trim($partes[1]);

        // Quitar comillas simples o dobles si las tiene
        $valor = trim($valor, "\"'");

        $_ENV[$clave] = $valor;
    }

    return true;
}

// Ruta al archivo .env (raíz del proyecto)
$rutaEnv = __DIR__ . '/../.env';

if (!cargarEnv($rutaEnv)) {
    error_log('No se encontró el archivo .env en ' . $rutaEnv);
    die('Falta la configuración: copia .env.example a .env y completa tus datos.');
}

// Credenciales leídas desde .env
$dbHost = $_ENV['DB_HOST'] ?? '';
$dbName = $_ENV['DB_NAME'] ?? '';
$dbUser = $_ENV['DB_USER'] ?? '';
$dbPass = $_ENV['DB_PASS'] ?? '';

// DB_PASS puede quedar vacía (común en local); el resto son obligatorias
if ($dbHost === '' || $dbName === '' || $dbUser === '') {
    error_log('Faltan variables de conexión en .env');
    die('Configuración incompleta: revisa DB_HOST, DB_NAME y DB_USER en tu archivo .env.');
}

try {
    // DSN con charset utf8mb4 para soportar emojis y acentos correctamente
    $dsn = 'mysql:host=' . $dbHost . ';dbname=' . $dbName . ';charset=utf8mb4';

    $opciones = [
        // Lanza una excepción ante cualquier error de SQL
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Devuelve los resultados como arreglos asociativos
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Usa sentencias preparadas reales (más seguras)
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $dbUser, $dbPass, $opciones);
} catch (PDOException $e) {
    // El error real se guarda en el log del servidor, nunca se muestra al usuario
    error_log('Error de conexión a la base de datos: ' . $e->getMessage());

    // Mensaje genérico para el usuario
    die('No se pudo conectar con la base de datos. Inténtalo más tarde.');
}
