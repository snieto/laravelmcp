# Tutorial Completo: Laravel MCP - De Principiante a Experto

## Índice General

### Parte 1: Fundamentos
1. **Introducción a Laravel MCP** ✓
2. Conceptos Básicos MCP
3. Arquitectura del Proyecto

### Parte 2: Domain Layer
4. Value Objects
5. Repositories Pattern
6. Domain Services

### Parte 3: MCP Servers - Tools
7. TaskToolsServer
8. AIAssistantServer

### Parte 4: MCP Servers - Resources
9. AnalyticsResourcesServer

### Parte 5: MCP Servers - Reports
10. ReportsServer

### Parte 6: Infrastructure
11. Eloquent Models y Relationships
12. Repository Implementation

### Parte 7: Práctica
13. Conexión y Uso de MCP Servers
14. Testing
15. Extensión del Proyecto

---

# Capítulo 1: Introducción a Laravel MCP

## 1.1 ¿Qué es MCP (Model Context Protocol)?

**MCP (Model Context Protocol)** es un protocolo de comunicación abierto desarrollado por Anthropic que permite a las aplicaciones de IA (como Claude) interactuar con servicios externos de manera estructurada y segura.

### Analogía Simple

Imagina que tienes un asistente de IA (Claude) que es muy inteligente pero **no tiene acceso directo a tus datos**. Es como tener un empleado brillante trabajando desde casa, pero necesita que le envíes los documentos y herramientas para hacer su trabajo.

**MCP es el sistema de mensajería** que permite:
- Que el asistente te pida información específica
- Que ejecute acciones en tu sistema (con tu permiso)
- Que acceda a datos en tiempo real
- Todo de forma estructurada y segura

### ¿Por qué es Importante?

Antes de MCP, para que una IA interactuara con tu aplicación necesitabas:
1. Copiar y pegar datos manualmente
2. Crear APIs REST personalizadas
3. Escribir código de integración complejo

**Con MCP**, defines una vez:
- Qué datos puede leer (Resources)
- Qué acciones puede ejecutar (Tools)
- Qué plantillas puede usar (Prompts)

Y cualquier cliente compatible con MCP puede usarlo inmediatamente.

## 1.2 ¿Qué es Laravel MCP?

**Laravel MCP** es un paquete oficial de Laravel que implementa el protocolo MCP, permitiéndote crear **servidores MCP** directamente en tus aplicaciones Laravel.

### Ventajas de Laravel MCP

1. **Integración Nativa con Laravel**
   - Usa Eloquent, Dependency Injection, Service Providers
   - Se integra con tu arquitectura existente
   - No necesitas aprender un framework nuevo

2. **Tipado Fuerte**
   - JSON Schema para validación de entrada
   - Type hints de PHP para seguridad de tipos
   - Autocompletado en tu IDE

3. **Arquitectura Limpia**
   - Separación clara entre Tools, Resources, Prompts
   - Cada servidor MCP es independiente
   - Fácil de mantener y extender

4. **Reutilización de Código**
   - Usa tus repositorios existentes
   - Comparte lógica de negocio
   - No duplicas código

## 1.3 Los Tres Pilares de MCP

MCP se basa en tres conceptos fundamentales:

### 1. **Tools (Herramientas)**

Son **acciones que la IA puede ejecutar**. Como botones que la IA puede presionar.

**Ejemplos prácticos:**
- `create-task`: Crear una nueva tarea
- `send-email`: Enviar un correo electrónico
- `generate-report`: Generar un reporte

**Características:**
- Pueden modificar datos (escritura)
- Reciben parámetros de entrada
- Devuelven resultados
- Tienen validación de entrada

**Analogía:** Tools son como **funciones de una API** que la IA puede llamar.

### 2. **Resources (Recursos)**

Son **datos de solo lectura** que la IA puede consultar. Como archivos que la IA puede leer.

**Ejemplos prácticos:**
- `team-metrics`: Métricas del equipo
- `project-status`: Estado de proyectos
- `user-list`: Lista de usuarios

**Características:**
- Solo lectura (no modifican datos)
- Actualizados en tiempo real
- Pueden tener parámetros (filtros)
- Devuelven datos estructurados

**Analogía:** Resources son como **endpoints GET de solo lectura** o **vistas de base de datos**.

### 3. **Prompts (Plantillas)**

Son **plantillas de instrucciones reutilizables** para la IA.

**Ejemplos prácticos:**
- `code-review-prompt`: Plantilla para revisar código
- `bug-report-template`: Plantilla para reportar bugs
- `standup-format`: Formato para daily standups

**Características:**
- Incluyen contexto específico
- Pueden tener parámetros dinámicos
- Estandarizan workflows
- Mejoran consistencia

**Analogía:** Prompts son como **macros o snippets** que estandarizan cómo se comunica la IA.

## 1.4 ¿Cuándo Usar Cada Uno?

### Usa **Tools** cuando necesites:
- ✅ Crear, actualizar o eliminar datos
- ✅ Ejecutar acciones con efectos secundarios
- ✅ Integrar con APIs externas
- ✅ Realizar cálculos complejos

**Ejemplo real del proyecto:**
```php
// Tool: CreateTask
// Permite a la IA crear tareas en el sistema
$task = createTask([
    'title' => 'Implementar login',
    'priority' => 'high',
    'assigned_to' => 5
]);
```

### Usa **Resources** cuando necesites:
- ✅ Proporcionar datos de solo lectura
- ✅ Compartir métricas en tiempo real
- ✅ Exponer información de configuración
- ✅ Permitir consultas sin modificaciones

**Ejemplo real del proyecto:**
```php
// Resource: TeamMetrics
// La IA puede consultar métricas sin modificar datos
$metrics = readResource('team-metrics', [
    'days' => 30
]);
```

### Usa **Prompts** cuando necesites:
- ✅ Estandarizar workflows repetitivos
- ✅ Proporcionar contexto especializado
- ✅ Crear plantillas reutilizables
- ✅ Mejorar consistencia en respuestas

**Ejemplo conceptual:**
```php
// Prompt: TaskDescriptionTemplate
// Genera descripciones de tareas consistentes
$prompt = usePrompt('task-description', [
    'task_type' => 'feature',
    'complexity' => 'medium'
]);
```

## 1.5 Arquitectura de un Servidor MCP en Laravel

Un servidor MCP en Laravel tiene esta estructura:

