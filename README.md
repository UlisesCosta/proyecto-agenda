# Agenda de Contactos

Aplicación web dinámica para gestionar una agenda de contactos, con operaciones
CRUD completas (crear, leer, actualizar y eliminar). Desarrollada con PHP y
MySQL, sin frameworks ni dependencias externas.

## Tecnologías

- **PHP** (con PDO y sentencias preparadas)
- **MySQL**
- **HTML** y **CSS** (hoja de estilos externa en `css/styles.css`)

## Funcionalidades

- Listar contactos
- Buscar contactos por nombre, apellido, teléfono o email
- Crear un contacto con foto
- Ver el detalle de un contacto
- Editar un contacto
- Eliminar un contacto (y su foto)
- Validación de datos y subida segura de imágenes (JPG, PNG y WebP, hasta 2 MB)
- Protección CSRF en los formularios que envían datos

## Estructura del proyecto

```
/
├── index.php, crear.php, guardar.php, editar.php,
│   actualizar.php, ver.php, eliminar.php   (páginas CRUD)
├── config/
│   └── db.php          (conexión PDO; lee las credenciales de .env)
├── includes/
│   ├── header.php
│   ├── menu.php
│   ├── footer.php
│   ├── form_contacto.php
│   └── funciones.php
├── css/
│   └── styles.css
├── archivos/           (fotos subidas)
├── .env.example        (plantilla de credenciales)
└── .env                (credenciales reales; NO se sube al repositorio)
```

## Instalación local

1. Instala un servidor con PHP y MySQL (por ejemplo XAMPP, WAMP o Laragon).
2. Crea la base de datos `agenda_contactos` y la tabla `contactos`.
3. Copia `.env.example` a `.env` y completa tus credenciales:

   ```env
   DB_HOST=localhost
   DB_NAME=agenda_contactos
   DB_USER=root
   DB_PASS=
   ```

4. Copia el proyecto a la carpeta pública del servidor (por ejemplo `htdocs`).
5. Abre `http://localhost/proyecto-agenda/` en tu navegador.


## Base de datos

El esquema completo está en `db_schema.sql`: crea la base `agenda_contactos` y
la tabla `contactos`. Se puede ejecutar desde phpMyAdmin o la consola de MySQL.

Tabla `contactos`:

| Campo     | Tipo    | Notas                          |
|-----------|---------|--------------------------------|
| id        | INT     | AUTO_INCREMENT, clave primaria |
| nombre    | VARCHAR | obligatorio                    |
| apellido  | VARCHAR | obligatorio                    |
| telefono  | VARCHAR | obligatorio                    |
| foto      | VARCHAR | obligatorio (nombre del archivo) |
| email     | VARCHAR | opcional                       |
| direccion | VARCHAR | opcional                       |
| notas     | TEXT    | opcional                       |

## Integrantes

- Ulises Hernandez Costa
- Fredy De la Cruz Gomez
- Ernesto Gonzalez Perez

## Materia

- Programación Web
