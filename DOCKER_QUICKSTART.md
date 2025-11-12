# 🐳 Docker Quick Start - TaskMaster AI

Guía rápida para levantar TaskMaster AI con Docker en menos de 5 minutos.

## ⚡ Instalación Rápida

### Opción 1: Usando Make (Recomendado)

```bash
# 1. Clonar el repositorio
git clone <repository-url>
cd laravelmcp

# 2. Instalar todo automáticamente
make install
```

Eso es todo! El comando `make install` hace:
- ✅ Construye los contenedores Docker
- ✅ Inicia todos los servicios
- ✅ Instala dependencias PHP (Composer)
- ✅ Instala dependencias Node.js
- ✅ Genera la clave de aplicación
- ✅ Ejecuta migraciones
- ✅ Carga datos de prueba (seeders)

### Opción 2: Manual

```bash
# 1. Construir contenedores
docker-compose build

# 2. Iniciar servicios
docker-compose up -d

# 3. Instalar dependencias
docker-compose exec app composer install
docker-compose exec node npm install

# 4. Configurar Laravel
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate

# 5. Base de datos
docker-compose exec app php artisan migrate --seed
```

## 🌐 Acceder a la Aplicación

Una vez iniciado, accede a:

| Servicio | URL | Descripción |
|----------|-----|-------------|
| **Aplicación Principal** | http://localhost:8000 | TaskMaster AI |
| **Adminer (DB)** | http://localhost:8080 | Administrador de PostgreSQL |
| **Vite Dev Server** | http://localhost:5173 | Hot Module Replacement |

### Credenciales por Defecto

**Adminer (PostgreSQL):**
- Sistema: `PostgreSQL`
- Servidor: `postgres`
- Usuario: `taskmaster`
- Contraseña: `secret`
- Base de datos: `taskmaster`

**Aplicación (después de seeders):**
- Email: `admin@taskmaster.ai`
- Password: `password`

## 📋 Comandos Comunes

### Gestión de Contenedores

```bash
make up          # Iniciar todos los servicios
make down        # Detener todos los servicios
make restart     # Reiniciar servicios
make logs        # Ver logs en tiempo real
make status      # Ver estado de contenedores
```

### Laravel / Artisan

```bash
make shell       # Abrir terminal en el contenedor
make artisan cmd="migrate"        # Ejecutar comando artisan
make migrate     # Ejecutar migraciones
make seed        # Ejecutar seeders
make fresh       # Reiniciar BD con datos de prueba
make test        # Ejecutar tests
```

### Composer & NPM

```bash
make composer         # Instalar dependencias PHP
make npm-install      # Instalar dependencias Node
make npm-dev          # Compilar assets (desarrollo)
make npm-build        # Compilar assets (producción)
```

### Cache & Optimización

```bash
make optimize    # Optimizar Laravel (cache configs)
make clear       # Limpiar todos los caches
```

### Base de Datos

```bash
make backup-db                    # Crear backup de PostgreSQL
make restore-db file=backup.sql   # Restaurar backup
```

## 🔧 Configuración Inicial

### 1. Variables de Entorno

El archivo `.env.example` ya está configurado para Docker. Las variables importantes son:

```env
# Base de datos (usa nombres de servicio Docker)
DB_HOST=postgres
DB_DATABASE=taskmaster
DB_USERNAME=taskmaster
DB_PASSWORD=secret

# Redis (para cache y queues)
REDIS_HOST=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis

# OpenAI (requerido para funciones AI)
OPENAI_API_KEY=sk-...
```

### 2. Configurar OpenAI API

Para usar las funcionalidades de AI, necesitas una API key de OpenAI:

```bash
# Editar .env
docker-compose exec app nano .env

# O desde tu editor local, luego reiniciar
make restart
```

Agrega tu clave:
```env
OPENAI_API_KEY=sk-tu-clave-aqui
OPENAI_DEFAULT_MODEL=gpt-4-turbo-preview
```

## 🚀 Flujo de Trabajo de Desarrollo

### Desarrollo Diario

```bash
# 1. Iniciar servicios
make up

# 2. Ver logs mientras trabajas (opcional)
make logs

# 3. Ejecutar comandos cuando necesites
make artisan cmd="make:controller MyController"
make test

# 4. Al terminar el día
make down
```