```
MCP Server
├── Nombre y Versión
├── Instrucciones (para la IA)
├── Tools (array de herramientas)
│   ├── CreateTask
│   ├── UpdateTask
│   └── DeleteTask
├── Resources (array de recursos)
│   ├── TeamMetrics
│   └── ProjectStatus
└── Prompts (array de plantillas)
    ├── CodeReviewPrompt
    └── BugReportPrompt
```

### Ejemplo: TaskToolsServer del Proyecto

```php
<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class TaskToolsServer extends Server
{
    // Identificación del servidor
    protected string $name = 'Task Management Tools';
    protected string $version = '1.0.0';

    // Instrucciones para la IA
    protected string $instructions = <<<'MARKDOWN'
        Este servidor proporciona herramientas para gestionar tareas.
        Puedes crear, listar, actualizar y eliminar tareas.
    MARKDOWN;

    // Herramientas disponibles
    protected array $tools = [
        \App\Mcp\Tools\CreateTask::class,
        \App\Mcp\Tools\ListTasks::class,
        \App\Mcp\Tools\UpdateTask::class,
        \App\Mcp\Tools\DeleteTask::class,
    ];

    // Este servidor no tiene resources ni prompts
    protected array $resources = [];
    protected array $prompts = [];
}
```

## 1.6 Flujo de Comunicación MCP

### Paso a Paso: ¿Cómo Funciona?

**1. Descubrimiento**
```
Cliente MCP (Claude) → "¿Qué servidores MCP tienes?"
Laravel App → "Tengo TaskToolsServer, AIAssistantServer, AnalyticsResourcesServer"
```

**2. Inspección**
```
Cliente → "¿Qué puede hacer TaskToolsServer?"
Laravel → "Puede create-task, list-tasks, update-task, delete-task"
```

**3. Schema Request**
```
Cliente → "¿Qué parámetros necesita create-task?"
Laravel → {
    "title": "string, required, 3-255 chars",
    "priority": "enum: low|medium|high|critical",
    "project_id": "integer, required"
}
```

**4. Ejecución**
```
Cliente → create-task({
    "title": "Fix login bug",
    "priority": "high",
    "project_id": 1
})
Laravel → Ejecuta CreateTask::handle()
Laravel → Valida entrada contra JSON Schema
Laravel → Llama al TaskRepository
Laravel → Crea la tarea en la base de datos
Laravel → Retorna resultado
```

**5. Respuesta**
```
Laravel → {
    "success": true,
    "task": {
        "id": 42,
        "title": "Fix login bug",
        "status": "pending",
        "priority": "high"
    }
}
Cliente → Procesa respuesta y continúa conversación
```

## 1.7 Beneficios de MCP en Este Proyecto

### 1. **Separación de Responsabilidades**

```
┌─────────────────────────────────────┐
│   Domain Layer (Lógica de Negocio) │
│   - TaskStatusManager               │
│   - TaskPriorityCalculator          │
└─────────────────────────────────────┘
                ↑
                │ Usa
                │
┌─────────────────────────────────────┐
│   MCP Layer (Interfaz para IA)     │
│   - CreateTask Tool                 │
│   - TeamMetrics Resource            │
└─────────────────────────────────────┘
                ↑
                │ Llama
                │
┌─────────────────────────────────────┐
│   Cliente MCP (Claude, etc)         │
└─────────────────────────────────────┘
```

**Ventaja:** Tu lógica de negocio es independiente de MCP. Si MCP cambia, solo modificas la capa MCP.

### 2. **Reutilización**

El mismo `TaskRepository` se usa en:
- ✅ Controladores web tradicionales
- ✅ API REST
- ✅ MCP Tools
- ✅ Comandos de consola
- ✅ Jobs en cola

### 3. **Validación Automática**

```php
public function schema(JsonSchema $schema): array
{
    return [
        'title' => $schema->string()
            ->minLength(3)
            ->maxLength(255)
            ->required(),
        'priority' => $schema->string()
            ->enum(['low', 'medium', 'high', 'critical'])
            ->default('medium'),
    ];
}
```

Laravel MCP valida automáticamente:
- ✅ Tipos de datos correctos
- ✅ Campos requeridos presentes
- ✅ Valores dentro de rangos permitidos
- ✅ Enums con valores válidos

### 4. **Documentación Automática**

Cada Tool, Resource y Prompt incluye:
- Descripción de qué hace
- Parámetros que acepta
- Tipos de datos esperados
- Valores por defecto

**La IA lee esta documentación automáticamente** y sabe cómo usar cada herramienta sin intervención manual.

## 1.8 Comparación: MCP vs REST API

| Aspecto | REST API | Laravel MCP |
|---------|----------|-------------|
| **Propósito** | Humanos y apps | IAs y agentes autónomos |
| **Descubrimiento** | Manual (docs) | Automático (schema) |
| **Validación** | FormRequest | JSON Schema integrado |
| **Documentación** | OpenAPI/Swagger | Metadata incluido |
| **Versionado** | URL-based | Server-level |
| **Autenticación** | JWT/OAuth | MCP protocol-level |

**No son excluyentes:** Tu aplicación puede tener ambos.

## 1.9 El Proyecto TaskMaster AI

Este tutorial usa un proyecto real de gestión de tareas con IA llamado **TaskMaster AI**.

### Funcionalidades Principales

**Gestión de Tareas:**
- Crear, listar, actualizar, eliminar tareas
- Asignar tareas a usuarios
- Gestionar estados y prioridades
- Agregar comentarios y etiquetas

**Inteligencia Artificial:**
- Generar descripciones de tareas
- Mejorar títulos de tareas
- Sugerir prioridades automáticamente
- Analizar productividad del equipo

**Analytics y Reportes:**
- Métricas del equipo en tiempo real
- Análisis de velocidad y tendencias
- Reportes de sprint
- Reportes de estado de proyectos
- Exportación de datos

### Stack Tecnológico

- **Backend:** Laravel 11
- **Base de Datos:** PostgreSQL
- **Frontend:** Livewire 3
- **Estilos:** Tailwind CSS
- **IA:** OpenAI GPT-4
- **Testing:** Pest PHP
- **Contenedores:** Docker

### Arquitectura DDD

El proyecto usa **Domain-Driven Design** con 3 bounded contexts:

1. **TaskManagement:** Gestión de tareas, proyectos, etiquetas
2. **AIIntegration:** Servicios de IA y generación de contenido
3. **Analytics:** Métricas, reportes, análisis de datos

## 1.10 Estructura de Directorios MCP

