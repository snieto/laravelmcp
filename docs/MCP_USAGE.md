# MCP_USAGE.md - Ejemplos de Uso de MCP Servers

## 🎯 Guía Práctica de Uso

Esta guía contiene ejemplos reales de cómo interactuar con los servidores MCP de TaskMaster AI desde diferentes clientes.

---

## 📱 Configuración de Clientes MCP

### Claude Desktop

Edita `~/Library/Application Support/Claude/claude_desktop_config.json` (macOS) o
`%APPDATA%/Claude/claude_desktop_config.json` (Windows):

```json
{
  "mcpServers": {
    "taskmaster-tasks": {
      "url": "http://localhost:8000/mcp/tasks",
      "transport": "http",
      "headers": {
        "Authorization": "Bearer 1|tu-token-sanctum-aqui",
        "Accept": "application/json"
      }
    },
    "taskmaster-ai": {
      "url": "http://localhost:8000/mcp/ai",
      "transport": "http",
      "headers": {
        "Authorization": "Bearer 1|tu-token-sanctum-aqui",
        "Accept": "application/json"
      }
    },
    "taskmaster-analytics": {
      "url": "http://localhost:8000/mcp/analytics",
      "transport": "http",
      "headers": {
        "Authorization": "Bearer 1|tu-token-sanctum-aqui",
        "Accept": "application/json"
      }
    },
    "taskmaster-prompts": {
      "command": "php",
      "args": ["artisan", "mcp:serve", "task-prompts"],
      "cwd": "/path/to/taskmaster"
    }
  }
}
```

### Obtener Token de Autenticación

```bash
# Método 1: Via API
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@taskmaster.test",
    "password": "password"
  }'

# Método 2: Via Tinker
php artisan tinker
>>> $user = User::first();
>>> $token = $user->createToken('claude-desktop')->plainTextToken;
>>> echo $token;
```

---

## 🛠️ EJEMPLOS: Task Tools Server

### Caso de Uso 1: Crear Tarea Simple

**Conversación con Claude Desktop:**

```
Usuario: Crea una tarea llamada "Implementar login con Google"
         en el proyecto 1 con prioridad alta

Claude ejecuta internamente:
{
  "tool": "create_task",
  "arguments": {
    "title": "Implementar login con Google",
    "project_id": 1,
    "priority": "high",
    "description": "Añadir autenticación OAuth2 con Google"
  }
}

Claude responde:
"He creado la tarea #42 'Implementar login con Google' con prioridad alta
en el proyecto. La tarea está en estado 'pending' y lista para ser asignada."
```

### Caso de Uso 2: Buscar Tareas Pendientes

**Conversación:**

```
Usuario: Muéstrame todas las tareas de alta prioridad que están
         pendientes en el proyecto E-commerce

Claude ejecuta:
{
  "tool": "search_tasks",
  "arguments": {
    "project_id": 1,
    "priority": "high",
    "status": "pending"
  }
}

Claude responde:
"Encontré 3 tareas de alta prioridad pendientes:

1. #42 - Implementar login con Google
2. #38 - Optimizar consultas de base de datos
3. #29 - Añadir validación de tarjetas de crédito

¿Quieres que te dé más detalles de alguna?"
```

### Caso de Uso 3: Asignar y Cambiar Estado

**Conversación:**

```
Usuario: Asigna la tarea #42 a Juan (user_id: 5) y márcala como en progreso

Claude ejecuta (dos llamadas):
1. {
     "tool": "assign_task",
     "arguments": {
       "task_id": 42,
       "user_id": 5,
       "notify": true
     }
   }

2. {
     "tool": "change_status",
     "arguments": {
       "task_id": 42,
       "status": "in_progress",
       "comment": "Juan ha comenzado a trabajar en esto"
     }
   }

Claude responde:
"Perfecto. He asignado la tarea a Juan y la he marcado como 'En Progreso'.
Juan recibirá una notificación por email."
```

### Caso de Uso 4: Workflow Completo

**Conversación:**

```
Usuario: Necesito crear una tarea para refactorizar el módulo de pagos,
         asignársela a María (user_id: 3) y añadir las etiquetas
         'refactoring' y 'payments'

Claude ejecuta:
{
  "tool": "create_task",
  "arguments": {
    "title": "Refactorizar módulo de pagos",
    "project_id": 1,
    "priority": "medium",
    "assigned_to": 3,
    "tags": ["refactoring", "payments"],
    "description": "Mejorar la estructura del código del módulo de pagos
                    para mejor mantenibilidad"
  }
}

Claude responde:
"He creado la tarea #43 'Refactorizar módulo de pagos', asignada a María
con las etiquetas 'refactoring' y 'payments'. La tarea tiene prioridad media."
```

---

## 🧠 EJEMPLOS: AI Assistant Server

