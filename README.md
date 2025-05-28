🧩 Vanity And Magic

Este repositorio contiene tres componentes principales:

- **admin**: Aplicación frontend construida con tecnologías web modernas.
- **api**: Backend basado en Laravel (PHP).
- **db.sql**: Script SQL para la base de datos PostgreSQL.

---

📦 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado lo siguiente:

- [Node.js (v18 o superior)](https://nodejs.org/)
- [pnpm](https://pnpm.io/installation)
- [PHP (v8.1 o superior)](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [PostgreSQL](https://www.postgresql.org/)

---

🖥️ 1. Frontend (`admin`)

🔧 Instalación de `pnpm`

Primero, instala `pnpm` globalmente, es decir, abrir cmd y ejecutar lo siguiente:

npm install -g pnpm

📥 Instalación de dependencias
Desde la raíz del proyecto, navega a la carpeta admin:

cd admin
pnpm install

🚀 Levantar servidor de desarrollo

pnpm run dev
Esto iniciará el servidor de desarrollo en http://localhost:5173 (o en otro puerto disponible).

⚙️ 2. Backend (api)
📥 Instalación de dependencias
Desde la raíz del proyecto, entra a la carpeta api:

cd api
composer install

⚙️ Crear archivo .env
Copia el archivo de entorno de ejemplo y edítalo:

cp .env.example .env
Luego, edita los valores de conexión a la base de datos en .env:

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nombre_de_tu_base
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

🔐 Generar clave de la aplicación

php artisan key:generate
🚀 Levantar el servidor

php artisan serve
Esto iniciará el backend en http://localhost:8000.

🗃️ 3. Base de Datos (db.sql)

🐘 Instalación de PostgreSQL

Descarga PostgreSQL desde su sitio oficial. Durante la instalación:

Establece una contraseña para el usuario postgres

Recuerda el puerto (usualmente 5432)

🛠️ Crear una nueva base de datos

Puedes hacerlo desde la terminal o con pgAdmin.

En la terminal con psql:

psql -U postgres
CREATE DATABASE nombre_de_tu_base;
\q

Con pgAdmin:

Abre pgAdmin.

Haz clic derecho en "Databases" > "Create" > "Database".

Asigna un nombre a la base de datos.

📥 Importar el archivo db.sql
Usando psql:

psql -U postgres -d nombre_de_tu_base -f ruta/a/db.sql
Ejemplo:


psql -U postgres -d mi_base -f ../db.sql
Usando pgAdmin:
Abre la base de datos creada.

Ve a "Tools" → "Query Tool".

Carga y ejecuta el archivo db.sql.

✅ Verificación Final
 Frontend (pnpm run dev) funcionando en http://localhost:5173

 Backend (php artisan serve) en http://localhost:8000

 Base de datos PostgreSQL configurada y conectada

 Archivo .env correctamente configurado en la carpeta api

📬 Soporte
Si tienes dudas o sugerencias, crea un issue en este repositorio. Estaremos encantados de ayudarte.