### Hacer Cambios en el Código

Los cambios se reflejan automáticamente gracias a los volúmenes de Docker:
- ✅ **PHP/Laravel**: Cambios instantáneos
- ✅ **Livewire**: Cambios instantáneos
- ✅ **CSS/JS (Vite)**: Hot Module Replacement activo en puerto 5173

### Agregar Dependencias

```bash
# PHP
docker-compose exec app composer require vendor/package

# Node.js
docker-compose exec node npm install package-name
```

## 🧪 Testing

```bash
# Ejecutar todos los tests
make test

# Con coverage
make test-coverage

# Tests específicos
docker-compose exec app php artisan test --filter=UserTest
```

## 🐛 Troubleshooting

### Los contenedores no inician

```bash
# Ver qué está pasando
docker-compose logs

# Reconstruir desde cero
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Error 500 en la aplicación

```bash
# Revisar logs de Laravel
make logs-app

# Limpiar caches
make clear

# Verificar permisos
make permissions
```

### PostgreSQL no conecta

```bash
# Verificar que esté corriendo
docker-compose ps postgres

# Ver logs
docker-compose logs postgres

# Verificar conectividad
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Assets no cargan

```bash
# Recompilar assets
docker-compose exec node npm run build

# O en modo desarrollo con HMR
docker-compose exec node npm run dev
```

### Limpiar Todo y Empezar de Nuevo

```bash
# Eliminar TODO (contenedores, volúmenes, imágenes)
make prune

# Reinstalar desde cero
make install
```

## 📊 Monitoreo

### Ver uso de recursos

```bash
# Estadísticas en tiempo real
docker stats

# Solo contenedores de TaskMaster
docker stats taskmaster_app taskmaster_postgres taskmaster_redis
```

### Verificar servicios

```bash
# PostgreSQL
docker-compose exec postgres pg_isready -U taskmaster

# Redis
docker-compose exec redis redis-cli ping

# Aplicación
curl http://localhost:8000
```

## 🔐 MCP Servers

### Iniciar servidor MCP

```bash
# Task Tools Server
make mcp-start server=task-tools

# Otros servidores disponibles
make mcp-start server=ai-assistant
make mcp-start server=analytics-resources
make mcp-start server=reports
make mcp-start server=task-prompts
make mcp-start server=ai-prompts
```

### MCP Inspector

```bash
# Abrir inspector para debugging
make mcp-inspector
```

## 📦 Estructura de Servicios

```
TaskMaster AI Docker Stack
├── app (PHP 8.3 FPM)           → Puerto 9000 (interno)
├── nginx (Web Server)          → Puerto 8000 (http://localhost:8000)
├── postgres (PostgreSQL 16)    → Puerto 5432
├── redis (Redis 7)             → Puerto 6379
├── node (Node.js 20 + Vite)    → Puerto 5173 (HMR)
├── queue (Laravel Queue)       → Worker en background
├── scheduler (Laravel Cron)    → Tareas programadas
└── adminer (DB UI)             → Puerto 8080 (http://localhost:8080)
```

## 🎯 Próximos Pasos

1. **Explorar la aplicación**: http://localhost:8000
2. **Ver la base de datos**: http://localhost:8080
3. **Crear un proyecto y tareas** en la UI
4. **Probar los MCP servers** con el inspector
5. **Configurar OpenAI** para funciones de AI
6. **Desarrollar nuevas features** con hot reload activo

## 📚 Más Información

- **Documentación completa de Docker**: `docker/README.md`
- **Documentación del proyecto**: `docs/`
- **Ayuda con Make**: `make help`

## 💡 Tips

- Usa `make help` para ver todos los comandos disponibles
- Los logs se guardan en `storage/logs/`
- Para producción, usa `docker-compose.prod.yml`
- Xdebug está habilitado por defecto en desarrollo
- Los volúmenes persisten datos incluso después de `make down`

## ❓ Necesitas Ayuda?

1. Revisa los logs: `make logs`
2. Consulta troubleshooting arriba
3. Lee la documentación extendida en `docker/README.md`
4. Verifica que todos los servicios estén corriendo: `make status`

---

**¡Listo para desarrollar! 🚀**

```bash
make up && make logs
```