### Caso de Uso 1: Generar Descripción Técnica

**Conversación:**

```
Usuario: Genera una descripción técnica detallada para la tarea #42
         (Implementar login con Google)

Claude ejecuta:
{
  "tool": "generate_task_description",
  "arguments": {
    "task_id": 42,
    "technical_level": "senior",
    "include_acceptance_criteria": true
  }
}

Respuesta del servidor (vía OpenAI):
{
  "success": true,
  "description": "## Objetivo\n\nImplementar autenticación OAuth 2.0...",
  "acceptance_criteria": [
    "Usuario puede iniciar sesión con su cuenta de Google",
    "Los tokens se almacenan de forma segura en la base de datos",
    "Se crea automáticamente una cuenta local vinculada",
    "Se manejan correctamente los errores de autenticación"
  ],
  "estimated_hours": 6
}

Claude presenta:
"He generado una descripción técnica completa para la tarea:

**Objetivo**
Implementar autenticación OAuth 2.0 con Google utilizando Laravel Socialite...

**Criterios de Aceptación:**
✓ Usuario puede iniciar sesión con su cuenta de Google
✓ Los tokens se almacenan de forma segura
✓ Se crea cuenta local vinculada automáticamente
✓ Manejo robusto de errores

**Estimación:** 6 horas

La descripción ha sido actualizada en la tarea."
```

### Caso de Uso 2: Resumir Proyecto

**Conversación:**

```
Usuario: Dame un resumen del estado actual del proyecto E-commerce

Claude ejecuta:
{
  "tool": "summarize_project",
  "arguments": {
    "project_id": 1,
    "include_metrics": true
  }
}

Claude presenta:
"**Resumen del Proyecto E-commerce**

📊 **Métricas Generales:**
- Total de tareas: 45
- Completadas: 28 (62%)
- En progreso: 12 (27%)
- Pendientes: 5 (11%)

🎯 **Estado Actual:**
El proyecto avanza bien con buen momentum. El equipo ha completado
la mayoría de las funcionalidades core.

⚠️ **Áreas de Atención:**
- 2 tareas bloqueadas que requieren intervención
- 3 tareas de alta prioridad sin asignar

🚀 **Próximos Hitos:**
- Completar integración de pagos (esta semana)
- Testing E2E (próxima semana)
- Beta release estimado en 2 semanas"
```

### Caso de Uso 3: Sugerencias de Priorización

**Conversación:**

```
Usuario: ¿Qué tareas debería priorizar en el proyecto E-commerce?

Claude ejecuta:
{
  "tool": "suggest_priorities",
  "arguments": {
    "project_id": 1,
    "criteria": "dependencies",
    "limit": 5
  }
}

Claude presenta:
"Basándome en el análisis de dependencias, recomiendo priorizar estas tareas:

1. **#38 - Optimizar consultas de base de datos** (Alta → Crítica)
   Razón: Bloquea 4 tareas de rendimiento
   Confianza: 92%

2. **#29 - Validación de tarjetas** (Media → Alta)
   Razón: Necesaria para completar el módulo de pagos
   Confianza: 88%

3. **#51 - Configurar CDN** (Baja → Media)
   Razón: Afecta tiempo de carga en producción
   Confianza: 75%

¿Quieres que actualice las prioridades automáticamente?"
```

### Caso de Uso 4: Generar Reporte con IA

**Conversación:**

```
Usuario: Genera un reporte semanal con análisis y recomendaciones

Claude ejecuta:
{
  "tool": "generate_report",
  "arguments": {
    "type": "weekly",
    "include_recommendations": true
  }
}

Claude presenta reporte narrativo con gráficos y análisis de IA...
```

---

## 📊 EJEMPLOS: Analytics Resources

### Caso de Uso 1: Consultar Productividad

**Conversación:**

```
Usuario: ¿Cuál es nuestra productividad este mes?

Claude lee recurso:
analytics://productivity

Datos obtenidos:
{
  "period": "last_30_days",
  "tasks_completed": 47,
  "tasks_created": 52,
  "average_completion_time_hours": 18.5,
  "velocity": 1.2,
  "burn_rate": 0.9
}

Claude analiza y responde:
"En los últimos 30 días, el equipo ha mostrado un buen rendimiento:

✅ **Tareas Completadas:** 47
📝 **Tareas Creadas:** 52
⏱️ **Tiempo Promedio:** 18.5 horas por tarea
📈 **Velocidad:** 1.2 (por encima del objetivo)
🔥 **Burn Rate:** 0.9 (saludable)

El equipo está manteniendo un ritmo sostenible y completando tareas
ligeramente más rápido que el objetivo."
```

### Caso de Uso 2: Estado del Equipo

**Conversación:**