```
app/Mcp/
├── Servers/              # Servidores MCP
│   ├── TaskToolsServer.php
│   ├── AIAssistantServer.php
│   ├── AnalyticsResourcesServer.php
│   └── ReportsServer.php
├── Tools/                # Herramientas (acciones)
│   ├── CreateTask.php
│   ├── ListTasks.php
│   ├── UpdateTask.php
│   ├── GenerateTaskDescription.php
│   └── ExportTasks.php
└── Resources/            # Recursos (datos)
    ├── TeamMetrics.php
    ├── ProjectMetrics.php
    └── VelocityTrends.php
```

## 1.11 Tu Primer Vistazo: CreateTask Tool

Veamos un ejemplo real simplificado:

```php
<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Illuminate\JsonSchema\JsonSchema;

class CreateTask extends Tool
{
    // ¿Qué hace esta herramienta?
    protected string $description = 'Create a new task in the system';

    // Constructor con dependencias inyectadas
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {}

    // Lógica de ejecución
    public function handle(Request $request): Response
    {
        // 1. Extraer datos de la petición
        $data = [
            'title' => $request->input('title'),
            'priority' => Priority::from($request->input('priority', 'medium')),
            'status' => Status::PENDING,
        ];

        // 2. Crear tarea usando el repositorio
        $task = $this->taskRepository->create($data);

        // 3. Retornar respuesta
        return Response::json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
            ],
        ]);
    }

    // Schema de validación
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()
                ->minLength(3)
                ->maxLength(255)
                ->required(),
            'priority' => $schema->string()
                ->enum(['low', 'medium', 'high', 'critical'])
                ->default('medium'),
        ];
    }
}
```

### Desglose Línea por Línea

**Líneas 1-5:** Imports necesarios
- `Tool`: Clase base para todas las herramientas
- `Request`: Petición entrante con parámetros
- `Response`: Respuesta que enviaremos
- `JsonSchema`: Para definir validación

**Línea 8:** Extender de `Tool`
- Todas las herramientas MCP heredan de esta clase

**Líneas 10-11:** Descripción
- La IA lee esto para saber qué hace la herramienta
- Debe ser claro y conciso

**Líneas 13-16:** Dependency Injection
- Laravel inyecta automáticamente `TaskRepositoryInterface`
- No necesitas instanciar manualmente
- Facilita testing y mantenimiento

**Línea 19:** Método `handle()`
- Recibe el `Request` con los parámetros
- Ejecuta la lógica
- Retorna un `Response`

**Líneas 21-25:** Preparar datos
- Extraemos parámetros del request
- Convertimos strings a Value Objects (`Priority::from()`)
- Establecemos valores por defecto (`Status::PENDING`)

**Línea 28:** Crear tarea
- Usamos el repositorio (patrón Repository)
- El repositorio maneja la persistencia
- Independiente de Eloquent (podríamos cambiar a MongoDB)

**Líneas 31-38:** Retornar respuesta
- Formato JSON estructurado
- `success: true` indica éxito
- Incluimos datos relevantes de la tarea creada

**Líneas 42-53:** Schema de validación
- Define qué parámetros acepta
- Tipos de datos
- Validaciones (longitud, enum, required)
- Laravel MCP valida automáticamente antes de llamar a `handle()`

## 1.12 ¿Qué Aprenderás en Este Tutorial?

### Nivel Principiante
- ✅ Qué es MCP y por qué existe
- ✅ Diferencia entre Tools, Resources, Prompts
- ✅ Crear tu primer MCP Server
- ✅ Crear tu primer Tool con validación
- ✅ Conectar Claude a tu servidor

### Nivel Intermedio
- ✅ Arquitectura DDD con MCP
- ✅ Value Objects y por qué usarlos
- ✅ Repository Pattern en profundidad
- ✅ Domain Services y lógica de negocio
- ✅ Testing de MCP Tools

### Nivel Avanzado
- ✅ Integración con OpenAI
- ✅ Analytics en tiempo real
- ✅ Generación de reportes complejos
- ✅ Optimización y caching
- ✅ Extensión del sistema

## 1.13 Prerrequisitos

### Conocimientos Necesarios
- PHP 8.1+ básico (clases, interfaces, enums)
- Laravel básico (Eloquent, Service Providers, Dependency Injection)
- SQL básico (queries, relaciones)
- Conceptos de API REST

### Conocimientos Opcionales (Ayudan)
- Domain-Driven Design (lo explicaremos)
- Testing con Pest/PHPUnit
- Docker y contenedores
- Livewire 3

### Software Requerido
- PHP 8.1 o superior
- Composer
- PostgreSQL (o Docker)
- Git
- IDE (recomendado: VS Code, PhpStorm, Cursor)

## 1.14 Cómo Usar Este Tutorial

### Estructura de Cada Capítulo

**📚 Teoría:** Conceptos y fundamentos
**💡 Ejemplos:** Código real comentado
**🔨 Práctica:** Ejercicios opcionales
**⚠️ Errores Comunes:** Qué evitar
**🚀 Tips Avanzados:** Optimizaciones y mejores prácticas

### Recomendaciones

1. **Lee en orden:** Los capítulos se construyen uno sobre otro
2. **Practica:** No solo leas, ejecuta el código
3. **Experimenta:** Modifica ejemplos y observa resultados
4. **Revisa el código fuente:** El proyecto completo está disponible
5. **Haz preguntas:** Usa los comentarios o busca comunidad

## 1.15 Resumen del Capítulo 1

🎯 **Conceptos Clave Aprendidos:**

1. **MCP** es un protocolo para que IAs interactúen con servicios
2. **Laravel MCP** integra MCP nativamente en Laravel
3. **Tools** ejecutan acciones (escritura)
4. **Resources** proporcionan datos (solo lectura)
5. **Prompts** son plantillas reutilizables
6. El proyecto usa **DDD** con 3 bounded contexts
7. MCP se integra con tu arquitectura existente

🔜 **Siguiente Capítulo:**
En el Capítulo 2 profundizaremos en los **Conceptos Básicos de MCP**, viendo en detalle cómo funcionan Tools, Resources y Prompts con ejemplos prácticos del proyecto.

---

**Estado del Tutorial:** Capítulo 1 de 15 completado ✓

---

# Capítulo 2: Conceptos Básicos de MCP

## 2.1 Introducción a los Componentes MCP

En el Capítulo 1 aprendiste que MCP tiene tres pilares fundamentales. Ahora vamos a profundizar en cada uno con ejemplos reales del proyecto TaskMaster AI.

### Los Tres Componentes en Detalle

