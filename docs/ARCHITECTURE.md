# ARCHITECTURE.md - Arquitectura Técnica

## 🏛️ Visión General

TaskMaster AI Platform utiliza una arquitectura hexagonal (ports & adapters) combinada con Domain-Driven Design (DDD) para lograr:

- ✅ Separación clara de responsabilidades
- ✅ Testabilidad completa
- ✅ Independencia de frameworks externos
- ✅ Escalabilidad y mantenibilidad

---

## 📐 Diagrama de Capas

```
┌─────────────────────────────────────────────────────────┐
│                  PRESENTATION LAYER                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Livewire   │  │   API REST   │  │  MCP Servers │  │
│  │  Components  │  │  Controllers │  │   (Ports)    │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└────────────────────────────┬────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────┐
│                  APPLICATION LAYER                       │
│  ┌──────────────────────────────────────────────────┐   │
│  │              USE CASES (Casos de Uso)            │   │
│  │  • CreateTaskUseCase                             │   │
│  │  • GenerateTaskDescriptionUseCase                │   │
│  │  • GetAnalyticsUseCase                           │   │
│  └──────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────┐   │
│  │                     DTOs                          │   │
│  │  • CreateTaskDTO                                 │   │
│  │  • TaskResponseDTO                               │   │
│  └──────────────────────────────────────────────────┘   │
└────────────────────────────┬────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────┐
│                    DOMAIN LAYER                          │
│  ┌─────────────────────────────────────────────┐        │
│  │         BOUNDED CONTEXT: Task Management     │        │
│  │  ┌──────────────┐  ┌──────────────────┐    │        │
│  │  │   Entities   │  │  Value Objects   │    │        │
│  │  │  • Task      │  │  • Priority      │    │        │
│  │  │  • Project   │  │  • Status        │    │        │
│  │  └──────────────┘  └──────────────────┘    │        │
│  │  ┌──────────────────────────────────────┐  │        │
│  │  │       Domain Services                │  │        │
│  │  │  • TaskStatusManager                 │  │        │
│  │  │  • TaskPriorityCalculator            │  │        │
│  │  └──────────────────────────────────────┘  │        │
│  │  ┌──────────────────────────────────────┐  │        │
│  │  │        Repositories (Interfaces)     │  │        │
│  │  │  • TaskRepositoryInterface           │  │        │
│  │  │  • ProjectRepositoryInterface        │  │        │
│  │  └──────────────────────────────────────┘  │        │
│  └─────────────────────────────────────────────┘        │
│                                                          │
│  ┌─────────────────────────────────────────────┐        │
│  │       BOUNDED CONTEXT: AI Integration        │        │
│  │  • OpenAIService                             │        │
│  │  • PromptBuilder                             │        │
│  │  • ContentGenerator                          │        │
│  └─────────────────────────────────────────────┘        │
│                                                          │
│  ┌─────────────────────────────────────────────┐        │
│  │    BOUNDED CONTEXT: Analytics & Reporting    │        │
│  │  • AnalyticsService                          │        │
│  │  • ReportGenerator                           │        │
│  └─────────────────────────────────────────────┘        │
└────────────────────────────┬────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────┐
│                INFRASTRUCTURE LAYER                      │
│  ┌──────────────────────────────────────────────────┐   │
│  │          Persistence (Adapters)                  │   │
│  │  • EloquentTaskRepository                        │   │
│  │  • EloquentProjectRepository                     │   │
│  └──────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────┐   │
│  │          External Services                       │   │
│  │  • OpenAIClient                                  │   │
│  │  • CacheService                                  │   │
│  └──────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────┐   │
│  │          MCP Server Implementations              │   │
│  │  • TaskToolsServer                               │   │
│  │  • AIAssistantServer                             │   │
│  └──────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────┘
```

---

## 🎯 Bounded Contexts

### 1. Task Management Context

**Responsabilidad**: Gestión completa del ciclo de vida de tareas y proyectos.

**Entidades**:
```php
// Domain/TaskManagement/Models/Task.php
class Task extends Model
{
    // Propiedades del modelo
    public function status(): Status;
    public function priority(): Priority;
    public function assignee(): ?User;

    // Métodos de dominio
    public function markAsCompleted(): void;
    public function assignTo(User $user): void;
    public function changePriority(Priority $priority): void;
}
```