```
Usuario: ¿Cómo está distribuida la carga de trabajo del equipo?

Claude lee recurso:
analytics://team-stats

Claude responde:
"**Distribución de Carga de Trabajo:**

⚠️ La carga NO está balanceada:

**Sobrecargados:**
- Juan (user_id: 1): 12 tareas activas
- María (user_id: 3): 10 tareas activas

**Subutilizados:**
- Pedro (user_id: 5): 3 tareas activas

**Recomendación:** Reasignar 3-4 tareas de Juan a Pedro
para balancear la carga."
```

### Caso de Uso 3: Salud de Proyectos

**Conversación:**

```
Usuario: ¿Cuáles proyectos están en riesgo?

Claude lee recurso:
analytics://project-health

Claude responde:
"**Estado de Salud de Proyectos:**

🟢 **Proyectos Saludables (2):**
- E-commerce: 75% health score
- Blog Platform: 82% health score

🟡 **Requieren Atención (1):**
- CRM System: 58% health score
  Problemas:
  - 3 tareas bloqueadas
  - 2 miembros inactivos esta semana
  - 15% sobre estimación de tiempo

🔴 **En Riesgo (0)**

¿Quieres que analice en detalle el CRM System?"
```

---

## 📝 EJEMPLOS: Task Prompts Server

### Caso de Uso 1: Crear Feature Task

**Conversación:**

```
Usuario: Ayúdame a crear una tarea bien estructurada para implementar
         un sistema de notificaciones push

Claude usa prompt:
create-feature-task

Variables:
{
  "feature_name": "Sistema de notificaciones push",
  "context": "Proyecto E-commerce necesita notificar a usuarios de ofertas"
}

Claude genera y crea tarea con estructura completa:

**Tarea #55: Implementar Sistema de Notificaciones Push**

**Descripción:**
Sistema para enviar notificaciones push a usuarios registrados de la
aplicación E-commerce, permitiendo notificar sobre ofertas especiales,
cambios de estado de pedidos, y mensajes importantes.

**Alcance Técnico:**
- Integración con Firebase Cloud Messaging (FCM)
- Endpoint API para registro de tokens de dispositivos
- Panel de administración para enviar notificaciones
- Sistema de plantillas para diferentes tipos de notificaciones

**Criterios de Aceptación:**
✓ Usuario puede subscribirse/desuscribirse de notificaciones
✓ Admin puede enviar notificaciones segmentadas
✓ Se registran métricas de entrega y apertura
✓ Sistema funciona en iOS y Android

**Dependencias:**
- Configuración de Firebase

**Estimación:** 16 horas
```

### Caso de Uso 2: Reportar Bug

**Conversación:**

```
Usuario: Tenemos un bug: el carrito de compras se vacía al recargar la página

Claude usa prompt:
create-bug-task

Genera tarea estructurada:

**Tarea #56: [BUG] Carrito se vacía al recargar página**

**Descripción del Problema:**
Los usuarios reportan que al recargar la página, todos los productos
en el carrito de compras desaparecen.

**Pasos para Reproducir:**
1. Añadir productos al carrito
2. Recargar la página (F5 o Cmd+R)
3. Observar que el carrito está vacío

**Comportamiento Esperado:**
El carrito debería mantener los productos después de recargar.

**Comportamiento Actual:**
El carrito se vacía completamente.

**Impacto:** Alto - Afecta conversión de ventas
**Prioridad:** Crítica
```

---

## 🔄 WORKFLOWS COMPLEJOS

### Workflow 1: Onboarding de Nueva Feature

```
Usuario: Quiero añadir un sistema de reviews de productos al e-commerce

Claude ejecuta secuencia:

1. create_task({
     title: "Sistema de reviews de productos",
     project_id: 1,
     priority: "high"
   })
   → Obtiene task_id: 60

2. generate_task_description({
     task_id: 60,
     technical_level: "senior"
   })
   → Genera descripción técnica completa

3. Usa prompt 'estimate-effort'
   → Estima: 24 horas (3 días)

4. suggest_priorities({
     project_id: 1
   })
   → Verifica que no bloquee otras tareas

Claude responde:
"He creado la tarea #60 'Sistema de reviews de productos' con:

✅ Descripción técnica detallada (generada con IA)
✅ Criterios de aceptación claros
✅ Estimación: 24 horas (3 días)
✅ Prioridad: Alta
✅ Sin dependencias bloqueantes

La tarea está lista para ser asignada. ¿A quién se la asigno?"
```

### Workflow 2: Sprint Planning Asistido