```
MCP Components
├── Tools (Herramientas)
│   ├── Ejecutan acciones
│   ├── Modifican estado
│   └── Ejemplo: CreateTask, UpdateTask
├── Resources (Recursos)
│   ├── Proporcionan datos
│   ├── Solo lectura
│   └── Ejemplo: TeamMetrics, ProjectMetrics
└── Prompts (Plantillas)
    ├── Templates reutilizables
    ├── Contexto especializado
    └── Ejemplo: TaskDescriptionTemplate
```

## 2.2 MCP Tools en Profundidad

### ¿Qué es un Tool?

Un **Tool** es una función que la IA puede ejecutar para realizar una acción específica en tu sistema. Es equivalente a un endpoint de API POST/PUT/DELETE, pero diseñado específicamente para que una IA lo use de manera autónoma.

### Anatomía de un Tool

Veamos en detalle cada parte de un Tool usando `CreateTask` como ejemplo:

```php
<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateTask extends Tool
{
    /**
     * Descripción del Tool para la IA
     */
    protected string $description = <<<'MARKDOWN'
        Create a new task in the project management system.

        This tool allows creating tasks with title, description, priority,
        and optional assignment to team members. Tasks start in 'pending' status.
    MARKDOWN;

    /**
     * Constructor con Dependency Injection
     */
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Lógica de ejecución del Tool
     */
    public function handle(Request $request): Response
    {
        // 1. Validar datos de entrada (automático vía schema)

        // 2. Preparar datos para creación
        $data = [
            'project_id' => $request->input('project_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'priority' => Priority::from($request->input('priority', 'medium')),
            'status' => Status::PENDING,
            'created_by' => $request->input('created_by'),
            'assigned_to' => $request->input('assigned_to'),
            'due_date' => $request->input('due_date'),
            'estimated_hours' => $request->input('estimated_hours'),
        ];

        // 3. Ejecutar acción de negocio
        $task = $this->taskRepository->create($data);

        // 4. Agregar tags si fueron proporcionados
        if ($tags = $request->input('tags')) {
            $this->taskRepository->attachTags($task->id, $tags);
        }

        // 5. Preparar respuesta exitosa
        return Response::json([
            'success' => true,
            'message' => "Task #{$task->id} '{$task->title}' created successfully",
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'project' => $task->project->name,
                'assigned_to' => $task->assignedTo?->name ?? 'Unassigned',
                'tags' => $task->tags->pluck('name')->toArray(),
                'created_at' => $task->created_at->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Schema de validación JSON
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()
                ->description('ID of the project this task belongs to')
                ->minimum(1)
                ->required(),

            'title' => $schema->string()
                ->description('Task title (clear and concise)')
                ->minLength(3)
                ->maxLength(255)
                ->required(),

            'description' => $schema->string()
                ->description('Detailed task description')
                ->optional(),

            'priority' => $schema->string()
                ->description('Task priority level')
                ->enum(['low', 'medium', 'high', 'critical'])
                ->default('medium')
                ->optional(),

            'created_by' => $schema->integer()
                ->description('User ID who creates the task')
                ->minimum(1)
                ->required(),

            'assigned_to' => $schema->integer()
                ->description('User ID to assign the task to')
                ->minimum(1)
                ->optional(),

            'due_date' => $schema->string()
                ->description('Due date in YYYY-MM-DD format')
                ->pattern('^\d{4}-\d{2}-\d{2}$')
                ->optional(),

            'estimated_hours' => $schema->number()
                ->description('Estimated hours to complete')
                ->minimum(0.5)
                ->maximum(1000)
                ->optional(),

            'tags' => $schema->array()
                ->description('Array of tag IDs to attach')
                ->items($schema->integer()->minimum(1))
                ->optional(),
        ];
    }
}
```

### Desglose Detallado

#### 1. **La Descripción (líneas 14-19)**

```php
protected string $description = <<<'MARKDOWN'
    Create a new task in the project management system.

    This tool allows creating tasks with title, description, priority,
    and optional assignment to team members. Tasks start in 'pending' status.
MARKDOWN;
```

**¿Para qué sirve?**
- La IA lee esta descripción para decidir cuándo usar el tool
- Debe ser clara y específica
- Puede incluir contexto importante (ej: "Tasks start in 'pending' status")

**Mejores prácticas:**
- ✅ Usa lenguaje claro y directo
- ✅ Explica qué hace, no cómo lo hace
- ✅ Menciona restricciones importantes
- ❌ No uses jerga técnica innecesaria
- ❌ No documentes implementación interna

#### 2. **Dependency Injection (líneas 21-25)**

```php
public function __construct(
    private readonly TaskRepositoryInterface $taskRepository
) {
}
```

**¿Por qué usar DI?**
- ✅ **Testeable:** Puedes mockear el repositorio en tests
- ✅ **Flexible:** Puedes cambiar la implementación sin tocar el Tool
- ✅ **Mantenible:** Dependencias explícitas y claras
- ✅ **SOLID:** Inversión de dependencias (la D de SOLID)

**¿Qué se inyecta?**
- Repositorios (acceso a datos)
- Servicios de dominio (lógica de negocio)
- Services externos (OpenAI, email, etc)
- **NO** inyectes modelos Eloquent directamente

#### 3. **El Método handle() (líneas 27-74)**

Este es el corazón del Tool. Veamos cada sección:

**3.1 Preparación de datos (líneas 38-48)**

```php
$data = [
    'project_id' => $request->input('project_id'),
    'title' => $request->input('title'),
    'priority' => Priority::from($request->input('priority', 'medium')),
    'status' => Status::PENDING,
    // ...
];
```

**Conceptos clave:**
- `$request->input('key')` extrae parámetros validados
- `Priority::from()` convierte string a Value Object (lo veremos en Cap. 4)
- `Status::PENDING` establece estado inicial
- Los datos ya están validados por el schema (no necesitas validar aquí)

**3.2 Ejecución de lógica de negocio (línea 51)**

```php
$task = $this->taskRepository->create($data);
```

**¿Por qué usar repositorio?**
- Abstrae la persistencia
- Permite cambiar DB sin tocar el Tool
- Facilita testing
- Separa responsabilidades

**3.3 Acciones adicionales (líneas 54-56)**

```php
if ($tags = $request->input('tags')) {
    $this->taskRepository->attachTags($task->id, $tags);
}
```

Después de la acción principal, puedes realizar acciones secundarias como:
- Agregar relaciones (tags, comentarios)
- Disparar eventos
- Enviar notificaciones
- Registrar logs

