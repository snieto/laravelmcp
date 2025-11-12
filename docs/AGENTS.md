# AGENTS.md - MCP Agents & Interactions

## 🤖 Guía de Agentes MCP

Este documento describe cómo los agentes de IA pueden interactuar con TaskMaster AI Platform mediante el Model Context Protocol.

## 📋 Índice de Servidores

| Servidor | Tipo | Endpoint | Propósito |
|----------|------|----------|-----------|
| Task Tools | Tools | `/mcp/tasks` | Gestión de tareas |
| AI Assistant | Tools | `/mcp/ai` | Generación con IA |
| Reports | Tools | `/mcp/reports` | Generación de reportes |
| Analytics | Resources | `/mcp/analytics` | Métricas y estadísticas |
| Task Prompts | Prompts | Local: `task-prompts` | Templates de tareas |
| AI Prompts | Prompts | Local: `ai-prompts` | Biblioteca de prompts |

---

## 🛠️ TASK TOOLS SERVER

### Conexión

```json
{
  "mcpServers": {
    "taskmaster-tasks": {
      "url": "http://localhost:8000/mcp/tasks",
      "transport": "http",
      "headers": {
        "Authorization": "Bearer YOUR_SANCTUM_TOKEN"
      }
    }
  }
}
```

### Herramientas Disponibles

#### 1. `create_task`
**Descripción**: Crea una nueva tarea en el sistema.

**Parámetros**:
```json
{
  "title": "string (required)",
  "description": "string (optional)",
  "project_id": "integer (required)",
  "priority": "low|medium|high|critical (optional, default: medium)",
  "due_date": "YYYY-MM-DD (optional)",
  "assigned_to": "integer (optional)",
  "tags": ["string"] (optional)
}
```

**Ejemplo de uso**:
```
Usuario: "Crea una tarea para implementar autenticación OAuth en el proyecto E-commerce"

Agente ejecuta:
create_task({
  "title": "Implementar autenticación OAuth",
  "description": "Añadir login social con Google y GitHub",
  "project_id": 1,
  "priority": "high",
  "tags": ["authentication", "security"]
})
```

**Respuesta**:
```json
{
  "success": true,
  "task": {
    "id": 42,
    "title": "Implementar autenticación OAuth",
    "status": "pending",
    "created_at": "2025-11-12T10:30:00Z"
  }
}
```

#### 2. `update_task`
**Descripción**: Actualiza una tarea existente.

**Parámetros**:
```json
{
  "task_id": "integer (required)",
  "title": "string (optional)",
  "description": "string (optional)",
  "status": "pending|in_progress|review|completed|blocked (optional)",
  "priority": "low|medium|high|critical (optional)",
  "assigned_to": "integer (optional)"
}
```

#### 3. `search_tasks`
**Descripción**: Busca tareas con filtros avanzados.

**Parámetros**:
```json
{
  "query": "string (optional)",
  "project_id": "integer (optional)",
  "status": "string (optional)",
  "assigned_to": "integer (optional)",
  "priority": "string (optional)",
  "tags": ["string"] (optional),
  "due_before": "YYYY-MM-DD (optional)",
  "limit": "integer (optional, default: 20)"
}
```

**Ejemplo**:
```
Usuario: "Muéstrame todas las tareas de alta prioridad que vencen esta semana"

Agente ejecuta:
search_tasks({
  "priority": "high",
  "due_before": "2025-11-19",
  "status": "pending,in_progress"
})
```

#### 4. `assign_task`
**Descripción**: Asigna una tarea a un usuario.

**Parámetros**:
```json
{
  "task_id": "integer (required)",
  "user_id": "integer (required)",
  "notify": "boolean (optional, default: true)"
}
```

#### 5. `change_status`
**Descripción**: Cambia el estado de una tarea.

**Parámetros**:
```json
{
  "task_id": "integer (required)",
  "status": "pending|in_progress|review|completed|blocked (required)",
  "comment": "string (optional)"
}
```

#### 6. `delete_task`
**Descripción**: Elimina una tarea (soft delete).

**Parámetros**:
```json
{
  "task_id": "integer (required)",
  "reason": "string (optional)"
}
```

---

## 🧠 AI ASSISTANT TOOLS SERVER

### Conexión

```json
{
  "mcpServers": {
    "taskmaster-ai": {
      "url": "http://localhost:8000/mcp/ai",
      "transport": "http",
      "headers": {
        "Authorization": "Bearer YOUR_SANCTUM_TOKEN"
      }
    }
  }
}
```

### Herramientas Disponibles

#### 1. `generate_task_description`
**Descripción**: Genera una descripción detallada para una tarea usando OpenAI.

