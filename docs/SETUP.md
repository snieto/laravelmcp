# SETUP.md - Guía de Instalación y Configuración

## 📋 Requisitos Previos

### Software Requerido

- **PHP**: >= 8.2
- **Composer**: >= 2.5
- **Node.js**: >= 18.x
- **PostgreSQL**: >= 14
- **Git**: >= 2.30

### Cuentas Necesarias

- ✅ Cuenta de OpenAI (con API key)
- ✅ Cuenta de GitHub (para el repositorio)

---

## 🚀 Instalación Paso a Paso

### 1. Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/taskmaster-ai.git
cd taskmaster-ai
```

### 2. Instalar Dependencias de PHP

```bash
composer install
```

### 3. Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Generar application key
php artisan key:generate
```

### 4. Configurar Base de Datos PostgreSQL

#### Opción A: Usando Docker

```bash
# Levantar contenedor PostgreSQL
docker run --name taskmaster-postgres \
  -e POSTGRES_USER=taskmaster \
  -e POSTGRES_PASSWORD=secret \
  -e POSTGRES_DB=taskmaster \
  -p 5432:5432 \
  -d postgres:16-alpine
```

#### Opción B: PostgreSQL Local

```bash
# Crear base de datos
psql -U postgres
CREATE DATABASE taskmaster;
CREATE USER taskmaster WITH PASSWORD 'secret';
GRANT ALL PRIVILEGES ON DATABASE taskmaster TO taskmaster;
\q
```

### 5. Configurar .env

Edita el archivo `.env` con tus credenciales:

```env
# Application
APP_NAME="TaskMaster AI"
APP_ENV=local
APP_KEY=base64:...  # Ya generada
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=taskmaster
DB_USERNAME=taskmaster
DB_PASSWORD=secret

# OpenAI
OPENAI_API_KEY=sk-proj-your-actual-api-key-here
OPENAI_ORGANIZATION=org-your-org-id  # Opcional

# MCP Configuration
MCP_ENABLED=true
MCP_AUTH_DRIVER=sanctum
MCP_WEB_PREFIX=/mcp

# Laravel Boost (opcional para desarrollo)
BOOST_ENABLED=true

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=redis  # o 'file' si no tienes Redis

# Queue
QUEUE_CONNECTION=database  # o 'redis' para producción

# Mail (opcional, para notificaciones)
MAIL_MAILER=log  # usar 'log' en desarrollo
```

### 6. Ejecutar Migraciones y Seeders

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders con datos de prueba
php artisan db:seed

# O todo junto (reset completo)
php artisan migrate:fresh --seed
```

### 7. Instalar Dependencias de Node.js

```bash
npm install
```

### 8. Compilar Assets

```bash
# Desarrollo (watch mode)
npm run dev

# Producción
npm run build
```

---

## 🔧 Instalación de Paquetes Específicos

### Laravel MCP

```bash
composer require laravel/mcp

# Publicar rutas AI
php artisan vendor:publish --tag=ai-routes
```

### Laravel Boost (Desarrollo)

```bash
composer require laravel/boost --dev

# Instalar Boost
php artisan boost:install
```

### OpenAI PHP Client

```bash
composer require openai-php/laravel
```

### Livewire

```bash
composer require livewire/livewire
```

### Pest (Testing)

```bash
composer require pestphp/pest --dev --with-all-dependencies
composer require pestphp/pest-plugin-laravel --dev

# Instalar Pest
php artisan pest:install
```

### Spatie Packages

```bash
# Data Transfer Objects
composer require spatie/laravel-data

# Query Builder
composer require spatie/laravel-query-builder

# Permission (opcional)
composer require spatie/laravel-permission
```

### Development Tools

```bash
# Debugbar
composer require barryvdh/laravel-debugbar --dev

# Laravel Pint (Code style)
composer require laravel/pint --dev

