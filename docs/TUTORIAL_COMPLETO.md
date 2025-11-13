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

---

# Capítulo 3: Arquitectura del Proyecto

## 3.1 Introducción a Domain-Driven Design (DDD)

**Domain-Driven Design** es un enfoque de desarrollo de software que pone el foco en el **dominio del negocio** (las reglas y lógica de tu aplicación) en el centro de todo.

### ¿Por qué DDD?

**Problema común sin DDD:**
```php
// ❌ Todo mezclado en un controlador
class TaskController {
    public function update(Request $request, $id) {
        $task = Task::find($id);

        // Validación
        if (!$task) return abort(404);

        // Lógica de negocio mezclada
        if ($task->status == 'completed' && $request->status == 'pending') {
            return back()->with('error', 'No puedes reabrir una tarea completada');
        }

        // Más lógica
        $task->status = $request->status;
        $task->save();

        // Notificación
        Mail::send(...);

        return back();
    }
}
```

**Problemas:**
- ❌ Lógica de negocio en el controlador
- ❌ Difícil de testear
- ❌ No reutilizable
- ❌ Violación de SRP (Single Responsibility Principle)

**Solución con DDD:**
```php
// ✅ Controlador limpio
class TaskController {
    public function update(
        Request $request,
        $id,
        TaskStatusManager $statusManager,
        TaskRepositoryInterface $repository
    ) {
        $task = $repository->findById($id);
        if (!$task) return abort(404);

        try {
            $statusManager->transitionTo($task, Status::from($request->status));
            return back()->with('success', 'Tarea actualizada');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

// ✅ Lógica de negocio en servicio de dominio
class TaskStatusManager {
    public function transitionTo(Task $task, Status $newStatus): bool {
        if (!$task->status->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "Cannot transition from {$task->status->value} to {$newStatus->value}"
            );
        }

        return $this->taskRepository->updateStatus($task->id, $newStatus);
    }
}
```

**Ventajas:**
- ✅ Lógica de negocio aislada y testeable
- ✅ Controlador simple y enfocado
- ✅ Reutilizable en APIs, comandos, MCP Tools, etc.
- ✅ Fácil de entender y mantener

### Conceptos Clave de DDD

**1. Domain (Dominio)**
El corazón de tu aplicación. Contiene:
- **Entities:** Objetos con identidad (Task, Project, User)
- **Value Objects:** Objetos sin identidad (Status, Priority, Email)
- **Domain Services:** Lógica de negocio compleja
- **Repository Interfaces:** Contratos para acceso a datos

**2. Bounded Context (Contexto Delimitado)**
Una frontera lógica que separa diferentes partes del dominio.

**Ejemplo en TaskMaster AI:**
- `TaskManagement`: Todo relacionado con tareas y proyectos
- `AIIntegration`: Todo relacionado con IA y OpenAI
- `Analytics`: Todo relacionado con métricas y reportes

Cada contexto puede tener su propio modelo de `Task` si es necesario, con diferentes atributos y comportamientos.

**3. Ubiquitous Language (Lenguaje Ubicuo)**
Un lenguaje común entre desarrolladores y expertos del dominio.

**Ejemplo:**
- ✅ "Una tarea en estado 'pending' puede transicionar a 'in_progress'"
- ❌ "Un registro con status 1 puede cambiar a status 2"

El código debe usar el mismo lenguaje que el negocio.

## 3.2 Los Tres Bounded Contexts del Proyecto

El proyecto TaskMaster AI se divide en 3 bounded contexts independientes:

### 1. **TaskManagement Context**

**Responsabilidad:** Gestión completa del ciclo de vida de tareas y proyectos.

**Estructura:**
```
app/Domain/TaskManagement/
├── Contracts/
│   └── Repositories/
│       ├── TaskRepositoryInterface.php
│       ├── ProjectRepositoryInterface.php
│       ├── TagRepositoryInterface.php
│       └── CommentRepositoryInterface.php
├── Services/
│   ├── TaskStatusManager.php
│   └── TaskPriorityCalculator.php
└── ValueObjects/
    ├── Status.php
    └── Priority.php
```

**Entidades principales:**
- `Task`: Una tarea con título, descripción, estado, prioridad
- `Project`: Un proyecto que agrupa tareas
- `Tag`: Etiqueta para categorizar tareas
- `Comment`: Comentario en una tarea

**Value Objects:**
- `Status`: Estado de la tarea (pending, in_progress, review, completed, blocked)
- `Priority`: Prioridad (low, medium, high, critical)

**Servicios de dominio:**
- `TaskStatusManager`: Gestiona transiciones de estado
- `TaskPriorityCalculator`: Calcula prioridad sugerida

**Reglas de negocio:**
- Una tarea `completed` solo puede transicionar a `in_progress` (reabrir)
- Una tarea `pending` no puede transicionar directamente a `review`
- Cada transición de estado es validada
- Las prioridades tienen scores para comparación

**Ejemplo de código:**
```php
// app/Domain/TaskManagement/ValueObjects/Status.php
enum Status: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case COMPLETED = 'completed';
    case BLOCKED = 'blocked';

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::PENDING => in_array($newStatus, [
                self::IN_PROGRESS,
                self::BLOCKED,
            ]),
            self::IN_PROGRESS => in_array($newStatus, [
                self::REVIEW,
                self::BLOCKED,
                self::PENDING,
            ]),
            self::REVIEW => in_array($newStatus, [
                self::COMPLETED,
                self::IN_PROGRESS,
                self::BLOCKED,
            ]),
            self::COMPLETED => in_array($newStatus, [
                self::IN_PROGRESS, // Reabrir tarea
            ]),
            self::BLOCKED => in_array($newStatus, [
                self::PENDING,
                self::IN_PROGRESS,
            ]),
        };
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::IN_PROGRESS => 'En Progreso',
            self::REVIEW => 'En Revisión',
            self::COMPLETED => 'Completada',
            self::BLOCKED => 'Bloqueada',
        };
    }
}
```

### 2. **AIIntegration Context**

**Responsabilidad:** Integración con servicios de IA (OpenAI) para generar contenido y análisis.

**Estructura:**
```
app/Domain/AIIntegration/
├── Services/
│   ├── OpenAIService.php
│   ├── TaskDescriptionGenerator.php
│   ├── PromptBuilder.php
│   └── ProductivityAnalyzer.php
└── Exceptions/
    └── OpenAIException.php
```

**Servicios:**
- `OpenAIService`: Cliente para llamadas a OpenAI API
- `TaskDescriptionGenerator`: Genera descripciones de tareas con IA
- `PromptBuilder`: Construye prompts estructurados
- `ProductivityAnalyzer`: Analiza productividad con IA

**Reglas de negocio:**
- Validación de API key antes de llamadas
- Manejo de rate limits de OpenAI
- Fallback cuando la API no está disponible
- Caché de respuestas costosas

**Ejemplo de código:**
```php
// app/Domain/AIIntegration/Services/TaskDescriptionGenerator.php
class TaskDescriptionGenerator
{
    public function __construct(
        private readonly OpenAIService $openai,
        private readonly PromptBuilder $promptBuilder
    ) {}

    public function generate(string $title, string $context = ''): string
    {
        // Construir prompt estructurado
        $prompt = $this->promptBuilder->buildTaskDescriptionPrompt([
            'title' => $title,
            'context' => $context,
        ]);

        try {
            // Llamar a OpenAI
            $response = $this->openai->complete($prompt, [
                'model' => 'gpt-4-turbo-preview',
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            return $response['choices'][0]['message']['content'];

        } catch (OpenAIException $e) {
            // Fallback: retornar contexto si IA falla
            return $context ?: "Task: {$title}";
        }
    }
}
```

### 3. **Analytics Context**

**Responsabilidad:** Recolección, cálculo y presentación de métricas y reportes.

**Estructura:**
```
app/Domain/Analytics/
└── Services/
    ├── MetricsCollector.php
    └── ReportGenerator.php
```

**Servicios:**
- `MetricsCollector`: Recolecta métricas del sistema
- `ReportGenerator`: Genera reportes formateados

**Métricas calculadas:**
- Velocity del equipo (tareas por día)
- Completion rate (% de tareas completadas)
- Average task age (edad promedio de tareas)
- Overdue tasks (tareas vencidas)
- Status distribution (distribución por estado)
- Priority breakdown (distribución por prioridad)

**Ejemplo de código:**
```php
// app/Domain/Analytics/Services/MetricsCollector.php
class MetricsCollector
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ProjectRepositoryInterface $projectRepository
    ) {}

    public function collectMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $tasks = $this->taskRepository->all();
        $periodTasks = $tasks->filter(fn($t) =>
            $t->created_at >= $startDate && $t->created_at <= $endDate
        );

        return [
            'tasks' => [
                'total' => $tasks->count(),
                'pending' => $tasks->where('status', 'pending')->count(),
                'in_progress' => $tasks->where('status', 'in_progress')->count(),
                'review' => $tasks->where('status', 'review')->count(),
                'completed' => $tasks->where('status', 'completed')->count(),
                'blocked' => $tasks->where('status', 'blocked')->count(),
                'overdue' => $tasks->filter(fn($t) => $t->isOverdue())->count(),
                'priority' => [
                    'low' => $tasks->where('priority', 'low')->count(),
                    'medium' => $tasks->where('priority', 'medium')->count(),
                    'high' => $tasks->where('priority', 'high')->count(),
                    'critical' => $tasks->where('priority', 'critical')->count(),
                ],
            ],
            'projects' => [
                'total' => $this->projectRepository->all()->count(),
                'active' => $this->projectRepository->getActive()->count(),
            ],
        ];
    }
}
```

### Comunicación Entre Contextos

Los bounded contexts se comunican a través de **interfaces bien definidas**:

```
┌────────────────────┐
│  TaskManagement    │
│                    │
│  - Task            │
│  - Project         │
│  - TaskRepository  │
└────────────────────┘
         ↓ usa
┌────────────────────┐
│  AIIntegration     │
│                    │
│  - Llama a OpenAI  │
│  - Genera contenido│
└────────────────────┘
         ↓ usa
┌────────────────────┐
│  Analytics         │
│                    │
│  - Lee tareas      │
│  - Calcula métricas│
└────────────────────┘
```

**Ejemplo de comunicación:**
```php
// MCP Tool que usa múltiples contextos
class GenerateTaskDescription extends Tool
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepo,        // TaskManagement
        private readonly TaskDescriptionGenerator $generator,      // AIIntegration
        private readonly MetricsCollector $metricsCollector       // Analytics
    ) {}

    public function handle(Request $request): Response
    {
        // 1. Obtener tarea (TaskManagement)
        $task = $this->taskRepo->findById($request->input('task_id'));

        // 2. Generar descripción con IA (AIIntegration)
        $description = $this->generator->generate($task->title);

        // 3. Actualizar tarea
        $this->taskRepo->update($task->id, ['description' => $description]);

        // 4. Registrar métrica (Analytics)
        $this->metricsCollector->recordAIUsage('task_description');

        return Response::json(['success' => true, 'description' => $description]);
    }
}
```

## 3.3 Las Cuatro Capas de la Arquitectura

El proyecto sigue una arquitectura en capas (Layered Architecture) combinada con DDD:

```
┌─────────────────────────────────────┐
│   Presentation Layer                │  ← UI, Controladores, MCP Tools
├─────────────────────────────────────┤
│   Application Layer                 │  ← Casos de uso, Coordinación
├─────────────────────────────────────┤
│   Domain Layer                      │  ← Lógica de negocio, Reglas
├─────────────────────────────────────┤
│   Infrastructure Layer              │  ← DB, APIs externas, Framework
└─────────────────────────────────────┘
```

### Capa 1: Domain Layer (Dominio)

**Ubicación:** `app/Domain/`

**Responsabilidad:** Contiene la lógica de negocio pura, independiente de frameworks y tecnologías.

**Contenido:**
- **Value Objects:** Objetos inmutables sin identidad
- **Entities:** Objetos con identidad (aunque usamos Eloquent models)
- **Repository Interfaces:** Contratos para acceso a datos
- **Domain Services:** Lógica de negocio compleja
- **Domain Events:** Eventos del dominio
- **Exceptions:** Excepciones del dominio

**Características:**
- ✅ No depende de Laravel
- ✅ No depende de Eloquent
- ✅ No depende de ninguna librería externa (excepto PHP)
- ✅ 100% testeable con unit tests
- ✅ Representa el conocimiento del negocio

**Ejemplo completo:**
```php
// app/Domain/TaskManagement/ValueObjects/Priority.php
enum Priority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function score(): int
    {
        return match($this) {
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::CRITICAL => 4,
        };
    }

    public function isHigherThan(self $other): bool
    {
        return $this->score() > $other->score();
    }

    public function label(): string
    {
        return match($this) {
            self::LOW => 'Baja',
            self::MEDIUM => 'Media',
            self::HIGH => 'Alta',
            self::CRITICAL => 'Crítica',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::LOW => 'green',
            self::MEDIUM => 'yellow',
            self::HIGH => 'orange',
            self::CRITICAL => 'red',
        };
    }
}
```

**Por qué Value Objects:**
- ✅ Encapsulan validación (no puedes crear un Priority inválido)
- ✅ Tienen comportamiento (score(), isHigherThan())
- ✅ Son inmutables (no se pueden modificar)
- ✅ Son tipo-seguros (`Priority::HIGH` vs `"high"`)

### Capa 2: Infrastructure Layer (Infraestructura)

**Ubicación:** `app/Infrastructure/`

**Responsabilidad:** Implementaciones concretas de los contratos del dominio usando tecnologías específicas.

**Contenido:**
- **Eloquent Models:** Modelos de base de datos
- **Repository Implementations:** Implementaciones con Eloquent
- **External Services:** Clientes para APIs externas
- **File System:** Acceso a archivos
- **Cache:** Implementación de caché

**Características:**
- ✅ Depende del Domain Layer
- ✅ Usa Laravel/Eloquent
- ✅ Implementa interfaces del dominio
- ✅ Maneja detalles técnicos

**Ejemplo completo:**
```php
// app/Infrastructure/Persistence/Eloquent/Repositories/EloquentTaskRepository.php
class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function findById(int $id): ?Task
    {
        return Task::with(['project', 'assignedTo', 'creator', 'tags', 'comments'])
            ->find($id);
    }

    public function findByStatus(Status $status): Collection
    {
        return Task::where('status', $status->value)
            ->with(['project', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByProject(int $projectId): Collection
    {
        return Task::where('project_id', $projectId)
            ->with(['assignedTo', 'tags'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): Task
    {
        return Task::create([
            'project_id' => $data['project_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'priority' => $data['priority'],
            'created_by' => $data['created_by'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'estimated_hours' => $data['estimated_hours'] ?? null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $task = $this->findById($id);
        if (!$task) {
            return false;
        }

        return $task->update($data);
    }

    public function updateStatus(int $id, Status $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    public function assign(int $taskId, ?int $userId): bool
    {
        return $this->update($taskId, ['assigned_to' => $userId]);
    }

    public function attachTags(int $taskId, array $tagIds): void
    {
        $task = $this->findById($taskId);
        if ($task) {
            $task->tags()->sync($tagIds);
        }
    }

    public function search(string $query): Collection
    {
        return Task::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with(['project', 'assignedTo'])
            ->get();
    }

    public function all(): Collection
    {
        return Task::with(['project', 'assignedTo'])->get();
    }
}
```

**Eloquent Models:**
```php
// app/Infrastructure/Persistence/Eloquent/Models/Task.php
class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'created_by',
        'assigned_to',
        'due_date',
        'estimated_hours',
        'actual_hours',
    ];

    protected $casts = [
        'status' => Status::class,        // Cast automático a Value Object
        'priority' => Priority::class,    // Cast automático a Value Object
        'due_date' => 'datetime',
    ];

    // Relaciones
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tag');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Métodos de ayuda
    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && $this->status !== Status::COMPLETED;
    }
}
```

**¿Por qué usar Eloquent si queremos DDD puro?**
- ✅ Pragmatismo: Eloquent es poderoso y rápido
- ✅ Casting: Podemos castear a Value Objects
- ✅ Repository Pattern: Aislamos Eloquent detrás de interfaces
- ✅ Testeable: Podemos mockear repositorios en tests

### Capa 3: Application Layer (Aplicación)

**Ubicación:** `app/Application/` (aunque en este proyecto está implícita)

**Responsabilidad:** Coordina casos de uso, orquesta servicios de dominio.

**Contenido:**
- **Use Cases:** Casos de uso de la aplicación
- **DTOs:** Data Transfer Objects
- **Commands:** Comandos de aplicación
- **Queries:** Consultas de aplicación

**Ejemplo conceptual:**
```php
// app/Application/UseCases/CreateTaskUseCase.php
class CreateTaskUseCase
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepo,
        private readonly ProjectRepositoryInterface $projectRepo,
        private readonly TaskDescriptionGenerator $aiGenerator
    ) {}

    public function execute(CreateTaskDTO $dto): Task
    {
        // 1. Validar que el proyecto existe
        $project = $this->projectRepo->findById($dto->projectId);
        if (!$project) {
            throw new ProjectNotFoundException();
        }

        // 2. Generar descripción con IA si no se proporciona
        $description = $dto->description;
        if (!$description && $dto->useAI) {
            $description = $this->aiGenerator->generate($dto->title);
        }

        // 3. Crear tarea
        $task = $this->taskRepo->create([
            'project_id' => $dto->projectId,
            'title' => $dto->title,
            'description' => $description,
            'priority' => $dto->priority,
            'status' => Status::PENDING,
            'created_by' => $dto->createdBy,
        ]);

        // 4. Disparar evento
        event(new TaskCreated($task));

        return $task;
    }
}
```

**En este proyecto:**
Los MCP Tools actúan como la capa de aplicación, coordinando servicios de dominio e infraestructura.

### Capa 4: Presentation Layer (Presentación)

**Ubicación:** `app/Http/`, `app/Livewire/`, `app/Mcp/`

**Responsabilidad:** Interfaz con el usuario (humano o IA).

**Contenido:**
- **Controllers:** Controladores HTTP tradicionales
- **Livewire Components:** Componentes interactivos
- **MCP Tools/Resources/Prompts:** Interfaz para IA
- **Form Requests:** Validación de entrada
- **Views:** Plantillas Blade

**Ejemplo - Controlador HTTP:**
```php
// app/Http/Controllers/TaskController.php
class TaskController extends Controller
{
    public function update(
        UpdateTaskRequest $request,
        int $id,
        TaskRepositoryInterface $repository,
        TaskStatusManager $statusManager
    ) {
        $task = $repository->findById($id);

        if (!$task) {
            return back()->with('error', 'Task not found');
        }

        // Actualizar datos básicos
        $repository->update($id, $request->validated());

        // Cambiar estado si se proporciona
        if ($request->has('status')) {
            try {
                $statusManager->transitionTo($task, Status::from($request->status));
            } catch (InvalidArgumentException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        return back()->with('success', 'Task updated successfully');
    }
}
```

**Ejemplo - Livewire Component:**
```php
// app/Livewire/TaskList.php
class TaskList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $projectFilter = '';

    public function __construct(
        private readonly TaskRepositoryInterface $repository
    ) {}

    public function render()
    {
        $tasks = $this->repository->all();

        // Aplicar filtros
        if ($this->search) {
            $tasks = $this->repository->search($this->search);
        }

        if ($this->statusFilter) {
            $tasks = $tasks->where('status', $this->statusFilter);
        }

        if ($this->projectFilter) {
            $tasks = $tasks->where('project_id', $this->projectFilter);
        }

        return view('livewire.task-list', [
            'tasks' => $tasks,
        ]);
    }
}
```

**Ejemplo - MCP Tool:**
```php
// app/Mcp/Tools/CreateTask.php
class CreateTask extends Tool
{
    // Ya lo vimos en el Capítulo 2
    // Es parte de la capa de presentación para IA
}
```

## 3.4 Flujo Completo a Través de las Capas

Veamos un ejemplo completo de cómo fluye una petición a través de todas las capas:

### Escenario: Usuario crea una tarea vía MCP Tool

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. PRESENTATION LAYER (MCP Tool)                                │
│                                                                  │
│ Claude ejecuta: CreateTask tool                                 │
│ Parámetros: {                                                   │
│   "project_id": 1,                                              │
│   "title": "Implement authentication",                         │
│   "priority": "high"                                            │
│ }                                                               │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 2. APPLICATION LAYER (Implícita en el Tool)                     │
│                                                                  │
│ CreateTask::handle() coordina:                                  │
│ - Extrae parámetros del Request                                │
│ - Convierte "high" a Priority::HIGH (Value Object)             │
│ - Establece Status::PENDING por defecto                        │
│ - Prepara array de datos                                        │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 3. DOMAIN LAYER (Lógica de Negocio)                            │
│                                                                  │
│ Priority::from("high") ejecuta:                                 │
│ - Valida que "high" es un valor válido                         │
│ - Retorna Priority::HIGH enum                                   │
│                                                                  │
│ Status::PENDING usado como estado inicial                       │
│ - Automáticamente tipado y validado                            │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 4. INFRASTRUCTURE LAYER (Persistencia)                         │
│                                                                  │
│ TaskRepository::create() ejecuta:                               │
│ - Recibe array con Value Objects                               │
│ - Task::create() (Eloquent Model)                              │
│ - Eloquent castea automáticamente:                             │
│   * Status::PENDING → 'pending' (en DB)                        │
│   * Priority::HIGH → 'high' (en DB)                            │
│ - INSERT en PostgreSQL                                          │
│ - Retorna Task model con Value Objects casteados               │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 5. VUELTA A PRESENTATION LAYER                                  │
│                                                                  │
│ CreateTask::handle() retorna Response::json([                   │
│   'success' => true,                                            │
│   'task' => [                                                   │
│     'id' => 42,                                                 │
│     'title' => 'Implement authentication',                      │
│     'status' => 'pending',  // Value Object → string            │
│     'priority' => 'high'    // Value Object → string            │
│   ]                                                             │
│ ])                                                              │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 6. CLIENTE MCP (Claude)                                         │
│                                                                  │
│ Claude recibe la respuesta y dice:                             │
│ "He creado la tarea #42 'Implement authentication' con          │
│  prioridad alta. La tarea está lista para ser asignada."       │
└─────────────────────────────────────────────────────────────────┘
```

### Código Completo del Flujo

**1. Presentation Layer - MCP Tool:**
```php
// app/Mcp/Tools/CreateTask.php
class CreateTask extends Tool
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository  // Dependency Injection
    ) {}

    public function handle(Request $request): Response
    {
        // APPLICATION LAYER: Coordinar y preparar datos
        $data = [
            'project_id' => $request->input('project_id'),
            'title' => $request->input('title'),
            'priority' => Priority::from($request->input('priority', 'medium')), // DOMAIN
            'status' => Status::PENDING,                                         // DOMAIN
            'created_by' => $request->input('created_by'),
        ];

        // INFRASTRUCTURE LAYER: Persistir
        $task = $this->taskRepository->create($data);

        // PRESENTATION LAYER: Formatear respuesta
        return Response::json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,      // Value Object → string
                'priority' => $task->priority->value,  // Value Object → string
            ],
        ]);
    }
}
```

**2. Domain Layer - Value Object:**
```php
// app/Domain/TaskManagement/ValueObjects/Priority.php
enum Priority: string
{
    case HIGH = 'high';
    case MEDIUM = 'medium';
    // ...

    public static function from(string $value): self
    {
        return match($value) {
            'high' => self::HIGH,
            'medium' => self::MEDIUM,
            'low' => self::LOW,
            'critical' => self::CRITICAL,
            default => throw new \ValueError("Invalid priority: {$value}"),
        };
    }
}
```

**3. Infrastructure Layer - Repository:**
```php
// app/Infrastructure/Persistence/Eloquent/Repositories/EloquentTaskRepository.php
class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function create(array $data): Task
    {
        // Eloquent casteará automáticamente Priority y Status
        return Task::create($data);
    }
}
```

**4. Infrastructure Layer - Eloquent Model:**
```php
// app/Infrastructure/Persistence/Eloquent/Models/Task.php
class Task extends Model
{
    protected $casts = [
        'status' => Status::class,      // Casting bidireccional automático
        'priority' => Priority::class,  // DB ↔ Value Object
    ];
}
```

## 3.5 Dependency Injection y Service Container

Laravel usa **Dependency Injection** extensivamente para resolver dependencias automáticamente.

### ¿Cómo funciona?

**Sin DI:**
```php
// ❌ Acoplamiento alto
class CreateTask extends Tool
{
    public function handle(Request $request): Response
    {
        // Instanciamos manualmente
        $repository = new EloquentTaskRepository();
        $task = $repository->create([...]);
        // ...
    }
}
```

**Problemas:**
- ❌ Difícil de testear (no puedes mockear)
- ❌ Acoplado a implementación específica
- ❌ Viola Dependency Inversion Principle

**Con DI:**
```php
// ✅ Bajo acoplamiento
class CreateTask extends Tool
{
    public function __construct(
        private readonly TaskRepositoryInterface $repository  // Interface, no implementación
    ) {}

    public function handle(Request $request): Response
    {
        $task = $this->repository->create([...]);  // Usa la interface
        // ...
    }
}
```

**Ventajas:**
- ✅ Testeable (puedes inyectar mock)
- ✅ Flexible (puedes cambiar implementación)
- ✅ Sigue SOLID principles

### Configurando el Service Container

**Service Provider:**
```php
// app/Providers/RepositoryServiceProvider.php
class RepositoryServiceProvider extends ServiceProvider
{
    // Binding de interfaces a implementaciones
    public $bindings = [
        TaskRepositoryInterface::class => EloquentTaskRepository::class,
        ProjectRepositoryInterface::class => EloquentProjectRepository::class,
        TagRepositoryInterface::class => EloquentTagRepository::class,
    ];

    public function register(): void
    {
        // Laravel resuelve automáticamente estos bindings
    }
}
```

**Registrar el Provider:**
```php
// bootstrap/providers.php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,  // ← Nuestro provider
];
```

**Ahora Laravel sabe:**
```
Cuando alguien pide TaskRepositoryInterface
    → Inyectar EloquentTaskRepository
```

### Testing con DI

**Test unitario mockeando repositorio:**
```php
// tests/Unit/Mcp/CreateTaskToolTest.php
class CreateTaskToolTest extends TestCase
{
    public function test_creates_task_successfully()
    {
        // 1. Crear mock del repositorio
        $mockRepo = $this->createMock(TaskRepositoryInterface::class);

        // 2. Configurar expectativa
        $mockRepo->expects($this->once())
            ->method('create')
            ->willReturn(new Task(['id' => 1, 'title' => 'Test']));

        // 3. Inyectar mock en el tool
        $tool = new CreateTask($mockRepo);

        // 4. Ejecutar
        $response = $tool->handle(new Request(['title' => 'Test', ...]));

        // 5. Verificar
        $this->assertTrue($response->getData()['success']);
    }
}
```

## 3.6 Estructura de Directorios Completa

```
laravelmcp/
├── app/
│   ├── Domain/                           # ← DOMAIN LAYER
│   │   ├── TaskManagement/
│   │   │   ├── Contracts/
│   │   │   │   └── Repositories/
│   │   │   │       ├── TaskRepositoryInterface.php
│   │   │   │       └── ProjectRepositoryInterface.php
│   │   │   ├── Services/
│   │   │   │   ├── TaskStatusManager.php
│   │   │   │   └── TaskPriorityCalculator.php
│   │   │   └── ValueObjects/
│   │   │       ├── Status.php
│   │   │       └── Priority.php
│   │   ├── AIIntegration/
│   │   │   └── Services/
│   │   │       ├── OpenAIService.php
│   │   │       └── TaskDescriptionGenerator.php
│   │   └── Analytics/
│   │       └── Services/
│   │           ├── MetricsCollector.php
│   │           └── ReportGenerator.php
│   │
│   ├── Infrastructure/                   # ← INFRASTRUCTURE LAYER
│   │   └── Persistence/
│   │       └── Eloquent/
│   │           ├── Models/
│   │           │   ├── Task.php
│   │           │   ├── Project.php
│   │           │   └── Tag.php
│   │           └── Repositories/
│   │               ├── EloquentTaskRepository.php
│   │               └── EloquentProjectRepository.php
│   │
│   ├── Mcp/                              # ← PRESENTATION LAYER (para IA)
│   │   ├── Servers/
│   │   │   ├── TaskToolsServer.php
│   │   │   └── AIAssistantServer.php
│   │   ├── Tools/
│   │   │   ├── CreateTask.php
│   │   │   └── UpdateTask.php
│   │   └── Resources/
│   │       ├── TeamMetrics.php
│   │       └── ProjectMetrics.php
│   │
│   ├── Http/                             # ← PRESENTATION LAYER (para humanos)
│   │   └── Controllers/
│   │       └── TaskController.php
│   │
│   ├── Livewire/                         # ← PRESENTATION LAYER (componentes)
│   │   ├── Dashboard.php
│   │   └── TaskList.php
│   │
│   └── Providers/                        # ← SERVICE CONTAINER
│       ├── AppServiceProvider.php
│       └── RepositoryServiceProvider.php
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   └── views/
│       ├── layouts/
│       └── livewire/
│
└── tests/
    ├── Unit/                             # Tests del Domain Layer
    │   └── Domain/
    ├── Feature/                          # Tests del Infrastructure Layer
    │   └── Repositories/
    └── Integration/                      # Tests de integración (MCP)
        └── Mcp/
