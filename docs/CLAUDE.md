# CLAUDE.md - TaskMaster AI Platform

## 🎯 Propósito del Proyecto

**TaskMaster AI Platform** es un sistema de gestión de tareas inteligente construido con Laravel que demuestra la integración completa del Model Context Protocol (MCP). El proyecto sirve como:

1. **Tutorial práctico** de Laravel MCP con casos de uso reales
2. **Proyecto portfolio** que demuestra arquitectura profesional
3. **Ejemplo de integración** con OpenAI API
4. **Implementación DDD** con SOLID principles

## 🏗️ Arquitectura

### Estructura DDD (Domain-Driven Design)

```
app/
├── Domain/              # Lógica de negocio pura
│   ├── TaskManagement/
│   │   ├── Models/      # Eloquent Models
│   │   ├── ValueObjects/
│   │   ├── Repositories/
│   │   ├── Services/
│   │   └── Events/
│   ├── AIIntegration/
│   │   ├── Services/
│   │   └── Contracts/
│   └── Analytics/
│       ├── Services/
│       └── ValueObjects/
├── Application/         # Casos de uso
│   ├── UseCases/
│   └── DTOs/
├── Infrastructure/      # Implementaciones técnicas
│   ├── Persistence/
│   ├── External/
│   └── MCP/            # Servidores MCP
└── Presentation/        # Interfaces
    ├── Http/
    └── Livewire/
```

### Bounded Contexts

#### 1. Task Management
- **Responsabilidad**: Gestión completa de proyectos, tareas y etiquetas
- **Entidades**: Project, Task, Tag, Comment
- **Value Objects**: Priority, Status, TaskDescription
- **MCP Servers**: Task Tools, Task Prompts

#### 2. AI Integration
- **Responsabilidad**: Integración con OpenAI para generación de contenido
- **Servicios**: OpenAIService, PromptBuilder, ContentGenerator
- **MCP Servers**: AI Assistant Tools, AI Prompts Library

#### 3. Analytics & Reporting
- **Responsabilidad**: Métricas, estadísticas y reportes
- **Servicios**: AnalyticsService, ReportGenerator
- **MCP Servers**: Analytics Resources, Reports Tools

## 🔌 Servidores MCP

### Task Tools Server (`/mcp/tasks`)
**Tipo**: Tools (Acciones)
**Puerto Web**: POST /mcp/tasks
**Comando Local**: `php artisan mcp:serve tasks`

**Herramientas Disponibles:**
- `create_task`: Crear nueva tarea
- `update_task`: Actualizar tarea existente
- `delete_task`: Eliminar tarea
- `search_tasks`: Búsqueda avanzada
- `assign_task`: Asignar tarea a usuario
- `change_status`: Cambiar estado de tarea

### AI Assistant Tools Server (`/mcp/ai`)
**Tipo**: Tools (Acciones con IA)
**Puerto Web**: POST /mcp/ai

**Herramientas Disponibles:**
- `generate_task_description`: Generar descripción detallada
- `summarize_project`: Resumir estado del proyecto
- `suggest_priorities`: IA sugiere prioridades
- `generate_report`: Generar reporte automático
- `analyze_sentiment`: Analizar sentimiento en comentarios

### Analytics Resources Server (`/mcp/analytics`)
**Tipo**: Resources (Datos de lectura)
**Puerto Web**: GET /mcp/analytics

**Recursos Expuestos:**
- `analytics://productivity`: Métricas de productividad
- `analytics://team-stats`: Estadísticas del equipo
- `analytics://project-health`: Salud de proyectos
- `analytics://time-tracking`: Seguimiento de tiempo

### Reports Tools Server (`/mcp/reports`)
**Tipo**: Tools (Generación de documentos)
**Puerto Web**: POST /mcp/reports

**Herramientas Disponibles:**
- `generate_weekly_report`: Reporte semanal
- `team_productivity_report`: Análisis de equipo
- `project_status_report`: Estado de proyectos
- `export_tasks`: Exportar tareas (CSV/PDF)

### Task Prompts Server (`/mcp/prompts/tasks`)
**Tipo**: Prompts (Plantillas)
**Comando Local**: `php artisan mcp:serve task-prompts`

**Prompts Disponibles:**
- `create-feature-task`: Plantilla para tareas de features
- `create-bug-task`: Plantilla para bugs
- `estimate-effort`: Estimar esfuerzo de tarea
- `retrospective`: Template retrospectiva

### AI Prompts Library Server (`/mcp/prompts/ai`)
**Tipo**: Prompts (Biblioteca)
**Comando Local**: `php artisan mcp:serve ai-prompts`