# Larastan (Static analysis)
composer require larastan/larastan --dev
```

### Livewire Components Adicionales

```bash
# Modales
composer require wire-elements/modal

# Notificaciones
composer require masmerise/livewire-toaster
```

---

## 🗄️ Configuración de PostgreSQL

### Optimizaciones Recomendadas

Edita `config/database.php`:

```php
'pgsql' => [
    'driver' => 'pgsql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => 'prefer',
    // Optimizaciones
    'options' => [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
],
```

---

## 🔐 Configuración de Autenticación

### Sanctum Setup

```bash
# Publicar configuración
php artisan vendor:publish --provider="Laravel\Sanctum\ServiceProvider"

# Migrar tablas de Sanctum
php artisan migrate
```

Configurar en `config/sanctum.php`:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

### Crear Usuario Admin

```bash
php artisan tinker
```

```php
$user = \App\Domain\TaskManagement\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@taskmaster.test',
    'password' => bcrypt('password'),
]);

// Generar token para MCP
$token = $user->createToken('mcp-client')->plainTextToken;
echo $token;  // Guardar este token
```

---

## 🤖 Configuración de OpenAI

### Verificar Conexión

```bash
php artisan tinker
```

```php
use OpenAI\Laravel\Facades\OpenAI;

$result = OpenAI::chat()->create([
    'model' => 'gpt-4',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, API!'],
    ],
]);

echo $result->choices[0]->message->content;
```

### Configuración Avanzada

Edita `config/openai.php`:

```php
return [
    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),
    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),
    'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4-turbo-preview'),
];
```

---

## 🔌 Configuración de MCP Servers

### Registrar Servidores

Edita `routes/ai.php`:

```php
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;
use App\Infrastructure\MCP\TaskToolsServer;
use App\Infrastructure\MCP\AIAssistantServer;
use App\Infrastructure\MCP\AnalyticsResourcesServer;
use App\Infrastructure\MCP\ReportsToolsServer;

// Web Servers (HTTP)
Mcp::web('/mcp/tasks', TaskToolsServer::class)
    ->middleware(['auth:sanctum']);

Mcp::web('/mcp/ai', AIAssistantServer::class)
    ->middleware(['auth:sanctum']);

Mcp::web('/mcp/analytics', AnalyticsResourcesServer::class)
    ->middleware(['auth:sanctum']);

Mcp::web('/mcp/reports', ReportsToolsServer::class)
    ->middleware(['auth:sanctum']);

// Local Servers (CLI)
Mcp::local('task-prompts', TaskPromptsServer::class);
Mcp::local('ai-prompts', AIPromptsLibraryServer::class);
```

### Verificar Servidores

```bash
# Listar servidores registrados
php artisan mcp:list

# Probar servidor específico
php artisan mcp:test /mcp/tasks
```

---

## 🧪 Configuración de Testing

### Configurar Database de Testing

Edita `.env.testing`:

```env
APP_ENV=testing
DB_CONNECTION=pgsql
DB_DATABASE=taskmaster_test

CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync

OPENAI_API_KEY=sk-test-fake-key  # Usar mocks en tests
```

### Crear Base de Datos de Testing

```bash
psql -U postgres
CREATE DATABASE taskmaster_test;
GRANT ALL PRIVILEGES ON DATABASE taskmaster_test TO taskmaster;
\q
```

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Con Pest
./vendor/bin/pest

# Con cobertura
./vendor/bin/pest --coverage

# Solo tests unitarios
./vendor/bin/pest tests/Unit

# Con paralelización
php artisan test --parallel
```

---

## 📱 Configuración de Livewire

### Publicar Configuración

```bash
php artisan livewire:publish --config
php artisan livewire:publish --assets
```

### Configurar Layout

Edita `config/livewire.php`:

```php
return [
    'layout' => 'layouts.app',
    'middleware_group' => ['web', 'auth'],
];
```

---

## 🎨 Configuración de Cursor (IDE)

### Configuración Recomendada