**Parámetros**:
```json
{
  "task_id": "integer (optional)",
  "title": "string (required if no task_id)",
  "context": "string (optional)",
  "technical_level": "junior|mid|senior (optional, default: mid)",
  "include_acceptance_criteria": "boolean (optional, default: true)"
}
```

**Ejemplo**:
```
Usuario: "Genera una descripción técnica para la tarea de OAuth"

Agente ejecuta:
generate_task_description({
  "task_id": 42,
  "technical_level": "senior",
  "include_acceptance_criteria": true
})
```

**Respuesta**:
```json
{
  "success": true,
  "description": "## Objetivo\nImplementar autenticación OAuth 2.0...",
  "acceptance_criteria": [
    "Usuario puede login con Google",
    "Usuario puede login con GitHub",
    "Tokens se almacenan de forma segura"
  ],
  "estimated_hours": 8
}
```

#### 2. `summarize_project`
**Descripción**: Genera un resumen ejecutivo del estado del proyecto.

**Parámetros**:
```json
{
  "project_id": "integer (required)",
  "include_metrics": "boolean (optional, default: true)",
  "format": "markdown|plain|html (optional, default: markdown)"
}
```

#### 3. `suggest_priorities`
**Descripción**: IA analiza tareas y sugiere prioridades.

**Parámetros**:
```json
{
  "project_id": "integer (required)",
  "criteria": "deadline|impact|effort|dependencies (optional)",
  "limit": "integer (optional, default: 10)"
}
```

**Ejemplo de respuesta**:
```json
{
  "suggestions": [
    {
      "task_id": 15,
      "current_priority": "medium",
      "suggested_priority": "high",
      "reason": "Bloqueada por 3 tareas dependientes",
      "confidence": 0.85
    }
  ]
}
```

#### 4. `generate_report`
**Descripción**: Genera un reporte narrativo con análisis de IA.

**Parámetros**:
```json
{
  "type": "weekly|monthly|project|sprint (required)",
  "project_id": "integer (optional)",
  "start_date": "YYYY-MM-DD (optional)",
  "end_date": "YYYY-MM-DD (optional)",
  "include_recommendations": "boolean (optional, default: true)"
}
```

#### 5. `analyze_sentiment`
**Descripción**: Analiza el sentimiento en comentarios de tareas.

**Parámetros**:
```json
{
  "task_id": "integer (optional)",
  "project_id": "integer (optional)",
  "period": "week|month|all (optional)"
}
```

---

## 📊 ANALYTICS RESOURCES SERVER

### Conexión

```json
{
  "mcpServers": {
    "taskmaster-analytics": {
      "url": "http://localhost:8000/mcp/analytics",
      "transport": "http",
      "headers": {
        "Authorization": "Bearer YOUR_SANCTUM_TOKEN"
      }
    }
  }
}
```

### Recursos Disponibles

#### 1. `analytics://productivity`
**Descripción**: Métricas de productividad del equipo.

**Datos expuestos**:
```json
{
  "period": "last_30_days",
  "tasks_completed": 47,
  "tasks_created": 52,
  "average_completion_time_hours": 18.5,
  "velocity": 1.2,
  "burn_rate": 0.9,
  "team_members": [
    {
      "user_id": 1,
      "name": "Juan Pérez",
      "tasks_completed": 15,
      "average_time_hours": 12.3
    }
  ]
}
```

**Uso desde agente**:
```
Usuario: "¿Cuál es nuestra productividad este mes?"

Agente lee recurso: analytics://productivity
→ Parsea JSON
→ Responde: "Este mes completaron 47 tareas en 30 días..."
```

#### 2. `analytics://team-stats`
**Descripción**: Estadísticas del equipo.

**Datos**:
```json
{
  "total_members": 5,
  "active_members": 4,
  "workload_distribution": {
    "balanced": false,
    "overloaded": [1, 3],
    "underutilized": [5]
  }
}
```

#### 3. `analytics://project-health`
**Descripción**: Salud de los proyectos.

**Datos**:
```json
{
  "projects": [
    {
      "id": 1,
      "name": "E-commerce",
      "health_score": 0.75,
      "status": "healthy",
      "risks": ["2 tareas bloqueadas"],
      "completion_percentage": 68
    }
  ]
}
```

#### 4. `analytics://time-tracking`
**Descripción**: Seguimiento de tiempo invertido.

---

## 📝 TASK PROMPTS SERVER (Local)

### Conexión

```json
{
  "mcpServers": {
    "taskmaster-prompts": {
      "command": "php",
      "args": ["artisan", "mcp:serve", "task-prompts"],
      "cwd": "/path/to/taskmaster"
    }
  }
}
```