**3.4 Respuesta (líneas 59-73)**

```php
return Response::json([
    'success' => true,
    'message' => "Task created successfully",
    'task' => [
        'id' => $task->id,
        'title' => $task->title,
        // ... más datos
    ],
]);
```

**Estructura de respuesta recomendada:**
- `success`: booleano indicando éxito
- `message`: mensaje descriptivo para la IA
- `data`: datos relevantes del resultado
- `metadata`: información adicional (timestamps, versión, etc)

#### 4. **El Schema (líneas 76-127)**

El schema define **qué parámetros acepta** el Tool y **cómo validarlos**.

**Tipos de validación disponibles:**

```php
// String
$schema->string()
    ->minLength(3)
    ->maxLength(255)
    ->pattern('^[A-Z]')  // Regex
    ->enum(['value1', 'value2'])

// Integer
$schema->integer()
    ->minimum(1)
    ->maximum(100)
    ->multipleOf(5)

// Number (float)
$schema->number()
    ->minimum(0.5)
    ->maximum(999.99)

// Boolean
$schema->boolean()

// Array
$schema->array()
    ->items($schema->string())
    ->minItems(1)
    ->maxItems(10)
    ->uniqueItems()

// Object
$schema->object()
    ->properties([
        'name' => $schema->string(),
        'age' => $schema->integer(),
    ])

// Requerido vs Opcional
$schema->string()->required()
$schema->string()->optional()

// Valor por defecto
$schema->string()->default('default-value')
```

### Ejemplo: UpdateTask Tool

Veamos otro ejemplo completo del proyecto:

```php
<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\Services\TaskStatusManager;
use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdateTask extends Tool
{
    protected string $description = 'Update an existing task with new information';

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TaskStatusManager $statusManager
    ) {
    }

    public function handle(Request $request): Response
    {
        $taskId = $request->input('task_id');

        // 1. Verificar que la tarea existe
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            return Response::json([
                'success' => false,
                'error' => "Task #{$taskId} not found",
            ]);
        }

        // 2. Preparar datos para actualización (solo campos proporcionados)
        $updateData = [];

        if ($request->has('title')) {
            $updateData['title'] = $request->input('title');
        }

        if ($request->has('description')) {
            $updateData['description'] = $request->input('description');
        }

        if ($request->has('priority')) {
            $updateData['priority'] = Priority::from($request->input('priority'));
        }

        // 3. Actualizar datos básicos
        if (!empty($updateData)) {
            $this->taskRepository->update($taskId, $updateData);
        }

        // 4. Cambio de estado (con validación de transición)
        if ($request->has('status')) {
            $newStatus = Status::from($request->input('status'));

            // Usar servicio de dominio para validar transición
            try {
                $this->statusManager->transitionTo($task, $newStatus);
            } catch (\InvalidArgumentException $e) {
                return Response::json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'available_transitions' => array_map(
                        fn($s) => $s->value,
                        $task->status->availableTransitions()
                    ),
                ]);
            }
        }

        // 5. Reasignación
        if ($request->has('assigned_to')) {
            $this->taskRepository->assign($taskId, $request->input('assigned_to'));
        }

        // 6. Obtener tarea actualizada
        $updatedTask = $this->taskRepository->findById($taskId);

        return Response::json([
            'success' => true,
            'message' => "Task #{$taskId} updated successfully",
            'task' => [
                'id' => $updatedTask->id,
                'title' => $updatedTask->title,
                'status' => $updatedTask->status->value,
                'priority' => $updatedTask->priority->value,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()
                ->description('ID of the task to update')
                ->minimum(1)
                ->required(),

            'title' => $schema->string()
                ->minLength(3)
                ->maxLength(255)
                ->optional(),

            'description' => $schema->string()
                ->optional(),

            'status' => $schema->string()
                ->enum(['pending', 'in_progress', 'review', 'completed', 'blocked'])
                ->optional(),

            'priority' => $schema->string()
                ->enum(['low', 'medium', 'high', 'critical'])
                ->optional(),

            'assigned_to' => $schema->integer()
                ->minimum(1)
                ->optional(),
        ];
    }
}
```

### Puntos Clave de UpdateTask

**1. Validación de existencia (líneas 26-32)**
```php
$task = $this->taskRepository->findById($taskId);
if (!$task) {
    return Response::json(['success' => false, 'error' => 'Not found']);
}
```
Siempre verifica que el recurso existe antes de actualizarlo.

**2. Actualización parcial (líneas 36-49)**
```php
if ($request->has('title')) {
    $updateData['title'] = $request->input('title');
}
```
Solo actualiza campos proporcionados, no sobrescribas con nulls.

**3. Validación de negocio (líneas 56-69)**
```php
try {
    $this->statusManager->transitionTo($task, $newStatus);
} catch (\InvalidArgumentException $e) {
    return Response::json(['success' => false, 'error' => $e->getMessage()]);
}
```
Usa servicios de dominio para reglas de negocio complejas.

**4. Respuesta con error informativo (líneas 64-68)**
```php
'available_transitions' => array_map(
    fn($s) => $s->value,
    $task->status->availableTransitions()
)
```
Ayuda a la IA proporcionando información sobre qué puede hacer.

## 2.3 MCP Resources en Profundidad

### ¿Qué es un Resource?

Un **Resource** es una fuente de datos de solo lectura que la IA puede consultar. No modifica nada, solo proporciona información actualizada.

### ¿Por qué usar Resources en lugar de Tools?

**Resources:**
- ✅ Son más rápidos (no ejecutan lógica compleja)
- ✅ Son seguros (solo lectura)
- ✅ La IA puede consultarlos cuando necesite
- ✅ Ideales para dashboards y métricas

**Tools:**
- ⚠️ Ejecutan acciones (potencialmente peligrosas)
- ⚠️ Pueden ser más lentos
- ⚠️ Requieren más validación

### Anatomía de un Resource

Veamos `TeamMetrics`, un Resource real del proyecto:

```php
<?php

namespace App\Mcp\Resources;

use App\Domain\Analytics\Services\MetricsCollector;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class TeamMetrics extends Resource
{
    /**
     * URI del resource (cómo se accede)
     */
    protected string $uri = 'team-metrics';

    /**
     * Descripción del resource
     */
    protected string $description = <<<'MARKDOWN'
        Get overall team performance metrics including task completion rates,
        status distribution, priority breakdown, and velocity trends.
    MARKDOWN;

    /**
     * Constructor con dependencias
     */
    public function __construct(
        private readonly MetricsCollector $metricsCollector,
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Método que retorna los datos
     */
    public function handle(Request $request): Response
    {
        // 1. Obtener parámetros opcionales
        $days = $request->input('days', 30);
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        // 2. Recolectar métricas usando servicio de dominio
        $metrics = $this->metricsCollector->collectMetrics($startDate, $endDate);

        // 3. Calcular métricas adicionales
        $tasksCompleted = $metrics['tasks']['completed'] ?? 0;
        $totalTasks = $metrics['tasks']['total'] ?? 1;
        $velocity = $days > 0 ? round($tasksCompleted / $days, 2) : 0;
        $completionRate = round(($tasksCompleted / $totalTasks) * 100, 1);

        // 4. Obtener distribución de estados
        $statusDistribution = [
            'pending' => $metrics['tasks']['pending'] ?? 0,
            'in_progress' => $metrics['tasks']['in_progress'] ?? 0,
            'review' => $metrics['tasks']['review'] ?? 0,
            'completed' => $metrics['tasks']['completed'] ?? 0,
            'blocked' => $metrics['tasks']['blocked'] ?? 0,
        ];

        // 5. Obtener distribución de prioridades
        $priorityBreakdown = [
            'low' => $metrics['tasks']['priority']['low'] ?? 0,
            'medium' => $metrics['tasks']['priority']['medium'] ?? 0,
            'high' => $metrics['tasks']['priority']['high'] ?? 0,
            'critical' => $metrics['tasks']['priority']['critical'] ?? 0,
        ];

        // 6. Calcular edad promedio de tareas
        $allTasks = $this->taskRepository->all();
        $totalAge = 0;
        foreach ($allTasks as $task) {
            $totalAge += $task->created_at->diffInDays(Carbon::now());
        }
        $averageAge = $allTasks->count() > 0
            ? round($totalAge / $allTasks->count(), 1)
            : 0;

        // 7. Retornar datos estructurados
        return Response::json([
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $days,
            ],
            'overview' => [
                'total_tasks' => $totalTasks,
                'tasks_completed' => $tasksCompleted,
                'completion_rate' => $completionRate.'%',
                'velocity' => $velocity.' tasks/day',
                'average_task_age' => $averageAge.' days',
            ],
            'status_distribution' => $statusDistribution,
            'priority_breakdown' => $priorityBreakdown,
            'velocity_data' => [
                'daily_average' => $velocity,
                'weekly_projection' => round($velocity * 7, 0),
                'monthly_projection' => round($velocity * 30, 0),
            ],
            'health_indicators' => [
                'blocked_tasks' => $statusDistribution['blocked'],
                'overdue_tasks' => $metrics['tasks']['overdue'] ?? 0,
                'high_priority_pending' => $this->taskRepository
                    ->findByPriority('high')
                    ->where('status', 'pending')
                    ->count(),
            ],
        ]);
    }
}
```

### Diferencias Clave: Tool vs Resource

| Aspecto | Tool | Resource |
|---------|------|----------|
| **URI/Nombre** | Verbo (create-task) | Sustantivo (team-metrics) |
| **Método handle()** | Modifica datos | Solo lee datos |
| **Schema** | Requerido | Opcional (solo params) |
| **Velocidad** | Variable | Rápido (solo lectura) |
| **Cacheable** | No | Sí (puedes cachear) |
| **Ejemplo** | CreateTask, UpdateTask | TeamMetrics, ProjectStatus |

### Resource con Parámetros: ProjectMetrics

Los Resources pueden aceptar parámetros en la URI:

```php
<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Server\Resource;

class ProjectMetrics extends Resource
{
    // URI con parámetro
    protected string $uri = 'project-metrics/{project_id}';

    protected string $description = 'Get metrics for a specific project';

    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        // Extraer parámetro de la URI
        $projectId = $request->input('project_id');

        // Validar que el proyecto existe
        $project = $this->projectRepository->findById($projectId);
        if (!$project) {
            return Response::json([
                'error' => "Project #{$projectId} not found",
            ]);
        }

        // Obtener métricas del proyecto
        $tasks = $this->taskRepository->findByProject($projectId);

        return Response::json([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            'metrics' => [
                'total_tasks' => $tasks->count(),
                'completed' => $tasks->where('status', 'completed')->count(),
                // ... más métricas
            ],
        ]);
    }
}
```

**Uso desde la IA:**
```
IA: "Muéstrame las métricas del proyecto 5"
MCP: project-metrics/5
```

## 2.4 MCP Prompts en Profundidad

### ¿Qué es un Prompt?

Un **Prompt** es una plantilla reutilizable de instrucciones que puede ser invocada por la IA con parámetros dinámicos.

### ¿Cuándo usar Prompts?

**Usa Prompts cuando:**
- ✅ Tienes workflows repetitivos que quieres estandarizar
- ✅ Necesitas proporcionar contexto específico del dominio
- ✅ Quieres asegurar consistencia en respuestas
- ✅ Tienes "recetas" de cómo hacer tareas específicas

**Ejemplo:** En lugar de que la IA invente cómo escribir una descripción de tarea cada vez, le das un prompt estandarizado.

### Anatomía de un Prompt