**Value Objects**:
```php
// Domain/TaskManagement/ValueObjects/Status.php
enum Status: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case COMPLETED = 'completed';
    case BLOCKED = 'blocked';

    public function canTransitionTo(Status $newStatus): bool;
}

// Domain/TaskManagement/ValueObjects/Priority.php
enum Priority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function score(): int;
}
```

**Repository Interface**:
```php
// Domain/TaskManagement/Repositories/TaskRepositoryInterface.php
interface TaskRepositoryInterface
{
    public function findById(int $id): ?Task;
    public function save(Task $task): void;
    public function delete(Task $task): void;
    public function findByFilters(array $filters): Collection;
}
```

**Domain Services**:
```php
// Domain/TaskManagement/Services/TaskStatusManager.php
class TaskStatusManager
{
    public function canTransition(Task $task, Status $newStatus): bool;
    public function transition(Task $task, Status $newStatus): void;
    public function getAvailableTransitions(Task $task): array;
}
```

**Domain Events**:
```php
// Domain/TaskManagement/Events/TaskCreated.php
class TaskCreated implements DomainEvent
{
    public function __construct(
        public readonly Task $task,
        public readonly Carbon $occurredAt
    ) {}
}

// Domain/TaskManagement/Events/TaskAssigned.php
class TaskAssigned implements DomainEvent
{
    public function __construct(
        public readonly Task $task,
        public readonly User $assignee,
        public readonly Carbon $occurredAt
    ) {}
}
```

### 2. AI Integration Context

**Responsabilidad**: Integración con OpenAI y generación de contenido inteligente.

**Servicios**:
```php
// Domain/AIIntegration/Services/OpenAIService.php
class OpenAIService
{
    public function __construct(
        private readonly OpenAI $client,
        private readonly PromptBuilder $promptBuilder
    ) {}

    public function generateTaskDescription(
        string $title,
        ?string $context = null,
        string $technicalLevel = 'mid'
    ): string;

    public function summarizeProject(Project $project): string;

    public function suggestPriorities(Collection $tasks): array;
}

// Domain/AIIntegration/Services/PromptBuilder.php
class PromptBuilder
{
    public function buildTaskDescriptionPrompt(
        string $title,
        ?string $context,
        string $level
    ): string;

    public function buildSummarizationPrompt(array $data): string;
}
```

**Contratos**:
```php
// Domain/AIIntegration/Contracts/AIServiceInterface.php
interface AIServiceInterface
{
    public function complete(string $prompt, array $options = []): string;
    public function chat(array $messages, array $options = []): string;
}
```

### 3. Analytics & Reporting Context

**Responsabilidad**: Cálculo de métricas, estadísticas y generación de reportes.

**Servicios**:
```php
// Domain/Analytics/Services/AnalyticsService.php
class AnalyticsService
{
    public function getProductivityMetrics(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): ProductivityMetrics;

    public function getTeamStats(): TeamStats;

    public function getProjectHealth(Project $project): ProjectHealth;
}

// Domain/Analytics/ValueObjects/ProductivityMetrics.php
class ProductivityMetrics
{
    public function __construct(
        public readonly int $tasksCompleted,
        public readonly int $tasksCreated,
        public readonly float $averageCompletionTimeHours,
        public readonly float $velocity,
        public readonly float $burnRate
    ) {}

    public function toArray(): array;
}
```

---

## 🔄 Flujo de Datos

### Ejemplo: Crear Tarea desde MCP

```
┌─────────────┐
│ MCP Client  │
│ (Claude)    │
└──────┬──────┘
       │ POST /mcp/tasks
       │ tool: create_task
       ▼
┌──────────────────────┐
│ TaskToolsServer      │ ← Infrastructure Layer
│ (MCP Server)         │
└──────┬───────────────┘
       │ validates request
       │ creates DTO
       ▼
┌──────────────────────┐
│ CreateTaskUseCase    │ ← Application Layer
│                      │
└──────┬───────────────┘
       │ orchestrates
       ▼
┌──────────────────────┐
│ TaskRepositoryImpl   │ ← Infrastructure Layer
│ (Eloquent)           │
└──────┬───────────────┘
       │ persists
       ▼
┌──────────────────────┐
│ Task Entity          │ ← Domain Layer
│ (Eloquent Model)     │
└──────┬───────────────┘
       │ emits event
       ▼
┌──────────────────────┐
│ TaskCreated Event    │ ← Domain Layer
│                      │
└──────────────────────┘
```