### Prompts Disponibles

#### 1. `create-feature-task`
**Descripción**: Plantilla para crear tareas de nuevas features.

**Variables**:
- `{feature_name}`: Nombre de la feature
- `{context}`: Contexto adicional

**Template**:
```
Crea una tarea para implementar la feature: {feature_name}

Contexto: {context}

La tarea debe incluir:
- Descripción técnica detallada
- Criterios de aceptación
- Estimación de esfuerzo
- Dependencias conocidas
```

#### 2. `create-bug-task`
**Descripción**: Plantilla para reportar bugs.

**Variables**:
- `{bug_description}`: Descripción del bug
- `{steps_to_reproduce}`: Pasos para reproducir

#### 3. `estimate-effort`
**Descripción**: Prompt para estimar esfuerzo.

**Uso**:
```
Usuario: "Estima el esfuerzo para la tarea 42"

Agente usa prompt: estimate-effort
→ Llena variables con datos de la tarea
→ Envía a OpenAI
→ Retorna estimación
```

#### 4. `retrospective`
**Descripción**: Template para retrospectivas de sprint.

---

## 📚 AI PROMPTS LIBRARY SERVER (Local)

### Conexión

```json
{
  "mcpServers": {
    "taskmaster-ai-prompts": {
      "command": "php",
      "args": ["artisan", "mcp:serve", "ai-prompts"],
      "cwd": "/path/to/taskmaster"
    }
  }
}
```

### Biblioteca de Prompts

#### 1. `technical-description`
**Uso**: Generar descripciones técnicas de alta calidad.

#### 2. `user-story`
**Uso**: Convertir requisitos en user stories.

**Formato**:
```
Como [tipo de usuario]
Quiero [acción]
Para [beneficio]
```

#### 3. `acceptance-criteria`
**Uso**: Generar criterios de aceptación claros.

#### 4. `code-review`
**Uso**: Prompts para realizar code reviews.

---

## 🔐 Autenticación

### Obtener Token Sanctum

```bash
# 1. Registrar usuario
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "AI Agent",
    "email": "agent@example.com",
    "password": "secret123"
  }'

# 2. Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "agent@example.com",
    "password": "secret123"
  }'

# Respuesta:
# {
#   "token": "1|abc123...",
#   "user": {...}
# }
```

### Usar Token en MCP

```json
{
  "headers": {
    "Authorization": "Bearer 1|abc123...",
    "Accept": "application/json"
  }
}
```

---

## 🎯 Casos de Uso Completos

### Caso 1: Crear y Enriquecer Tarea

```
1. Usuario: "Crea una tarea para implementar dark mode"

2. Agente:
   - Ejecuta: create_task(title="Implementar dark mode", project_id=1)
   - Obtiene: task_id=50

3. Agente:
   - Ejecuta: generate_task_description(task_id=50, technical_level="mid")
   - OpenAI genera descripción completa

4. Agente:
   - Ejecuta: update_task(task_id=50, description=<generated>)

5. Respuesta al usuario: "Tarea creada con descripción técnica detallada"
```

### Caso 2: Análisis de Productividad

```
1. Usuario: "¿Cómo va el equipo esta semana?"

2. Agente:
   - Lee: analytics://productivity
   - Lee: analytics://team-stats

3. Agente:
   - Ejecuta: generate_report(type="weekly", include_recommendations=true)

4. Respuesta: Reporte narrativo con métricas y sugerencias de IA
```

### Caso 3: Priorización Inteligente

```
1. Usuario: "¿Qué tareas debería hacer primero del proyecto X?"

2. Agente:
   - Ejecuta: suggest_priorities(project_id=X, criteria="impact")
   - Obtiene lista ordenada con reasoning

3. Agente:
   - Para cada tarea sugerida:
     - Ejecuta: update_task(task_id=Y, priority="high")

4. Respuesta: "He actualizado las prioridades según impacto..."
```

---

## 🧪 Testing de Agentes

```bash
# Test manual de servidor
php artisan mcp:test /mcp/tasks

# Test con curl
curl -X POST http://localhost:8000/mcp/tasks \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "method": "tools/call",
    "params": {
      "name": "create_task",
      "arguments": {
        "title": "Test task",
        "project_id": 1
      }
    }
  }'
```

---

## 📖 Referencias

- [MCP Specification](https://spec.modelcontextprotocol.io)
- [Laravel MCP Docs](https://laravel.com/docs/12.x/mcp)
- [OpenAI API Reference](https://platform.openai.com/docs)

---

**Actualizado**: 2025-11-12
**Versión**: 1.0.0