```php
<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;

class TaskDescriptionTemplate extends Prompt
{
    /**
     * Nombre del prompt
     */
    protected string $name = 'task-description-template';

    /**
     * Descripción del prompt
     */
    protected string $description = <<<'MARKDOWN'
        Generate a comprehensive task description following company standards.
        Includes: objective, acceptance criteria, technical considerations.
    MARKDOWN;

    /**
     * Argumentos que acepta el prompt
     */
    protected array $arguments = [
        [
            'name' => 'task_type',
            'description' => 'Type of task (feature, bug, refactor, test)',
            'required' => true,
        ],
        [
            'name' => 'complexity',
            'description' => 'Complexity level (low, medium, high)',
            'required' => false,
        ],
        [
            'name' => 'context',
            'description' => 'Additional context about the task',
            'required' => false,
        ],
    ];

    /**
     * Generar el prompt con parámetros
     */
    public function handle(Request $request): Response
    {
        $taskType = $request->input('task_type');
        $complexity = $request->input('complexity', 'medium');
        $context = $request->input('context', '');

        // Construir plantilla específica según tipo
        $template = match($taskType) {
            'feature' => $this->featureTemplate($complexity, $context),
            'bug' => $this->bugTemplate($complexity, $context),
            'refactor' => $this->refactorTemplate($complexity, $context),
            'test' => $this->testTemplate($complexity, $context),
            default => $this->genericTemplate($complexity, $context),
        };

        return Response::json([
            'template' => $template,
            'instructions' => 'Use this template as a guide for the task description',
        ]);
    }

    /**
     * Template para features
     */
    private function featureTemplate(string $complexity, string $context): string
    {
        return <<<TEMPLATE
# Feature Description

## Objective
{$context}

## User Story
As a [role]
I want to [action]
So that [benefit]

## Acceptance Criteria
- [ ] Criterion 1
- [ ] Criterion 2
- [ ] Criterion 3

## Technical Considerations
Complexity: {$complexity}
- Architecture impact
- Database changes required
- API endpoints needed
- Frontend components

## Definition of Done
- [ ] Code written and reviewed
- [ ] Tests passing (unit + integration)
- [ ] Documentation updated
- [ ] Deployed to staging
- [ ] QA approved

TEMPLATE;
    }

    /**
     * Template para bugs
     */
    private function bugTemplate(string $complexity, string $context): string
    {
        return <<<TEMPLATE
# Bug Report

## Description
{$context}

## Steps to Reproduce
1.
2.
3.

## Expected Behavior
What should happen

## Actual Behavior
What actually happens

## Impact
Severity: {$complexity}
- Users affected:
- Business impact:
- Workaround available:

## Root Cause Analysis
(To be filled after investigation)

## Fix
(To be filled during implementation)

## Testing
- [ ] Bug fixed and verified
- [ ] Regression tests added
- [ ] No new bugs introduced

TEMPLATE;
    }

    // ... más templates para otros tipos
}
```

### Ventajas de Usar Prompts

**1. Consistencia**
```
Sin Prompt: Cada descripción de tarea es diferente
Con Prompt: Todas siguen el mismo formato estándar
```

**2. Calidad**
```
Sin Prompt: La IA puede olvidar incluir criterios de aceptación
Con Prompt: La plantilla asegura que se incluyan todos los elementos
```

**3. Eficiencia**
```
Sin Prompt: Usuario debe explicar cada vez el formato deseado
Con Prompt: Un simple "usa task-description-template" es suficiente
```

**4. Onboarding**
```
Sin Prompt: Nuevos devs deben aprender el formato
Con Prompt: El prompt documenta las mejores prácticas
```

## 2.5 Comparación Práctica de los Tres Componentes

### Escenario: Sistema de Gestión de Tareas

Veamos cómo se usa cada componente para el mismo dominio:

#### **Tools** - Acciones sobre Tareas

```php
// CreateTask.php
"Create a new task"
Parámetros: title, description, priority
Resultado: Tarea creada en DB

// UpdateTask.php
"Update an existing task"
Parámetros: task_id, nuevos valores
Resultado: Tarea actualizada

// AssignTask.php
"Assign a task to a user"
Parámetros: task_id, user_id
Resultado: Tarea asignada
```

**Uso por la IA:**
```
Usuario: "Crea una tarea para implementar login"
IA: [Usa CreateTask tool]
    - title: "Implementar sistema de login"
    - priority: "high"
    - project_id: 1
```

#### **Resources** - Consulta de Información

```php
// TeamMetrics.php
URI: team-metrics
Retorna: Métricas generales del equipo

// ProjectMetrics.php
URI: project-metrics/{project_id}
Retorna: Métricas específicas de un proyecto

// UserProductivity.php
URI: user-productivity/{user_id}
Retorna: Productividad de un usuario
```

**Uso por la IA:**
```
Usuario: "¿Cómo va el equipo este mes?"
IA: [Lee resource team-metrics con days=30]
    - Velocity: 2.5 tasks/day
    - Completion rate: 85%
    - Blocked tasks: 3
IA: "El equipo completa 2.5 tareas por día con 85% de tasa de completitud.
     Hay 3 tareas bloqueadas que necesitan atención."
```

#### **Prompts** - Plantillas Estandarizadas

```php
// TaskDescriptionTemplate.php
name: task-description-template
Argumentos: task_type, complexity
Retorna: Plantilla markdown

// CodeReviewPrompt.php
name: code-review-checklist
Argumentos: language, framework
Retorna: Checklist de revisión
```

**Uso por la IA:**
```
Usuario: "Ayúdame a describir esta feature"
IA: [Usa prompt task-description-template]
    - task_type: "feature"
    - complexity: "high"
IA: "Aquí está una plantilla estándar:
     # Feature Description
     ## Objective
     ## User Story
     ## Acceptance Criteria
     ..."
```

## 2.6 Ciclo de Vida de una Petición MCP

Veamos el flujo completo cuando la IA usa un Tool:

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. Usuario hace pregunta                                         │
│    "Crea una tarea urgente para fix del bug de login"          │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 2. IA analiza la petición                                       │
│    - Identifica que necesita crear una tarea                    │
│    - Busca un Tool apropiado                                    │
│    - Encuentra: CreateTask                                      │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 3. IA consulta el schema del Tool                               │
│    GET /mcp/tools/create-task/schema                            │
│    Respuesta: {                                                 │
│      "title": "string, required, 3-255 chars",                  │
│      "priority": "enum: low|medium|high|critical"               │
│    }                                                            │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 4. IA prepara parámetros según schema                           │
│    {                                                            │
│      "title": "Fix bug de login",                               │
│      "priority": "critical",                                    │
│      "project_id": 1                                            │
│    }                                                            │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 5. Laravel MCP valida parámetros                                │
│    - ✓ title: longitud OK (15 chars)                           │
│    - ✓ priority: valor válido (critical)                       │
│    - ✓ project_id: tipo correcto (integer)                     │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 6. Laravel ejecuta CreateTask::handle()                         │
│    - Extrae parámetros del Request                             │
│    - Convierte strings a Value Objects                          │
│    - Llama a TaskRepository::create()                           │
│    - Eloquent inserta en PostgreSQL                             │
│    - Retorna Task model                                         │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 7. Tool retorna Response                                        │
│    {                                                            │
│      "success": true,                                           │
│      "task": {                                                  │
│        "id": 42,                                                │
│        "title": "Fix bug de login",                             │
│        "status": "pending"                                      │
│      }                                                          │
│    }                                                            │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 8. IA procesa respuesta y responde al usuario                  │
│    "He creado la tarea #42 'Fix bug de login' con prioridad    │
│     crítica. La tarea está en estado 'pending' y lista para     │
│     ser asignada."                                              │
└─────────────────────────────────────────────────────────────────┘
```

## 2.7 Manejo de Errores en MCP

### Errores de Validación

```php
public function schema(JsonSchema $schema): array
{
    return [
        'task_id' => $schema->integer()->minimum(1)->required(),
    ];
}