### Ejemplo: Generar Descripción con IA

```
┌─────────────┐
│ MCP Client  │
└──────┬──────┘
       │ tool: generate_task_description
       ▼
┌──────────────────────────────┐
│ AIAssistantServer            │ ← Infrastructure/MCP
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ GenerateTaskDescriptionUC    │ ← Application
└──────┬───────────────────────┘
       │
       ├─────────────────────────┐
       │                         │
       ▼                         ▼
┌──────────────────┐    ┌──────────────────┐
│ TaskRepository   │    │ OpenAIService    │ ← Domain
└──────┬───────────┘    └──────┬───────────┘
       │                       │
       │ get task             │ generate
       ▼                       ▼
┌──────────────────┐    ┌──────────────────┐
│ Task Entity      │    │ OpenAI Client    │ ← Infrastructure
└──────────────────┘    └──────────────────┘
```

---

## 🧩 Dependency Injection

### Service Container Bindings

```php
// app/Providers/AppServiceProvider.php

public function register(): void
{
    // Repositories
    $this->app->bind(
        TaskRepositoryInterface::class,
        EloquentTaskRepository::class
    );

    $this->app->bind(
        ProjectRepositoryInterface::class,
        EloquentProjectRepository::class
    );

    // Services
    $this->app->singleton(OpenAIService::class, function ($app) {
        return new OpenAIService(
            OpenAI::client(config('openai.api_key')),
            $app->make(PromptBuilder::class)
        );
    });

    $this->app->singleton(AnalyticsService::class);

    // AI Interface
    $this->app->bind(
        AIServiceInterface::class,
        OpenAIService::class
    );
}
```

---

## 🗄️ Base de Datos

### Diagrama ER

```
┌─────────────────┐         ┌─────────────────┐
│     users       │         │    projects     │
├─────────────────┤         ├─────────────────┤
│ id              │         │ id              │
│ name            │         │ name            │
│ email           │         │ description     │
│ password        │         │ owner_id  ──────┼──┐
│ created_at      │         │ status          │  │
│ updated_at      │         │ created_at      │  │
└────────┬────────┘         └────────┬────────┘  │
         │                           │            │
         │ 1                      1  │            │
         │                           │            │
         │                         * │            │
         │                  ┌────────▼────────┐   │
         │                  │     tasks       │   │
         │                  ├─────────────────┤   │
         │                  │ id              │   │
         │                  │ project_id      │   │
         │         ┌────────┼ assigned_to     │   │
         │         │        │ title           │   │
         │         │        │ description     │   │
         │         │        │ status          │   │
         │         │        │ priority        │   │
         │         │        │ due_date        │   │
         │         │        │ created_at      │   │
         │         │        │ updated_at      │   │
         │         │        └────────┬────────┘   │
         └─────────┘                 │            │
                                   * │            │
                                     │            │
                            ┌────────▼────────┐   │
                            │   task_tag      │   │
                            ├─────────────────┤   │
                            │ task_id         │   │
                            │ tag_id          │   │
                            └────────┬────────┘   │
                                     │            │
                                   * │            │
                            ┌────────▼────────┐   │
                            │      tags       │   │
                            ├─────────────────┤   │
                            │ id              │   │
                            │ name            │   │
                            │ color           │   │
                            └─────────────────┘   │
                                                   │
┌──────────────────────────────────────────────────┘
│
│         ┌─────────────────┐
└────────►│    comments     │
          ├─────────────────┤
          │ id              │
          │ task_id         │
          │ user_id         │
          │ content         │
          │ created_at      │
          └─────────────────┘
```

### Migraciones Clave

```php
// database/migrations/create_tasks_table.php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    $table->enum('status', ['pending', 'in_progress', 'review', 'completed', 'blocked'])
          ->default('pending');
    $table->enum('priority', ['low', 'medium', 'high', 'critical'])
          ->default('medium');
    $table->date('due_date')->nullable();
    $table->integer('estimated_hours')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['project_id', 'status']);
    $table->index(['assigned_to', 'status']);
    $table->index('due_date');
});
```