**Biblioteca de Prompts:**
- `technical-description`: Descripciones técnicas
- `user-story`: Generar user stories
- `acceptance-criteria`: Criterios de aceptación
- `code-review`: Prompts para code review

## 🔐 Autenticación

### Usuarios Web (Sanctum)
```php
// Login tradicional
POST /api/login
POST /api/register
POST /api/logout
```

### MCP Servers (OAuth 2.1)
```php
// Configurado en routes/ai.php
Mcp::web('/mcp/tasks', TaskToolsServer::class)
    ->middleware(['auth:sanctum']);
```

## 🧪 Testing

### Estrategia TDD

```bash
# Tests unitarios (Domain)
./vendor/bin/pest tests/Unit

# Tests de integración (Application)
./vendor/bin/pest tests/Feature

# Tests de MCP servers
./vendor/bin/pest tests/MCP

# Tests E2E con Livewire
./vendor/bin/pest tests/Livewire
```

### Cobertura Esperada
- Domain Layer: 100%
- Application Layer: >90%
- MCP Servers: >85%
- Livewire Components: >80%

## 🚀 Comandos Útiles

```bash
# Desarrollo
php artisan serve
php artisan queue:work
npm run dev

# MCP Servers
php artisan mcp:list                    # Listar servidores
php artisan mcp:serve tasks             # Servidor local
php artisan mcp:test /mcp/tasks         # Probar servidor

# Testing
php artisan test                        # PHPUnit
./vendor/bin/pest                       # Pest
./vendor/bin/pest --coverage            # Con cobertura

# Code Quality
./vendor/bin/pint                       # Formatear código
./vendor/bin/phpstan analyse            # Análisis estático

# Database
php artisan migrate:fresh --seed        # Reset DB con datos
php artisan db:seed --class=DemoSeeder  # Solo datos demo
```

## 📝 Principios de Desarrollo

### SOLID
- **S**ingle Responsibility: Cada clase tiene una única responsabilidad
- **O**pen/Closed: Abierto a extensión, cerrado a modificación
- **L**iskov Substitution: Interfaces y contratos claros
- **I**nterface Segregation: Interfaces específicas
- **D**ependency Inversion: Depender de abstracciones

### DRY (Don't Repeat Yourself)
- Reutilización mediante servicios compartidos
- Traits para comportamientos comunes
- Base classes para funcionalidad común

### Clean Code
- Nombres descriptivos
- Funciones pequeñas y enfocadas
- Comentarios solo cuando son necesarios
- Tests como documentación

## 🔧 Configuración

### Variables de Entorno Importantes

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=taskmaster
DB_USERNAME=postgres
DB_PASSWORD=secret

# OpenAI
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=org-...

# MCP
MCP_ENABLED=true
MCP_AUTH_DRIVER=sanctum

# Laravel Boost
BOOST_ENABLED=true
```

## 📚 Recursos Adicionales

- [Laravel MCP Documentation](https://laravel.com/docs/12.x/mcp)
- [Model Context Protocol Spec](https://spec.modelcontextprotocol.io)
- [OpenAI PHP Client](https://github.com/openai-php/laravel)
- [Laravel Boost](https://laravel.com/ai/boost)

## 🎓 Objetivo de Aprendizaje

Este proyecto te enseñará:
1. ✅ Cómo implementar servidores MCP en Laravel
2. ✅ Diferencias entre Tools, Resources y Prompts
3. ✅ Integración de OpenAI en aplicaciones Laravel
4. ✅ Arquitectura DDD en proyectos reales
5. ✅ Testing profesional con TDD
6. ✅ Livewire para UIs reactivas
7. ✅ Best practices de Laravel

## 🤝 Para Colaboradores (Claude)

### Al trabajar en este proyecto:
1. **Respeta la arquitectura DDD** - No mezclar capas
2. **Escribe tests primero** (TDD)
3. **Usa type hints** en todos los métodos
4. **Documenta las clases públicas** con PHPDoc
5. **Sigue PSR-12** (Laravel Pint configurado)
6. **Commits semánticos**: `feat:`, `fix:`, `docs:`, `test:`

### Flujo de trabajo:
```bash
# 1. Crear test (falla)
# 2. Implementar funcionalidad (test pasa)
# 3. Refactorizar
# 4. Commit
```

---

**Versión**: 1.0.0
**Laravel**: 11.x
**PHP**: 8.2+
**Autor**: Tutorial Laravel MCP
**Licencia**: MIT