Crear `.cursor/config.json`:

```json
{
  "php.suggest.basic": true,
  "php.validate.executablePath": "/usr/bin/php",
  "intelephense.environment.phpVersion": "8.2.0",
  "intelephense.files.exclude": [
    "**/vendor/**",
    "**/node_modules/**"
  ],
  "editor.formatOnSave": true,
  "editor.codeActionsOnSave": {
    "source.fixAll": true
  }
}
```

### Extensiones Recomendadas

- PHP Intelephense
- Laravel Extra Intellisense
- Laravel Blade Snippets
- Pest Snippets
- EditorConfig

---

## 🚀 Levantar el Proyecto

### Servidor de Desarrollo

```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Vite (Assets)
npm run dev

# Terminal 3: Queue Worker (opcional)
php artisan queue:work

# Terminal 4: Laravel Horizon (opcional, para queues avanzadas)
php artisan horizon
```

### Acceder a la Aplicación

- **Web App**: http://localhost:8000
- **API**: http://localhost:8000/api
- **MCP Servers**: http://localhost:8000/mcp/*

---

## 🧹 Comandos de Mantenimiento

### Limpiar Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Optimizaciones (Producción)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

### Reset Completo

```bash
php artisan migrate:fresh --seed
php artisan cache:clear
npm run build
```

---

## 🐳 Docker Setup (Alternativo)

### Docker Compose

Crear `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www/html
    depends_on:
      - postgres
      - redis
    environment:
      - DB_HOST=postgres
      - REDIS_HOST=redis

  postgres:
    image: postgres:16-alpine
    ports:
      - "5432:5432"
    environment:
      POSTGRES_DB: taskmaster
      POSTGRES_USER: taskmaster
      POSTGRES_PASSWORD: secret
    volumes:
      - postgres_data:/var/lib/postgresql/data

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

volumes:
  postgres_data:
```

### Levantar con Docker

```bash
docker-compose up -d
docker-compose exec app php artisan migrate --seed
```

---

## 🔍 Troubleshooting

### Error: SQLSTATE[08006]

**Problema**: No puede conectar a PostgreSQL

**Solución**:
```bash
# Verificar que PostgreSQL esté corriendo
sudo systemctl status postgresql

# Verificar conexión
psql -U taskmaster -d taskmaster -h 127.0.0.1
```

### Error: OpenAI API Key Invalid

**Problema**: Clave de OpenAI inválida

**Solución**:
```bash
# Verificar variable de entorno
php artisan tinker
>>> env('OPENAI_API_KEY')

# Limpiar cache de config
php artisan config:clear
```

### Error: Class 'Livewire\Component' not found

**Problema**: Livewire no está correctamente instalado

**Solución**:
```bash
composer require livewire/livewire
php artisan livewire:publish --assets
```

### Error: Permission Denied en storage/

**Problema**: Permisos incorrectos en directorios

**Solución**:
```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## ✅ Verificación Final

### Checklist

```bash
# ✓ PHP versión correcta
php -v

# ✓ Composer instalado
composer --version

# ✓ Base de datos conectada
php artisan db:monitor

# ✓ Migraciones ejecutadas
php artisan migrate:status

# ✓ MCP servers registrados
php artisan mcp:list

# ✓ Tests pasando
./vendor/bin/pest

# ✓ OpenAI conectado
php artisan tinker
>>> OpenAI::chat()->create([...])
```

---

## 📚 Próximos Pasos

1. ✅ Lee `docs/CLAUDE.md` para entender la arquitectura
2. ✅ Lee `docs/AGENTS.md` para aprender sobre MCP servers
3. ✅ Explora `docs/MCP_USAGE.md` para ver ejemplos
4. ✅ Ejecuta los tests: `./vendor/bin/pest`
5. ✅ Crea tu primer servidor MCP personalizado

---

**Última actualización**: 2025-11-12
**Soporte**: Consulta la documentación en `docs/`