```

## 3.7 Ventajas de Esta Arquitectura

### 1. **Separación de Responsabilidades**

Cada capa tiene un propósito claro:
- Domain: Reglas de negocio
- Infrastructure: Detalles técnicos
- Presentation: Interfaz con el usuario

### 2. **Testeable**

```php
// Test unitario de Domain (no necesita DB)
class StatusTest extends TestCase
{
    public function test_pending_can_transition_to_in_progress()
    {
        $pending = Status::PENDING;
        $this->assertTrue($pending->canTransitionTo(Status::IN_PROGRESS));
    }
}

// Test de Infrastructure (necesita DB)
class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_task()
    {
        $repo = app(TaskRepositoryInterface::class);
        $task = $repo->create([...]);
        $this->assertNotNull($task);
    }
}
```

### 3. **Flexible y Mantenible**

Puedes cambiar tecnologías sin afectar la lógica de negocio:

```php
// Cambiar de Eloquent a Doctrine ORM
// Solo modificas la capa Infrastructure

// Antes:
class EloquentTaskRepository implements TaskRepositoryInterface { ... }

// Después:
class DoctrineTaskRepository implements TaskRepositoryInterface { ... }

// El Domain Layer NO CAMBIA
// Los MCP Tools NO CAMBIAN
// Solo cambias el binding en el ServiceProvider
```

### 4. **Reutilizable**

La misma lógica de negocio se usa en:
- ✅ Controladores HTTP
- ✅ MCP Tools
- ✅ API REST
- ✅ Comandos de consola
- ✅ Jobs en cola
- ✅ Tests

### 5. **Escalable**

Agregar nuevas funcionalidades es estructurado:
1. Crea Value Objects/Entities en Domain
2. Crea Services de dominio si hay lógica compleja
3. Crea Repository Interface en Domain
4. Implementa Repository en Infrastructure
5. Crea Tool/Controller en Presentation
6. Registra bindings en ServiceProvider

## 3.8 Resumen del Capítulo 3

🎯 **Conceptos Clave Aprendidos:**

1. **Domain-Driven Design (DDD)** pone el negocio al centro
   - Ubiquitous Language: Código que habla el lenguaje del negocio
   - Value Objects: Objetos inmutables con comportamiento
   - Domain Services: Lógica de negocio compleja
   - Repository Interfaces: Contratos para acceso a datos

2. **3 Bounded Contexts** separan responsabilidades
   - TaskManagement: Gestión de tareas y proyectos
   - AIIntegration: Servicios de IA y OpenAI
   - Analytics: Métricas y reportes

3. **4 Capas arquitectónicas** organizan el código
   - Domain Layer: Lógica de negocio pura
   - Infrastructure Layer: Detalles técnicos (Eloquent, DB)
   - Application Layer: Coordinación de casos de uso
   - Presentation Layer: Interfaz (MCP, HTTP, Livewire)

4. **Dependency Injection** permite flexibilidad
   - Service Container resuelve dependencias
   - Interfaces en vez de implementaciones concretas
   - Testeable y mantenible

5. **Value Objects** encapsulan validación y comportamiento
   - Status y Priority son enums con métodos
   - Casting automático Eloquent ↔ Value Object
   - Tipo-seguro y inmutable

🔜 **Próximo Capítulo:**
En el Capítulo 4 profundizaremos en **Value Objects**, viendo cómo crearlos, usarlos, y por qué son fundamentales en DDD.

---

**Estado del Tutorial:** Capítulos 1-3 de 15 completados ✓


# Capítulo 4: Value Objects en Profundidad

## 4.1 ¿Qué es un Value Object?

Un **Value Object** es un concepto fundamental en Domain-Driven Design (DDD) que representa un valor inmutable que:

1. **No tiene identidad propia** - Se identifica por sus atributos, no por un ID
2. **Es inmutable** - Una vez creado, no puede cambiar
3. **Encapsula comportamiento** - No es solo un contenedor de datos
4. **Se compara por valor** - Dos Value Objects son iguales si sus atributos son iguales

### 🔍 Analogía del Mundo Real

Piensa en una **moneda de $100 pesos**:

```
🪙 Billete #123456 de $100
🪙 Billete #789012 de $100
```

¿Son iguales? **Sí**, para ti valen lo mismo aunque tengan números de serie diferentes. No te importa *cuál* billete tienes, solo su **valor**. Eso es un Value Object.

Compare con una **Entity** (Entidad):

```
👤 Juan Pérez (ID: 1234)
👤 Juan Pérez (ID: 5678)
```

¿Son iguales? **No**, aunque tengan el mismo nombre, son personas diferentes con identidades únicas. Eso es una Entity.

### 📊 Value Object vs Entity

| Característica | Value Object | Entity |
|---------------|--------------|--------|
| **Identidad** | No tiene ID | Tiene ID único |
| **Igualdad** | Por valor de atributos | Por ID |
| **Mutabilidad** | Inmutable | Puede cambiar |
| **Ejemplo** | Status, Priority, Email, Money | Task, User, Project |

## 4.2 Value Objects en Nuestro Proyecto

En **TaskMaster AI** tenemos dos Value Objects principales:

1. **Status** - El estado de una tarea (pending, in_progress, review, completed, blocked)
2. **Priority** - La prioridad de una tarea (low, medium, high, critical)

Ambos están implementados como **PHP 8.1+ Enums**, que son la forma moderna y type-safe de crear Value Objects.

### 📁 Ubicación en el Proyecto

```
app/Domain/TaskManagement/ValueObjects/
├── Status.php      # 127 líneas - Estado del ciclo de vida
└── Priority.php    # 131 líneas - Nivel de prioridad
```

## 4.3 Status Value Object - Análisis Completo

### 4.3.1 Código Completo Comentado

```php
<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\ValueObjects;

/**
 * Status Value Object
 *
 * Representa el estado de una tarea en el ciclo de vida.
 * Inmutable y type-safe usando PHP 8.1+ enums.
 */
enum Status: string  // ← Backed enum con valores string
{
    // ═══════════════════════════════════════════════════════════
    // 📌 CASOS DEL ENUM (los 5 estados posibles)
    // ═══════════════════════════════════════════════════════════

    case PENDING = 'pending';           // Tarea creada, sin empezar
    case IN_PROGRESS = 'in_progress';   // Alguien trabajando en ella
    case REVIEW = 'review';             // Esperando revisión
    case COMPLETED = 'completed';       // Tarea finalizada
    case BLOCKED = 'blocked';           // Bloqueada por algún impedimento

    // ═══════════════════════════════════════════════════════════
    // 🏷️ MÉTODO: label() - Nombre legible en español
    // ═══════════════════════════════════════════════════════════

    /**
     * Obtiene el nombre legible del estado
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::IN_PROGRESS => 'En Progreso',
            self::REVIEW => 'En Revisión',
            self::COMPLETED => 'Completada',
            self::BLOCKED => 'Bloqueada',
        };
    }

    // ═══════════════════════════════════════════════════════════
    // 🎨 MÉTODO: color() - Color para la interfaz de usuario
    // ═══════════════════════════════════════════════════════════

    /**
     * Obtiene el color para UI
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'gray',      // Gris - sin empezar
            self::IN_PROGRESS => 'blue',  // Azul - en trabajo
            self::REVIEW => 'yellow',     // Amarillo - esperando
            self::COMPLETED => 'green',   // Verde - terminado
            self::BLOCKED => 'red',       // Rojo - problema
        };
    }

    // ═══════════════════════════════════════════════════════════
    // ✅ MÉTODO: canTransitionTo() - Reglas de transición
    // ═══════════════════════════════════════════════════════════

    /**
     * Verifica si puede transicionar a otro estado
     *
     * Esta es LÓGICA DE NEGOCIO - Define las reglas del ciclo
     * de vida de una tarea.
     */
    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            // PENDING → Solo puede ir a IN_PROGRESS o BLOCKED
            self::PENDING => in_array($newStatus, [
                self::IN_PROGRESS,  // Alguien empieza a trabajar
                self::BLOCKED,      // Se bloquea sin empezar
            ]),

            // IN_PROGRESS → Puede ir a REVIEW, BLOCKED, o volver a PENDING
            self::IN_PROGRESS => in_array($newStatus, [
                self::REVIEW,       // Termina y pide revisión
                self::BLOCKED,      // Se encuentra un bloqueador
                self::PENDING,      // Se devuelve (no era el momento)
            ]),

            // REVIEW → Puede COMPLETARSE, volver a IN_PROGRESS, o BLOQUEARSE
            self::REVIEW => in_array($newStatus, [
                self::COMPLETED,    // Revisión aprobada ✓
                self::IN_PROGRESS,  // Requiere cambios, vuelve a trabajo
                self::BLOCKED,      // Se encuentra problema en revisión
            ]),

            // COMPLETED → Solo puede REABRIRSE a IN_PROGRESS
            self::COMPLETED => in_array($newStatus, [
                self::IN_PROGRESS,  // Reabrir tarea (nueva funcionalidad)
            ]),

            // BLOCKED → Puede desbloquearse a PENDING o IN_PROGRESS
            self::BLOCKED => in_array($newStatus, [
                self::PENDING,      // Se desbloquea pero nadie la retoma
                self::IN_PROGRESS,  // Se desbloquea y se retoma trabajo
            ]),
        };
    }

    // ═══════════════════════════════════════════════════════════
    // 📋 MÉTODO: availableTransitions() - Estados disponibles
    // ═══════════════════════════════════════════════════════════

    /**
     * Obtiene las transiciones disponibles desde este estado
     *
     * @return array<self>
     */
    public function availableTransitions(): array
    {
        $transitions = [];

        // Itera sobre TODOS los casos del enum
        foreach (self::cases() as $status) {
            // Si es una transición válida, agrégala
            if ($this->canTransitionTo($status)) {
                $transitions[] = $status;
            }
        }

        return $transitions;
    }

    // ═══════════════════════════════════════════════════════════
    // 🏁 MÉTODO: isFinal() - ¿Es un estado final?
    // ═══════════════════════════════════════════════════════════

    /**
     * Verifica si la tarea está en un estado final
     */
    public function isFinal(): bool
    {
        // Solo COMPLETED es considerado final
        // (aunque puede reabrirse)
        return $this === self::COMPLETED;
    }

    // ═══════════════════════════════════════════════════════════
    // 🔄 MÉTODO: isActive() - ¿Está en trabajo activo?
    // ═══════════════════════════════════════════════════════════

    /**
     * Verifica si la tarea está activa (en trabajo)
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::IN_PROGRESS,  // Alguien trabajando
            self::REVIEW,       // Esperando feedback
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // 📦 MÉTODO: toArray() - Para formularios y selects
    // ═══════════════════════════════════════════════════════════

    /**
     * Obtiene todos los estados como array asociativo
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $status) {
            $result[$status->value] = $status->label();
        }
        return $result;

        // Retorna algo como:
        // [
        //     'pending' => 'Pendiente',
        //     'in_progress' => 'En Progreso',
        //     ...
        // ]
    }
}
```

### 4.3.2 Diagrama del Ciclo de Vida

```
┌─────────────────────────────────────────────────────────────┐
│              📊 CICLO DE VIDA DE UNA TAREA                  │
└─────────────────────────────────────────────────────────────┘

        ┌──────────────┐
        │   PENDING    │ ← Tarea creada
        │  (Pendiente) │
        └──────┬───────┘
               │
               │ Alguien empieza
               ▼
        ┌──────────────┐
    ┌──▶│ IN_PROGRESS  │──┐
    │   │(En Progreso) │  │
    │   └──────┬───────┘  │
    │          │          │
    │   Termina│          │ Se encuentra
    │          ▼          │ un bloqueador
    │   ┌──────────────┐  │
    │   │    REVIEW    │  │
    │   │ (En Revisión)│  │
    │   └──────┬───────┘  │
    │          │          │
    │   Aprueba│          │
    │          ▼          ▼
    │   ┌──────────────┐  ┌──────────────┐
    │   │  COMPLETED   │  │   BLOCKED    │
    │   │ (Completada) │  │  (Bloqueada) │
    │   └──────┬───────┘  └──────┬───────┘
    │          │                 │
    └──────────┘                 └─────────▶ PENDING
         Reabrir                  Desbloquear

Leyenda:
─────▶  Transición normal
═════▶  Transición especial
```

### 4.3.3 Ejemplos de Uso

#### **Ejemplo 1: Validar transiciones**

```php
use App\Domain\TaskManagement\ValueObjects\Status;

// Crear una tarea en estado PENDING
$currentStatus = Status::PENDING;

// ✅ Transición VÁLIDA
if ($currentStatus->canTransitionTo(Status::IN_PROGRESS)) {
    echo "✓ Puedes empezar a trabajar";
}

// ❌ Transición INVÁLIDA
if (!$currentStatus->canTransitionTo(Status::COMPLETED)) {
    echo "✗ No puedes completar una tarea sin trabajarla";
}
```

#### **Ejemplo 2: Obtener transiciones disponibles**

```php
$status = Status::REVIEW;

$available = $status->availableTransitions();
// Retorna: [Status::COMPLETED, Status::IN_PROGRESS, Status::BLOCKED]

foreach ($available as $nextStatus) {
    echo "Puedes cambiar a: " . $nextStatus->label();
}

// Salida:
// Puedes cambiar a: Completada
// Puedes cambiar a: En Progreso
// Puedes cambiar a: Bloqueada
```

#### **Ejemplo 3: Usar en UI (Blade/Livewire)**

```php
<!-- resources/views/livewire/task-card.blade.php -->

<div class="task-card">
    <span class="badge bg-{{ $task->status->color() }}">
        {{ $task->status->label() }}
    </span>

    @if($task->status->isActive())
        <span class="pulse-indicator">●</span>
    @endif

    @if($task->status->isFinal())
        <span class="checkmark">✓</span>
    @endif
</div>
```

#### **Ejemplo 4: Validación en MCP Tool**

```php
// app/MCP/Presentation/TaskTools/UpdateTask.php

public function handle(array $params): array
{
    $task = $this->repository->findById($params['id']);

    if (isset($params['status'])) {
        $newStatus = Status::from($params['status']);

        // ✅ VALIDAR la transición antes de aplicar
        if (!$task->status->canTransitionTo($newStatus)) {
            throw new InvalidStatusTransitionException(
                "No puedes cambiar de {$task->status->label()} " .
                "a {$newStatus->label()}"
            );
        }

        $task->status = $newStatus;
    }

    $this->repository->update($task);

    return ['success' => true, 'task' => $task];
}
```

## 4.4 Priority Value Object - Análisis Completo

### 4.4.1 Código Completo Comentado

```php
<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\ValueObjects;

/**
 * Priority Value Object
 *
 * Representa la prioridad de una tarea.
 * Inmutable y type-safe usando PHP 8.1+ enums.
 */
enum Priority: string
{
    // ═══════════════════════════════════════════════════════════
    // 📌 CASOS DEL ENUM (las 4 prioridades)
    // ═══════════════════════════════════════════════════════════

    case LOW = 'low';           // Prioridad baja
    case MEDIUM = 'medium';     // Prioridad media
    case HIGH = 'high';         // Prioridad alta
    case CRITICAL = 'critical'; // Prioridad crítica

    // ═══════════════════════════════════════════════════════════
    // 🏷️ MÉTODO: label() - Nombre legible en español
    // ═══════════════════════════════════════════════════════════

    public function label(): string
    {
        return match($this) {
            self::LOW => 'Baja',
            self::MEDIUM => 'Media',
            self::HIGH => 'Alta',
            self::CRITICAL => 'Crítica',
        };
    }

    // ═══════════════════════════════════════════════════════════
    // 🎨 MÉTODO: color() - Color para UI
    // ═══════════════════════════════════════════════════════════

    public function color(): string
    {
        return match($this) {
            self::LOW => 'gray',        // Gris - no urgente
            self::MEDIUM => 'blue',     // Azul - normal
            self::HIGH => 'orange',     // Naranja - importante
            self::CRITICAL => 'red',    // Rojo - urgente
        };
    }

    // ═══════════════════════════════════════════════════════════
    // 🔢 MÉTODO: score() - Valor numérico para ordenamiento
    // ═══════════════════════════════════════════════════════════

    /**
     * Obtiene el score numérico de la prioridad (para ordenamiento)
     */
    public function score(): int
    {
        return match($this) {
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::CRITICAL => 4,
        };
    }

    // ═══════════════════════════════════════════════════════════
    // ⚖️ MÉTODO: compare() - Comparar dos prioridades
    // ═══════════════════════════════════════════════════════════

    /**
     * Compara esta prioridad con otra
     *
     * @return int -1 si es menor, 0 si es igual, 1 si es mayor
     */
    public function compare(self $other): int
    {
        return $this->score() <=> $other->score();
    }

    // ═══════════════════════════════════════════════════════════
    // 📊 MÉTODOS: Comparaciones booleanas
    // ═══════════════════════════════════════════════════════════

    /**
     * Verifica si esta prioridad es mayor que otra
     */
    public function isHigherThan(self $other): bool
    {
        return $this->score() > $other->score();
    }

    /**
     * Verifica si esta prioridad es menor que otra
     */
    public function isLowerThan(self $other): bool
    {
        return $this->score() < $other->score();
    }

    // ═══════════════════════════════════════════════════════════
    // ⚠️ MÉTODO: requiresImmediateAttention()
    // ═══════════════════════════════════════════════════════════

    /**
     * Verifica si requiere atención inmediata
     *
     * LÓGICA DE NEGOCIO: Solo HIGH y CRITICAL son urgentes
     */
    public function requiresImmediateAttention(): bool
    {
        return in_array($this, [self::HIGH, self::CRITICAL]);
    }

    // ═══════════════════════════════════════════════════════════
    // 😀 MÉTODO: emoji() - Representación visual
    // ═══════════════════════════════════════════════════════════

    /**
     * Obtiene el emoji representativo
     */
    public function emoji(): string
    {
        return match($this) {
            self::LOW => '🔵',
            self::MEDIUM => '🟡',
            self::HIGH => '🟠',
            self::CRITICAL => '🔴',
        };
    }

    // ═══════════════════════════════════════════════════════════
    // 📦 MÉTODOS ESTÁTICOS: Utilidades
    // ═══════════════════════════════════════════════════════════

    /**
     * Obtiene todas las prioridades como array asociativo
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $priority) {
            $result[$priority->value] = $priority->label();
        }
        return $result;
    }

    /**
     * Obtiene las prioridades ordenadas por score
     *
     * @return array<self>
     */
    public static function ordered(): array
    {
        $priorities = self::cases();
        usort($priorities, fn($a, $b) => $a->score() <=> $b->score());
        return $priorities;
    }
}
```

### 4.4.2 Ejemplos de Uso

#### **Ejemplo 1: Comparar prioridades**

```php
use App\Domain\TaskManagement\ValueObjects\Priority;

$taskA = Priority::HIGH;
$taskB = Priority::MEDIUM;

if ($taskA->isHigherThan($taskB)) {
    echo "Task A tiene mayor prioridad";
}

// Comparación de 3 vías
$result = $taskA->compare($taskB);
// Retorna 1 (taskA es mayor que taskB)
```

#### **Ejemplo 2: Ordenar tareas por prioridad**

```php
// Colección de tareas
$tasks = [
    ['name' => 'Fix bug', 'priority' => Priority::CRITICAL],
    ['name' => 'Code review', 'priority' => Priority::MEDIUM],
    ['name' => 'Refactor', 'priority' => Priority::LOW],
    ['name' => 'Deploy', 'priority' => Priority::HIGH],
];

// Ordenar por score de prioridad (mayor primero)
usort($tasks, function($a, $b) {
    return $b['priority']->score() <=> $a['priority']->score();
});

// Resultado:
// 1. Fix bug (CRITICAL - 4)
// 2. Deploy (HIGH - 3)
// 3. Code review (MEDIUM - 2)
// 4. Refactor (LOW - 1)
```

#### **Ejemplo 3: Filtrar tareas urgentes**

```php
$tasks = Task::all();

// Filtrar solo tareas urgentes
$urgentTasks = $tasks->filter(function($task) {
    return $task->priority->requiresImmediateAttention();
});

// Retorna solo tareas con prioridad HIGH o CRITICAL
```

#### **Ejemplo 4: Dashboard de prioridades**

```php
<!-- resources/views/livewire/priority-dashboard.blade.php -->

<div class="priority-grid">
    @foreach(Priority::ordered() as $priority)
        <div class="priority-column">
            <h3>
                <span class="emoji">{{ $priority->emoji() }}</span>
                {{ $priority->label() }}
            </h3>

            @php
                $count = $tasks->where('priority', $priority)->count();
            @endphp

            <div class="count badge bg-{{ $priority->color() }}">
                {{ $count }} tareas
            </div>

            @if($priority->requiresImmediateAttention())
                <span class="urgent-badge">⚠️ URGENTE</span>
            @endif
        </div>
    @endforeach
</div>
```

## 4.5 Ventajas de los Value Objects

### 1. **Type Safety (Seguridad de Tipos)**

**❌ Sin Value Objects (código frágil):**

```php
// Cualquier string es válido → PELIGROSO
function updateTaskStatus(int $taskId, string $status)
{
    // ¿Qué pasa si alguien pasa 'pendiente' en vez de 'pending'?
    // ¿O 'done' en vez de 'completed'?
    // ¡ERROR EN RUNTIME!

    $task = Task::find($taskId);
    $task->status = $status;  // No hay validación
    $task->save();
}

// Llamadas problemáticas:
updateTaskStatus(1, 'pendiente');  // ❌ Typo
updateTaskStatus(1, 'PENDING');    // ❌ Case incorrecto
updateTaskStatus(1, 'done');       // ❌ Valor inválido
```

**✅ Con Value Objects (código robusto):**

```php
// Solo valores válidos del enum son permitidos
function updateTaskStatus(int $taskId, Status $status)
{
    $task = Task::find($taskId);
    $task->status = $status;
    $task->save();
}

// Solo estas llamadas son posibles:
updateTaskStatus(1, Status::PENDING);      // ✓
updateTaskStatus(1, Status::IN_PROGRESS);  // ✓

// Estas ni siquiera compilan:
updateTaskStatus(1, 'pending');    // ❌ TypeError
updateTaskStatus(1, 'done');       // ❌ TypeError
```

### 2. **Lógica de Negocio Encapsulada**

**❌ Sin Value Objects (lógica dispersa):**

```php
// La lógica de transición está DUPLICADA en múltiples lugares

// En el Controller:
if ($task->status === 'pending' && $newStatus === 'completed') {
    throw new Exception("No puedes completar sin trabajarla");
}

// En el MCP Tool:
if ($task->status === 'pending' && $newStatus === 'completed') {
    throw new Exception("No puedes completar sin trabajarla");
}

// En el Job:
if ($task->status === 'pending' && $newStatus === 'completed') {
    throw new Exception("No puedes completar sin trabajarla");
}

// 🚨 PROBLEMA: Si cambian las reglas, tienes que actualizar 10 lugares
```

**✅ Con Value Objects (lógica centralizada):**

```php
// La lógica está EN UN SOLO LUGAR

// En cualquier parte del código:
if (!$task->status->canTransitionTo($newStatus)) {
    throw new InvalidTransitionException(
        "No puedes cambiar de {$task->status->label()} " .
        "a {$newStatus->label()}"
    );
}

// ✅ Si cambian las reglas, solo modificas Status.php
```

### 3. **Código Autodocumentado**

**❌ Sin Value Objects:**

```php
// ¿Qué significa este 3?
$task->priority = 3;

// ¿Y este string?
$task->status = 'in_progress';

// Tienes que ir a la documentación o base de datos para saberlo
```

**✅ Con Value Objects:**

```php
// Es obvio qué significa
$task->priority = Priority::HIGH;
$task->status = Status::IN_PROGRESS;

// El código se lee como lenguaje natural (Ubiquitous Language)
```

### 4. **Refactoring Seguro**

**❌ Sin Value Objects:**

```php
// Si quieres renombrar 'in_progress' → 'working'
// Tienes que buscar TODOS los strings en TODO el código
// Y esperar no romper nada

// ¿Cuántos lugares hay que cambiar? ¿100? ¿200?
```

**✅ Con Value Objects:**

```php
// Solo cambias el valor del enum:
enum Status: string
{
    case IN_PROGRESS = 'working';  // ← Un solo cambio
}

// TODO el código sigue funcionando porque usa Status::IN_PROGRESS
```

### 5. **Prevención de Errores**

```php
// ❌ Sin Value Objects - Error silencioso
$task->status = 'inprogress';  // Typo → tarea queda en estado inválido
$task->save();                  // No hay error, pero la data está corrupta

// ✅ Con Value Objects - Error explícito
$task->status = Status::from('inprogress');  // ← ValueError inmediato
// "inprogress is not a valid backing value for enum Status"
```

## 4.6 Cómo Eloquent Usa los Value Objects

### 4.6.1 Casting Automático

Laravel Eloquent puede convertir automáticamente entre strings en la base de datos y Value Objects en tu código:

```php
// app/Infrastructure/TaskManagement/Models/Task.php

class Task extends Model
{
    protected $casts = [
        'status' => Status::class,     // ← Casting automático
        'priority' => Priority::class, // ← Casting automático
    ];
}
```

**¿Cómo funciona?**

```php
// 1. GUARDAR en base de datos
$task = new Task();
$task->status = Status::IN_PROGRESS;  // ← Value Object
$task->save();

// Eloquent automáticamente convierte:
// Status::IN_PROGRESS → 'in_progress' (string en DB)

// 2. LEER desde base de datos
$task = Task::find(1);

// Eloquent automáticamente convierte:
// 'in_progress' (string en DB) → Status::IN_PROGRESS (Value Object)

echo $task->status->label();  // "En Progreso"
```

### 4.6.2 Consultas con Value Objects

```php
// Puedes usar Value Objects directamente en queries

// Buscar tareas pendientes
$tasks = Task::where('status', Status::PENDING)->get();

// Buscar tareas con prioridad alta o crítica
$urgentTasks = Task::whereIn('priority', [
    Priority::HIGH,
    Priority::CRITICAL
])->get();

// Eloquent convierte automáticamente:
// Priority::HIGH → 'high'
// Priority::CRITICAL → 'critical'
```

## 4.7 Testing de Value Objects

### 4.7.1 Test de Status

```php
// tests/Unit/Domain/ValueObjects/StatusTest.php

use App\Domain\TaskManagement\ValueObjects\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /** @test */
    public function it_has_correct_labels()
    {
        $this->assertEquals('Pendiente', Status::PENDING->label());
        $this->assertEquals('En Progreso', Status::IN_PROGRESS->label());
        $this->assertEquals('Completada', Status::COMPLETED->label());
    }

    /** @test */
    public function it_validates_transitions_correctly()
    {
        $pending = Status::PENDING;

        // Transiciones válidas desde PENDING
        $this->assertTrue($pending->canTransitionTo(Status::IN_PROGRESS));
        $this->assertTrue($pending->canTransitionTo(Status::BLOCKED));

        // Transiciones inválidas desde PENDING
        $this->assertFalse($pending->canTransitionTo(Status::REVIEW));
        $this->assertFalse($pending->canTransitionTo(Status::COMPLETED));
    }

    /** @test */
    public function completed_tasks_can_be_reopened()
    {
        $completed = Status::COMPLETED;

        // Puede reabrirse
        $this->assertTrue($completed->canTransitionTo(Status::IN_PROGRESS));

        // Pero no puede ir a otros estados
        $this->assertFalse($completed->canTransitionTo(Status::PENDING));
        $this->assertFalse($completed->canTransitionTo(Status::REVIEW));
    }

    /** @test */
    public function it_returns_available_transitions()
    {
        $review = Status::REVIEW;
        $transitions = $review->availableTransitions();

        $this->assertCount(3, $transitions);
        $this->assertContains(Status::COMPLETED, $transitions);
        $this->assertContains(Status::IN_PROGRESS, $transitions);
        $this->assertContains(Status::BLOCKED, $transitions);
    }

    /** @test */
    public function it_identifies_final_status()
    {
        $this->assertTrue(Status::COMPLETED->isFinal());
        $this->assertFalse(Status::PENDING->isFinal());
        $this->assertFalse(Status::IN_PROGRESS->isFinal());
    }

    /** @test */
    public function it_identifies_active_status()
    {
        $this->assertTrue(Status::IN_PROGRESS->isActive());
        $this->assertTrue(Status::REVIEW->isActive());
        $this->assertFalse(Status::PENDING->isActive());
        $this->assertFalse(Status::COMPLETED->isActive());
    }
}
```

### 4.7.2 Test de Priority

```php
// tests/Unit/Domain/ValueObjects/PriorityTest.php