```
Usuario: Ayúdame a planificar el sprint de la próxima semana
         para el proyecto E-commerce

Claude ejecuta:

1. Lee: analytics://team-stats
   → Obtiene capacidad del equipo

2. search_tasks({
     project_id: 1,
     status: "pending",
     limit: 50
   })
   → Obtiene backlog

3. suggest_priorities({
     project_id: 1,
     criteria: "impact"
   })
   → IA sugiere priorización

4. Calcula capacidad vs trabajo

Claude presenta:
"**Sprint Planning - Proyecto E-commerce**

📊 **Capacidad del Equipo:**
- 4 desarrolladores activos
- 32 horas/persona = 128 horas totales
- Buffer 20% = ~102 horas efectivas

🎯 **Tareas Recomendadas (95 horas estimadas):**

**Alta Prioridad (60h):**
1. #42 - Login con Google (6h) → Juan
2. #38 - Optimizar queries (8h) → María
3. #29 - Validación tarjetas (10h) → Pedro
4. #55 - Notificaciones push (16h) → Juan
5. #60 - Sistema de reviews (20h) → María

**Media Prioridad (35h):**
6. #47 - Mejorar UI checkout (12h) → Ana
7. #51 - Configurar CDN (8h) → Pedro
8. #53 - Tests E2E (15h) → Ana

¿Quieres que asigne estas tareas y las marque para el sprint?"
```

### Workflow 3: Code Review Context

```
Usuario: Voy a hacer code review de la tarea #42. Dame contexto

Claude ejecuta:

1. search_tasks({ task_id: 42 })
   → Obtiene detalles de la tarea

2. Lee comentarios y cambios de estado
   → Historial completo

3. Usa prompt 'code-review'
   → Template de checklist

Claude responde:
"**Context para Code Review - Tarea #42**

**📋 Información de la Tarea:**
- Título: Implementar login con Google
- Asignado a: Juan
- Estado: Review
- Prioridad: Alta
- Tiempo invertido: 7 horas (estimado: 6h)

**🎯 Criterios de Aceptación:**
✓ Usuario puede iniciar sesión con Google
✓ Tokens almacenados de forma segura
✓ Cuenta local creada automáticamente
✓ Manejo de errores robusto

**🔍 Checklist de Code Review:**
- [ ] Código sigue PSR-12
- [ ] Tests unitarios incluidos
- [ ] No hay secretos hardcodeados
- [ ] Manejo adecuado de excepciones
- [ ] Documentación actualizada
- [ ] Migraciones incluidas si aplica
- [ ] OAuth tokens encriptados

**💬 Comentarios del Desarrollador:**
'Implementado con Laravel Socialite. Añadidos tests de integración.'"
```

---

## 🔌 Testing Manual de MCP

### Test con cURL

```bash
# 1. Obtener token
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@taskmaster.test","password":"password"}' \
  | jq -r '.token')

# 2. Llamar a MCP server
curl -X POST http://localhost:8000/mcp/tasks \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "method": "tools/call",
    "params": {
      "name": "create_task",
      "arguments": {
        "title": "Test task from cURL",
        "project_id": 1,
        "priority": "medium"
      }
    }
  }' | jq
```

### Test con Artisan

```bash
# Test servidor local
php artisan mcp:serve task-prompts --test

# Llamar herramienta específica
php artisan mcp:call task-prompts create-feature-task \
  --feature_name="Dark Mode" \
  --context="Add dark theme support"
```

---

## 📚 Mejores Prácticas

### 1. Manejo de Errores

```
Usuario: Crea una tarea sin especificar el proyecto

Claude intenta:
{
  "tool": "create_task",
  "arguments": {
    "title": "Nueva tarea"
    // Falta project_id
  }
}

Error recibido:
{
  "error": "Validation failed",
  "message": "project_id is required"
}

Claude responde:
"Necesito saber en qué proyecto crear la tarea.
¿En cuál proyecto la creo?"
```

### 2. Confirmaciones

```
Usuario: Elimina la tarea #42

Claude pregunta antes de ejecutar:
"¿Estás seguro de que quieres eliminar la tarea #42
'Implementar login con Google'? Esta acción marcará
la tarea como eliminada (soft delete)."

Usuario: Sí, elimínala

Claude ejecuta:
{
  "tool": "delete_task",
  "arguments": {
    "task_id": 42,
    "reason": "Usuario solicitó eliminación"
  }
}
```

### 3. Batch Operations

```
Usuario: Cambia todas las tareas de baja prioridad del proyecto 1 a media

Claude ejecuta:
1. search_tasks({ project_id: 1, priority: "low" })
   → Encuentra 8 tareas

2. Para cada tarea:
   update_task({ task_id: X, priority: "medium" })

Claude confirma:
"He actualizado 8 tareas de prioridad baja a media en el proyecto E-commerce."
```

---

## 🎓 Recursos Adicionales

- **Documentación MCP**: https://spec.modelcontextprotocol.io
- **Laravel MCP Docs**: https://laravel.com/docs/12.x/mcp
- **CLAUDE.md**: Arquitectura del proyecto
- **AGENTS.md**: Referencia completa de servidores

---

**Última actualización**: 2025-11-12
**Versión**: 1.0.0