// Si la IA envía task_id = -5
// Laravel MCP rechaza ANTES de llamar a handle()
// Respuesta automática:
{
    "error": "Validation failed",
    "details": {
        "task_id": ["The task_id must be at least 1"]
    }
}
```

### Errores de Negocio

```php
public function handle(Request $request): Response
{
    $task = $this->taskRepository->findById($request->input('task_id'));

    if (!$task) {
        return Response::json([
            'success' => false,
            'error' => 'Task not found',
            'error_code' => 'TASK_NOT_FOUND',
            'task_id' => $request->input('task_id'),
        ]);
    }

    // ... continuar con la lógica
}
```

### Errores de Autorización

```php
public function handle(Request $request): Response
{
    $user = $request->user();
    $task = $this->taskRepository->findById($request->input('task_id'));

    if ($task->project->owner_id !== $user->id) {
        return Response::json([
            'success' => false,
            'error' => 'Unauthorized',
            'message' => 'You do not have permission to update this task',
        ]);
    }

    // ... continuar
}
```

### Excepciones Inesperadas

```php
public function handle(Request $request): Response
{
    try {
        // Lógica que puede fallar
        $task = $this->taskRepository->create($data);

        return Response::json(['success' => true, 'task' => $task]);

    } catch (\Exception $e) {
        // Logging
        \Log::error('CreateTask failed', [
            'error' => $e->getMessage(),
            'data' => $data,
        ]);

        return Response::json([
            'success' => false,
            'error' => 'Internal server error',
            'message' => config('app.debug') ? $e->getMessage() : 'An error occurred',
        ]);
    }
}
```

## 2.8 Mejores Prácticas

### Para Tools

✅ **DO:**
- Usa nombres verbales claros (create-task, update-user)
- Valida exhaustivamente con JSON Schema
- Retorna datos útiles en la respuesta
- Maneja errores gracefully
- Usa dependency injection
- Separa lógica de negocio en servicios

❌ **DON'T:**
- No pongas lógica de negocio en el Tool
- No hagas queries directas a DB (usa repositorios)
- No retornes datos sensibles (passwords, tokens)
- No ignores errores
- No uses nombres ambiguos

### Para Resources

✅ **DO:**
- Usa nombres sustantivos claros (team-metrics, user-list)
- Cachea cuando sea posible
- Retorna solo datos necesarios
- Incluye metadatos útiles (timestamps, pagination)
- Acepta parámetros de filtrado

❌ **DON'T:**
- No ejecutes operaciones pesadas sin caché
- No retornes todo el dataset (pagina)
- No incluyas datos sensibles
- No modifiques estado

### Para Prompts

✅ **DO:**
- Proporciona plantillas claras y estructuradas
- Incluye ejemplos dentro del prompt
- Acepta parámetros para customización
- Documenta el propósito del prompt

❌ **DON'T:**
- No hagas prompts demasiado rígidos
- No asumas contexto que la IA no tiene
- No incluyas información desactualizada

## 2.9 Ejercicios Prácticos

### Ejercicio 1: Crear un Tool Simple

Crea un Tool llamado `CompleteTask` que:
- Acepte un `task_id`
- Cambie el estado de la tarea a "completed"
- Retorne la tarea actualizada

<details>
<summary>Ver solución</summary>

```php
<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Status;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CompleteTask extends Tool
{
    protected string $description = 'Mark a task as completed';

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        $taskId = $request->input('task_id');

        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            return Response::json([
                'success' => false,
                'error' => 'Task not found',
            ]);
        }

        $this->taskRepository->updateStatus($taskId, Status::COMPLETED);

        $updatedTask = $this->taskRepository->findById($taskId);

        return Response::json([
            'success' => true,
            'message' => "Task #{$taskId} marked as completed",
            'task' => [
                'id' => $updatedTask->id,
                'title' => $updatedTask->title,
                'status' => $updatedTask->status->value,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()
                ->description('ID of the task to complete')
                ->minimum(1)
                ->required(),
        ];
    }
}
```
</details>

### Ejercicio 2: Crear un Resource

Crea un Resource llamado `TaskSummary` que retorne un resumen de todas las tareas agrupadas por estado.

<details>
<summary>Ver solución</summary>

```php
<?php

namespace App\Mcp\Resources;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class TaskSummary extends Resource
{
    protected string $uri = 'task-summary';

    protected string $description = 'Get a summary of all tasks grouped by status';

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        $allTasks = $this->taskRepository->all();

        $summary = [
            'total' => $allTasks->count(),
            'by_status' => [
                'pending' => $allTasks->where('status', 'pending')->count(),
                'in_progress' => $allTasks->where('status', 'in_progress')->count(),
                'review' => $allTasks->where('status', 'review')->count(),
                'completed' => $allTasks->where('status', 'completed')->count(),
                'blocked' => $allTasks->where('status', 'blocked')->count(),
            ],
        ];

        return Response::json($summary);
    }
}
```
</details>

## 2.10 Resumen del Capítulo 2

🎯 **Conceptos Clave Aprendidos:**

1. **Tools** son acciones que modifican estado
   - Tienen descripción, handle(), y schema()
   - Usan dependency injection
   - Validan entrada con JSON Schema
   - Retornan Response estructurado

2. **Resources** proporcionan datos de solo lectura
   - Tienen URI, descripción, y handle()
   - Pueden aceptar parámetros
   - Ideales para métricas y dashboards
   - Pueden ser cacheados

3. **Prompts** son plantillas reutilizables
   - Estandarizan workflows
   - Mejoran consistencia
   - Pueden ser parametrizados
   - Documentan mejores prácticas

4. **Manejo de errores** es crítico
   - Validación automática vía schema
   - Errores de negocio en handle()
   - Respuestas informativas para la IA
   - Logging para debugging

5. **Mejores prácticas** aseguran calidad
   - Nombres descriptivos
   - Separación de responsabilidades
   - Dependency injection
   - Validación exhaustiva

🔜 **Próximo Capítulo:**
En el Capítulo 3 exploraremos la **Arquitectura del Proyecto** completa, incluyendo Domain-Driven Design, Bounded Contexts, y las capas de la aplicación.

---

**Estado del Tutorial:** Capítulos 1-2 de 15 completados ✓
