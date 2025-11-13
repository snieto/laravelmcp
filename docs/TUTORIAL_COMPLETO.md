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

¿Listo para continuar con el Capítulo 2?
