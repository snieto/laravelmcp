# TaskMaster AI - Docker Setup

Este directorio contiene toda la configuración de Docker para el proyecto TaskMaster AI.

## Estructura de Archivos

```
docker/
├── Dockerfile              # Multi-stage Dockerfile (development & production)
├── nginx/
│   ├── nginx.conf         # Configuración principal de Nginx
│   └── conf.d/
│       └── default.conf   # Virtual host para la aplicación
├── php/
│   ├── local.ini          # Configuración PHP para desarrollo
│   ├── production.ini     # Configuración PHP para producción
│   └── xdebug.ini         # Configuración de Xdebug
└── README.md              # Este archivo
```

## Servicios Incluidos

### 1. **app** - PHP 8.3 FPM
- Framework: Laravel 11
- Extensions: PostgreSQL, Redis, GD, Zip, etc.
- Xdebug habilitado en modo desarrollo
- Puerto interno: 9000

### 2. **nginx** - Web Server
- Servidor web Nginx Alpine
- Puerto: **8000**
- Configuración optimizada para Laravel y Livewire
- Compresión Gzip habilitada

### 3. **postgres** - PostgreSQL 16
- Base de datos principal
- Puerto: **5432**
- Credenciales por defecto:
  - Database: `taskmaster`
  - User: `taskmaster`
  - Password: `secret`

### 4. **redis** - Redis 7
- Cache y sistema de colas
- Puerto: **6379**

### 5. **node** - Node.js 20
- Compilación de assets con Vite
- Puerto: **5173** (HMR - Hot Module Replacement)

### 6. **queue** - Laravel Queue Worker
- Procesa trabajos en segundo plano
- Intentos: 3
- Timeout: 90 segundos

### 7. **scheduler** - Laravel Scheduler
- Ejecuta tareas programadas cada minuto
- Equivalente a cron

### 8. **adminer** - Database Management UI
- Interfaz web para gestionar PostgreSQL
- Puerto: **8080**
- Acceso: http://localhost:8080

## Inicio Rápido

### 1. Instalación Completa

```bash
# Usando Make (recomendado)
make install

# O manualmente
docker-compose build
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
docker-compose exec node npm install
```

### 2. Acceder a la Aplicación

- **Aplicación principal**: http://localhost:8000
- **Adminer (DB UI)**: http://localhost:8080
- **Vite HMR**: http://localhost:5173

## Comandos Útiles (Make)

```bash
make help              # Muestra todos los comandos disponibles
make up                # Inicia los contenedores
make down              # Detiene los contenedores
make restart           # Reinicia los contenedores
make logs              # Muestra logs en tiempo real
make shell             # Abre shell en el contenedor app
make artisan cmd="..."  # Ejecuta comandos artisan
make migrate           # Ejecuta migraciones
make seed              # Ejecuta seeders
make test              # Ejecuta tests
make optimize          # Optimiza Laravel
make clear             # Limpia caches
```

## Comandos Docker Compose Directos

### Gestión de Contenedores

```bash
# Iniciar todos los servicios
docker-compose up -d

# Iniciar servicios específicos
docker-compose up -d app nginx postgres

# Detener todos los servicios
docker-compose down

# Detener sin eliminar volúmenes
docker-compose stop

# Reiniciar servicios
docker-compose restart

# Ver logs
docker-compose logs -f
docker-compose logs -f app
docker-compose logs -f nginx

# Ver estado de contenedores
docker-compose ps
```

### Ejecución de Comandos

```bash
# Shell en el contenedor app
docker-compose exec app sh

# Artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan test

# Composer
docker-compose exec app composer install
docker-compose exec app composer update

# NPM
docker-compose exec node npm install
docker-compose exec node npm run dev
docker-compose exec node npm run build

# PostgreSQL
docker-compose exec postgres psql -U taskmaster -d taskmaster
```

## Configuración de Variables de Entorno

Copia el archivo `.env.example` a `.env` y ajusta las siguientes variables:

```env
# Application
APP_NAME="TaskMaster AI"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=taskmaster
DB_USERNAME=taskmaster
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# OpenAI (requerido para funcionalidades AI)
OPENAI_API_KEY=your-api-key-here
OPENAI_DEFAULT_MODEL=gpt-4-turbo-preview
```

## Desarrollo con Xdebug

### Configuración en PHPStorm / Cursor

1. Ve a **Settings → PHP → Servers**
2. Crea un nuevo servidor:
   - Name: `taskmaster`
   - Host: `localhost`
   - Port: `8000`
   - Debugger: `Xdebug`
   - ✓ Use path mappings
   - Map: `/your/local/path` → `/var/www/html`

3. Inicia el debug listener
4. Coloca breakpoints en tu código
5. Accede a la aplicación

### Configuración en VSCode

Agrega a `.vscode/launch.json`:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}"
      }
    }
  ]
}
```

## Mantenimiento

### Backup de Base de Datos

```bash
# Crear backup
make backup-db

# O manualmente
docker-compose exec postgres pg_dump -U taskmaster taskmaster > backup.sql
```

### Restaurar Base de Datos

```bash
# Restaurar backup
make restore-db file=backup.sql

# O manualmente
docker-compose exec -T postgres psql -U taskmaster taskmaster < backup.sql
```

### Limpiar Todo

```bash
# Eliminar contenedores, volúmenes y caché
make prune

# O manualmente
docker-compose down -v
docker system prune -af --volumes
```

### Actualizar Dependencias

```bash
# PHP
docker-compose exec app composer update

# Node.js
docker-compose exec node npm update
```

## Troubleshooting

### Los contenedores no inician

```bash
# Ver logs detallados
docker-compose logs

# Verificar estado
docker-compose ps

# Reconstruir contenedores
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Error de permisos en storage/

```bash
# Arreglar permisos
make permissions

# O manualmente
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### PostgreSQL no conecta

```bash
# Verificar que el contenedor esté ejecutándose
docker-compose ps postgres

# Ver logs de PostgreSQL
docker-compose logs postgres

# Reiniciar contenedor
docker-compose restart postgres
```

### Redis no conecta

```bash
# Verificar Redis
docker-compose exec redis redis-cli ping
# Debería responder: PONG

# Ver logs
docker-compose logs redis
```

### Assets no compilan

```bash
# Reinstalar node_modules
docker-compose exec node rm -rf node_modules
docker-compose exec node npm install

# Limpiar cache de Vite
docker-compose exec node npm run build -- --force
```

## MCP Servers

### Iniciar Servidor MCP

```bash
# Usando Make
make mcp-start server=task-tools

# O directamente
docker-compose exec app php artisan mcp:start task-tools
```

### MCP Inspector

```bash
# Abrir inspector
make mcp-inspector

# O directamente
docker-compose exec app php artisan mcp:inspector
```

## Producción

### Build para Producción

```bash
# Construir imagen de producción
docker-compose -f docker-compose.prod.yml build

# Iniciar en modo producción
docker-compose -f docker-compose.prod.yml up -d
```

### Optimizaciones de Producción

```bash
# Optimizar Laravel
docker-compose exec app php artisan optimize
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Compilar assets
docker-compose exec node npm run build
```

## Monitoreo

### Ver uso de recursos

```bash
# Ver estadísticas de contenedores
docker stats

# Ver solo app
docker stats taskmaster_app
```

### Verificar health checks

```bash
# PostgreSQL
docker-compose exec postgres pg_isready -U taskmaster

# Redis
docker-compose exec redis redis-cli ping
```

## Seguridad

### Cambiar Credenciales por Defecto

1. Actualiza el `.env`:
   ```env
   DB_PASSWORD=tu-password-seguro
   ```

2. Reconstruye los contenedores:
   ```bash
   docker-compose down -v
   docker-compose up -d
   ```

### Generar Nueva Application Key

```bash
docker-compose exec app php artisan key:generate
```

## Soporte

Para más información, consulta:
- [Documentación de Laravel](https://laravel.com/docs)
- [Documentación de Docker](https://docs.docker.com)
- [Documentación de Laravel MCP](https://github.com/laravel/mcp)
