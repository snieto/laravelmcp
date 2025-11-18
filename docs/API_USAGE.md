# API REST - Guía de Uso

## Configuración Inicial

### 1. Ejecutar Migraciones y Seeders

```bash
php artisan migrate:fresh --seed
```

Esto creará:
- 6 usuarios (admin@taskmaster.test + 5 desarrolladores)
- 3 proyectos
- 87 tareas con variedad de estados
- 10 tags
- Múltiples comentarios

### 2. Obtener Token de Autenticación

El seeder automáticamente genera un token para el usuario admin. También puedes generar uno manualmente:

```bash
php artisan tinker
>>> $user = User::first()
>>> $token = $user->createToken('api-token')->plainTextToken
>>> echo $token
```

Guarda este token para usarlo en las peticiones API.

## Endpoints Disponibles

Base URL: `http://localhost/api`

Todos los endpoints requieren autenticación con Sanctum:
```
Authorization: Bearer {tu-token-aqui}
```

### Listar Tareas

**GET** `/api/tasks`

Obtiene todas las tareas con sus relaciones.

**Parámetros de Query (opcionales):**
- `project_id` - Filtrar por ID de proyecto
- `status` - Filtrar por estado (pending, in_progress, review, completed, blocked)
- `priority` - Filtrar por prioridad (low, medium, high, critical)
- `assigned_to` - Filtrar por ID de usuario asignado
- `search` - Buscar en título y descripción

**Ejemplo:**
```bash
curl -H "Authorization: Bearer {token}" \
  http://localhost/api/tasks

# Con filtros
curl -H "Authorization: Bearer {token}" \
  "http://localhost/api/tasks?status=in_progress&priority=high"
```

**Respuesta:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Implementar autenticación",
      "description": "...",
      "status": "in_progress",
      "priority": "high",
      "project": {
        "id": 1,
        "name": "TaskMaster API",
        "description": "..."
      },
      "assignee": {
        "id": 2,
        "name": "María García",
        "email": "maria@example.com"
      },
      "tags": [...],
      "is_overdue": false,
      "is_completed": false,
      "created_at": "2024-01-15T10:00:00+00:00",
      "updated_at": "2024-01-15T12:30:00+00:00"
    }
  ],
  "meta": {
    "count": 87,
    "total": 87
  }
}
```

### Obtener Tarea Individual

**GET** `/api/tasks/{id}`

Obtiene una tarea específica con todas sus relaciones (proyecto, asignado, tags, comentarios).

**Ejemplo:**
```bash
curl -H "Authorization: Bearer {token}" \
  http://localhost/api/tasks/1
```

**Respuesta:**
```json
{
  "data": {
    "id": 1,
    "title": "...",
    "description": "...",
    "project": {...},
    "assignee": {...},
    "tags": [...],
    "comments": [
      {
        "id": 1,
        "content": "Gran progreso!",
        "user": "María García",
        "created_at": "2024-01-15T10:00:00+00:00"
      }
    ],
    "...": "..."
  }
}
```

### Crear Nueva Tarea

**POST** `/api/tasks`

Crea una nueva tarea.

**Campos requeridos:**
- `title` (string, max: 255)
- `project_id` (integer, debe existir)

**Campos opcionales:**
- `description` (string)
- `assigned_to` (integer, ID de usuario)
- `status` (enum: pending, in_progress, review, completed, blocked)
- `priority` (enum: low, medium, high, critical)
- `due_date` (date, formato: YYYY-MM-DD, no puede ser pasado)
- `estimated_hours` (integer, >= 0)
- `tags` (array de IDs de tags)

**Ejemplo:**
```bash
curl -X POST \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Nueva tarea desde API",
    "description": "Descripción detallada",
    "project_id": 1,
    "assigned_to": 2,
    "status": "pending",
    "priority": "high",
    "due_date": "2024-12-31",
    "estimated_hours": 8,
    "tags": [1, 2, 3]
  }' \
  http://localhost/api/tasks
```

**Respuesta:** Status 201 Created
```json
{
  "data": {
    "id": 88,
    "title": "Nueva tarea desde API",
    "...": "..."
  }
}
```

### Actualizar Tarea

**PUT/PATCH** `/api/tasks/{id}`

Actualiza una tarea existente.

Todos los campos son opcionales. Solo envía los campos que quieres actualizar.

**Ejemplo:**
```bash
curl -X PATCH \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed",
    "actual_hours": 6
  }' \
  http://localhost/api/tasks/1
```

**Respuesta:**
```json
{
  "data": {
    "id": 1,
    "status": "completed",
    "actual_hours": 6,
    "...": "..."
  }
}
```

### Eliminar Tarea

**DELETE** `/api/tasks/{id}`

Elimina (soft delete) una tarea.

**Ejemplo:**
```bash
curl -X DELETE \
  -H "Authorization: Bearer {token}" \
  http://localhost/api/tasks/1
```

**Respuesta:**
```json
{
  "message": "Task deleted successfully"
}
```

## Manejo de Errores

### 404 Not Found
```json
{
  "message": "Task not found"
}
```

### 422 Validation Error
```json
{
  "message": "The title field is required. (and 1 more error)",
  "errors": {
    "title": ["The task title is required."],
    "project_id": ["The task must belong to a project."]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

## Pruebas con Postman

1. **Importar Collection:**
   - Crea una nueva colección en Postman
   - Añade las variables:
     - `base_url`: `http://localhost/api`
     - `token`: Tu token de Sanctum

2. **Configurar Autenticación:**
   - En cada request, añade header:
   - Key: `Authorization`
   - Value: `Bearer {{token}}`

3. **Crear Requests:**
   - GET Tasks: `{{base_url}}/tasks`
   - POST Task: `{{base_url}}/tasks` con body JSON
   - GET Task: `{{base_url}}/tasks/1`
   - PATCH Task: `{{base_url}}/tasks/1` con body JSON
   - DELETE Task: `{{base_url}}/tasks/1`

## Testing Automatizado

Puedes crear tests de API en `tests/Feature/Api/TaskControllerTest.php`:

```php
public function test_can_list_tasks()
{
    $user = User::factory()->create();
    Task::factory()->count(5)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/tasks');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'status', 'priority']
            ],
            'meta' => ['count', 'total']
        ]);
}
```

## Recursos Relacionados

- Ver `docs/TUTORIAL_COMPLETO.md` Capítulo 14 para configuración de Claude Desktop
- Ver `database/seeders/DevelopmentSeeder.php` para datos de prueba
- Ver `tests/Integration/Mcp/` para ejemplos de testing

## Próximos Pasos

1. Implementar paginación en el endpoint index
2. Añadir endpoint para comentarios: `POST /api/tasks/{task}/comments`
3. Añadir endpoint para asignación: `PATCH /api/tasks/{task}/assign`
4. Añadir rate limiting
5. Documentar con OpenAPI/Swagger