use App\Domain\TaskManagement\ValueObjects\Priority;
use PHPUnit\Framework\TestCase;

class PriorityTest extends TestCase
{
    /** @test */
    public function it_has_correct_scores()
    {
        $this->assertEquals(1, Priority::LOW->score());
        $this->assertEquals(2, Priority::MEDIUM->score());
        $this->assertEquals(3, Priority::HIGH->score());
        $this->assertEquals(4, Priority::CRITICAL->score());
    }

    /** @test */
    public function it_compares_priorities_correctly()
    {
        $low = Priority::LOW;
        $high = Priority::HIGH;

        $this->assertTrue($high->isHigherThan($low));
        $this->assertTrue($low->isLowerThan($high));
        $this->assertFalse($low->isHigherThan($high));
    }

    /** @test */
    public function it_identifies_urgent_priorities()
    {
        $this->assertTrue(Priority::HIGH->requiresImmediateAttention());
        $this->assertTrue(Priority::CRITICAL->requiresImmediateAttention());
        $this->assertFalse(Priority::MEDIUM->requiresImmediateAttention());
        $this->assertFalse(Priority::LOW->requiresImmediateAttention());
    }

    /** @test */
    public function it_returns_ordered_priorities()
    {
        $ordered = Priority::ordered();

        $this->assertCount(4, $ordered);
        $this->assertEquals(Priority::LOW, $ordered[0]);
        $this->assertEquals(Priority::MEDIUM, $ordered[1]);
        $this->assertEquals(Priority::HIGH, $ordered[2]);
        $this->assertEquals(Priority::CRITICAL, $ordered[3]);
    }
}
```

## 4.8 Cuándo Crear un Value Object

### ✅ Deberías crear un Value Object cuando:

1. **Tiene validaciones propias**
   - Email (debe tener formato email)
   - PhoneNumber (debe tener formato válido)
   - Money (debe tener currency y amount)

2. **Tiene comportamiento de negocio**
   - Status (reglas de transición)
   - Priority (comparaciones)
   - DateRange (contains, overlaps)

3. **Es usado en múltiples lugares**
   - Si usas el mismo conjunto de validaciones en 3+ lugares
   - Si la lógica se repite

4. **Representa un concepto del dominio**
   - Color (hex, rgb, hsl conversions)
   - Coordinates (latitude, longitude)
   - Address (street, city, zip)

### ❌ NO necesitas un Value Object cuando:

1. **Es solo un string simple sin validación**
   - `$task->title` → Solo un string
   - `$user->name` → Solo un string

2. **No tiene comportamiento**
   - `$product->description` → Solo texto
   - `$post->content` → Solo markdown

3. **Es usado en un solo lugar**
   - Un string que solo validas una vez
   - No hay lógica compartida

## 4.9 Creando Tu Propio Value Object

### Ejemplo: EmailAddress Value Object

```php
<?php

namespace App\Domain\Shared\ValueObjects;