---

## 🔌 MCP Server Architecture

### Server Base Class

```php
// Infrastructure/MCP/AbstractMCPServer.php
abstract class AbstractMCPServer
{
    abstract public function tools(): array;
    abstract public function resources(): array;
    abstract public function prompts(): array;

    protected function validateRequest(array $data): void;
    protected function buildResponse($data): array;
    protected function handleError(\Throwable $e): array;
}
```

### Task Tools Server Implementation

```php
// Infrastructure/MCP/TaskToolsServer.php
class TaskToolsServer extends AbstractMCPServer
{
    public function __construct(
        private readonly CreateTaskUseCase $createTask,
        private readonly UpdateTaskUseCase $updateTask,
        private readonly SearchTasksUseCase $searchTasks
    ) {}

    public function tools(): array
    {
        return [
            'create_task' => [
                'description' => 'Creates a new task',
                'inputSchema' => $this->createTaskSchema(),
                'handler' => fn($args) => $this->createTask->execute(
                    CreateTaskDTO::fromArray($args)
                )
            ],
            // ... más tools
        ];
    }

    private function createTaskSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'project_id' => ['type' => 'integer'],
                'priority' => [
                    'type' => 'string',
                    'enum' => ['low', 'medium', 'high', 'critical']
                ],
                // ...
            ],
            'required' => ['title', 'project_id']
        ];
    }
}
```

---

## 🧪 Testing Strategy

### Pirámide de Testing

```
           ┌──────────┐
          /  E2E Tests  \     (5%)
         /──────────────\
        /   Integration   \   (20%)
       /       Tests       \
      /────────────────────\
     /     Unit Tests       \ (75%)
    /─────────────────────── \
   /    Domain & Application  \
  /─────────────────────────────\
```

### Estructura de Tests

```
tests/
├── Unit/
│   ├── Domain/
│   │   ├── TaskManagement/
│   │   │   ├── ValueObjects/PriorityTest.php
│   │   │   ├── ValueObjects/StatusTest.php
│   │   │   └── Services/TaskStatusManagerTest.php
│   │   └── AIIntegration/
│   │       └── Services/PromptBuilderTest.php
│   └── Application/
│       └── UseCases/CreateTaskUseCaseTest.php
├── Feature/
│   ├── Api/
│   │   └── TaskApiTest.php
│   └── Livewire/
│       └── TaskListComponentTest.php
├── Integration/
│   └── MCP/
│       ├── TaskToolsServerTest.php
│       ├── AIAssistantServerTest.php
│       └── AnalyticsResourcesServerTest.php
└── E2E/
    └── TaskWorkflowTest.php
```

---

## 🚀 Performance Considerations

### Caching Strategy

```php
// Cache de Analytics (10 minutos)
Cache::remember('analytics:productivity', 600, function () {
    return $this->analyticsService->getProductivityMetrics();
});

// Cache de Project Health (5 minutos)
Cache::tags(['projects', "project:{$projectId}"])
    ->remember("project:{$projectId}:health", 300, function () {
        return $this->getProjectHealth($projectId);
    });
```

### Queue Jobs

```php
// Jobs asíncronos para operaciones pesadas
dispatch(new GenerateWeeklyReportJob($projectId));
dispatch(new RecalculateAnalyticsJob())->delay(now()->addMinutes(5));
```

### Database Optimization

```php
// Eager loading para evitar N+1
Task::with(['project', 'assignee', 'tags'])->get();

// Chunking para grandes datasets
Task::chunk(100, function ($tasks) {
    // Process tasks
});
```

---

## 📦 Deployment

### Docker Architecture

```
┌─────────────────────────────────────────┐
│           Nginx (Reverse Proxy)         │
└────────────┬────────────────────────────┘
             │
     ┌───────┴────────┐
     │                │
┌────▼─────┐    ┌────▼────────┐
│   PHP    │    │  Laravel    │
│   FPM    │◄───┤  Horizon    │
└────┬─────┘    │  (Queues)   │
     │          └─────────────┘
     │
┌────▼───────────────┐
│   PostgreSQL       │
└────────────────────┘
```

---

**Última actualización**: 2025-11-12
**Versión**: 1.0.0