class EmailAddress
{
    private string $value;

    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                "'{$email}' no es un email válido"
            );
        }

        $this->value = strtolower($email);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    public function localPart(): string
    {
        return substr($this->value, 0, strpos($this->value, '@'));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

**Uso:**

```php
$email = new EmailAddress('juan@example.com');

echo $email->value();      // "juan@example.com"
echo $email->domain();     // "example.com"
echo $email->localPart();  // "juan"

// Inmutabilidad
$email->value = 'otro@mail.com';  // ❌ Error - propiedad privada

// Comparación por valor
$email1 = new EmailAddress('test@mail.com');
$email2 = new EmailAddress('test@mail.com');
$email1->equals($email2);  // true
```

## 4.10 Ejercicios Prácticos

### Ejercicio 1: Extender Status

Agrega un nuevo método `timeInStatus()` que retorne el tiempo promedio que una tarea pasa en ese estado:

```php
// Tu implementación:
public function averageTimeInStatus(): int
{
    return match($this) {
        self::PENDING => 48,        // 48 horas promedio
        self::IN_PROGRESS => 72,    // 72 horas promedio
        self::REVIEW => 24,         // 24 horas promedio
        self::COMPLETED => 0,       // No aplica
        self::BLOCKED => ???,       // ← ¿Cuánto debería ser?
    };
}
```

### Ejercicio 2: Crear Difficulty Value Object

Crea un nuevo Value Object `Difficulty` con los casos: TRIVIAL, EASY, MEDIUM, HARD, EXPERT

Debe tener:
- `label()`: Nombre en español
- `estimatedHours()`: Horas estimadas (1, 4, 8, 16, 40)
- `requiredExperience()`: Nivel requerido (junior, mid, senior, expert)
- `color()`: Color para UI

### Ejercicio 3: DateRange Value Object

Crea un Value Object para rangos de fechas:

```php
class DateRange
{
    public function __construct(
        private Carbon $start,
        private Carbon $end
    ) {
        if ($start->isAfter($end)) {
            throw new \InvalidArgumentException(
                "Start date must be before end date"
            );
        }
    }

    public function contains(Carbon $date): bool
    {
        // TODO: Implementar
    }

    public function overlaps(self $other): bool
    {
        // TODO: Implementar
    }

    public function durationInDays(): int
    {
        // TODO: Implementar
    }
}
```

## 4.11 Resumen del Capítulo 4

🎯 **Conceptos Clave Aprendidos:**

1. **Value Objects** son objetos inmutables identificados por su valor
   - No tienen ID único
   - Se comparan por sus atributos
   - Encapsulan comportamiento

2. **PHP 8.1+ Enums** son perfectos para Value Objects
   - Type-safe y modernos
   - Backed enums con valores string/int
   - Métodos personalizados con lógica de negocio

3. **Status Value Object** controla el ciclo de vida
   - 5 estados: PENDING, IN_PROGRESS, REVIEW, COMPLETED, BLOCKED
   - Validación de transiciones
   - Métodos de consulta (isFinal, isActive)

4. **Priority Value Object** maneja urgencia
   - 4 niveles: LOW, MEDIUM, HIGH, CRITICAL
   - Comparaciones (isHigherThan, isLowerThan)
   - Score numérico para ordenamiento

5. **Ventajas sobre strings/ints simples**
   - Type safety (errores en compile-time)
   - Lógica centralizada
   - Código autodocumentado
   - Refactoring seguro

6. **Eloquent Casting** integra Value Objects
   - Conversión automática DB ↔ Value Object
   - Uso directo en queries
   - Sin código boilerplate

🔜 **Próximo Capítulo:**
En el Capítulo 5 exploraremos el **Patrón Repository**, viendo cómo abstraer el acceso a datos y mantener el Domain Layer independiente de la tecnología de persistencia.

---

**Estado del Tutorial:** Capítulos 1-4 de 15 completados ✓


# Capítulo 5: Patrón Repository

## 5.1 ¿Qué es el Patrón Repository?

El **Patrón Repository** es un patrón de diseño que actúa como una **capa de abstracción** entre la lógica de negocio y la capa de acceso a datos.

### 🔍 Analogía del Mundo Real

Imagina que estás en una **biblioteca**:

```
TÚ (Domain Layer)
    ↓
    "Necesito el libro 'Laravel MCP'"
    ↓
BIBLIOTECARIO (Repository)
    ↓
    "Voy a buscarlo..."
    ↓
ESTANTERÍA (Base de Datos)
```

**Tú no necesitas saber:**
- ¿En qué estante está el libro?
- ¿Cómo está organizada la biblioteca (por autor, tema, fecha)?
- ¿Está en papel, microfilm, o digital?

**Solo le pides al bibliotecario** y él se encarga de todos los detalles.

Eso es exactamente lo que hace un Repository: **abstrae los detalles de cómo se almacenan y recuperan los datos**.

### 📊 Sin Repository vs Con Repository

#### **❌ Sin Repository (Acoplamiento directo a Eloquent)**

```php
// app/MCP/Presentation/TaskTools/CreateTask.php

class CreateTask
{
    public function handle(array $params): array
    {
        // ❌ Acoplamiento directo a Eloquent
        $task = Task::create([
            'title' => $params['title'],
            'status' => Status::PENDING,
            // ...
        ]);

        return ['success' => true, 'task' => $task];
    }
}
```

**Problemas:**
1. ❌ Acoplado a Eloquent (no puedes cambiar a Doctrine, MongoDB, etc.)
2. ❌ Difícil de testear (necesitas una base de datos real)
3. ❌ Lógica de consultas dispersa (queries duplicadas en múltiples lugares)
4. ❌ Viola el principio de Inversión de Dependencias (DDD)

#### **✅ Con Repository (Abstracción limpia)**

```php
// app/MCP/Presentation/TaskTools/CreateTask.php

class CreateTask
{
    public function __construct(
        private TaskRepositoryInterface $repository  // ← Interfaz, no implementación
    ) {}

    public function handle(array $params): array
    {
        // ✅ Desacoplado de la tecnología de persistencia
        $task = $this->repository->create([
            'title' => $params['title'],
            'status' => Status::PENDING,
            // ...
        ]);

        return ['success' => true, 'task' => $task];
    }
}
```

**Ventajas:**
1. ✅ Desacoplado de la tecnología (puedes cambiar Eloquent sin tocar el Tool)
2. ✅ Fácil de testear (inyectas un mock del repository)
3. ✅ Lógica de consultas centralizada (queries reutilizables)
4. ✅ Cumple el principio de Inversión de Dependencias

## 5.2 Arquitectura del Patrón Repository

```
┌─────────────────────────────────────────────────────────────┐
│                  ARQUITECTURA REPOSITORY                    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ PRESENTATION LAYER (MCP Tools)                              │
│                                                             │
│  CreateTask Tool                                            │
│  ├─ Necesita guardar una tarea                             │
│  └─ Depende de: TaskRepositoryInterface                    │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      │ Inyección de Dependencias
                      │
┌─────────────────────▼───────────────────────────────────────┐
│ DOMAIN LAYER (Contratos / Interfaces)                      │
│                                                             │
│  interface TaskRepositoryInterface                          │
│  {                                                          │
│      public function create(array $data): Task;             │
│      public function findById(int $id): ?Task;              │
│      public function all(): Collection;                     │
│      // ...más métodos                                      │
│  }                                                          │
│                                                             │
│  ← Define QUÉ operaciones existen (contrato)               │
│  ← NO define CÓMO se implementan                           │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      │ Implementa
                      │
┌─────────────────────▼───────────────────────────────────────┐
│ INFRASTRUCTURE LAYER (Implementaciones concretas)           │
│                                                             │
│  class EloquentTaskRepository                               │
│      implements TaskRepositoryInterface                     │
│  {                                                          │
│      public function create(array $data): Task              │
│      {                                                      │
│          return Task::create($data); ← Eloquent             │
│      }                                                      │
│                                                             │
│      public function findById(int $id): ?Task               │
│      {                                                      │
│          return Task::with([...])->find($id); ← Eloquent    │
│      }                                                      │
│  }                                                          │
│                                                             │
│  ← Define CÓMO se implementan las operaciones               │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      │ Usa
                      │
┌─────────────────────▼───────────────────────────────────────┐
│ BASE DE DATOS (PostgreSQL)                                  │
│                                                             │
│  Tabla: tasks                                               │
│  ├─ id                                                      │
│  ├─ title                                                   │
│  ├─ status                                                  │
│  └─ ...                                                     │
└─────────────────────────────────────────────────────────────┘
```

### 🔑 Principio Clave: Inversión de Dependencias

El **Domain Layer** (lógica de negocio) NO depende del **Infrastructure Layer** (detalles técnicos).

En su lugar:
- Domain define una **interfaz** (contrato)
- Infrastructure **implementa** esa interfaz
- Laravel inyecta la implementación en runtime

```
┌──────────────────┐           ┌──────────────────┐
│  CreateTask      │───────────▶│ TaskRepository   │
│  (Presentation)  │  usa       │ Interface        │
└──────────────────┘            │ (Domain)         │
                                └────────▲─────────┘
                                         │
                                         │ implementa
                                         │
                                ┌────────┴─────────┐
                                │ EloquentTask     │
                                │ Repository       │
                                │ (Infrastructure) │
                                └──────────────────┘
```

## 5.3 TaskRepositoryInterface - El Contrato

### 5.3.1 Código Completo Comentado

```php
<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\Contracts\Repositories;

use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use Illuminate\Database\Eloquent\Collection;

/**
 * TaskRepositoryInterface
 *
 * Contrato que define TODAS las operaciones de persistencia
 * para el agregado Task.
 *
 * Este contrato vive en el DOMAIN LAYER y define QUÉ operaciones
 * existen, pero NO cómo se implementan.
 */
interface TaskRepositoryInterface
{
    // ═══════════════════════════════════════════════════════════
    // 📖 MÉTODOS DE CONSULTA (Queries)
    // ═══════════════════════════════════════════════════════════

    /**
     * Find a task by its ID.
     *
     * @param int $id - El ID de la tarea
     * @return Task|null - La tarea o null si no existe
     */
    public function findById(int $id): ?Task;

    /**
     * Get all tasks.
     *
     * @return Collection - Todas las tareas del sistema
     */
    public function all(): Collection;

    /**
     * Get tasks by project ID.
     *
     * @param int $projectId
     * @return Collection - Tareas del proyecto
     */
    public function findByProjectId(int $projectId): Collection;

    /**
     * Get tasks assigned to a specific user.
     *
     * @param int $userId
     * @return Collection - Tareas asignadas al usuario
     */
    public function findByAssignedTo(int $userId): Collection;

    /**
     * Get tasks created by a specific user.
     *
     * @param int $userId
     * @return Collection - Tareas creadas por el usuario
     */
    public function findByCreator(int $userId): Collection;

    /**
     * Get tasks by status.
     *
     * Nota: Usa el Value Object Status, no un string
     *
     * @param Status $status - El estado a buscar
     * @return Collection - Tareas con ese estado
     */
    public function findByStatus(Status $status): Collection;

    /**
     * Get tasks by priority.
     *
     * Nota: Usa el Value Object Priority, no un string
     *
     * @param Priority $priority - La prioridad a buscar
     * @return Collection - Tareas con esa prioridad
     */
    public function findByPriority(Priority $priority): Collection;

    /**
     * Get high priority tasks.
     *
     * Método de conveniencia que encapsula lógica de negocio:
     * "Tareas con prioridad HIGH o CRITICAL"
     *
     * @return Collection
     */
    public function getHighPriority(): Collection;

    /**
     * Get overdue tasks.
     *
     * Método de conveniencia que encapsula lógica de negocio:
     * "Tareas cuya fecha límite ya pasó"
     *
     * @return Collection
     */
    public function getOverdue(): Collection;

    /**
     * Get pending tasks.
     *
     * Método de conveniencia: tareas con status = PENDING
     *
     * @return Collection
     */
    public function getPending(): Collection;

    /**
     * Get in-progress tasks.
     *
     * Método de conveniencia: tareas con status = IN_PROGRESS
     *
     * @return Collection
     */
    public function getInProgress(): Collection;

    /**
     * Get completed tasks.
     *
     * Método de conveniencia: tareas con status = COMPLETED
     *
     * @return Collection
     */
    public function getCompleted(): Collection;

    /**
     * Search tasks by title or description.
     *
     * @param string $query - Término de búsqueda
     * @return Collection - Tareas que coinciden con la búsqueda
     */
    public function search(string $query): Collection;

    // ═══════════════════════════════════════════════════════════
    // ✏️ MÉTODOS DE COMANDO (Commands)
    // ═══════════════════════════════════════════════════════════

    /**
     * Create a new task.
     *
     * @param array $data - Datos de la tarea
     * @return Task - La tarea creada
     */
    public function create(array $data): Task;

    /**
     * Update an existing task.
     *
     * @param int $id - ID de la tarea
     * @param array $data - Datos a actualizar
     * @return bool - true si se actualizó, false si no existe
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a task.
     *
     * @param int $id - ID de la tarea
     * @return bool - true si se eliminó, false si no existe
     */
    public function delete(int $id): bool;

    /**
     * Update task status.
     *
     * Método de conveniencia para actualizar solo el status
     *
     * @param int $id
     * @param Status $status - Nuevo status
     * @return bool
     */
    public function updateStatus(int $id, Status $status): bool;

    /**
     * Assign task to a user.
     *
     * Método de conveniencia para asignar tarea
     *
     * @param int $taskId
     * @param int $userId
     * @return bool
     */
    public function assign(int $taskId, int $userId): bool;

    // ═══════════════════════════════════════════════════════════
    // 🏷️ MÉTODOS DE RELACIONES
    // ═══════════════════════════════════════════════════════════

    /**
     * Attach tags to a task.
     *
     * @param int $taskId
     * @param array $tagIds - Array de IDs de tags
     * @return void
     */
    public function attachTags(int $taskId, array $tagIds): void;

    /**
     * Detach tags from a task.
     *
     * @param int $taskId
     * @param array $tagIds - Array de IDs de tags
     * @return void
     */
    public function detachTags(int $taskId, array $tagIds): void;
}
```

### 5.3.2 Ubicación en el Proyecto

```
app/Domain/TaskManagement/Contracts/Repositories/
└── TaskRepositoryInterface.php  # 113 líneas - El contrato

¿Por qué en Domain/Contracts?
- Porque el DOMAIN define QUÉ necesita
- No le importa el CÓMO (eso es Infrastructure)
```

### 5.3.3 Características Clave de la Interfaz

#### 1. **Usa Value Objects en los métodos**

```php
// ✅ CORRECTO - Type-safe
public function findByStatus(Status $status): Collection;
public function findByPriority(Priority $priority): Collection;

// ❌ INCORRECTO - Acepta cualquier string
public function findByStatus(string $status): Collection;
public function findByPriority(string $priority): Collection;
```

#### 2. **Métodos de conveniencia**

```php
// Métodos específicos que encapsulan lógica de negocio

// En vez de obligar al cliente a escribir:
$tasks = $repository->findByPriority(Priority::HIGH)
    ->merge($repository->findByPriority(Priority::CRITICAL));

// El repository ofrece:
$tasks = $repository->getHighPriority();
```

#### 3. **Separación Query/Command (CQRS light)**

```php
// QUERIES (lectura) - Retornan datos
public function findById(int $id): ?Task;
public function all(): Collection;
public function search(string $query): Collection;

// COMMANDS (escritura) - Modifican el estado
public function create(array $data): Task;
public function update(int $id, array $data): bool;
public function delete(int $id): bool;
```

## 5.4 EloquentTaskRepository - La Implementación

### 5.4.1 Código Completo Comentado

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use Illuminate\Database\Eloquent\Collection;

/**
 * EloquentTaskRepository
 *
 * Implementación CONCRETA del TaskRepositoryInterface usando Eloquent ORM.
 *
 * Esta clase vive en INFRASTRUCTURE LAYER y define CÓMO se implementan
 * las operaciones usando Eloquent.
 *
 * Si mañana quieres usar Doctrine ORM, creas DoctrineTaskRepository
 * que también implemente TaskRepositoryInterface, y solo cambias el
 * binding en el ServiceProvider. ¡El resto del código no cambia!
 */
class EloquentTaskRepository implements TaskRepositoryInterface
{
    // ═══════════════════════════════════════════════════════════
    // 📖 IMPLEMENTACIÓN DE QUERIES
    // ═══════════════════════════════════════════════════════════

    public function findById(int $id): ?Task
    {
        // Carga eager loading de relaciones para evitar N+1
        return Task::with([
            'project',      // Proyecto de la tarea
            'assignedTo',   // Usuario asignado
            'createdBy',    // Usuario creador
            'tags',         // Tags asociadas
            'comments'      // Comentarios
        ])->find($id);
    }

    public function all(): Collection
    {
        return Task::with(['project', 'assignedTo', 'tags'])
            ->latest()  // Orden descendente por created_at
            ->get();
    }

    public function findByProjectId(int $projectId): Collection
    {
        return Task::where('project_id', $projectId)
            ->with(['assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function findByAssignedTo(int $userId): Collection
    {
        return Task::where('assigned_to', $userId)
            ->with(['project', 'tags'])
            ->latest()
            ->get();
    }

    public function findByCreator(int $userId): Collection
    {
        return Task::where('created_by', $userId)
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function findByStatus(Status $status): Collection
    {
        // Eloquent convierte automáticamente Status enum → string
        return Task::where('status', $status)
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function findByPriority(Priority $priority): Collection
    {
        // Usa un Query Scope definido en el modelo Task
        return Task::byPriority($priority)
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function getHighPriority(): Collection
    {
        // Usa un Query Scope: Task::scopeHighPriority()
        return Task::highPriority()
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function getOverdue(): Collection
    {
        // Usa un Query Scope: Task::scopeOverdue()
        return Task::overdue()
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function getPending(): Collection
    {
        // Usa un Query Scope: Task::scopePending()
        return Task::pending()
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function getInProgress(): Collection
    {
        // Usa un Query Scope: Task::scopeInProgress()
        return Task::inProgress()
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function getCompleted(): Collection
    {
        // Usa un Query Scope: Task::scopeCompleted()
        return Task::completed()
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    public function search(string $query): Collection
    {
        return Task::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with(['project', 'assignedTo', 'tags'])
            ->latest()
            ->get();
    }

    // ═══════════════════════════════════════════════════════════
    // ✏️ IMPLEMENTACIÓN DE COMMANDS
    // ═══════════════════════════════════════════════════════════

    public function create(array $data): Task
    {
        // Eloquent mass assignment
        return Task::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $task = Task::find($id);

        if (!$task) {
            return false;  // Tarea no existe
        }

        return $task->update($data);
    }

    public function delete(int $id): bool
    {
        $task = Task::find($id);

        if (!$task) {
            return false;  // Tarea no existe
        }

        return $task->delete();
    }

    public function updateStatus(int $id, Status $status): bool
    {
        // Reutiliza el método update()
        return $this->update($id, ['status' => $status]);
    }

    public function assign(int $taskId, int $userId): bool
    {
        // Reutiliza el método update()
        return $this->update($taskId, ['assigned_to' => $userId]);
    }

    // ═══════════════════════════════════════════════════════════
    // 🏷️ IMPLEMENTACIÓN DE RELACIONES
    // ═══════════════════════════════════════════════════════════

    public function attachTags(int $taskId, array $tagIds): void
    {
        $task = Task::find($taskId);

        if ($task) {
            // Relación many-to-many
            $task->tags()->attach($tagIds);
        }
    }

    public function detachTags(int $taskId, array $tagIds): void
    {
        $task = Task::find($taskId);

        if ($task) {
            // Relación many-to-many
            $task->tags()->detach($tagIds);
        }
    }
}
```

### 5.4.2 Ubicación en el Proyecto

```
app/Infrastructure/Persistence/Eloquent/Repositories/
└── EloquentTaskRepository.php  # 170 líneas - La implementación

¿Por qué en Infrastructure/Persistence/Eloquent?
- Porque es un DETALLE de implementación
- Usa Eloquent (tecnología específica)
- Podría haber DoctrineTaskRepository en el mismo nivel
```

### 5.4.3 Características Clave de la Implementación

#### 1. **Eager Loading para evitar N+1**

```php
// ✅ CORRECTO - Carga relaciones de una vez
public function findById(int $id): ?Task
{
    return Task::with(['project', 'assignedTo', 'tags'])->find($id);
}

// SQL generado:
// SELECT * FROM tasks WHERE id = 1
// SELECT * FROM projects WHERE id IN (...)
// SELECT * FROM users WHERE id IN (...)
// SELECT * FROM tags WHERE id IN (...)
// ↑ 4 queries eficientes

// ❌ INCORRECTO - N+1 problem
public function findById(int $id): ?Task
{
    return Task::find($id);
}

// Si luego haces: $task->project->name
// SQL generado:
// SELECT * FROM tasks WHERE id = 1
// SELECT * FROM projects WHERE id = 5  ← Query adicional
// SELECT * FROM users WHERE id = 10    ← Query adicional
// ↑ 1 + N queries (ineficiente)
```

#### 2. **Query Scopes para reutilización**

```php
// En vez de duplicar queries, usa scopes del modelo:

// ✅ CORRECTO
public function getHighPriority(): Collection
{
    return Task::highPriority()->get();
}

// El scope está definido en Task model:
// public function scopeHighPriority($query)
// {
//     return $query->whereIn('priority', [
//         Priority::HIGH,
//         Priority::CRITICAL
//     ]);
// }

// ❌ INCORRECTO - Query duplicada
public function getHighPriority(): Collection
{
    return Task::whereIn('priority', ['high', 'critical'])->get();
}
```

#### 3. **Reutilización de métodos**

```php
// Métodos simples reutilizan métodos más generales

public function updateStatus(int $id, Status $status): bool
{
    return $this->update($id, ['status' => $status]);
}

public function assign(int $taskId, int $userId): bool
{
    return $this->update($taskId, ['assigned_to' => $userId]);
}

// Ambos reutilizan update() - DRY (Don't Repeat Yourself)
```

## 5.5 RepositoryServiceProvider - El Pegamento

### 5.5.1 Código Completo

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaskRepository;
use Illuminate\Support\ServiceProvider;

/**
 * RepositoryServiceProvider
 *
 * Registra los BINDINGS entre interfaces y implementaciones concretas.
 *
 * Esto le dice a Laravel:
 * "Cuando alguien pida TaskRepositoryInterface, dale EloquentTaskRepository"
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * Este array es una forma conveniente de registrar bindings.
     * Laravel los registra automáticamente.
     *
     * @var array<string, string>
     */
    public $bindings = [
        // Interface => Implementación concreta
        TaskRepositoryInterface::class => EloquentTaskRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // Aquí podrías registrar bindings manualmente:
        // $this->app->bind(TaskRepositoryInterface::class, EloquentTaskRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Código que necesita ejecutarse después de que
        // todos los servicios estén registrados
    }
}
```

### 5.5.2 ¿Cómo Funciona el Binding?

```
┌─────────────────────────────────────────────────────────────┐
│              CÓMO FUNCIONA LA INYECCIÓN                     │
└─────────────────────────────────────────────────────────────┘

1. CreateTask Tool se instancia:

   class CreateTask
   {
       public function __construct(
           TaskRepositoryInterface $repository  // ← Pide la interfaz
       ) {}
   }

2. Laravel consulta el Service Container:

   "¿Qué implementación debo inyectar para TaskRepositoryInterface?"

3. El RepositoryServiceProvider responde:

   $bindings = [
       TaskRepositoryInterface::class => EloquentTaskRepository::class
   ];

4. Laravel instancia EloquentTaskRepository:

   $repository = new EloquentTaskRepository();

5. Laravel inyecta la instancia en CreateTask:

   $tool = new CreateTask($repository);

6. CreateTask funciona sin saber que está usando Eloquent:

   $task = $this->repository->create([...]);
   // ↑ Llama a EloquentTaskRepository::create()
```

### 5.5.3 Cambiar de Implementación

Si mañana quieres cambiar de Eloquent a Doctrine ORM:

```php
// Paso 1: Crea la nueva implementación
class DoctrineTaskRepository implements TaskRepositoryInterface
{
    public function findById(int $id): ?Task
    {
        // Usa Doctrine en vez de Eloquent
        return $this->entityManager->find(Task::class, $id);
    }

    // ...implementa todos los métodos
}

// Paso 2: Cambia el binding
public $bindings = [
    TaskRepositoryInterface::class => DoctrineTaskRepository::class,
    //                                ↑ Solo cambias esto
];

// Paso 3: ¡Listo!
// TODO el código sigue funcionando sin cambios
// - MCP Tools: Sin cambios
// - Domain Services: Sin cambios
// - Tests: Sin cambios (usando mocks)
```

## 5.6 Uso del Repository en MCP Tools

### 5.6.1 CreateTask Tool

```php
<?php

namespace App\MCP\Presentation\TaskTools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;

class CreateTask
{
    /**
     * Constructor Injection
     *
     * Laravel inyecta automáticamente la implementación
     * configurada en RepositoryServiceProvider
     */
    public function __construct(
        private TaskRepositoryInterface $repository
    ) {}

    public function handle(array $params): array
    {
        // Usa el repository sin saber que es Eloquent
        $task = $this->repository->create([
            'title' => $params['title'],
            'description' => $params['description'] ?? null,
            'status' => Status::PENDING,
            'priority' => Priority::from($params['priority'] ?? 'medium'),
            'project_id' => $params['project_id'],
            'assigned_to' => $params['assigned_to'] ?? null,
            'created_by' => auth()->id(),
            'due_date' => $params['due_date'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => 'Task created successfully',
            'task' => $task->toArray(),
        ];
    }
}
```

### 5.6.2 GetTaskById Tool

```php
<?php

namespace App\MCP\Presentation\TaskTools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;

class GetTaskById
{
    public function __construct(
        private TaskRepositoryInterface $repository
    ) {}

    public function handle(array $params): array
    {
        $task = $this->repository->findById($params['id']);

        if (!$task) {
            return [
                'success' => false,
                'error' => 'Task not found',
            ];
        }

        return [
            'success' => true,
            'task' => $task->toArray(),
        ];
    }
}
```

### 5.6.3 ListHighPriorityTasks Tool

```php
<?php

namespace App\MCP\Presentation\TaskTools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;

class ListHighPriorityTasks
{
    public function __construct(
        private TaskRepositoryInterface $repository
    ) {}

    public function handle(array $params): array
    {
        // Usa el método de conveniencia del repository
        $tasks = $this->repository->getHighPriority();

        return [
            'success' => true,
            'count' => $tasks->count(),
            'tasks' => $tasks->map(fn($task) => $task->toArray())->toArray(),
        ];
    }
}
```

## 5.7 Testing con Mocks

### 5.7.1 ¿Por qué usar Mocks?

```
SIN Repository Pattern:
┌──────────────┐
│  Test        │
└──────┬───────┘
       │ Necesita DB real
       ▼
┌──────────────┐
│  Database    │ ← Lento, frágil, necesita setup
└──────────────┘

CON Repository Pattern:
┌──────────────┐
│  Test        │
└──────┬───────┘
       │ Usa mock
       ▼
┌──────────────┐
│  Mock        │ ← Rápido, controlable, sin setup
│  Repository  │
└──────────────┘
```

### 5.7.2 Test Unitario con Mock

```php
<?php

// tests/Unit/MCP/TaskTools/CreateTaskTest.php

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use App\MCP\Presentation\TaskTools\CreateTask;

test('it creates a task successfully', function () {
    // 1. Crear un MOCK del repository
    $mockRepository = Mockery::mock(TaskRepositoryInterface::class);

    // 2. Definir el comportamiento esperado
    $mockRepository->shouldReceive('create')
        ->once()  // Debe llamarse exactamente una vez
        ->with([  // Con estos parámetros
            'title' => 'Test Task',
            'description' => null,
            'status' => Status::PENDING,
            'priority' => Priority::MEDIUM,
            'project_id' => 1,
            'assigned_to' => null,
            'created_by' => 1,
            'due_date' => null,
        ])
        ->andReturn(new Task([  // Y retornar esto
            'id' => 1,
            'title' => 'Test Task',
            'status' => Status::PENDING,
            'priority' => Priority::MEDIUM,
        ]));

    // 3. Inyectar el mock en el Tool
    $tool = new CreateTask($mockRepository);

    // 4. Ejecutar el tool
    $result = $tool->handle([
        'title' => 'Test Task',
        'project_id' => 1,
    ]);

    // 5. Verificar el resultado
    expect($result['success'])->toBeTrue();
    expect($result['task']['title'])->toBe('Test Task');
});
```

### 5.7.3 Test de Integración (con DB real)

```php
<?php

// tests/Feature/Repositories/TaskRepositoryTest.php

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Domain\TaskManagement\ValueObjects\Priority;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can create a task in database', function () {
    // 1. Obtener la implementación REAL del repository
    $repository = app(TaskRepositoryInterface::class);

    // 2. Crear una tarea
    $task = $repository->create([
        'title' => 'Integration Test Task',
        'status' => Status::PENDING,
        'priority' => Priority::HIGH,
        'project_id' => 1,
        'created_by' => 1,
    ]);

    // 3. Verificar que se creó en la base de datos
    expect($task->id)->not->toBeNull();
    expect($task->title)->toBe('Integration Test Task');

    // 4. Verificar que se puede recuperar
    $retrieved = $repository->findById($task->id);
    expect($retrieved->id)->toBe($task->id);
});

test('it finds tasks by status', function () {
    $repository = app(TaskRepositoryInterface::class);

    // Crear tareas con diferentes estados
    $repository->create(['status' => Status::PENDING, 'title' => 'Task 1']);
    $repository->create(['status' => Status::IN_PROGRESS, 'title' => 'Task 2']);
    $repository->create(['status' => Status::PENDING, 'title' => 'Task 3']);

    // Buscar solo las pendientes
    $pendingTasks = $repository->findByStatus(Status::PENDING);

    expect($pendingTasks)->toHaveCount(2);
});
```

## 5.8 Ventajas del Patrón Repository

### 1. **Desacoplamiento**

```php
// ✅ CON Repository: Código desacoplado
class CreateTask
{
    public function __construct(
        private TaskRepositoryInterface $repository  // ← Interfaz
    ) {}

    // No sabe si es Eloquent, Doctrine, MongoDB, API REST, etc.
}

// ❌ SIN Repository: Código acoplado
class CreateTask
{
    public function handle()
    {
        Task::create([...]);  // ← Acoplado a Eloquent
    }
}
```

### 2. **Testabilidad**

```php
// ✅ CON Repository: Usa mocks (rápido)
$mockRepo = Mockery::mock(TaskRepositoryInterface::class);
$tool = new CreateTask($mockRepo);

// ❌ SIN Repository: Necesita DB real (lento)
$tool = new CreateTask();
$tool->handle();  // ← Requiere DB, migraciones, seeders...
```

### 3. **Reutilización de Queries**

```php
// ✅ CON Repository: Query centralizada
public function getHighPriority(): Collection
{
    return Task::whereIn('priority', ['high', 'critical'])->get();
}

// Se usa en:
// - TaskToolsServer
// - Dashboard Livewire
// - ReportsServer
// - API REST
// ↑ Una sola implementación

// ❌ SIN Repository: Query duplicada
// Cada lugar implementa su propia versión de la query
// = Código duplicado, inconsistencias, bugs
```

### 4. **Cambio de Tecnología**

```php
// Hoy: Eloquent + PostgreSQL
$bindings = [
    TaskRepositoryInterface::class => EloquentTaskRepository::class
];

// Mañana: MongoDB
$bindings = [
    TaskRepositoryInterface::class => MongoTaskRepository::class
];

// Pasado mañana: API Externa
$bindings = [
    TaskRepositoryInterface::class => ApiTaskRepository::class
];

// ✅ El resto del código NO CAMBIA
```

### 5. **Encapsulación de Lógica Compleja**

```php
// En vez de esto en cada Tool:
$tasks = Task::where('due_date', '<', now())
    ->whereNotIn('status', ['completed', 'blocked'])
    ->with(['project', 'assignedTo'])
    ->get();

// Encapsulas en el Repository:
$tasks = $repository->getOverdue();

// Más legible, reutilizable, y mantenible
```

## 5.9 Cuándo NO usar Repository

### ❌ NO uses Repository si:

1. **Tu aplicación es muy simple**
   - 5 tablas o menos
   - No planeas cambiar de ORM nunca
   - No necesitas tests unitarios

2. **Solo haces CRUD básico sin lógica**
   - No tienes queries complejas
   - No hay reglas de negocio

3. **Usas Eloquent en toda su extensión**
   - Necesitas todas las features de Eloquent (observers, events, etc.)
   - El patrón Repository puede sentirse como "overhead"

### ✅ USA Repository si:

1. **Tu aplicación es mediana o grande**
   - Múltiples bounded contexts
   - Lógica de negocio compleja

2. **Necesitas testabilidad**
   - Tests unitarios rápidos
   - CI/CD con tests frecuentes

3. **Posible cambio de tecnología**
   - Migración futura de Eloquent a Doctrine
   - Migración de PostgreSQL a MongoDB
   - Integración con APIs externas

4. **Queries complejas reutilizables**
   - Filtros complicados
   - Joins múltiples
   - Lógica de negocio en queries

## 5.10 Ejercicios Prácticos

### Ejercicio 1: Crear CommentRepository

Crea la interfaz y la implementación para el repositorio de comentarios:

```php
// 1. Interfaz
interface CommentRepositoryInterface
{
    public function findById(int $id): ?Comment;
    public function findByTaskId(int $taskId): Collection;
    public function create(array $data): Comment;
    public function delete(int $id): bool;
    // TODO: Agrega más métodos que consideres necesarios
}

// 2. Implementación
class EloquentCommentRepository implements CommentRepositoryInterface
{
    // TODO: Implementa todos los métodos
}

// 3. Binding en RepositoryServiceProvider
public $bindings = [
    CommentRepositoryInterface::class => EloquentCommentRepository::class,
];
```

### Ejercicio 2: Agregar Método de Búsqueda Avanzada

Agrega un método al TaskRepository que permita búsqueda avanzada:

```php
interface TaskRepositoryInterface
{
    /**
     * Advanced search with multiple filters
     *
     * @param array $filters - [
     *     'status' => Status,
     *     'priority' => Priority,
     *     'assigned_to' => int,
     *     'project_id' => int,
     *     'overdue' => bool,
     *     'query' => string
     * ]
     * @return Collection
     */
    public function advancedSearch(array $filters): Collection;
}

// TODO: Implementa en EloquentTaskRepository
```

### Ejercicio 3: Test con Mock

Escribe un test unitario para el UpdateTask Tool usando un mock:

```php
test('it updates a task successfully', function () {
    // 1. Crear mock del repository
    $mockRepository = Mockery::mock(TaskRepositoryInterface::class);

    // 2. Definir comportamiento
    // TODO: Define shouldReceive('update')...

    // 3. Crear el tool con el mock
    // TODO: $tool = new UpdateTask($mockRepository);

    // 4. Ejecutar y verificar
    // TODO: $result = $tool->handle([...]);
    // TODO: expect($result['success'])->toBeTrue();
});
```

## 5.11 Resumen del Capítulo 5

🎯 **Conceptos Clave Aprendidos:**

1. **Patrón Repository** abstrae el acceso a datos
   - Desacopla lógica de negocio de la persistencia
   - Interfaz en Domain, implementación en Infrastructure
   - Laravel inyecta la implementación via Service Container

2. **TaskRepositoryInterface** define el contrato
   - 18 métodos de consulta (queries)
   - 5 métodos de comando (commands)
   - 2 métodos de relaciones
   - Usa Value Objects (Status, Priority)

3. **EloquentTaskRepository** implementa el contrato
   - Usa Eloquent ORM
   - Eager loading para evitar N+1
   - Query Scopes para reutilización
   - 170 líneas de código

4. **RepositoryServiceProvider** registra bindings
   - Conecta interfaces con implementaciones
   - Permite cambiar implementaciones fácilmente
   - Service Container resuelve dependencias

5. **Ventajas sobre acceso directo a Eloquent**
   - Desacoplamiento (puedes cambiar ORM)
   - Testabilidad (mocks rápidos)
   - Reutilización (queries centralizadas)
   - Encapsulación (lógica compleja oculta)

6. **Testing**
   - Unit tests con mocks (rápidos, sin DB)
   - Integration tests con DB real (lentos, completos)
   - Mockery para crear mocks de interfaces

🔜 **Próximo Capítulo:**
En el Capítulo 6 exploraremos **Domain Services**, viendo cómo encapsular lógica de negocio compleja que no pertenece a una entidad o value object específico.

---

**Estado del Tutorial:** Capítulos 1-5 de 15 completados ✓


# Capítulo 6: Domain Services

## 6.1 ¿Qué es un Domain Service?

Un **Domain Service** es una clase que encapsula **lógica de negocio** que:

1. **No pertenece a una entidad específica** - La lógica involucra múltiples entidades o agregados
2. **No pertenece a un Value Object** - La lógica es más compleja que un simple cálculo
3. **No pertenece a un Repository** - La lógica no es solo acceso a datos
4. **Es parte del Domain Layer** - Es lógica de negocio pura, no infraestructura

### 🔍 Analogía del Mundo Real

Imagina una **tienda de computadoras**:

```
ENTIDADES (Entities):
- Computadora → tiene marca, modelo, precio
- Cliente → tiene nombre, dirección, historial

VALUE OBJECTS:
- Precio → tiene monto y moneda
- Dirección → tiene calle, ciudad, código postal

DOMAIN SERVICES:
- CalculadorDePrecioFinal
  ↳ Toma: Computadora + Cliente + Descuentos + Impuestos
  ↳ Retorna: Precio final
  ↳ Lógica: Aplica descuentos por cliente frecuente, calcula impuestos
           según la región, aplica promociones vigentes...

- GestorDeGarantias
  ↳ Toma: Computadora + Cliente + Compra
  ↳ Retorna: Garantía activa o no
  ↳ Lógica: Valida fecha de compra, estado del producto, términos...
```

**¿Por qué no puede ser un método de Computadora?**
- Porque involucra al Cliente (clientes frecuentes tienen descuento)
- Porque involucra impuestos externos (región del cliente)
- Porque involucra promociones (entidad separada)

**¿Por qué no puede ser un método de Cliente?**
- Porque involucra la Computadora (precio base)
- Porque involucra promociones (no es responsabilidad del cliente)

**Solución:** Un Domain Service coordina estas entidades con la lógica de negocio.

### 📊 Cuándo Usar un Domain Service

```
┌─────────────────────────────────────────────────────────────┐
│           ¿DÓNDE PONER LA LÓGICA DE NEGOCIO?               │
└─────────────────────────────────────────────────────────────┘

❓ ¿La lógica solo usa datos de UNA entidad?
   ✅ → Método en la ENTIDAD

❓ ¿La lógica es un cálculo simple e inmutable?
   ✅ → Método en un VALUE OBJECT

❓ ¿La lógica solo lee/guarda datos sin transformación?
   ✅ → Método en el REPOSITORY

❓ ¿La lógica involucra MÚLTIPLES entidades/agregados?
❓ ¿La lógica es COMPLEJA y tiene reglas de negocio?
❓ ¿La lógica NO es responsabilidad de una sola entidad?
   ✅ → DOMAIN SERVICE

❓ ¿La lógica usa APIs externas, envía emails, etc.?
   ❌ → APPLICATION SERVICE (no Domain)
```

## 6.2 Domain Services en Nuestro Proyecto

En **TaskMaster AI** tenemos 8 Domain Services:

```
app/Domain/
├── TaskManagement/Services/
│   ├── TaskStatusManager.php           # Gestión de transiciones
│   └── TaskPriorityCalculator.php      # Cálculo de prioridades
├── AIIntegration/Services/
│   ├── OpenAIService.php                # Integración con OpenAI
│   ├── TaskDescriptionGenerator.php    # Generación de descripciones
│   ├── PromptBuilder.php                # Construcción de prompts
│   └── ProductivityAnalyzer.php         # Análisis de productividad
└── Analytics/Services/
    ├── MetricsCollector.php             # Recolección de métricas
    └── ReportGenerator.php              # Generación de reportes
```

## 6.3 TaskStatusManager - Gestión de Transiciones

### 6.3.1 ¿Por qué es un Domain Service?

```php
// ❌ OPCIÓN 1: Método en Task entity
class Task
{
    public function transitionTo(Status $newStatus): bool
    {
        // Problema: Task necesita acceso al Repository para guardarse
        // Problema: Mezcla lógica de negocio con persistencia
    }
}

// ❌ OPCIÓN 2: Método en Status value object
enum Status
{
    public function transitionTask(Task $task, Status $newStatus): bool
    {
        // Problema: Un Value Object no debería modificar entidades
        // Problema: Viola el principio de responsabilidad única
    }
}

// ✅ SOLUCIÓN: Domain Service
class TaskStatusManager
{
    // Coordina: Task + Status + Repository
    // Encapsula: Lógica de transición + validación
    // Responsabilidad: Gestionar el ciclo de vida de tareas
}
```

### 6.3.2 Código Completo Comentado

```php
<?php

declare(strict_types=1);

namespace App\Domain\TaskManagement\Services;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use InvalidArgumentException;

/**
 * TaskStatusManager
 *
 * DOMAIN SERVICE que encapsula la lógica de negocio para
 * gestionar transiciones de estado en tareas.
 *
 * ¿Por qué es un Domain Service?
 * - Coordina Task + Status + Repository
 * - Aplica reglas de negocio (validación de transiciones)
 * - No es responsabilidad de una sola entidad
 */
class TaskStatusManager
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {}

    // ═══════════════════════════════════════════════════════════
    // 🔄 TRANSICIÓN PRINCIPAL
    // ═══════════════════════════════════════════════════════════

    /**
     * Transition a task to a new status.
     *
     * LÓGICA DE NEGOCIO:
     * 1. Obtener el status actual de la tarea
     * 2. Validar que la transición sea permitida
     * 3. Si es válida, actualizar en el repository
     * 4. Si no es válida, lanzar excepción
     *
     * @throws InvalidArgumentException If the transition is not allowed
     */
    public function transitionTo(Task $task, Status $newStatus): bool
    {
        $currentStatus = $task->status;

        // Validación usando Value Object (Status::canTransitionTo)
        if (!$currentStatus->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "Cannot transition from {$currentStatus->value} to {$newStatus->value}"
            );
        }

        // Persistencia usando Repository
        return $this->taskRepository->updateStatus($task->id, $newStatus);
    }

    // ═══════════════════════════════════════════════════════════
    // 🎯 MÉTODOS DE CONVENIENCIA
    // ═══════════════════════════════════════════════════════════

    /**
     * Mark a task as in progress.
     *
     * Método de conveniencia que encapsula la intención de negocio.
     */
    public function markAsInProgress(Task $task): bool
    {
        return $this->transitionTo($task, Status::IN_PROGRESS);
    }

    /**
     * Mark a task as completed.
     */
    public function markAsCompleted(Task $task): bool
    {
        return $this->transitionTo($task, Status::COMPLETED);
    }

    /**
     * Mark a task as blocked.
     */
    public function markAsBlocked(Task $task): bool
    {
        return $this->transitionTo($task, Status::BLOCKED);
    }

    /**
     * Mark a task as in review.
     */
    public function markAsReview(Task $task): bool
    {
        return $this->transitionTo($task, Status::REVIEW);
    }

    /**
     * Reopen a task (move back to pending).
     *
     * Lógica de negocio: "Reabrir" significa volver a PENDING
     */
    public function reopen(Task $task): bool
    {
        return $this->transitionTo($task, Status::PENDING);
    }

    // ═══════════════════════════════════════════════════════════
    // 📋 CONSULTAS DE NEGOCIO
    // ═══════════════════════════════════════════════════════════

    /**
     * Get available transitions for a task's current status.
     *
     * @return array<Status>
     */
    public function getAvailableTransitions(Task $task): array
    {
        return $task->status->availableTransitions();
    }

    /**
     * Check if a transition is valid for a task.
     */
    public function canTransition(Task $task, Status $newStatus): bool
    {
        return $task->status->canTransitionTo($newStatus);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabel(Status $status): string
    {
        return $status->label();
    }

    /**
     * Get the status color for UI.
     */
    public function getStatusColor(Status $status): string
    {
        return $status->color();
    }
}
```

### 6.3.3 Uso en MCP Tools

```php
// app/MCP/Presentation/TaskTools/UpdateTaskStatus.php

use App\Domain\TaskManagement\Services\TaskStatusManager;
use App\Domain\TaskManagement\ValueObjects\Status;

class UpdateTaskStatus
{
    public function __construct(
        private TaskRepositoryInterface $repository,
        private TaskStatusManager $statusManager  // ← Inyección del Domain Service
    ) {}

    public function handle(array $params): array
    {
        $task = $this->repository->findById($params['id']);

        if (!$task) {
            return ['success' => false, 'error' => 'Task not found'];
        }

        $newStatus = Status::from($params['status']);

        try {
            // Usa el Domain Service para aplicar lógica de negocio
            $this->statusManager->transitionTo($task, $newStatus);

            return [
                'success' => true,
                'message' => "Task status updated to {$newStatus->label()}",
                'task' => $task->fresh()->toArray(),
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'available_transitions' => $this->statusManager
                    ->getAvailableTransitions($task)
                    ->map(fn($s) => $s->value),
            ];
        }
    }
}
```

## 6.4 TaskDescriptionGenerator - IA para Tareas

### 6.4.1 ¿Por qué es un Domain Service?

```php
// Este Domain Service coordina:
// 1. Task (entidad)
// 2. OpenAIService (otro Domain Service)
// 3. PromptBuilder (otro Domain Service)
//
// Lógica de negocio: "Generar descripciones técnicas de calidad"
```

### 6.4.2 Código Completo Comentado

```php
<?php

declare(strict_types=1);

namespace App\Domain\AIIntegration\Services;

use App\Infrastructure\Persistence\Eloquent\Models\Task;

/**
 * TaskDescriptionGenerator
 *
 * DOMAIN SERVICE que encapsula la lógica de negocio para
 * generar descripciones de tareas usando IA.
 *
 * ¿Por qué es un Domain Service?
 * - Coordina Task + OpenAI + PromptBuilder
 * - Aplica reglas de negocio (fallbacks, validaciones)
 * - Encapsula conocimiento del dominio (qué hace una buena descripción)
 */
class TaskDescriptionGenerator
{
    public function __construct(
        private readonly OpenAIService $openAIService,
        private readonly PromptBuilder $promptBuilder
    ) {}

    // ═══════════════════════════════════════════════════════════
    // 📝 GENERACIÓN DE DESCRIPCIONES
    // ═══════════════════════════════════════════════════════════

    /**
     * Generate a detailed technical description for a task.
     *
     * LÓGICA DE NEGOCIO:
     * 1. Verificar si OpenAI está disponible
     * 2. Si no está, usar descripción fallback
     * 3. Si está, construir prompt y generar con IA
     */
    public function generate(Task $task): string
    {
        // Regla de negocio: Siempre tener fallback
        if (!$this->openAIService->isAvailable()) {
            return $this->generateFallbackDescription($task);
        }

        // Usa PromptBuilder (otro Domain Service)
        $prompt = $this->promptBuilder->buildTaskDescriptionPrompt($task);

        // Usa OpenAIService con parámetros de negocio
        return $this->openAIService->complete($prompt, [
            'temperature' => 0.7,  // Creatividad moderada
            'max_tokens' => 1000,   // Descripción detallada
        ]);
    }

    /**
     * Generate acceptance criteria for a task.
     *
     * LÓGICA DE NEGOCIO:
     * - Criterios de aceptación deben ser específicos y medibles
     * - Temperature más baja (0.6) para mayor precisión
     */
    public function generateAcceptanceCriteria(Task $task): string
    {
        if (!$this->openAIService->isAvailable()) {
            return $this->generateFallbackCriteria($task);
        }

        $prompt = $this->promptBuilder->buildAcceptanceCriteriaPrompt($task);

        return $this->openAIService->complete($prompt, [
            'temperature' => 0.6,  // Menos creatividad, más precisión
            'max_tokens' => 800,
        ]);
    }

    /**
     * Suggest subtasks for a complex task.
     *
     * LÓGICA DE NEGOCIO:
     * - Tareas complejas se dividen en 3-7 subtareas
     * - Retorna array estructurado (JSON)
     */
    public function suggestSubtasks(Task $task): array
    {
        if (!$this->openAIService->isAvailable()) {
            return [];  // Sin fallback para esta feature
        }

        $prompt = $this->promptBuilder->buildSubtaskPrompt($task);

        $response = $this->openAIService->generateJson(
            "Return a JSON array with key 'subtasks' containing suggested subtasks. {$prompt}",
            ['temperature' => 0.7]
        );

        return $response['subtasks'] ?? [];
    }

    /**
     * Improve task title to be more descriptive.
     *
     * LÓGICA DE NEGOCIO:
     * - Títulos claros, accionables, best practices
     * - Temperature baja (0.5) para consistencia
     * - Respuesta corta (100 tokens máx)
     */
    public function improveTitle(Task $task): string
    {
        if (!$this->openAIService->isAvailable()) {
            return $task->title;  // Sin cambios si no hay IA
        }

        $systemMessage = 'You are a technical project manager. Improve task titles to be clear, actionable, and follow best practices.';
        $userMessage = "Improve this task title: \"{$task->title}\"\n\nContext: {$task->description}\n\nReturn only the improved title, nothing else.";

        return $this->openAIService->completeWithSystem($systemMessage, $userMessage, [
            'temperature' => 0.5,   // Consistencia
            'max_tokens' => 100,     // Solo el título
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // 🔄 FALLBACKS (Lógica de negocio de resiliencia)
    // ═══════════════════════════════════════════════════════════

    /**
     * Generate a fallback description when AI is unavailable.
     *
     * REGLA DE NEGOCIO: Siempre proveer algo útil, nunca fallar
     */
    private function generateFallbackDescription(Task $task): string
    {
        return "Task: {$task->title}\n\n"
            ."This task requires implementation and testing. "
            .'Please refer to the project requirements and ensure all acceptance criteria are met.';
    }

    /**
     * Generate fallback acceptance criteria.
     */
    private function generateFallbackCriteria(Task $task): string
    {
        return "- Task is completed as described\n"
            ."- Code is tested and reviewed\n"
            ."- Documentation is updated\n"
            .'- No regressions introduced';
    }
}
```

### 6.4.3 Uso en MCP Tools

```php
// app/MCP/Presentation/AITools/GenerateTaskDescription.php

use App\Domain\AIIntegration\Services\TaskDescriptionGenerator;

class GenerateTaskDescription
{
    public function __construct(
        private TaskRepositoryInterface $repository,
        private TaskDescriptionGenerator $generator  // ← Domain Service
    ) {}

    public function handle(array $params): array
    {
        $task = $this->repository->findById($params['task_id']);

        if (!$task) {
            return ['success' => false, 'error' => 'Task not found'];
        }

        // El Domain Service encapsula toda la lógica
        $description = $this->generator->generate($task);
        $criteria = $this->generator->generateAcceptanceCriteria($task);
        $subtasks = $this->generator->suggestSubtasks($task);

        // Actualizar la tarea con los resultados
        $this->repository->update($task->id, [
            'description' => $description,
            'acceptance_criteria' => $criteria,
        ]);

        return [
            'success' => true,
            'description' => $description,
            'acceptance_criteria' => $criteria,
            'suggested_subtasks' => $subtasks,
        ];
    }
}
```

## 6.5 OpenAIService - Integración con IA

### 6.5.1 Código Completo Comentado

```php
<?php

declare(strict_types=1);

namespace App\Domain\AIIntegration\Services;

use OpenAI\Laravel\Facades\OpenAI;

/**
 * OpenAIService
 *
 * DOMAIN SERVICE que encapsula la integración con OpenAI.
 *
 * ¿Por qué es un Domain Service?
 * - Encapsula conocimiento del dominio (parámetros, modelos)
 * - Abstrae la API externa para el Domain Layer
 * - Aplica configuraciones de negocio (temperaturas, tokens)
 */
class OpenAIService
{
    /**
     * Generate a completion using OpenAI.
     *
     * LÓGICA DE NEGOCIO:
     * - Modelo por defecto desde config
     * - Parámetros sensatos (temperatura, max_tokens)
     * - Manejo de errores con string vacío
     */
    public function complete(string $prompt, array $options = []): string
    {
        $defaultOptions = [
            'model' => config('openai.default_model', 'gpt-4-turbo-preview'),
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        $response = OpenAI::chat()->create(array_merge($defaultOptions, $options));

        return $response->choices[0]->message->content ?? '';
    }

    /**
     * Generate a completion with a system message.
     *
     * LÓGICA DE NEGOCIO:
     * - System message define el comportamiento del asistente
     * - Útil para definir roles (project manager, developer, etc.)
     */
    public function completeWithSystem(string $systemMessage, string $userMessage, array $options = []): string
    {
        $messages = [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => $userMessage],
        ];

        $defaultOptions = [
            'model' => config('openai.default_model', 'gpt-4-turbo-preview'),
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        $response = OpenAI::chat()->create(array_merge($defaultOptions, $options));

        return $response->choices[0]->message->content ?? '';
    }

    /**
     * Generate structured JSON output.
     *
     * LÓGICA DE NEGOCIO:
     * - Fuerza respuesta en formato JSON
     * - Retorna array para fácil manipulación
     * - Fallback a array vacío si falla el parsing
     */
    public function generateJson(string $prompt, array $options = []): array
    {
        $options['response_format'] = ['type' => 'json_object'];

        $response = $this->complete($prompt, $options);

        return json_decode($response, true) ?? [];
    }

    /**
     * Count tokens in a text (approximate).
     *
     * LÓGICA DE NEGOCIO:
     * - Aproximación: 1 token ≈ 4 caracteres
     * - Útil para estimar costos antes de llamar a la API
     */
    public function estimateTokens(string $text): int
    {
        // Regla de negocio: Rough approximation
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Check if API is configured and available.
     *
     * LÓGICA DE NEGOCIO:
     * - Verifica que haya API key configurada
     * - Usado por otros services para decidir usar fallbacks
     */
    public function isAvailable(): bool
    {
        return !empty(config('openai.api_key'));
    }
}
```

## 6.6 PromptBuilder - Construcción de Prompts

### 6.6.1 ¿Por qué es un Domain Service?

```php
// PromptBuilder encapsula CONOCIMIENTO DEL DOMINIO sobre:
// 1. Cómo estructurar prompts efectivos
// 2. Qué información incluir para cada tipo de tarea
// 3. Formato y estructura de las respuestas esperadas
//
// No es un simple "string concatenator"
// Es conocimiento experto sobre prompt engineering
```

### 6.6.2 Ejemplo: buildTaskDescriptionPrompt

```php
/**
 * Build a prompt for generating task descriptions.
 *
 * CONOCIMIENTO DEL DOMINIO:
 * - Una buena descripción incluye: overview, approach, challenges, outcome
 * - Necesita contexto: proyecto, título, prioridad, descripción actual
 * - Formato: markdown para mejor legibilidad
 */
public function buildTaskDescriptionPrompt(Task $task): string
{
    $project = $task->project;

    return <<<PROMPT
    You are a technical project manager. Generate a detailed technical description for the following task.

    **Project:** {$project->name}
    **Task Title:** {$task->title}
    **Priority:** {$task->priority->value}
    **Current Description:** {$task->description}

    Generate a comprehensive technical description that includes:
    1. Overview of what needs to be done
    2. Technical approach or considerations
    3. Potential challenges or dependencies
    4. Expected outcome

    Keep it concise but informative. Use markdown formatting.
    PROMPT;
}
```

## 6.7 Ventajas de los Domain Services

### 1. **Encapsulación de Lógica Compleja**

```php
// ❌ SIN Domain Service: Lógica dispersa
class UpdateTaskStatusTool
{
    public function handle(array $params)
    {
        $task = Task::find($params['id']);
        $newStatus = Status::from($params['status']);

        // Lógica duplicada en múltiples Tools
        if ($task->status === Status::PENDING && $newStatus === Status::COMPLETED) {
            throw new Exception("Cannot complete without working");
        }
        if ($task->status === Status::COMPLETED && $newStatus === Status::PENDING) {
            throw new Exception("Cannot reopen completed task");
        }
        // ...más validaciones

        $task->update(['status' => $newStatus]);
    }
}

// ✅ CON Domain Service: Lógica centralizada
class UpdateTaskStatusTool
{
    public function handle(array $params)
    {
        $task = $this->repository->findById($params['id']);
        $newStatus = Status::from($params['status']);

        // Una línea - toda la lógica encapsulada
        $this->statusManager->transitionTo($task, $newStatus);
    }
}
```

### 2. **Reutilización**

```php
// El mismo Domain Service se usa en:

// 1. MCP Tools
$this->statusManager->markAsCompleted($task);

// 2. Controladores HTTP
$this->statusManager->markAsInProgress($task);

// 3. Livewire Components
$this->statusManager->markAsBlocked($task);

// 4. Jobs en Cola
$this->statusManager->markAsReview($task);

// 5. Tests
$this->statusManager->transitionTo($task, Status::COMPLETED);

// ✅ Una implementación, cinco usos
```

### 3. **Testabilidad**

```php
// tests/Unit/Domain/Services/TaskStatusManagerTest.php

test('it validates transitions correctly', function () {
    $mockRepository = Mockery::mock(TaskRepositoryInterface::class);
    $statusManager = new TaskStatusManager($mockRepository);

    $task = new Task(['status' => Status::PENDING]);

    // ✅ Test unitario puro - sin base de datos
    expect($statusManager->canTransition($task, Status::IN_PROGRESS))
        ->toBeTrue();

    expect($statusManager->canTransition($task, Status::COMPLETED))
        ->toBeFalse();
});

test('it throws exception for invalid transition', function () {
    $mockRepository = Mockery::mock(TaskRepositoryInterface::class);
    $statusManager = new TaskStatusManager($mockRepository);

    $task = new Task(['status' => Status::PENDING]);

    // Debe lanzar excepción
    $statusManager->transitionTo($task, Status::COMPLETED);
})->throws(InvalidArgumentException::class);
```

### 4. **Separación de Responsabilidades**

```php
// Cada Domain Service tiene UNA responsabilidad clara:

TaskStatusManager          → Gestiona transiciones de estado
TaskDescriptionGenerator   → Genera descripciones con IA
PromptBuilder              → Construye prompts efectivos
OpenAIService              → Integra con OpenAI API
MetricsCollector           → Recopila métricas del sistema
ReportGenerator            → Genera reportes de análisis

// Si necesitas cambiar cómo se generan descripciones:
// → Solo modificas TaskDescriptionGenerator
// → El resto del sistema no cambia
```

### 5. **Expresividad del Código**

```php
// ❌ Sin Domain Service: ¿Qué hace esto?
$task->status = Status::IN_PROGRESS;
$task->save();

// ✅ Con Domain Service: Intención clara
$this->statusManager->markAsInProgress($task);

// ❌ Sin Domain Service: ¿Qué hace esto?
$prompt = "Generate description for: {$task->title}...";
$response = OpenAI::chat()->create([...]);
$task->description = $response->choices[0]->message->content;

// ✅ Con Domain Service: Intención clara
$description = $this->descriptionGenerator->generate($task);
```

## 6.8 Domain Service vs Application Service

### Diferencias Clave

```
┌─────────────────────────────────────────────────────────────┐
│           DOMAIN SERVICE vs APPLICATION SERVICE             │
└─────────────────────────────────────────────────────────────┘

DOMAIN SERVICE:
├─ Ubicación: app/Domain/*/Services/
├─ Lógica: Negocio pura (reglas, validaciones, cálculos)
├─ Dependencias: Otros Domain Services, Repositories, Value Objects
├─ NO hace: Emails, APIs externas, filesystem, cache
├─ Ejemplos: TaskStatusManager, PromptBuilder
└─ Objetivo: Encapsular conocimiento del dominio

APPLICATION SERVICE:
├─ Ubicación: app/Application/*/Services/ (o app/Services/)
├─ Lógica: Coordinación y orquestación
├─ Dependencias: Domain Services, Infrastructure services
├─ SÍ hace: Emails, APIs externas, filesystem, cache, DB transactions
├─ Ejemplos: SendTaskNotification, ExportTasksToPDF
└─ Objetivo: Coordinar casos de uso completos
```

### Ejemplo Comparativo

```php
// ✅ DOMAIN SERVICE: TaskStatusManager
class TaskStatusManager
{
    public function transitionTo(Task $task, Status $newStatus): bool
    {
        // LÓGICA DE NEGOCIO PURA
        if (!$task->status->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(...);
        }

        return $this->repository->updateStatus($task->id, $newStatus);
    }
}

// ✅ APPLICATION SERVICE: TaskStatusNotifier
class TaskStatusNotifier
{
    public function notifyStatusChange(Task $task, Status $oldStatus, Status $newStatus): void
    {
        // ORQUESTACIÓN DE INFRAESTRUCTURA

        // 1. Usa Domain Service para validar
        if (!$this->statusManager->canTransition($task, $newStatus)) {
            throw new InvalidTransitionException();
        }

        // 2. Aplica el cambio
        $this->statusManager->transitionTo($task, $newStatus);

        // 3. Envía notificaciones (INFRASTRUCTURE)
        Mail::to($task->assignedTo)->send(
            new TaskStatusChanged($task, $oldStatus, $newStatus)
        );

        // 4. Registra en log (INFRASTRUCTURE)
        Log::info("Task {$task->id} changed from {$oldStatus->value} to {$newStatus->value}");

        // 5. Dispara evento (INFRASTRUCTURE)
        event(new TaskStatusUpdated($task));
    }
}
```

## 6.9 Cuándo NO usar Domain Service

### ❌ NO uses Domain Service si:

1. **La lógica pertenece a una entidad**
   ```php
   // ❌ NO necesitas un Domain Service
   class UserService
   {
       public function getFullName(User $user): string
       {
           return $user->first_name . ' ' . $user->last_name;
       }
   }

   // ✅ Debe ser método de User
   class User
   {
       public function getFullName(): string
       {
           return "{$this->first_name} {$this->last_name}";
       }
   }
   ```

2. **La lógica pertenece a un Value Object**
   ```php
   // ❌ NO necesitas un Domain Service
   class MoneyService
   {
       public function add(Money $a, Money $b): Money
       {
           return new Money($a->amount + $b->amount, $a->currency);
       }
   }

   // ✅ Debe ser método de Money
   class Money
   {
       public function add(Money $other): self
       {
           return new self($this->amount + $other->amount, $this->currency);
       }
   }
   ```

3. **Es solo acceso a datos (usa Repository)**
   ```php
   // ❌ NO necesitas un Domain Service
   class TaskFinderService
   {
       public function findById(int $id): ?Task
       {
           return Task::find($id);
       }
   }

   // ✅ Usa Repository
   $task = $this->taskRepository->findById($id);
   ```

4. **Es infraestructura, no negocio (usa Application Service)**
   ```php
   // ❌ NO es Domain Service (usa APIs externas)
   class EmailService  // → Debe ser Application Service
   {
       public function send(string $to, string $subject): void
       {
           Mail::to($to)->send(...);  // ← Infraestructura
       }
   }
   ```

## 6.10 Ejercicios Prácticos

### Ejercicio 1: TaskAssignmentService

Crea un Domain Service que gestione la asignación de tareas:

```php
<?php

namespace App\Domain\TaskManagement\Services;

class TaskAssignmentService
{
    public function __construct(
        private TaskRepositoryInterface $repository
    ) {}

    /**
     * Assign a task to a user.
     *
     * LÓGICA DE NEGOCIO:
     * - Validar que la tarea no esté completada
     * - Validar que el usuario tenga capacidad (máx 10 tareas activas)
     * - Actualizar assigned_to
     * - Retornar true/false
     */
    public function assignTo(Task $task, int $userId): bool
    {
        // TODO: Implementar
    }

    /**
     * Unassign a task.
     */
    public function unassign(Task $task): bool
    {
        // TODO: Implementar
    }

    /**
     * Reassign from one user to another.
     */
    public function reassign(Task $task, int $newUserId): bool
    {
        // TODO: Implementar
    }

    /**
     * Check if user can be assigned more tasks.
     */
    public function canAssign(int $userId): bool
    {
        // TODO: Contar tareas activas del usuario
        // TODO: Retornar true si < 10
    }
}
```

### Ejercicio 2: TaskPriorityCalculator

Crea un Domain Service que calcule automáticamente la prioridad:

```php
<?php

namespace App\Domain\TaskManagement\Services;

class TaskPriorityCalculator
{
    /**
     * Calculate recommended priority based on task attributes.
     *
     * LÓGICA DE NEGOCIO:
     * - Si due_date < 2 días → CRITICAL
     * - Si due_date < 5 días → HIGH
     * - Si tiene palabras clave urgentes → HIGH
     * - Por defecto → MEDIUM
     */
    public function calculatePriority(Task $task): Priority
    {
        // TODO: Implementar lógica
    }

    /**
     * Suggest priority increase if task is overdue.
     */
    public function suggestPriorityIncrease(Task $task): ?Priority
    {
        // TODO: Implementar
    }
}
```

### Ejercicio 3: Test del TaskStatusManager

Escribe tests para el TaskStatusManager:

```php
<?php

// tests/Unit/Domain/Services/TaskStatusManagerTest.php

test('it transitions task to valid status', function () {
    // TODO: Mock repository
    // TODO: Crear TaskStatusManager
    // TODO: Crear Task con status PENDING
    // TODO: Transicionar a IN_PROGRESS
    // TODO: Verificar que se llamó updateStatus en el repository
});

test('it throws exception for invalid transition', function () {
    // TODO: Intentar transición inválida (PENDING → COMPLETED)
    // TODO: Verificar que lanza InvalidArgumentException
})->throws(InvalidArgumentException::class);

test('it marks task as completed', function () {
    // TODO: Usar método markAsCompleted
    // TODO: Verificar que funcionó correctamente
});
```

## 6.11 Resumen del Capítulo 6

🎯 **Conceptos Clave Aprendidos:**

1. **Domain Services** encapsulan lógica de negocio compleja
   - No pertenece a una entidad específica
   - Coordina múltiples entidades/agregados
   - Vive en el Domain Layer

2. **Cuándo usar Domain Service**
   - Lógica involucra múltiples entidades
   - Lógica es compleja (no es un simple cálculo)
   - No es responsabilidad de una sola entidad
   - No es solo acceso a datos

3. **TaskStatusManager** gestiona transiciones
   - Valida transiciones usando Value Object
   - Coordina Task + Status + Repository
   - Provee métodos de conveniencia
   - 112 líneas de código

4. **TaskDescriptionGenerator** usa IA
   - Coordina Task + OpenAI + PromptBuilder
   - Provee fallbacks para resiliencia
   - Encapsula conocimiento sobre buenas descripciones
   - 108 líneas de código

5. **OpenAIService** abstrae API externa
   - Encapsula configuración y parámetros
   - Provee métodos especializados (JSON, system message)
   - Estima tokens, verifica disponibilidad
   - 80 líneas de código

6. **PromptBuilder** encapsula prompt engineering
   - Conocimiento del dominio sobre prompts efectivos
   - Reutilizable en múltiples contexts
   - 140 líneas de código

7. **Ventajas sobre código disperso**
   - Encapsulación (lógica centralizada)
   - Reutilización (un servicio, muchos usos)
   - Testabilidad (tests unitarios puros)
   - Expresividad (intención clara)

8. **Domain Service vs Application Service**
   - Domain: Lógica de negocio pura
   - Application: Orquestación + infraestructura

🔜 **Próximo Capítulo:**
En el Capítulo 7 exploraremos **TaskToolsServer**, el primer MCP Server completo que expone 6 herramientas CRUD para gestión de tareas.

---

**Estado del Tutorial:** Capítulos 1-6 de 15 completados ✓


# Capítulo 7: TaskToolsServer - MCP Server CRUD Completo

## 7.1 ¿Qué es un MCP Server?

Un **MCP Server** es un servidor que expone herramientas (Tools), recursos (Resources) y prompts (Prompts) a través del protocolo MCP para que los LLMs puedan interactuar con tu aplicación Laravel.

### 🔍 Analogía del Mundo Real

Imagina que tu aplicación Laravel es una **cocina profesional**:

```
SIN MCP Server:
┌──────────────┐
│   Asistente  │  "¿Puedes hacerme un café?"
│     IA       │
└──────────────┘
       ↓
    ❌ No puede acceder a la cocina
    ❌ No sabe qué máquinas hay
    ❌ No puede usar los electrodomésticos

CON MCP Server:
┌──────────────┐
│   Asistente  │  "¿Puedes hacerme un café?"
│     IA       │
└───────┬──────┘
        │
        ▼
┌────────────────────────────────┐
│   MCP Server (Menú de Tools)   │
│                                │
│  ✅ hacer_cafe()               │
│  ✅ calentar_agua()            │
│  ✅ tostar_pan()               │
│  ✅ preparar_desayuno()        │
└───────┬────────────────────────┘
        │
        ▼
┌────────────────┐
│   Laravel      │  Ejecuta la acción
│   Application  │  Accede a la base de datos
└────────────────┘  Aplica lógica de negocio
```

**TaskToolsServer** es como el **"Menú de la Cocina"** que le dice al asistente IA:
- Qué puede hacer (Tools disponibles)
- Qué parámetros necesita (JSON Schema)
- Cómo usar cada herramienta (Descripciones)

## 7.2 Anatomía del TaskToolsServer

### 7.2.1 Código Completo Comentado

```php
<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AssignTask;
use App\Mcp\Tools\CreateTask;
use App\Mcp\Tools\DeleteTask;
use App\Mcp\Tools\GetTask;
use App\Mcp\Tools\ListTasks;
use App\Mcp\Tools\UpdateTask;
use Laravel\Mcp\Server;

/**
 * TaskToolsServer
 *
 * Servidor MCP que expone 6 herramientas CRUD para gestión de tareas.
 *
 * Este servidor permite que LLMs:
 * - Creen tareas en proyectos
 * - Listen y filtren tareas
 * - Obtengan detalles de tareas
 * - Actualicen tareas (con validación de transiciones)
 * - Eliminen tareas
 * - Asignen tareas a usuarios
 */
class TaskToolsServer extends Server
{
    // ═══════════════════════════════════════════════════════════
    // 📋 METADATOS DEL SERVIDOR
    // ═══════════════════════════════════════════════════════════

    /**
     * The MCP server's name.
     *
     * Este nombre aparece en:
     * - Lista de servidores disponibles
     * - Logs y debugging
     * - Configuración del LLM
     */
    protected string $name = 'TaskMaster AI - Task Tools';

    /**
     * The MCP server's version.
     *
     * Útil para:
     * - Versionado de API
     * - Breaking changes
     * - Compatibilidad con clientes
     */
    protected string $version = '1.0.0';

    // ═══════════════════════════════════════════════════════════
    // 📖 INSTRUCCIONES PARA EL LLM
    // ═══════════════════════════════════════════════════════════

    /**
     * The MCP server's instructions for the LLM.
     *
     * Este texto es CRÍTICO:
     * - El LLM lo lee para entender cómo usar las herramientas
     * - Debe ser claro, conciso y completo
     * - Incluye guías de uso y mejores prácticas
     */
    protected string $instructions = <<<'MARKDOWN'
        # TaskMaster AI - Task Management Tools

        This MCP server provides comprehensive task management capabilities for the TaskMaster AI platform.

        ## Available Tools

        - **create_task**: Create a new task in a project
        - **list_tasks**: List and filter tasks by project, status, assignee, or search
        - **get_task**: Get detailed information about a specific task
        - **update_task**: Update task details, status, priority, or assignments
        - **delete_task**: Delete a task
        - **assign_task**: Assign a task to a user

        ## Usage Guidelines

        1. Always specify required fields when creating tasks (project_id, title, created_by)
        2. Use appropriate priority levels: low, medium, high, critical
        3. Status transitions follow rules: pending → in_progress → review → completed
        4. Search tasks using keywords in title or description
        5. Filter tasks by project, status, or assignee for focused views

        ## Authentication

        This server requires authentication via Laravel Sanctum tokens.
        Include your token in the Authorization header.
    MARKDOWN;

    // ═══════════════════════════════════════════════════════════
    // 🛠️ HERRAMIENTAS REGISTRADAS
    // ═══════════════════════════════════════════════════════════

    /**
     * The tools registered with this MCP server.
     *
     * IMPORTANTE: El orden puede importar en algunos clientes MCP
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        CreateTask::class,    // CRUD: Create
        ListTasks::class,     // CRUD: Read (many)
        GetTask::class,       // CRUD: Read (one)
        UpdateTask::class,    // CRUD: Update
        DeleteTask::class,    // CRUD: Delete
        AssignTask::class,    // Action: Assign (lógica de negocio)
    ];

    /**
     * The resources registered with this MCP server.
     *
     * Este servidor NO tiene Resources (son read-only data)
     * Ver Analytics Servers para ejemplos de Resources
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * Este servidor NO tiene Prompts
     * Ver TaskPromptsServer para ejemplos de Prompts
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
```

### 7.2.2 ¿Qué Hace Cada Parte?

```
┌─────────────────────────────────────────────────────────────┐
│              FLUJO DE UNA SOLICITUD MCP                     │
└─────────────────────────────────────────────────────────────┘

1. LLM lee las instructions:
   "Necesito crear una tarea llamada 'Fix login bug'"

2. LLM decide usar la herramienta:
   create_task

3. LLM prepara los parámetros según el JSON Schema:
   {
     "project_id": 1,
     "title": "Fix login bug",
     "priority": "high",
     "created_by": 1
   }

4. Servidor MCP valida el JSON Schema:
   ✅ project_id es integer
   ✅ title tiene entre 3-255 caracteres
   ✅ priority es un enum válido

5. Laravel instancia CreateTask Tool:
   - Inyecta TaskRepositoryInterface
   - Llama a handle() con Request

6. CreateTask::handle() ejecuta:
   - Usa el Repository para crear la tarea
   - Aplica Value Objects (Priority, Status)
   - Retorna Response JSON

7. Respuesta llega al LLM:
   {
     "success": true,
     "task": { "id": 42, "title": "Fix login bug", ... },
     "message": "Task #42 created successfully"
   }

8. LLM interpreta y responde al usuario:
   "✓ He creado la tarea #42: 'Fix login bug' con prioridad alta"
```

## 7.3 CreateTask Tool - Análisis Completo

### 7.3.1 Código Completo Comentado

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

/**
 * CreateTask Tool
 *
 * Herramienta MCP para crear nuevas tareas en proyectos.
 *
 * RESPONSABILIDAD:
 * - Validar parámetros de entrada (vía JSON Schema)
 * - Crear tarea usando Repository
 * - Aplicar valores por defecto (status = PENDING)
 * - Retornar respuesta estructurada
 */
class CreateTask extends Tool
{
    // ═══════════════════════════════════════════════════════════
    // 📖 DESCRIPCIÓN PARA EL LLM
    // ═══════════════════════════════════════════════════════════

    /**
     * The tool's description.
     *
     * Esta descripción es leída por el LLM para decidir cuándo
     * usar esta herramienta.
     *
     * DEBE SER:
     * - Clara y concisa
     * - Explicar QUÉ hace (no CÓMO)
     * - Mencionar parámetros importantes
     */
    protected string $description = <<<'MARKDOWN'
        Create a new task in a project. Specify the project ID, title, description, priority, and other details.
        The task will be created with 'pending' status by default.
    MARKDOWN;

    // ═══════════════════════════════════════════════════════════
    // 💉 INYECCIÓN DE DEPENDENCIAS
    // ═══════════════════════════════════════════════════════════

    /**
     * Constructor con Dependency Injection
     *
     * Laravel automáticamente inyecta el Repository configurado
     * en RepositoryServiceProvider
     */
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {}

    // ═══════════════════════════════════════════════════════════
    // 🎯 MÉTODO PRINCIPAL
    // ═══════════════════════════════════════════════════════════

    /**
     * Handle the tool request.
     *
     * Este método se ejecuta cuando el LLM llama a la herramienta.
     *
     * @param Request $request - Parámetros validados por JSON Schema
     * @return Response - Respuesta JSON para el LLM
     */
    public function handle(Request $request): Response
    {
        // 1. Construir data array desde Request
        $data = [
            'project_id' => $request->input('project_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),

            // Value Objects desde strings
            'priority' => Priority::from($request->input('priority', 'medium')),
            'status' => Status::PENDING,  // ← Siempre PENDING al crear

            // Campos opcionales
            'created_by' => $request->input('created_by'),
            'assigned_to' => $request->input('assigned_to'),
            'due_date' => $request->input('due_date'),
            'estimated_hours' => $request->input('estimated_hours'),
        ];

        // 2. Crear tarea usando Repository (patrón Repository)
        $task = $this->taskRepository->create($data);

        // 3. Retornar respuesta estructurada
        return Response::json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,      // ← Enum → string
                'priority' => $task->priority->value,  // ← Enum → string
                'created_at' => $task->created_at->toIso8601String(),
            ],
            'message' => "Task #{$task->id} created successfully: {$task->title}",
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // 📋 JSON SCHEMA (VALIDACIÓN AUTOMÁTICA)
    // ═══════════════════════════════════════════════════════════

    /**
     * Get the tool's input schema.
     *
     * Este schema define:
     * - Qué parámetros acepta la herramienta
     * - Tipos de datos
     * - Validaciones (min, max, enum, etc.)
     * - Campos requeridos vs opcionales
     *
     * Laravel valida automáticamente antes de llamar a handle()
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // CAMPOS REQUERIDOS
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

            'project_id' => $schema->integer()
                ->description('The ID of the project this task belongs to')
                ->required(),  // ← REQUERIDO

            'title' => $schema->string()
                ->description('The task title')
                ->minLength(3)      // ← Validación: mínimo 3 caracteres
                ->maxLength(255)    // ← Validación: máximo 255 caracteres
                ->required(),       // ← REQUERIDO

            'created_by' => $schema->integer()
                ->description('User ID of the task creator')
                ->required(),  // ← REQUERIDO

            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            // CAMPOS OPCIONALES
            // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

            'description' => $schema->string()
                ->description('Detailed description of the task')
                ->optional(),  // ← OPCIONAL

            'priority' => $schema->enum(['low', 'medium', 'high', 'critical'])
                ->description('Task priority level')
                ->default('medium'),  // ← Valor por defecto

            'assigned_to' => $schema->integer()
                ->description('User ID of the assignee')
                ->optional(),  // ← OPCIONAL

            'due_date' => $schema->string()
                ->description('Due date in ISO 8601 format (e.g., 2025-11-20)')
                ->optional(),  // ← OPCIONAL

            'estimated_hours' => $schema->integer()
                ->description('Estimated hours to complete the task')
                ->minimum(1)       // ← Validación: al menos 1 hora
                ->optional(),      // ← OPCIONAL
        ];
    }
}
```

### 7.3.2 Flujo de Ejecución Detallado

```
┌─────────────────────────────────────────────────────────────┐
│         FLUJO COMPLETO: CreateTask Tool                     │
└─────────────────────────────────────────────────────────────┘

1. LLM DECIDE USAR CREATETASK
   ├─ Lee description: "Create a new task in a project"
   ├─ Usuario dijo: "Create task 'Fix bug' in project 1"
   └─ Decisión: ✓ Usar create_task

2. LLM PREPARA PARÁMETROS
   ├─ Lee schema para saber qué enviar
   ├─ Genera JSON:
   │   {
   │     "project_id": 1,
   │     "title": "Fix bug",
   │     "priority": "high",
   │     "created_by": 1
   │   }
   └─ Envía request MCP

3. MCP SERVER VALIDA (AUTOMÁTICO)
   ├─ project_id: ✓ es integer
   ├─ title: ✓ tiene entre 3-255 chars
   ├─ priority: ✓ es uno de [low, medium, high, critical]
   ├─ created_by: ✓ es integer
   └─ Si todo OK → continúa, si no → error 400

4. LARAVEL INSTANCIA CREATETASK
   ├─ Service Container resuelve dependencias
   ├─ Inyecta TaskRepositoryInterface
   │   └─ (que en realidad es EloquentTaskRepository)
   └─ Crea instancia de CreateTask

5. CREATETASK::HANDLE() SE EJECUTA
   ├─ Construye $data array
   ├─ Convierte strings a Value Objects:
   │   ├─ 'high' → Priority::HIGH
   │   └─ PENDING → Status::PENDING (por defecto)
   ├─ Llama a $taskRepository->create($data)
   └─ Repository guarda en DB con Eloquent

6. ELOQUENT PROCESA
   ├─ Task::create($data)
   ├─ Casting automático:
   │   ├─ Priority::HIGH → 'high' (string en DB)
   │   └─ Status::PENDING → 'pending' (string en DB)
   └─ Retorna Task model

7. CREATETASK RETORNA RESPONSE
   ├─ Construye Response::json([...])
   ├─ Convierte Value Objects a strings:
   │   ├─ $task->status->value → 'pending'
   │   └─ $task->priority->value → 'high'
   └─ Retorna JSON estructurado

8. LLM RECIBE RESPUESTA
   {
     "success": true,
     "task": {
       "id": 42,
       "title": "Fix bug",
       "status": "pending",
       "priority": "high",
       "created_at": "2025-11-13T10:30:00Z"
     },
     "message": "Task #42 created successfully: Fix bug"
   }

9. LLM RESPONDE AL USUARIO
   "✓ Creé la tarea #42: 'Fix bug' con prioridad alta en proyecto 1"
```

### 7.3.3 Ejemplo de Uso desde Claude

```
USER:
"Create a task called 'Implement user authentication'
in project 2 with high priority"

CLAUDE (internamente):
1. Analiza el mensaje
2. Identifica: necesita crear tarea
3. Consulta herramientas disponibles
4. Encuentra: create_task
5. Lee schema para saber parámetros
6. Prepara JSON:

{
  "project_id": 2,
  "title": "Implement user authentication",
  "priority": "high",
  "created_by": 1  // (obtiene del contexto)
}

7. Llama al MCP Tool
8. Recibe respuesta
9. Responde al usuario:

CLAUDE:
"✓ I've created task #15: 'Implement user authentication'
in project 2 with high priority. The task is currently pending."
```

## 7.4 ListTasks Tool - Filtros y Búsqueda

### 7.4.1 Código Completo Comentado

```php
<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Status;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

/**
 * ListTasks Tool
 *
 * Herramienta MCP para listar y filtrar tareas.
 *
 * CARACTERÍSTICAS:
 * - Filtro por proyecto
 * - Filtro por status
 * - Filtro por usuario asignado
 * - Búsqueda de texto
 * - Listar todas si no hay filtros
 */
class ListTasks extends Tool
{
    protected string $description = <<<'MARKDOWN'
        List tasks with optional filters. You can filter by project, status, assigned user, or search by text.
        Returns a paginated list of tasks with their details.
    MARKDOWN;

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * Handle the tool request.
     *
     * LÓGICA:
     * 1. Obtener filtros del request
     * 2. Aplicar filtros en orden de prioridad
     * 3. Formatear resultado
     * 4. Retornar JSON
     */
    public function handle(Request $request): Response
    {
        // 1. Extraer filtros
        $projectId = $request->input('project_id');
        $status = $request->input('status');
        $assignedTo = $request->input('assigned_to');
        $search = $request->input('search');

        // 2. Aplicar filtros (cascada de if-elseif)
        // IMPORTANTE: Solo un filtro a la vez (prioridad)
        if ($projectId) {
            $tasks = $this->taskRepository->findByProjectId($projectId);
        } elseif ($status) {
            // Convierte string → Status enum
            $tasks = $this->taskRepository->findByStatus(Status::from($status));
        } elseif ($assignedTo) {
            $tasks = $this->taskRepository->findByAssignedTo($assignedTo);
        } elseif ($search) {
            // Búsqueda en title y description
            $tasks = $this->taskRepository->search($search);
        } else {
            // Sin filtros: todas las tareas
            $tasks = $this->taskRepository->all();
        }

        // 3. Formatear tareas para el LLM
        $formattedTasks = $tasks->map(fn ($task) => [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,           // Enum → string
            'priority' => $task->priority->value,       // Enum → string
            'project_id' => $task->project_id,
            'project_name' => $task->project->name ?? null,  // Relación
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignedTo->name ?? null,  // Relación
            'due_date' => $task->due_date?->toDateString(),
            'created_at' => $task->created_at->toIso8601String(),
        ])->values();  // ← Re-indexa array (0, 1, 2...)

        // 4. Retornar respuesta
        return Response::json([
            'success' => true,
            'count' => $tasks->count(),
            'tasks' => $formattedTasks,
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * NOTA: Todos los parámetros son OPCIONALES
     * Sin parámetros = listar todas las tareas
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()
                ->description('Filter tasks by project ID')
                ->optional(),

            'status' => $schema->enum(['pending', 'in_progress', 'review', 'completed', 'blocked'])
                ->description('Filter tasks by status')
                ->optional(),

            'assigned_to' => $schema->integer()
                ->description('Filter tasks by assignee user ID')
                ->optional(),

            'search' => $schema->string()
                ->description('Search tasks by title or description')
                ->optional(),
        ];
    }
}
```

### 7.4.2 Ejemplos de Uso

```
┌─────────────────────────────────────────────────────────────┐
│               EJEMPLOS DE USO: ListTasks                    │
└─────────────────────────────────────────────────────────────┘

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EJEMPLO 1: Listar todas las tareas
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

USER: "Show me all tasks"

CLAUDE llama:
list_tasks({})  // ← Sin parámetros

Resultado:
{
  "success": true,
  "count": 42,
  "tasks": [
    { "id": 1, "title": "Task 1", ... },
    { "id": 2, "title": "Task 2", ... },
    ...
  ]
}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EJEMPLO 2: Filtrar por proyecto
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

USER: "What tasks are in project 3?"

CLAUDE llama:
list_tasks({ "project_id": 3 })

Resultado:
{
  "success": true,
  "count": 5,
  "tasks": [
    { "id": 10, "title": "...", "project_id": 3 },
    { "id": 11, "title": "...", "project_id": 3 },
    ...
  ]
}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EJEMPLO 3: Filtrar por status
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

USER: "Show me all tasks in review"

CLAUDE llama:
list_tasks({ "status": "review" })

Resultado:
{
  "success": true,
  "count": 3,
  "tasks": [
    { "id": 5, "title": "...", "status": "review" },
    { "id": 8, "title": "...", "status": "review" },
    ...
  ]
}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EJEMPLO 4: Buscar por texto
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

USER: "Find tasks related to authentication"

CLAUDE llama:
list_tasks({ "search": "authentication" })

Resultado:
{
  "success": true,
  "count": 2,
  "tasks": [
    { "id": 15, "title": "Implement user authentication", ... },
    { "id": 20, "title": "Fix authentication bug", ... }
  ]
}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
EJEMPLO 5: Filtrar por usuario asignado
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

USER: "What are John's tasks?" (user_id = 5)

CLAUDE llama:
list_tasks({ "assigned_to": 5 })

Resultado:
{
  "success": true,
  "count": 7,
  "tasks": [
    { "id": 1, "title": "...", "assigned_to": 5, "assignee_name": "John" },
    { "id": 4, "title": "...", "assigned_to": 5, "assignee_name": "John" },
    ...
  ]
}
```

## 7.5 UpdateTask Tool - Validación de Transiciones

### 7.5.1 Código Completo Comentado

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

/**
 * UpdateTask Tool
 *
 * Herramienta MCP para actualizar tareas existentes.
 *
 * CARACTERÍSTICAS ESPECIALES:
 * - Actualización parcial (solo campos enviados)
 * - Validación de transiciones de estado
 * - Usa TaskStatusManager (Domain Service)
 */
class UpdateTask extends Tool
{
    protected string $description = <<<'MARKDOWN'
        Update an existing task. You can update title, description, status, priority, assignee, due date, and hours.
        Status transitions are validated to ensure proper workflow.
    MARKDOWN;

    /**
     * Inyección de MÚLTIPLES dependencias
     *
     * - Repository: Para leer y guardar
     * - StatusManager: Para validar transiciones (Domain Service)
     */
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TaskStatusManager $statusManager  // ← Domain Service
    ) {}

    /**
     * Handle the tool request.
     *
     * FLUJO:
     * 1. Verificar que la tarea existe
     * 2. Construir data con solo los campos enviados
     * 3. Validar status si fue enviado
     * 4. Actualizar tarea
     * 5. Retornar tarea actualizada
     */
    public function handle(Request $request): Response
    {
        // 1. Buscar tarea
        $taskId = $request->input('task_id');
        $task = $this->taskRepository->findById($taskId);

        if (!$task) {
            return Response::json([
                'success' => false,
                'error' => "Task #{$taskId} not found",
            ]);
        }

        $data = [];

        // 2. Construir data solo con campos enviados (actualización parcial)
        if ($request->has('title')) {
            $data['title'] = $request->input('title');
        }

        if ($request->has('description')) {
            $data['description'] = $request->input('description');
        }

        if ($request->has('priority')) {
            $data['priority'] = Priority::from($request->input('priority'));
        }

        if ($request->has('assigned_to')) {
            $data['assigned_to'] = $request->input('assigned_to');
        }

        if ($request->has('due_date')) {
            $data['due_date'] = $request->input('due_date');
        }

        if ($request->has('estimated_hours')) {
            $data['estimated_hours'] = $request->input('estimated_hours');
        }

        if ($request->has('actual_hours')) {
            $data['actual_hours'] = $request->input('actual_hours');
        }

        // 3. VALIDACIÓN ESPECIAL: Status transitions
        if ($request->has('status')) {
            $newStatus = Status::from($request->input('status'));

            // Usa Domain Service para validar
            if (!$this->statusManager->canTransition($task, $newStatus)) {
                return Response::json([
                    'success' => false,
                    'error' => "Cannot transition from {$task->status->value} to {$newStatus->value}",
                    'available_transitions' => array_map(
                        fn ($status) => $status->value,
                        $this->statusManager->getAvailableTransitions($task)
                    ),
                ]);
            }

            $data['status'] = $newStatus;
        }

        // 4. Actualizar tarea
        $updated = $this->taskRepository->update($taskId, $data);

        if (!$updated) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to update task',
            ]);
        }

        // 5. Recargar tarea para obtener datos actualizados
        $task = $this->taskRepository->findById($taskId);

        return Response::json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'updated_at' => $task->updated_at->toIso8601String(),
            ],
            'message' => "Task #{$task->id} updated successfully",
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * NOTA:
     * - task_id es REQUERIDO
     * - Todos los demás campos son OPCIONALES (actualización parcial)
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()
                ->description('The ID of the task to update')
                ->minimum(1)
                ->required(),  // ← ÚNICO CAMPO REQUERIDO

            'title' => $schema->string()
                ->description('New task title')
                ->minLength(3)
                ->maxLength(255)
                ->optional(),

            'description' => $schema->string()
                ->description('New task description')
                ->optional(),

            'status' => $schema->enum(['pending', 'in_progress', 'review', 'completed', 'blocked'])
                ->description('New task status (transitions are validated)')
                ->optional(),

            'priority' => $schema->enum(['low', 'medium', 'high', 'critical'])
                ->description('New task priority')
                ->optional(),

            'assigned_to' => $schema->integer()
                ->description('User ID to assign the task to')
                ->optional(),

            'due_date' => $schema->string()
                ->description('New due date in ISO 8601 format')
                ->optional(),

            'estimated_hours' => $schema->integer()
                ->description('Estimated hours to complete')
                ->minimum(1)
                ->optional(),

            'actual_hours' => $schema->integer()
                ->description('Actual hours spent')
                ->minimum(0)
                ->optional(),
        ];
    }
}
```

### 7.5.2 Ejemplo: Validación de Transiciones

```
┌─────────────────────────────────────────────────────────────┐
│        EJEMPLO: Transición Inválida Detectada              │
└─────────────────────────────────────────────────────────────┘

USER:
"Mark task #5 as completed"

Estado actual de task #5:
{
  "id": 5,
  "status": "pending",  ← Actualmente PENDING
  ...
}

CLAUDE llama:
update_task({
  "task_id": 5,
  "status": "completed"  ← Intenta cambiar a COMPLETED
})

UpdateTask::handle() ejecuta:
1. Busca task #5: ✓ existe
2. Extrae newStatus = Status::COMPLETED
3. Valida transición:
   $this->statusManager->canTransition($task, Status::COMPLETED)
   ↓
   Status::PENDING->canTransitionTo(Status::COMPLETED)
   ↓
   return false;  ← PENDING no puede ir directo a COMPLETED

4. Retorna error con transiciones disponibles:

RESPONSE:
{
  "success": false,
  "error": "Cannot transition from pending to completed",
  "available_transitions": [
    "in_progress",  ← PENDING puede ir aquí
    "blocked"       ← O aquí
  ]
}

CLAUDE responde al usuario:
"I cannot mark task #5 as completed because it's currently pending.
Tasks must go through these steps: pending → in_progress → review → completed.

Would you like me to mark it as 'in_progress' instead?"
```

## 7.6 Resumen de las 6 Herramientas

```
┌─────────────────────────────────────────────────────────────┐
│          TASKTOOLS SERVER: 6 HERRAMIENTAS CRUD              │
└─────────────────────────────────────────────────────────────┘

1. CreateTask
   ├─ Operación: CREATE (CRUD)
   ├─ Parámetros: project_id, title, description, priority, etc.
   ├─ Validaciones: JSON Schema
   ├─ Lógica: Repository->create()
   └─ Retorna: Tarea creada con id

2. ListTasks
   ├─ Operación: READ many (CRUD)
   ├─ Filtros: project_id, status, assigned_to, search
   ├─ Validaciones: Ninguna (todos opcionales)
   ├─ Lógica: Repository->findBy*() o all()
   └─ Retorna: Array de tareas

3. GetTask
   ├─ Operación: READ one (CRUD)
   ├─ Parámetros: task_id
   ├─ Validaciones: task_id requerido
   ├─ Lógica: Repository->findById()
   └─ Retorna: Tarea con detalles completos

4. UpdateTask
   ├─ Operación: UPDATE (CRUD)
   ├─ Parámetros: task_id + campos a actualizar
   ├─ Validaciones: JSON Schema + TaskStatusManager
   ├─ Lógica: Repository->update() con validación de transiciones
   └─ Retorna: Tarea actualizada

5. DeleteTask
   ├─ Operación: DELETE (CRUD)
   ├─ Parámetros: task_id
   ├─ Validaciones: Verifica existencia
   ├─ Lógica: Repository->delete()
   └─ Retorna: Confirmación de eliminación

6. AssignTask
   ├─ Operación: ACTION (no es CRUD puro)
   ├─ Parámetros: task_id, user_id
   ├─ Validaciones: Existencia de task y user
   ├─ Lógica: Repository->assign()
   └─ Retorna: Tarea asignada
```

## 7.7 Ejercicios Prácticos

### Ejercicio 1: Crear GetTask Tool

Implementa el Tool que falta (solo mostramos el skeleton):

```php
<?php

namespace App\Mcp\Tools;

class GetTask extends Tool
{
    protected string $description = <<<'MARKDOWN'
        Get detailed information about a specific task by its ID.
        Returns all task fields including relationships (project, assignee, tags, comments).
    MARKDOWN;

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {}

    public function handle(Request $request): Response
    {
        // TODO: Implementar
        // 1. Obtener task_id del request
        // 2. Buscar tarea con findById()
        // 3. Si no existe, retornar error
        // 4. Formatear tarea con todas sus relaciones
        // 5. Retornar JSON
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()
                ->description('The ID of the task to retrieve')
                ->minimum(1)
                ->required(),
        ];
    }
}
```

### Ejercicio 2: Agregar Filtro Combinado

Modifica ListTasks para permitir filtros combinados (actualmente solo usa uno):

```php
// ACTUAL: Solo un filtro a la vez
if ($projectId) {
    $tasks = $this->taskRepository->findByProjectId($projectId);
} elseif ($status) {
    // ...
}

// MEJORADO: Filtros combinados
// TODO: Implementar lógica que permita:
// - project_id + status
// - project_id + assigned_to
// - status + assigned_to
// - Todos juntos
```

### Ejercicio 3: Test de UpdateTask

Escribe tests para el UpdateTask Tool:

```php
test('it updates task title successfully', function () {
    // TODO: Mock repository
    // TODO: Mock status manager
    // TODO: Crear UpdateTask tool
    // TODO: Simular request con task_id y title
    // TODO: Verificar que se llamó update() con datos correctos
});

test('it rejects invalid status transition', function () {
    // TODO: Mock repository que retorna task con status PENDING
    // TODO: Mock status manager que retorna false para canTransition
    // TODO: Intentar cambiar a COMPLETED
    // TODO: Verificar que retorna error con available_transitions
});
```

## 7.8 Resumen del Capítulo 7

🎯 **Conceptos Clave Aprendidos:**

1. **MCP Server** estructura y responsabilidades
   - Metadatos (name, version)
   - Instructions para el LLM
   - Registro de Tools, Resources, Prompts
   - 87 líneas de código

2. **CreateTask Tool** - operación CREATE
   - Descripción clara para el LLM
   - JSON Schema con validaciones
   - Dependency Injection (Repository)
   - Value Objects (Priority, Status)
   - 97 líneas de código

3. **ListTasks Tool** - operación READ (many)
   - Múltiples filtros opcionales
   - Lógica de cascada (if-elseif)
   - Formateo de relaciones
   - 93 líneas de código

4. **UpdateTask Tool** - operación UPDATE
   - Actualización parcial (solo campos enviados)
   - Validación de transiciones con Domain Service
   - Mensajes de error informativos
   - 162 líneas de código

5. **Flujo completo MCP**
   - LLM lee instructions y schema
   - Prepara parámetros JSON
   - Validación automática
   - Tool ejecuta lógica de negocio
   - Respuesta estructurada

6. **Integración arquitectural**
   - Tools usan Repositories (patrón Repository)
   - Tools usan Domain Services (TaskStatusManager)
   - Value Objects en toda la stack
   - Dependency Injection automática

🔜 **Próximo Capítulo:**
En el Capítulo 8 exploraremos **AIAssistantServer**, viendo cómo integrar IA generativa en las herramientas MCP para generar descripciones, sugerir prioridades, y analizar productividad.

---

**Estado del Tutorial:** Capítulos 1-7 de 15 completados ✓

---

# Capítulo 8: AIAssistantServer - Herramientas con Inteligencia Artificial

## 8.1 ¿Qué es el AIAssistantServer?

El `AIAssistantServer` es un MCP Server especializado que aprovecha la **Inteligencia Artificial (OpenAI GPT-4)** para potenciar la gestión de tareas. Mientras que `TaskToolsServer` (Capítulo 7) se enfoca en operaciones CRUD básicas, este servidor proporciona **capacidades inteligentes** que ayudan a los usuarios a:

- Generar descripciones técnicas detalladas automáticamente
- Mejorar títulos de tareas para hacerlos más claros y accionables
- Calcular prioridades óptimas basadas en múltiples factores
- Analizar productividad del equipo e identificar cuellos de botella

### Analogía: Asistente Personal Inteligente

Imagina que tienes un **asistente personal experto** que:

1. **Escritor Técnico** → Escribe descripciones detalladas cuando le das solo un título
2. **Editor Profesional** → Mejora tus títulos para que sean más claros
3. **Analista de Prioridades** → Evalúa qué tareas son urgentes considerando múltiples factores
4. **Consultor de Productividad** → Analiza el rendimiento del equipo y sugiere mejoras

Eso es exactamente lo que hace el `AIAssistantServer`.

### Diferencia con TaskToolsServer

| Aspecto | TaskToolsServer (Cap 7) | AIAssistantServer (Cap 8) |
|---------|-------------------------|---------------------------|
| **Propósito** | CRUD operations | AI-powered assistance |
| **Tipo de tareas** | Create, Read, Update, Delete | Generate, Analyze, Suggest, Improve |
| **Tecnología** | Laravel + Eloquent | Laravel + OpenAI GPT-4 |
| **Dependencias** | Repository pattern | Repository + Domain Services + AI |
| **Ejemplos** | create_task, list_tasks | generate_description, analyze_productivity |

---

## 8.2 Anatomía del AIAssistantServer

Veamos el código completo del servidor:

```php
<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AnalyzeProductivity;
use App\Mcp\Tools\GenerateTaskDescription;
use App\Mcp\Tools\ImproveTaskTitle;
use App\Mcp\Tools\SuggestPriority;
use Laravel\Mcp\Server;

class AIAssistantServer extends Server
{
    protected string $name = 'TaskMaster AI - AI Assistant';

    protected string $version = '1.0.0';

    protected string $instructions = <<<'MARKDOWN'
        # TaskMaster AI - AI Assistant

        This MCP server provides AI-powered assistance for task management using OpenAI's GPT models.

        ## Available Tools

        - **generate_task_description**: Generate comprehensive technical descriptions for tasks
        - **improve_task_title**: Improve task titles to be more clear and actionable
        - **suggest_priority**: Calculate optimal task priority based on multiple factors
        - **analyze_productivity**: Analyze team productivity and identify bottlenecks

        ## Features

        1. **AI-Generated Content**: Uses GPT-4 to generate high-quality task descriptions
        2. **Smart Prioritization**: Analyzes due dates, complexity, and activity to suggest priorities
        3. **Productivity Insights**: Identifies bottlenecks and provides actionable recommendations
        4. **Title Optimization**: Improves task titles following best practices

        ## Requirements

        - OpenAI API key must be configured in OPENAI_API_KEY environment variable
        - Default model: gpt-4-turbo-preview (configurable via OPENAI_DEFAULT_MODEL)
    MARKDOWN;

    protected array $tools = [
        GenerateTaskDescription::class,
        ImproveTaskTitle::class,
        SuggestPriority::class,
        AnalyzeProductivity::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
```

### Elementos Clave del Servidor

#### 1. **Metadata del Servidor**

```php
protected string $name = 'TaskMaster AI - AI Assistant';
protected string $version = '1.0.0';
```

- **Name**: Identifica este servidor como especializado en IA
- **Version**: Semantic versioning para control de cambios

#### 2. **Instructions (Críticas para el LLM)**

Las instrucciones le dicen a Claude (o cualquier LLM) **cómo y cuándo usar estas herramientas**:

```markdown
## Features

1. **AI-Generated Content**: Uses GPT-4 to generate high-quality task descriptions
2. **Smart Prioritization**: Analyzes due dates, complexity, and activity to suggest priorities
3. **Productivity Insights**: Identifies bottlenecks and provides actionable recommendations
4. **Title Optimization**: Improves task titles following best practices

## Requirements

- OpenAI API key must be configured in OPENAI_API_KEY environment variable
```

**¿Por qué son importantes?**

- Claude lee estas instrucciones y aprende que estas herramientas requieren OpenAI API key
- Si el usuario no tiene API key configurada, Claude puede advertirle antes de intentar usar las herramientas
- Las instrucciones explican claramente **qué hace cada herramienta** y **cuándo usarla**

#### 3. **Tools Array (4 Herramientas AI)**

```php
protected array $tools = [
    GenerateTaskDescription::class,  // AI: Generate descriptions
    ImproveTaskTitle::class,         // AI: Improve titles
    SuggestPriority::class,          // AI-powered: Calculate priority
    AnalyzeProductivity::class,      // AI: Analyze productivity
];
```

Estas 4 herramientas representan las **4 capacidades inteligentes** del servidor.

---

## 8.3 Herramienta 1: GenerateTaskDescription

### Propósito

Generar una **descripción técnica detallada** para una tarea que solo tiene un título básico.

### Código Completo (78 líneas)

```php
<?php

namespace App\Mcp\Tools;

use App\Domain\AIIntegration\Services\TaskDescriptionGenerator;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GenerateTaskDescription extends Tool
{
    protected string $description = <<<'MARKDOWN'
        Generate a detailed AI-powered description for a task.
        Requires OpenAI API key to be configured.
        Returns a comprehensive technical description with approach, challenges, and expected outcome.
    MARKDOWN;

    public function __construct(
        private readonly TaskDescriptionGenerator $descriptionGenerator,
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
                'error' => "Task #{$taskId} not found",
            ]);
        }

        try {
            $generatedDescription = $this->descriptionGenerator->generate($task);

            return Response::json([
                'success' => true,
                'task_id' => $task->id,
                'task_title' => $task->title,
                'original_description' => $task->description,
                'generated_description' => $generatedDescription,
                'message' => 'AI-generated description created successfully',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to generate description: '.$e->getMessage(),
                'hint' => 'Make sure OPENAI_API_KEY is configured in .env',
            ]);
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()
                ->description('The ID of the task to generate description for')
                ->minimum(1)
                ->required(),
        ];
    }
}
```

### Análisis Línea por Línea

#### Dependency Injection (líneas 20-26)

```php
public function __construct(
    private readonly TaskDescriptionGenerator $descriptionGenerator,
    private readonly TaskRepositoryInterface $taskRepository
) {
}
```

**Inyecta 2 dependencias:**

1. **TaskDescriptionGenerator**: Domain Service (Capítulo 6) que encapsula la lógica de IA
2. **TaskRepositoryInterface**: Repository para obtener el task

**¿Por qué no llamar directamente a OpenAI?**

❌ **Mal enfoque**:
```php
// Acoplamiento directo a OpenAI
$client = new OpenAI\Client($apiKey);
$response = $client->completions()->create([...]);
```

✅ **Buen enfoque**:
```php
// Abstracción mediante Domain Service
$description = $this->descriptionGenerator->generate($task);
```

**Ventajas:**
- **Testeable**: Puedes mockear `TaskDescriptionGenerator` en tests
- **Reutilizable**: Otros lugares pueden usar el mismo servicio
- **Mantenible**: Si cambias de OpenAI a otro provider, solo modificas el Domain Service

#### Handle Method (líneas 28-62)

**Paso 1: Obtener Task (líneas 31-35)**

```php
$taskId = $request->input('task_id');
$task = $this->taskRepository->findById($taskId);

if (!$task) {
    return Response::json([
        'success' => false,
        'error' => "Task #{$taskId} not found",
    ]);
}
```

- Busca el task por ID
- Retorna error 404 si no existe

**Paso 2: Generar Descripción con IA (líneas 37-55)**

```php
try {
    $generatedDescription = $this->descriptionGenerator->generate($task);

    return Response::json([
        'success' => true,
        'task_id' => $task->id,
        'task_title' => $task->title,
        'original_description' => $task->description,
        'generated_description' => $generatedDescription,
        'message' => 'AI-generated description created successfully',
    ]);
} catch (\Exception $e) {
    return Response::json([
        'success' => false,
        'error' => 'Failed to generate description: '.$e->getMessage(),
        'hint' => 'Make sure OPENAI_API_KEY is configured in .env',
    ]);
}
```

**Manejo de Errores Robusto:**

- Si OpenAI falla (API key inválida, rate limit, error de red), captura la excepción
- Retorna error con **hint útil** para el usuario

**¿Qué hace `generate()` internamente?** (del Domain Service, Capítulo 6):

```php
// Simplified from TaskDescriptionGenerator
public function generate(Task $task): string
{
    if (!$this->openAIService->isAvailable()) {
        return $this->generateFallbackDescription($task);
    }

    $prompt = $this->promptBuilder->buildTaskDescriptionPrompt($task);

    return $this->openAIService->complete($prompt, [
        'temperature' => 0.7,
        'max_tokens' => 800,
    ]);
}
```

- Construye un prompt específico para generación de descripciones
- Llama a OpenAI con temperatura 0.7 (balance creatividad/consistencia)
- Máximo 800 tokens de respuesta

#### JSON Schema (líneas 64-71)

```php
public function schema(JsonSchema $schema): array
{
    return [
        'task_id' => $schema->integer()
            ->description('The ID of the task to generate description for')
            ->minimum(1)
            ->required(),
    ];
}
```

**Schema simple:**

- Solo requiere `task_id` (integer >= 1)
- La IA hace el resto del trabajo

### Flujo Completo

```
┌─────────────────────────────────────────────────────────────────┐
│                    GenerateTaskDescription Tool                  │
└─────────────────────────────────────────────────────────────────┘

User asks Claude:
"Generate a description for task #42"
           │
           ▼
Claude calls MCP Tool:
{
  "name": "generate_task_description",
  "arguments": { "task_id": 42 }
}
           │
           ▼
┌──────────────────────────────────────────────────────────────────┐
│ 1. Schema Validation                                             │
│    ✓ task_id is integer                                          │
│    ✓ task_id >= 1                                                │
└──────────────────────────────────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────────────────────────────┐
│ 2. Find Task                                                     │
│    Repository: findById(42)                                      │
│    Result: Task object                                           │
└──────────────────────────────────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────────────────────────────┐
│ 3. Generate Description (Domain Service)                        │
│                                                                   │
│    TaskDescriptionGenerator:                                     │
│    ┌────────────────────────────────────────────────────┐       │
│    │ a) Check OpenAI availability                       │       │
│    │ b) Build prompt with PromptBuilder                 │       │
│    │ c) Call OpenAI API (GPT-4)                         │       │
│    │ d) Return generated text                           │       │
│    └────────────────────────────────────────────────────┘       │
└──────────────────────────────────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────────────────────────────┐
│ 4. Return Response                                               │
│    {                                                              │
│      "success": true,                                            │
│      "task_id": 42,                                              │
│      "task_title": "Implement user authentication",             │
│      "original_description": "Add login feature",               │
│      "generated_description": "Implement a secure user          │
│        authentication system with the following components:     │
│        1. Login/logout endpoints with JWT tokens                │
│        2. Password hashing with bcrypt                          │
│        3. Rate limiting to prevent brute force attacks          │
│        4. Session management with Redis                         │
│        Expected outcome: Users can securely log in..."          │
│    }                                                              │
└──────────────────────────────────────────────────────────────────┘
           │
           ▼
Claude shows result to user:
"I've generated a detailed description for task #42..."
```

### Ejemplo de Uso Real

**Conversación con Claude:**

```
User: Task #42 has the title "Implement user authentication"
      but no description. Can you generate one?

Claude: I'll generate a detailed description for task #42 using AI.
        [Calls generate_task_description tool with task_id: 42]

Tool Response:
{
  "success": true,
  "generated_description": "Implement a secure user authentication system
    with the following components:

    1. **Login/Logout Endpoints**
       - POST /api/auth/login: Accept email/password, return JWT token
       - POST /api/auth/logout: Invalidate JWT token

    2. **Password Security**
       - Use bcrypt with cost factor 12 for password hashing
       - Implement password strength validation (min 8 chars, 1 uppercase, 1 number)

    3. **Security Measures**
       - Rate limiting: 5 login attempts per 15 minutes per IP
       - JWT tokens expire after 24 hours
       - Refresh tokens for seamless re-authentication

    4. **Session Management**
       - Store active sessions in Redis for fast lookup
       - Implement logout from all devices feature

    **Expected Outcome**: Users can securely log in and access protected
    resources. Failed login attempts are logged and rate-limited."
}

Claude: I've generated a comprehensive technical description for task #42.
        The description includes security best practices, implementation
        details for endpoints, password handling, rate limiting, and session
        management. Would you like me to update the task with this description?
```

---

## 8.4 Herramienta 2: ImproveTaskTitle

### Propósito

Mejorar títulos de tareas para que sean **más claros, accionables y sigan mejores prácticas**.

### Código Completo (80 líneas)

```php
<?php

namespace App\Mcp\Tools;

use App\Domain\AIIntegration\Services\TaskDescriptionGenerator;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ImproveTaskTitle extends Tool
{
    protected string $description = <<<'MARKDOWN'
        Use AI to improve a task title to be more clear, actionable, and follow best practices.
        The AI considers the task description and context to suggest a better title.
    MARKDOWN;

    public function __construct(
        private readonly TaskDescriptionGenerator $descriptionGenerator,
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
                'error' => "Task #{$taskId} not found",
            ]);
        }

        try {
            $improvedTitle = $this->descriptionGenerator->improveTitle($task);

            // Clean up the response (remove quotes if present)
            $improvedTitle = trim($improvedTitle, '"\'');

            return Response::json([
                'success' => true,
                'task_id' => $task->id,
                'original_title' => $task->title,
                'improved_title' => $improvedTitle,
                'apply' => false,
                'message' => 'AI-improved title generated. Set apply=true to update the task.',
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to improve title: '.$e->getMessage(),
                'hint' => 'Make sure OPENAI_API_KEY is configured in .env',
            ]);
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()
                ->description('The ID of the task to improve the title for')
                ->minimum(1)
                ->required(),
        ];
    }
}
```

### Características Especiales

#### 1. **Limpieza de Respuesta (líneas 40-41)**

```php
$improvedTitle = $this->descriptionGenerator->improveTitle($task);

// Clean up the response (remove quotes if present)
$improvedTitle = trim($improvedTitle, '"\'');
```

**¿Por qué es necesario?**

A veces OpenAI retorna títulos con comillas:
- Input: `"Fix bug in login"`
- Output: `"Resolve authentication failure in login endpoint"`

La limpieza remueve las comillas para tener un título limpio.

#### 2. **Modo Preview (línea 47)**

```php
'apply' => false,
'message' => 'AI-improved title generated. Set apply=true to update the task.',
```

**Patrón de diseño importante:**

- Por defecto, **solo sugiere** el título mejorado, no lo aplica
- El usuario puede revisar la sugerencia antes de aplicarla
- Si el usuario quiere aplicarlo, necesitaría usar `update_task` del TaskToolsServer

**Flujo completo:**

```
1. User: "Improve title for task #42"
   → Tool returns: { "improved_title": "...", "apply": false }

2. Claude shows: "Here's the improved title: '...'. Would you like me to apply it?"

3. User: "Yes, apply it"
   → Claude calls update_task with new title
```

### Ejemplo de Uso

**Antes:**

```
Task #42: "login stuff"
```

**Llamada a ImproveTaskTitle:**

```json
{
  "name": "improve_task_title",
  "arguments": { "task_id": 42 }
}
```

**Respuesta:**

```json
{
  "success": true,
  "task_id": 42,
  "original_title": "login stuff",
  "improved_title": "Implement secure user authentication with JWT tokens",
  "apply": false,
  "message": "AI-improved title generated. Set apply=true to update the task."
}
```

**Después (si el usuario acepta):**

```
Task #42: "Implement secure user authentication with JWT tokens"
```

### ¿Qué hace `improveTitle()` internamente?

```php
// Simplified from TaskDescriptionGenerator (Domain Service)
public function improveTitle(Task $task): string
{
    if (!$this->openAIService->isAvailable()) {
        return $this->generateFallbackTitle($task);
    }

    $prompt = $this->promptBuilder->buildTitleImprovementPrompt($task);

    return $this->openAIService->complete($prompt, [
        'temperature' => 0.5, // Lower temperature for consistency
        'max_tokens' => 100,  // Titles are short
    ]);
}
```

**Prompt ejemplo:**

```
Improve the following task title to be more clear and actionable:

Current title: "login stuff"
Description: "Add user authentication to the system"

Provide a single improved title that:
1. Is clear and specific
2. Starts with an action verb (Implement, Fix, Add, etc.)
3. Is concise (under 10 words)
4. Follows task management best practices

Improved title:
```

**OpenAI responde:**

```
"Implement secure user authentication with JWT tokens"
```

---

## 8.5 Herramienta 3: SuggestPriority

### Propósito

Calcular y sugerir una **prioridad óptima** para una tarea basándose en múltiples factores cuantitativos.

### Código Completo (104 líneas)

```php
<?php

namespace App\Mcp\Tools;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\Services\TaskPriorityCalculator;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SuggestPriority extends Tool
{
    protected string $description = <<<'MARKDOWN'
        Calculate and suggest an optimal priority for a task based on multiple factors:
        - Due date urgency
        - Current priority
        - Task complexity (estimated hours)
        - Activity level (number of comments)

        Returns suggested priority with explanation.
    MARKDOWN;

    public function __construct(
        private readonly TaskPriorityCalculator $priorityCalculator,
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
                'error' => "Task #{$taskId} not found",
            ]);
        }

        $suggestedPriority = $this->priorityCalculator->calculateSuggestedPriority($task);
        $currentPriority = $task->priority;

        // Build explanation
        $factors = [];

        if ($task->due_date) {
            $daysUntil = now()->diffInDays($task->due_date, false);
            if ($daysUntil < 0) {
                $factors[] = "Task is overdue by ".abs($daysUntil).' days';
            } elseif ($daysUntil <= 1) {
                $factors[] = 'Task is due very soon (within 24 hours)';
            } elseif ($daysUntil <= 3) {
                $factors[] = 'Task is due soon (within 3 days)';
            }
        }

        if ($task->estimated_hours && $task->estimated_hours >= 20) {
            $factors[] = 'Large task ('.$task->estimated_hours.' estimated hours)';
        }

        if ($task->comments()->count() > 5) {
            $factors[] = 'High activity ('.$task->comments()->count().' comments)';
        }

        $shouldEscalate = $suggestedPriority->score() > $currentPriority->score();

        return Response::json([
            'success' => true,
            'task_id' => $task->id,
            'task_title' => $task->title,
            'current_priority' => $currentPriority->value,
            'suggested_priority' => $suggestedPriority->value,
            'should_escalate' => $shouldEscalate,
            'priority_change' => $shouldEscalate ? 'increase' : ($suggestedPriority->score() < $currentPriority->score() ? 'decrease' : 'no change'),
            'factors' => $factors,
            'explanation' => $shouldEscalate
                ? "Consider escalating this task to {$suggestedPriority->value} priority based on the factors above."
                : "Current priority level seems appropriate for this task.",
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'task_id' => $schema->integer()
                ->description('The ID of the task to analyze')
                ->minimum(1)
                ->required(),
        ];
    }
}
```

### Análisis Detallado

#### 1. **Algoritmo de Cálculo (TaskPriorityCalculator)**

```php
$suggestedPriority = $this->priorityCalculator->calculateSuggestedPriority($task);
```

**¿Qué hace este Domain Service?** (117 líneas, ya vimos en la lectura)

```php
public function calculateSuggestedPriority(Task $task): Priority
{
    $score = 0;

    // Factor 1: Due date urgency (0-4 points)
    if ($task->due_date) {
        $daysUntilDue = Carbon::now()->diffInDays($task->due_date, false);

        if ($daysUntilDue < 0) {
            $score += 4;  // Overdue
        } elseif ($daysUntilDue <= 1) {
            $score += 3;  // Due today/tomorrow
        } elseif ($daysUntilDue <= 3) {
            $score += 2;  // Due within 3 days
        } elseif ($daysUntilDue <= 7) {
            $score += 1;  // Due within a week
        }
    }

    // Factor 2: Current priority score (0-3 points)
    $score += $task->priority->score();
    // LOW=0, MEDIUM=1, HIGH=2, CRITICAL=3

    // Factor 3: Task complexity (0-2 points)
    if ($task->estimated_hours) {
        if ($task->estimated_hours >= 20) {
            $score += 2;  // Large task
        } elseif ($task->estimated_hours >= 10) {
            $score += 1;  // Medium task
        }
    }

    // Factor 4: Activity level (0-1 point)
    if ($task->comments()->count() > 5) {
        $score += 1;  // High activity
    }

    // Convert score to priority
    return $this->scoreToPriority($score);
}

private function scoreToPriority(int $score): Priority
{
    return match (true) {
        $score >= 8 => Priority::CRITICAL,
        $score >= 5 => Priority::HIGH,
        $score >= 3 => Priority::MEDIUM,
        default => Priority::LOW,
    };
}
```

**Sistema de Puntuación:**

| Score Range | Priority | Example Scenario |
|-------------|----------|-----------------|
| 0-2 | LOW | Far future, small task |
| 3-4 | MEDIUM | Due next week, medium task |
| 5-7 | HIGH | Due in 3 days, large task |
| 8+ | CRITICAL | Overdue, complex, high activity |

**Ejemplo de cálculo:**

```
Task: "Implement user authentication"
- due_date: tomorrow (+3 points)
- current priority: MEDIUM (+1 point)
- estimated_hours: 25 (+2 points)
- comments: 7 (+1 point)
────────────────────────────────────
Total Score: 7 points → HIGH priority
```

#### 2. **Construcción de Factores (líneas 45-64)**

```php
$factors = [];

if ($task->due_date) {
    $daysUntil = now()->diffInDays($task->due_date, false);
    if ($daysUntil < 0) {
        $factors[] = "Task is overdue by ".abs($daysUntil).' days';
    } elseif ($daysUntil <= 1) {
        $factors[] = 'Task is due very soon (within 24 hours)';
    } elseif ($daysUntil <= 3) {
        $factors[] = 'Task is due soon (within 3 days)';
    }
}

if ($task->estimated_hours && $task->estimated_hours >= 20) {
    $factors[] = 'Large task ('.$task->estimated_hours.' estimated hours)';
}

if ($task->comments()->count() > 5) {
    $factors[] = 'High activity ('.$task->comments()->count().' comments)';
}
```

**Propósito:**

- Construir un **array de explicaciones legibles** para el usuario
- Transparencia: el usuario entiende **por qué** se sugiere una prioridad específica

#### 3. **Respuesta con Recomendación (líneas 66-81)**

```php
$shouldEscalate = $suggestedPriority->score() > $currentPriority->score();

return Response::json([
    'success' => true,
    'task_id' => $task->id,
    'task_title' => $task->title,
    'current_priority' => $currentPriority->value,
    'suggested_priority' => $suggestedPriority->value,
    'should_escalate' => $shouldEscalate,
    'priority_change' => $shouldEscalate ? 'increase' : (...),
    'factors' => $factors,
    'explanation' => $shouldEscalate
        ? "Consider escalating this task to {$suggestedPriority->value} priority based on the factors above."
        : "Current priority level seems appropriate for this task.",
]);
```

**Información rica:**

- **current_priority**: Prioridad actual
- **suggested_priority**: Prioridad sugerida
- **should_escalate**: Boolean que indica si debería aumentar
- **priority_change**: "increase", "decrease", o "no change"
- **factors**: Array de razones específicas
- **explanation**: Texto explicativo para el usuario

### Ejemplo de Uso Real

**Conversación:**

```
User: Check if task #42 needs a priority change

Claude: I'll analyze task #42 to suggest an optimal priority.
        [Calls suggest_priority tool with task_id: 42]

Tool Response:
{
  "success": true,
  "task_id": 42,
  "task_title": "Implement user authentication",
  "current_priority": "medium",
  "suggested_priority": "high",
  "should_escalate": true,
  "priority_change": "increase",
  "factors": [
    "Task is due soon (within 3 days)",
    "Large task (25 estimated hours)",
    "High activity (7 comments)"
  ],
  "explanation": "Consider escalating this task to high priority based on the factors above."
}

Claude: Based on my analysis, task #42 should be escalated to HIGH priority.
        Here's why:

        • The task is due in 3 days
        • It's a large task with 25 estimated hours
        • There's high activity with 7 comments

        Would you like me to update the task priority to HIGH?
```

---

## 8.6 Herramienta 4: AnalyzeProductivity

### Propósito

Analizar métricas de productividad del equipo, identificar cuellos de botella y proporcionar recomendaciones con IA.

### Código Completo (86 líneas)

```php
<?php

namespace App\Mcp\Tools;

use App\Domain\AIIntegration\Services\ProductivityAnalyzer;
use App\Domain\Analytics\Services\MetricsCollector;
use Carbon\Carbon;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class AnalyzeProductivity extends Tool
{
    protected string $description = <<<'MARKDOWN'
        Analyze team productivity metrics using AI.
        Provides insights, identifies bottlenecks, and suggests improvements.
        Optionally specify a time period (default: last 7 days).
    MARKDOWN;

    public function __construct(
        private readonly ProductivityAnalyzer $productivityAnalyzer,
        private readonly MetricsCollector $metricsCollector
    ) {
    }

    public function handle(Request $request): Response
    {
        $days = $request->input('days', 7);
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        try {
            // Collect metrics
            $metrics = $this->metricsCollector->collectMetrics($startDate, $endDate);

            // Analyze with AI
            $analysis = $this->productivityAnalyzer->analyze($metrics);

            // Identify bottlenecks
            $tasksArray = $metrics['tasks'] ?? [];
            $bottlenecks = $this->productivityAnalyzer->identifyBottlenecks($tasksArray);

            return Response::json([
                'success' => true,
                'period' => [
                    'days' => $days,
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'metrics' => $metrics,
                'ai_analysis' => $analysis,
                'bottlenecks' => $bottlenecks,
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to analyze productivity: '.$e->getMessage(),
                'hint' => 'Make sure OPENAI_API_KEY is configured in .env',
            ]);
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'days' => $schema->integer()
                ->description('Number of days to analyze (default: 7)')
                ->minimum(1)
                ->maximum(90)
                ->default(7)
                ->optional(),
        ];
    }
}
```

### Análisis Detallado

#### 1. **Parámetro Flexible (líneas 29-31)**

```php
$days = $request->input('days', 7);
$endDate = Carbon::now();
$startDate = $endDate->copy()->subDays($days);
```

**Schema permite configuración:**

```php
'days' => $schema->integer()
    ->minimum(1)
    ->maximum(90)
    ->default(7)
    ->optional(),
```

**Casos de uso:**

- `days: 7` → Análisis semanal (default)
- `days: 30` → Análisis mensual
- `days: 90` → Análisis trimestral

#### 2. **Tres Pasos de Análisis (líneas 34-42)**

**Paso 1: Recolectar Métricas**

```php
$metrics = $this->metricsCollector->collectMetrics($startDate, $endDate);
```

**Ejemplo de métricas recolectadas:**

```php
[
    'tasks_completed' => 45,
    'tasks_in_progress' => 12,
    'tasks_blocked' => 3,
    'tasks_in_review' => 8,
    'total_hours_spent' => 320,
    'average_completion_time' => 2.5, // days
    'tasks' => [
        ['id' => 1, 'status' => 'completed', 'hours' => 8, ...],
        ['id' => 2, 'status' => 'blocked', 'hours' => 0, ...],
        // ...
    ],
]
```

**Paso 2: Análisis con IA**

```php
$analysis = $this->productivityAnalyzer->analyze($metrics);
```

**¿Qué hace `analyze()` del ProductivityAnalyzer?** (152 líneas)

```php
public function analyze(array $metrics): string
{
    if (!$this->openAIService->isAvailable()) {
        return $this->generateFallbackAnalysis($metrics);
    }

    $prompt = $this->promptBuilder->buildProductivityAnalysisPrompt($metrics);

    return $this->openAIService->complete($prompt, [
        'temperature' => 0.6,
        'max_tokens' => 1500,
    ]);
}
```

**Prompt ejemplo:**

```
Analyze the following team productivity metrics and provide insights:

Period: Last 7 days
Tasks Completed: 45
Tasks In Progress: 12
Tasks Blocked: 3
Tasks In Review: 8
Total Hours Spent: 320
Average Completion Time: 2.5 days

Provide:
1. Overall productivity assessment
2. Key strengths and weaknesses
3. Trends and patterns
4. Specific actionable recommendations

Analysis:
```

**OpenAI responde con análisis detallado (ejemplo):**

```markdown
## Overall Productivity Assessment

The team shows strong productivity with 45 completed tasks in 7 days (6.4 tasks/day).
Average completion time of 2.5 days is within healthy range.

## Key Observations

**Strengths:**
- High completion rate (78% of active tasks)
- Consistent daily velocity

**Concerns:**
- 3 blocked tasks indicating potential dependencies issues
- Review queue building up with 8 tasks (possible bottleneck)

## Trends

- Tasks are moving through workflow efficiently except for review stage
- Blocked tasks have been stagnant for average of 4 days

## Recommendations

1. **Review Process**: Add a second reviewer or increase review capacity
2. **Blocked Tasks**: Daily standup to unblock dependencies
3. **Documentation**: Review blocked tasks to identify common blockers
4. **Automation**: Consider automated testing to reduce review time
```

**Paso 3: Identificar Cuellos de Botella**

```php
$tasksArray = $metrics['tasks'] ?? [];
$bottlenecks = $this->productivityAnalyzer->identifyBottlenecks($tasksArray);
```

**¿Qué hace `identifyBottlenecks()`?**

```php
public function identifyBottlenecks(array $tasks): array
{
    $bottlenecks = [];

    // Count tasks by status
    $statusCounts = [];
    foreach ($tasks as $task) {
        $status = $task['status'] ?? 'unknown';
        $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
    }

    // Detect blocked tasks bottleneck
    if (isset($statusCounts['blocked']) && $statusCounts['blocked'] > 3) {
        $bottlenecks[] = [
            'type' => 'blocked_tasks',
            'count' => $statusCounts['blocked'],
            'severity' => 'high',
            'message' => "High number of blocked tasks ({$statusCounts['blocked']})",
        ];
    }

    // Detect review backlog
    if (isset($statusCounts['review']) && $statusCounts['review'] > 5) {
        $bottlenecks[] = [
            'type' => 'review_backlog',
            'count' => $statusCounts['review'],
            'severity' => 'medium',
            'message' => "Review backlog building up ({$statusCounts['review']} tasks)",
        ];
    }

    return $bottlenecks;
}
```

**Ejemplo de bottlenecks detectados:**

```php
[
    [
        'type' => 'review_backlog',
        'count' => 8,
        'severity' => 'medium',
        'message' => 'Review backlog building up (8 tasks)',
    ],
]
```

#### 3. **Respuesta Rica (líneas 44-54)**

```php
return Response::json([
    'success' => true,
    'period' => [
        'days' => 7,
        'start' => '2025-11-06',
        'end' => '2025-11-13',
    ],
    'metrics' => $metrics,
    'ai_analysis' => $analysis,
    'bottlenecks' => $bottlenecks,
]);
```

**3 tipos de información:**

1. **metrics**: Datos cuantitativos (números, conteos)
2. **ai_analysis**: Análisis cualitativo con IA (texto markdown)
3. **bottlenecks**: Problemas específicos detectados (estructurados)

### Ejemplo de Uso Real

**Conversación:**

```
User: Analyze our team's productivity for the last 2 weeks

Claude: I'll analyze your team's productivity over the last 14 days.
        [Calls analyze_productivity tool with days: 14]

Tool Response:
{
  "success": true,
  "period": {
    "days": 14,
    "start": "2025-10-30",
    "end": "2025-11-13"
  },
  "metrics": {
    "tasks_completed": 87,
    "tasks_in_progress": 15,
    "tasks_blocked": 5,
    "tasks_in_review": 12,
    "total_hours_spent": 680,
    "average_completion_time": 3.2
  },
  "ai_analysis": "## Overall Productivity Assessment\n\nThe team shows strong productivity with 87 completed tasks in 14 days (6.2 tasks/day)...",
  "bottlenecks": [
    {
      "type": "blocked_tasks",
      "count": 5,
      "severity": "high",
      "message": "High number of blocked tasks (5)"
    },
    {
      "type": "review_backlog",
      "count": 12,
      "severity": "medium",
      "message": "Review backlog building up (12 tasks)"
    }
  ]
}

Claude: Here's the productivity analysis for the last 2 weeks:

📊 **Metrics Summary:**
- ✅ 87 tasks completed (6.2 tasks/day)
- 🔄 15 tasks in progress
- 🚫 5 tasks blocked
- 👀 12 tasks in review
- ⏱️ 680 hours total (48.6 hours/day)
- 📈 Average completion: 3.2 days

⚠️ **Bottlenecks Identified:**

1. **High Priority - Blocked Tasks (5)**
   - 5 tasks are currently blocked, which is above the healthy threshold

2. **Medium Priority - Review Backlog (12)**
   - 12 tasks waiting for review, indicating a potential bottleneck

🤖 **AI Analysis:**

[AI-generated markdown analysis here...]

The team shows strong overall productivity, but there are two areas that need attention:
1. The review process may need additional capacity
2. Blocked tasks should be addressed daily to prevent prolonged delays

Would you like me to:
- Show details of the blocked tasks?
- Suggest ways to improve the review process?
- Generate a detailed report?
```

---

## 8.7 Comparación de las 4 Herramientas

| Herramienta | Tipo de IA | Input | Output | Domain Service Usado | Use Case Principal |
|-------------|-----------|-------|--------|---------------------|-------------------|
| **GenerateTaskDescription** | Generativa (GPT-4) | task_id | Descripción técnica detallada | TaskDescriptionGenerator | Generar documentación para tareas con títulos básicos |
| **ImproveTaskTitle** | Generativa (GPT-4) | task_id | Título mejorado | TaskDescriptionGenerator | Mejorar claridad y formato de títulos |
| **SuggestPriority** | Analítica (Algoritmo) | task_id | Prioridad sugerida + factores | TaskPriorityCalculator | Detectar tareas que necesitan escalación |
| **AnalyzeProductivity** | Analítica (GPT-4 + Algoritmos) | days (opcional) | Métricas + análisis + bottlenecks | ProductivityAnalyzer, MetricsCollector | Identificar problemas de flujo de trabajo |

### Tipos de IA

**1. IA Generativa (GPT-4)**

- **GenerateTaskDescription**: Genera texto nuevo (descripciones)
- **ImproveTaskTitle**: Transforma texto existente (títulos)

**2. IA Analítica (Algoritmos + GPT-4)**

- **SuggestPriority**: Algoritmo de scoring + explicación con IA opcional
- **AnalyzeProductivity**: Algoritmos de detección de bottlenecks + análisis narrativo con GPT-4

---

## 8.8 Integración con Domain Services

Estas herramientas MCP **no contienen lógica de negocio**, sino que **delegan** a Domain Services:

```
┌─────────────────────────────────────────────────────────────────┐
│                       Layered Architecture                       │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ Presentation Layer (MCP Tools)                                  │
│                                                                  │
│ ┌─────────────────┐  ┌─────────────────┐  ┌────────────────┐   │
│ │ Generate        │  │ Improve         │  │ Suggest        │   │
│ │ TaskDescription │  │ TaskTitle       │  │ Priority       │   │
│ └────────┬────────┘  └────────┬────────┘  └────────┬───────┘   │
│          │                     │                     │            │
└──────────┼─────────────────────┼─────────────────────┼───────────┘
           │                     │                     │
           ▼                     ▼                     ▼
┌─────────────────────────────────────────────────────────────────┐
│ Domain Layer (Domain Services)                                  │
│                                                                  │
│ ┌──────────────────────┐  ┌──────────────────────┐             │
│ │ TaskDescription      │  │ TaskPriority         │             │
│ │ Generator            │  │ Calculator           │             │
│ │                      │  │                      │             │
│ │ - generate()         │  │ - calculateSuggest...│             │
│ │ - improveTitle()     │  │ - compare()          │             │
│ │ - generateCriteria() │  │ - sortByPriority()   │             │
│ └──────────┬───────────┘  └──────────┬───────────┘             │
│            │                          │                          │
│            ▼                          ▼                          │
│ ┌──────────────────────┐  ┌──────────────────────┐             │
│ │ OpenAIService        │  │ PromptBuilder        │             │
│ │                      │  │                      │             │
│ │ - complete()         │  │ - buildTaskDescri... │             │
│ │ - generateJson()     │  │ - buildTitleImpro... │             │
│ │ - isAvailable()      │  │ - buildPrioritySu... │             │
│ └──────────────────────┘  └──────────────────────┘             │
└─────────────────────────────────────────────────────────────────┘
           │                          │
           ▼                          ▼
┌─────────────────────────────────────────────────────────────────┐
│ Infrastructure Layer                                            │
│                                                                  │
│ ┌──────────────────────┐  ┌──────────────────────┐             │
│ │ OpenAI API Client    │  │ Task Repository      │             │
│ │ (External)           │  │ (Eloquent)           │             │
│ └──────────────────────┘  └──────────────────────┘             │
└─────────────────────────────────────────────────────────────────┘
```

**Ventajas de esta arquitectura:**

1. **Separation of Concerns**: MCP Tools son solo adaptadores
2. **Reusabilidad**: Los Domain Services pueden usarse desde:
   - MCP Tools
   - Controllers HTTP
   - Artisan Commands
   - Jobs en cola
3. **Testabilidad**: Puedes testear Domain Services sin MCP
4. **Mantenibilidad**: Cambios en OpenAI API solo afectan OpenAIService

---

## 8.9 Resumen del Capítulo

### Conceptos Clave Aprendidos

1. **AIAssistantServer**: MCP Server especializado en herramientas con IA
2. **4 Herramientas AI**:
   - **GenerateTaskDescription**: Genera descripciones técnicas detalladas
   - **ImproveTaskTitle**: Mejora títulos siguiendo best practices
   - **SuggestPriority**: Calcula prioridad óptima con algoritmo de scoring
   - **AnalyzeProductivity**: Analiza métricas y detecta bottlenecks
3. **Domain Services**: Lógica de IA encapsulada y reutilizable
4. **Fallback Mechanisms**: Funcionalidad degradada cuando OpenAI no está disponible
5. **Arquitectura en Capas**: MCP Tools → Domain Services → Infrastructure

### Patrón de Diseño: Service-Oriented MCP Tools

```
MCP Tool (thin adapter)
    ↓
    Uses 1-2 Domain Services
    ↓
    Domain Services encapsulate business logic
    ↓
    Domain Services use Infrastructure (OpenAI, Repositories)
```

**Ventajas:**

- **Testable**: Mock Domain Services en unit tests
- **Reusable**: Domain Services usados desde múltiples lugares
- **Maintainable**: Cambios aislados en Domain Services

### Diferencias con TaskToolsServer (Capítulo 7)

| Aspecto | TaskToolsServer | AIAssistantServer |
|---------|----------------|-------------------|
| Propósito | CRUD operations | AI assistance |
| Complejidad | Simple (direct DB access) | Complex (AI + algorithms) |
| Latencia | Baja (<100ms) | Alta (1-5s para OpenAI) |
| Costo | Gratis | Requiere OpenAI API key |
| Errores | DB errors | DB + OpenAI + rate limiting |

### Flujo Completo de un Tool AI

```
User → Claude → MCP Tool → Schema Validation → Repository → Domain Service
   ↓                                                              ↓
   ↓                                                     OpenAI API / Algorithm
   ↓                                                              ↓
   ← Response ← Claude ← MCP Tool ← Response ← Domain Service ←─┘
```

---

🔜 **Próximo Capítulo:**
En el Capítulo 9 exploraremos **AnalyticsResourcesServer**, viendo cómo exponer métricas y datos analíticos como Recursos MCP que los LLMs pueden consultar.

---

**Estado del Tutorial:** Capítulos 1-8 de 15 completados ✓

---

# Capítulo 9: AnalyticsResourcesServer - MCP Resources (Datos de Solo Lectura)

## 9.1 ¿Qué son los MCP Resources?

Hasta ahora hemos trabajado con **MCP Tools** (Capítulos 7-8), que son **acciones** que el LLM puede ejecutar. En este capítulo introducimos un concepto fundamental diferente: **MCP Resources**.

### Diferencia entre Tools y Resources

| Aspecto | Tools (Chapters 7-8) | Resources (Chapter 9) |
|---------|---------------------|----------------------|
| **Propósito** | Ejecutar acciones | Consultar datos |
| **Operación** | Write (modifican estado) | Read-only (solo lectura) |
| **Ejemplos** | create_task, update_task, delete_task | team-metrics, project-metrics, user-productivity |
| **Parámetros** | JSON Schema con validación | URI patterns (e.g., `/resource/{id}`) |
| **Cuándo usar** | Para modificar datos | Para consultar información |
| **Equivalente REST** | POST, PUT, DELETE | GET |

### Analogía: Biblioteca vs Librería de Consulta

**Tools (Biblioteca tradicional):**
- Puedes **prestar libros** (create)
- Puedes **devolver libros** (update status)
- Puedes **reservar libros** (update assignment)
- Acciones que **modifican el estado del sistema**

**Resources (Librería de Consulta):**
- Puedes **consultar el catálogo** (team-metrics)
- Puedes **ver estadísticas de préstamos** (velocity-trends)
- Puedes **leer reportes de libros atrasados** (overdue-report)
- Consultas que **no modifican nada**, solo retornan información

---

## 9.2 Anatomía del AnalyticsResourcesServer

```php
<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class AnalyticsResourcesServer extends Server
{
    protected string $name = 'Analytics Resources';

    protected string $version = '1.0.0';

    protected string $instructions = <<<'MARKDOWN'
        # Analytics Resources Server

        This server provides read-only access to analytics and metrics data through MCP Resources.

        ## Available Resources

        1. **team-metrics**: Get overall team performance metrics
        2. **project-metrics/{project_id}**: Get detailed metrics for a specific project
        3. **user-productivity/{user_id}**: Get individual user productivity metrics
        4. **velocity-trends**: Get task velocity and trend analysis
        5. **overdue-report**: Get list of overdue tasks with analysis

        ## Usage Patterns

        Resources are read-only and automatically refresh with latest data.
        Use these resources to:
        - Monitor team performance in real-time
        - Identify bottlenecks and blockers
        - Track project health and progress
    MARKDOWN;

    protected array $tools = [];

    protected array $resources = [
        \App\Mcp\Resources\TeamMetrics::class,
        \App\Mcp\Resources\ProjectMetrics::class,
        \App\Mcp\Resources\UserProductivity::class,
        \App\Mcp\Resources\VelocityTrends::class,
        \App\Mcp\Resources\OverdueReport::class,
    ];

    protected array $prompts = [];
}
```

### Elementos Clave

**1. Tools Array vacío**
```php
protected array $tools = [];  // No tools in this server!
```

Este servidor **solo expone Resources**, no Tools. Separación de responsabilidades.

**2. Resources Array (5 recursos)**
```php
protected array $resources = [
    \App\Mcp\Resources\TeamMetrics::class,
    \App\Mcp\Resources\ProjectMetrics::class,
    \App\Mcp\Resources\UserProductivity::class,
    \App\Mcp\Resources\VelocityTrends::class,
    \App\Mcp\Resources\OverdueReport::class,
];
```

Cada recurso expone diferentes vistas de los datos analíticos.

---

## 9.3 Resource 1: TeamMetrics (Métricas Generales del Equipo)

### Propósito

Proporcionar una **vista general del rendimiento del equipo completo**: velocidad, tasa de completitud, distribución de tareas por estado y prioridad.

### Código Completo (106 líneas)

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
    protected string $uri = 'team-metrics';

    protected string $description = <<<'MARKDOWN'
        Get overall team performance metrics including task completion rates,
        status distribution, priority breakdown, and velocity trends.
    MARKDOWN;

    public function __construct(
        private readonly MetricsCollector $metricsCollector,
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        // Get date range from parameters or use defaults (last 30 days)
        $days = $request->input('days', 30);
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        // Collect comprehensive metrics
        $metrics = $this->metricsCollector->collectMetrics($startDate, $endDate);

        // Calculate velocity (tasks completed per day)
        $tasksCompleted = $metrics['tasks']['completed'] ?? 0;
        $velocity = $days > 0 ? round($tasksCompleted / $days, 2) : 0;

        // Calculate completion rate
        $totalTasks = $metrics['tasks']['total'] ?? 1;
        $completionRate = round(($tasksCompleted / $totalTasks) * 100, 1);

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
            ],
            'status_distribution' => [
                'pending' => $metrics['tasks']['pending'] ?? 0,
                'in_progress' => $metrics['tasks']['in_progress'] ?? 0,
                'review' => $metrics['tasks']['review'] ?? 0,
                'completed' => $tasksCompleted,
                'blocked' => $metrics['tasks']['blocked'] ?? 0,
            ],
            'priority_breakdown' => [
                'low' => $metrics['tasks']['priority']['low'] ?? 0,
                'medium' => $metrics['tasks']['priority']['medium'] ?? 0,
                'high' => $metrics['tasks']['priority']['high'] ?? 0,
                'critical' => $metrics['tasks']['priority']['critical'] ?? 0,
            ],
            'health_indicators' => [
                'blocked_tasks' => $metrics['tasks']['blocked'] ?? 0,
                'overdue_tasks' => $metrics['tasks']['overdue'] ?? 0,
                'high_priority_pending' => $this->taskRepository->findByPriority('high')
                    ->where('status', 'pending')->count(),
            ],
        ]);
    }
}
```

### Características Clave

#### 1. **URI Simple (sin parámetros)**

```php
protected string $uri = 'team-metrics';
```

No usa parámetros en el URI. Es un recurso de **vista general**.

#### 2. **Parámetros Opcionales en Request**

```php
$days = $request->input('days', 30);
```

A diferencia de Tools (que usan JSON Schema), Resources reciben parámetros opcionales via `$request->input()`.

**Claude puede llamar:**
```
GET team-metrics              // Default: 30 días
GET team-metrics?days=7       // Últimos 7 días
GET team-metrics?days=90      // Últimos 90 días
```

#### 3. **Respuesta Estructurada**

```json
{
  "period": { "start": "2025-10-14", "end": "2025-11-13", "days": 30 },
  "overview": {
    "total_tasks": 150,
    "tasks_completed": 98,
    "completion_rate": "65.3%",
    "velocity": "3.27 tasks/day"
  },
  "status_distribution": {
    "pending": 15,
    "in_progress": 22,
    "review": 8,
    "completed": 98,
    "blocked": 7
  },
  "priority_breakdown": {
    "low": 30,
    "medium": 80,
    "high": 35,
    "critical": 5
  },
  "health_indicators": {
    "blocked_tasks": 7,
    "overdue_tasks": 12,
    "high_priority_pending": 8
  }
}
```

### Ejemplo de Uso con Claude

```
User: Show me team performance for last 2 weeks

Claude: I'll fetch team metrics for the last 14 days.
        [Accesses team-metrics resource with days=14]

Resource Response:
{
  "period": { "days": 14, "start": "2025-10-30", "end": "2025-11-13" },
  "overview": {
    "total_tasks": 82,
    "tasks_completed": 52,
    "completion_rate": "63.4%",
    "velocity": "3.71 tasks/day"
  },
  "status_distribution": {
    "pending": 8,
    "in_progress": 14,
    "review": 5,
    "completed": 52,
    "blocked": 3
  },
  ...
}

Claude: Here's your team's performance over the last 2 weeks:

📊 **Overview:**
- Total tasks: 82
- Completed: 52 (63.4% completion rate)
- Velocity: 3.71 tasks/day (strong pace!)

📈 **Current Work:**
- 8 pending tasks
- 14 in progress
- 5 awaiting review
- ⚠️ 3 blocked tasks

🎯 **Priority Distribution:**
- 3 critical tasks
- 18 high priority
- 45 medium priority
- 16 low priority

⚠️ **Health Indicators:**
- 3 blocked tasks (needs attention)
- 6 overdue tasks (requires review)
- 4 high-priority tasks still pending

Overall, the team is performing well with a solid velocity of 3.7 tasks/day.
However, I recommend addressing the 3 blocked tasks to prevent bottlenecks.
```

---

## 9.4 Resource 2: ProjectMetrics (Métricas por Proyecto)

### Propósito

Proporcionar métricas **detalladas para un proyecto específico**, incluyendo health score, contribuciones del equipo, y varianza de horas.

### Código Completo (182 líneas)

```php
<?php

namespace App\Mcp\Resources;

use App\Domain\TaskManagement\Contracts\Repositories\ProjectRepositoryInterface;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class ProjectMetrics extends Resource
{
    protected string $uri = 'project-metrics/{project_id}';

    protected string $description = <<<'MARKDOWN'
        Get detailed metrics for a specific project including completion percentage,
        task breakdown, average completion time, and team member contributions.
    MARKDOWN;

    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        $projectId = $request->input('project_id');

        if (!$projectId) {
            return Response::json([
                'error' => 'project_id is required',
                'usage' => 'Provide project_id in the URI: project-metrics/1',
            ]);
        }

        $project = $this->projectRepository->findById($projectId);

        if (!$project) {
            return Response::json(['error' => "Project #{$projectId} not found"]);
        }

        // Get all tasks for this project
        $tasks = $this->taskRepository->findByProject($projectId);
        $totalTasks = $tasks->count();

        // Status breakdown
        $statusBreakdown = [
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'review' => $tasks->where('status', 'review')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'blocked' => $tasks->where('status', 'blocked')->count(),
        ];

        // Calculate completion percentage
        $completedTasks = $statusBreakdown['completed'];
        $completionPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // Team member contributions
        $contributorStats = [];
        $tasksGroupedByAssignee = $tasks->groupBy('assigned_to');
        foreach ($tasksGroupedByAssignee as $userId => $userTasks) {
            if ($userId) {
                $user = $userTasks->first()->assignedTo;
                $contributorStats[] = [
                    'user_id' => $userId,
                    'user_name' => $user->name ?? 'Unknown',
                    'total_tasks' => $userTasks->count(),
                    'completed' => $userTasks->where('status', 'completed')->count(),
                    'in_progress' => $userTasks->where('status', 'in_progress')->count(),
                ];
            }
        }

        // Calculate estimated vs actual hours
        $totalEstimatedHours = $tasks->sum('estimated_hours') ?? 0;
        $totalActualHours = $tasks->sum('actual_hours') ?? 0;
        $hoursDelta = $totalActualHours - $totalEstimatedHours;

        return Response::json([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'owner' => $project->owner->name ?? 'Unassigned',
            ],
            'summary' => [
                'total_tasks' => $totalTasks,
                'completion_percentage' => $completionPercentage.'%',
                'overdue_tasks' => $tasks->filter(fn($t) => $t->due_date && $t->due_date->isPast())->count(),
            ],
            'status_breakdown' => $statusBreakdown,
            'time_tracking' => [
                'total_estimated_hours' => $totalEstimatedHours,
                'total_actual_hours' => $totalActualHours,
                'hours_delta' => $hoursDelta,
                'variance_percentage' => $totalEstimatedHours > 0
                    ? round(($hoursDelta / $totalEstimatedHours) * 100, 1).'%'
                    : 'N/A',
            ],
            'team_contributions' => $contributorStats,
            'health_score' => $this->calculateProjectHealth(
                $completionPercentage,
                $statusBreakdown['blocked']
            ),
        ]);
    }

    private function calculateProjectHealth(float $completionPercentage, int $blockedTasks): array
    {
        $score = 100;

        if ($completionPercentage < 30) {
            $score -= 20;
        } elseif ($completionPercentage < 50) {
            $score -= 10;
        }

        $score -= min($blockedTasks * 10, 30);
        $score = max(0, $score);

        $status = match(true) {
            $score >= 90 => 'excellent',
            $score >= 80 => 'good',
            $score >= 60 => 'fair',
            $score >= 40 => 'poor',
            default => 'critical',
        };

        return ['score' => $score, 'status' => $status];
    }
}
```

### Características Clave

#### 1. **URI con Parámetro**

```php
protected string $uri = 'project-metrics/{project_id}';
```

**Patrón REST-like:** El `{project_id}` es parte del URI.

**Claude puede llamar:**
```
GET project-metrics/1    // Proyecto 1
GET project-metrics/42   // Proyecto 42
```

#### 2. **Health Score Calculation**

```php
private function calculateProjectHealth(float $completionPercentage, int $blockedTasks): array
{
    $score = 100;

    // Deduct for low completion
    if ($completionPercentage < 30) {
        $score -= 20;
    }

    // Deduct for blocked tasks (max -30)
    $score -= min($blockedTasks * 10, 30);

    $status = match(true) {
        $score >= 90 => 'excellent',
        $score >= 80 => 'good',
        $score >= 60 => 'fair',
        $score >= 40 => 'poor',
        default => 'critical',
    };

    return ['score' => $score, 'status' => $status];
}
```

**Scoring Algorithm:**
- Comienza con 100 puntos
- Resta puntos por baja completitud (-10 o -20)
- Resta por tareas bloqueadas (10 puntos cada una, máximo -30)

#### 3. **Team Contributions**

```php
$contributorStats = [];
$tasksGroupedByAssignee = $tasks->groupBy('assigned_to');
foreach ($tasksGroupedByAssignee as $userId => $userTasks) {
    $contributorStats[] = [
        'user_id' => $userId,
        'user_name' => $user->name,
        'total_tasks' => $userTasks->count(),
        'completed' => $userTasks->where('status', 'completed')->count(),
        'in_progress' => $userTasks->where('status', 'in_progress')->count(),
    ];
}
```

Agrupa tareas por asignado para ver contribuciones individuales al proyecto.

### Ejemplo de Uso

```
User: How is project #5 doing?

Claude: I'll get detailed metrics for project #5.
        [Accesses project-metrics/5 resource]

Resource Response:
{
  "project": {
    "id": 5,
    "name": "E-Commerce Redesign",
    "status": "active",
    "owner": "Sarah Johnson"
  },
  "summary": {
    "total_tasks": 45,
    "completion_percentage": "68.9%",
    "overdue_tasks": 3
  },
  "status_breakdown": {
    "pending": 5,
    "in_progress": 8,
    "review": 1,
    "completed": 31,
    "blocked": 0
  },
  "time_tracking": {
    "total_estimated_hours": 240,
    "total_actual_hours": 275,
    "hours_delta": 35,
    "variance_percentage": "14.6%"
  },
  "team_contributions": [
    { "user_name": "Alice Chen", "total_tasks": 18, "completed": 14 },
    { "user_name": "Bob Martinez", "total_tasks": 15, "completed": 10 },
    { "user_name": "Carol White", "total_tasks": 12, "completed": 7 }
  ],
  "health_score": {
    "score": 90,
    "status": "excellent"
  }
}

Claude: **Project: E-Commerce Redesign**
Owner: Sarah Johnson | Status: Active

📊 **Progress:**
- 31 of 45 tasks completed (68.9%)
- Health Score: 90/100 (Excellent!)
- 3 tasks overdue (needs attention)

📈 **Current Status:**
- 5 pending tasks
- 8 in progress
- 1 in review
- ✅ No blocked tasks

⏱️ **Time Tracking:**
- Estimated: 240 hours
- Actual: 275 hours
- Variance: +35 hours (14.6% over estimate)

👥 **Team Contributions:**
1. Alice Chen: 14/18 completed (78%)
2. Bob Martinez: 10/15 completed (67%)
3. Carol White: 7/12 completed (58%)

💡 **Insights:**
- Project health is excellent with 90/100 score
- Team is delivering, but running 14.6% over time estimates
- Consider reviewing time estimation process
- Address 3 overdue tasks to maintain momentum
```

---

## 9.5 Resource 3: UserProductivity (Productividad Individual)

### Propósito

Analizar la productividad de un usuario individual con workload balance, productivity score, y recent activity.

### Código Abreviado (228 líneas - mostrando partes clave)

```php
class UserProductivity extends Resource
{
    protected string $uri = 'user-productivity/{user_id}';

    public function handle(Request $request): Response
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);

        // Get all tasks assigned to this user
        $allTasks = $this->taskRepository->findByAssignee($userId);

        // Calculate metrics
        $completionRate = /* ... */;
        $averageCompletionTime = /* ... */;
        $velocity = /* ... */;
        $overdueTasks = /* ... */;

        // Workload balance
        $currentWorkload = $allTasks->whereIn('status', ['pending', 'in_progress']);
        $priorityWorkload = [
            'low' => $currentWorkload->where('priority', 'low')->count(),
            'medium' => $currentWorkload->where('priority', 'medium')->count(),
            'high' => $currentWorkload->where('priority', 'high')->count(),
            'critical' => $currentWorkload->where('priority', 'critical')->count(),
        ];

        // Recent activity timeline (last 10 tasks)
        $recentActivity = $allTasks->sortByDesc('updated_at')->take(10)->map(/*...*/);

        return Response::json([
            'user' => ['id' => $user->id, 'name' => $user->name],
            'overview' => [
                'total_tasks' => $totalTasks,
                'current_workload' => $currentWorkload->count(),
                'completion_rate' => $completionRate.'%',
                'velocity' => $velocity.' tasks/day',
            ],
            'priority_workload' => $priorityWorkload,
            'workload_balance' => $this->calculateWorkloadBalance($priorityWorkload),
            'recent_activity' => $recentActivity,
            'productivity_score' => $this->calculateProductivityScore(/*...*/),
        ]);
    }

    private function calculateWorkloadBalance(array $priorityWorkload): array
    {
        $total = array_sum($priorityWorkload);
        $criticalCount = $priorityWorkload['critical'];
        $highCount = $priorityWorkload['high'];

        if ($criticalCount > 5 || $highCount > 10) {
            return [
                'status' => 'overloaded',
                'message' => 'High number of critical/high priority tasks',
                'recommendation' => 'Consider redistributing some tasks',
            ];
        } elseif ($total > 20) {
            return ['status' => 'busy', 'message' => 'Large number of active tasks'];
        } else {
            return ['status' => 'balanced', 'message' => 'Workload appears well balanced'];
        }
    }

    private function calculateProductivityScore(float $completionRate, float $velocity, int $overdueTasks): array
    {
        $score = 0;

        // Completion rate (max 40 points)
        $score += min($completionRate * 0.4, 40);

        // Velocity (max 30 points)
        $score += min($velocity * 30, 30);

        // Penalize for overdue tasks (max -30)
        $score -= min($overdueTasks * 10, 30);

        $score = max(0, min(100, $score));

        $rating = match(true) {
            $score >= 80 => 'excellent',
            $score >= 60 => 'good',
            $score >= 40 => 'fair',
            default => 'needs_improvement',
        };

        return ['score' => round($score, 1), 'rating' => $rating];
    }
}
```

### Algoritmos Destacados

#### 1. **Workload Balance (Balanceo de Carga)**

```php
if ($criticalCount > 5 || $highCount > 10) {
    return ['status' => 'overloaded', ...];
} elseif ($total > 20) {
    return ['status' => 'busy', ...];
} else {
    return ['status' => 'balanced', ...];
}
```

**Detecta sobrecarga:**
- Más de 5 tareas critical → Overloaded
- Más de 10 tareas high → Overloaded
- Más de 20 tareas activas → Busy

#### 2. **Productivity Score (Puntuación de Productividad)**

```
Score = (Completion Rate × 0.4) + (Velocity × 30) - (Overdue × 10)
        ↑                         ↑                 ↑
    Max 40 puntos            Max 30 puntos     Penalización
```

**Ejemplo:**
```
User con 75% completion rate, 1.2 tasks/day, 2 overdue:
Score = (75 × 0.4) + (1.2 × 30) - (2 × 10)
      = 30 + 36 - 20
      = 46 points → "fair" rating
```

---

## 9.6 Resource 4: VelocityTrends (Tendencias de Velocidad)

### Propósito

Analizar tendencias de velocidad del equipo con forecasting, consistency analysis, y burndown data.

### Características Destacadas (285 líneas)

```php
class VelocityTrends extends Resource
{
    protected string $uri = 'velocity-trends';

    public function handle(Request $request): Response
    {
        $days = $request->input('days', 30);
        $projectId = $request->input('project_id', null);

        // Calculate daily velocity
        $dailyVelocity = $this->calculateDailyVelocity($tasks, $startDate, $endDate);

        // Calculate trend (first half vs second half)
        $trend = $this->calculateTrend($dailyVelocity);

        // Forecast completion date
        $activeTasks = $allTasks->whereIn('status', ['pending', 'in_progress'])->count();
        $forecastDays = $averageVelocity > 0 ? ceil($activeTasks / $averageVelocity) : null;
        $forecastDate = $forecastDays ? Carbon::now()->addDays($forecastDays)->toDateString() : 'N/A';

        // Calculate consistency (standard deviation)
        $consistency = $this->calculateConsistency($dailyVelocity);

        return Response::json([
            'velocity_metrics' => [
                'average_daily' => $averageVelocity.' tasks/day',
                'trend' => $trend,
                'consistency' => $consistency,
            ],
            'daily_velocity' => $dailyVelocity,
            'forecast' => [
                'active_tasks' => $activeTasks,
                'estimated_days_to_complete' => $forecastDays,
                'forecast_completion_date' => $forecastDate,
                'confidence' => $this->calculateForecastConfidence($consistency),
            ],
            'insights' => $this->generateInsights($trend, $consistency, $averageVelocity),
        ]);
    }

    private function calculateTrend(array $dailyVelocity): array
    {
        $midpoint = (int) floor(count($dailyVelocity) / 2);
        $firstHalf = array_slice($dailyVelocity, 0, $midpoint);
        $secondHalf = array_slice($dailyVelocity, $midpoint);

        $firstHalfAvg = array_sum(array_column($firstHalf, 'completed')) / count($firstHalf);
        $secondHalfAvg = array_sum(array_column($secondHalf, 'completed')) / count($secondHalf);

        $change = $secondHalfAvg - $firstHalfAvg;
        $percentageChange = $firstHalfAvg > 0 ? round(($change / $firstHalfAvg) * 100, 1) : 0;

        $direction = $percentageChange > 10 ? 'increasing'
                   : ($percentageChange < -10 ? 'decreasing' : 'stable');

        return [
            'direction' => $direction,
            'percentage_change' => $percentageChange.'%',
            'first_half_avg' => round($firstHalfAvg, 2),
            'second_half_avg' => round($secondHalfAvg, 2),
        ];
    }

    private function calculateConsistency(array $dailyVelocity): array
    {
        $values = array_column($dailyVelocity, 'completed');
        $average = array_sum($values) / count($values);

        $variance = 0;
        foreach ($values as $value) {
            $variance += pow($value - $average, 2);
        }
        $stdDev = sqrt($variance / count($values));

        $status = $stdDev > $average ? 'inconsistent'
                : ($stdDev > $average * 0.5 ? 'moderate' : 'consistent');

        return [
            'status' => $status,
            'standard_deviation' => round($stdDev, 2),
            'coefficient_of_variation' => round(($stdDev / $average) * 100, 1).'%',
        ];
    }
}
```

### Algoritmos Avanzados

#### 1. **Trend Analysis (Análisis de Tendencia)**

```
Period: 30 days

First Half (15 days):     Second Half (15 days):
Day 1-15                  Day 16-30
Avg: 3.2 tasks/day        Avg: 4.1 tasks/day

Change: +0.9 tasks/day
Percentage: +28.1%  →  Trend: "increasing"
```

#### 2. **Consistency Analysis (Desviación Estándar)**

```
Daily completions: [2, 4, 3, 5, 2, 4, 3, 2, 4, 5]
Average: 3.4 tasks/day
Standard Deviation: 1.07

Coefficient of Variation: (1.07 / 3.4) × 100 = 31.5%

If CV < 50%: "consistent"
If 50% < CV < 100%: "moderate"
If CV > 100%: "inconsistent"
```

#### 3. **Forecasting**

```
Active Tasks: 45
Average Velocity: 3.2 tasks/day

Estimated Days = 45 / 3.2 = 14.06 days ≈ 14 days

Forecast Completion Date: Today + 14 days = 2025-11-27

Confidence Level:
- Consistent velocity → "high" confidence
- Moderate variance → "medium" confidence
- Inconsistent → "low" confidence
```

---

## 9.7 Resource 5: OverdueReport (Reporte de Tareas Atrasadas)

### Propósito

Generar un reporte comprehensivo de todas las tareas atrasadas con risk assessment, agrupación, y recomendaciones accionables.

### Código Abreviado (304 líneas - partes clave)

```php
class OverdueReport extends Resource
{
    protected string $uri = 'overdue-report';

    public function handle(Request $request): Response
    {
        // Filter overdue tasks
        $overdueTasks = $allTasks->filter(function ($task) {
            return $task->due_date
                && $task->due_date->isPast()
                && $task->status !== 'completed';
        });

        if ($overdueTasks->isEmpty()) {
            return Response::json([
                'summary' => ['total_overdue' => 0, 'message' => 'No overdue tasks'],
                'tasks' => [],
            ]);
        }

        // Map tasks with risk assessment
        $tasksList = $overdueTasks->map(function ($task) {
            $daysOverdue = abs($task->due_date->diffInDays(Carbon::now()));
            $risk = $this->assessRisk($task, $daysOverdue);

            return [
                'task_id' => $task->id,
                'title' => $task->title,
                'days_overdue' => $daysOverdue,
                'risk_level' => $risk['level'],
                'risk_score' => $risk['score'],
            ];
        })->sortByDesc('risk_score')->values();

        // Group by project and assignee
        $groupedByProject = $overdueTasks->groupBy('project_id')->map(/*...*/);
        $groupedByAssignee = $overdueTasks->groupBy('assigned_to')->map(/*...*/);

        // Generate recommendations
        $recommendations = $this->generateRecommendations([
            'total' => $totalOverdue,
            'critical' => $criticalOverdue,
            'blocked' => $blockedOverdue,
        ]);

        return Response::json([
            'summary' => [
                'total_overdue' => $totalOverdue,
                'critical_priority' => $criticalOverdue,
                'average_days_overdue' => $avgDaysOverdue,
            ],
            'impact_assessment' => $this->calculateImpact($overdueTasks),
            'tasks' => $tasksList,
            'grouped_by_project' => $groupedByProject,
            'grouped_by_assignee' => $groupedByAssignee,
            'recommendations' => $recommendations,
            'urgency_level' => $this->determineUrgencyLevel(/*...*/),
        ]);
    }

    private function assessRisk($task, int $daysOverdue): array
    {
        $score = 0;

        // Days overdue (max 40 points)
        if ($daysOverdue > 30) {
            $score += 40;
        } elseif ($daysOverdue > 14) {
            $score += 30;
        } elseif ($daysOverdue > 7) {
            $score += 20;
        } else {
            $score += 10;
        }

        // Priority (max 30 points)
        $score += match ($task->priority->value) {
            'critical' => 30,
            'high' => 20,
            'medium' => 10,
            'low' => 5,
        };

        // Status (max 30 points)
        if ($task->status === 'blocked') {
            $score += 30;
        }

        $level = $score >= 70 ? 'critical'
               : ($score >= 50 ? 'high'
               : ($score >= 30 ? 'medium' : 'low'));

        return ['score' => $score, 'level' => $level];
    }

    private function generateRecommendations(array $stats): array
    {
        $recommendations = [];

        if ($stats['critical'] > 0) {
            $recommendations[] = [
                'priority' => 'urgent',
                'action' => 'Immediately address '.$stats['critical'].' critical overdue tasks',
                'reason' => 'Critical tasks pose highest risk to project success',
            ];
        }

        if ($stats['blocked'] > 0) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'Unblock '.$stats['blocked'].' blocked tasks',
                'reason' => 'Blocked tasks prevent progress on dependent work',
            ];
        }

        return $recommendations;
    }
}
```

### Risk Scoring Algorithm

```
Risk Score = Days Overdue (40 max) + Priority (30 max) + Status (30 max)

Example:
Task: "Fix payment gateway"
- 25 days overdue → +40 points
- Priority: critical → +30 points
- Status: blocked → +30 points
──────────────────────────
Total: 100 points → Risk Level: CRITICAL

Urgency Levels:
- 0-29:  low risk
- 30-49: medium risk
- 50-69: high risk
- 70+:   CRITICAL risk
```

---

## 9.8 Comparación de los 5 Resources

| Resource | URI Pattern | Parameters | Primary Metric | Special Feature |
|----------|------------|------------|----------------|-----------------|
| **TeamMetrics** | `team-metrics` | days (optional) | Velocity, completion rate | Health indicators |
| **ProjectMetrics** | `project-metrics/{id}` | project_id (required) | Completion %, health score | Team contributions |
| **UserProductivity** | `user-productivity/{id}` | user_id (required) | Productivity score | Workload balance |
| **VelocityTrends** | `velocity-trends` | days, project_id | Trend analysis | Forecasting |
| **OverdueReport** | `overdue-report` | None | Overdue count | Risk assessment |

---

## 9.9 Resumen del Capítulo

### Conceptos Clave Aprendidos

1. **MCP Resources vs Tools**:
   - Resources = Read-only data sources
   - Tools = Write operations (actions)

2. **URI Patterns**:
   - Simple: `team-metrics`
   - Parametrized: `project-metrics/{project_id}`

3. **5 Resources Analíticos**:
   - TeamMetrics: Vista general del equipo
   - ProjectMetrics: Métricas por proyecto con health score
   - UserProductivity: Análisis individual con workload balance
   - VelocityTrends: Tendencias y forecasting
   - OverdueReport: Tareas atrasadas con risk assessment

4. **Algoritmos Avanzados**:
   - Health scoring
   - Risk assessment
   - Trend analysis (first/second half comparison)
   - Consistency calculation (standard deviation)
   - Forecasting (velocity-based)

### Patrones de Diseño

**1. Read-Only by Nature**

```php
// Resources NEVER modify data
public function handle(Request $request): Response
{
    $data = $this->repository->findSomething();  // Read only
    return Response::json($data);                // Return data
}
```

**2. URI-Based Routing**

```php
protected string $uri = 'resource/{param}';
$param = $request->input('param');  // Extract from URI
```

**3. Comprehensive Responses**

```json
{
  "summary": {},
  "details": {},
  "breakdowns": {},
  "recommendations": [],
  "insights": []
}
```

---

**Próximo**: Capítulo 10 - Eloquent Models y Relationships en Profundidad

---

**Estado del Tutorial:** Capítulos 1-9 de 15 completados ✓
