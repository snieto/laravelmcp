# 🚀 TaskMaster AI Platform

> Sistema de Gestión de Tareas Inteligente con Laravel MCP

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-14+-336791?logo=postgresql)](https://postgresql.org)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-4E56A6?logo=livewire)](https://livewire.laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 📖 Sobre el Proyecto

**TaskMaster AI** es una aplicación completa de gestión de tareas que demuestra la integración del **Model Context Protocol (MCP)** con Laravel. El proyecto combina arquitectura **Domain-Driven Design (DDD)**, integración con **OpenAI**, y una interfaz moderna con **Livewire**.

### 🎯 Objetivos

- ✅ Aprender a implementar servidores MCP en Laravel
- ✅ Demostrar diferentes tipos de servidores (Tools, Resources, Prompts)
- ✅ Integrar IA (OpenAI) en aplicaciones Laravel
- ✅ Aplicar arquitectura DDD con SOLID principles
- ✅ Desarrollar con TDD (Test-Driven Development)
- ✅ Crear un proyecto portfolio profesional

## ✨ Características

### 🎨 Funcionalidades Core

- 📋 **Gestión de Tareas**: CRUD completo con estados, prioridades y asignaciones
- 🎯 **Proyectos**: Organización de tareas por proyectos
- 👥 **Multi-usuario**: Sistema de autenticación con roles
- 🏷️ **Etiquetas**: Categorización flexible de tareas
- 💬 **Comentarios**: Discusión colaborativa en tareas

### 🤖 Integración con IA

- 🧠 **Generación de Descripciones**: OpenAI genera descripciones técnicas detalladas
- 📊 **Análisis de Productividad**: IA analiza métricas y sugiere mejoras
- 🎯 **Priorización Inteligente**: Sugerencias automáticas de prioridades
- 📄 **Reportes Narrativos**: Generación de reportes con análisis de IA

### 🔌 Servidores MCP

| Servidor | Tipo | Endpoint | Descripción |
|----------|------|----------|-------------|
| **Task Tools** | Tools | `/mcp/tasks` | CRUD de tareas desde clientes MCP |
| **AI Assistant** | Tools | `/mcp/ai` | Generación de contenido con IA |
| **Analytics** | Resources | `/mcp/analytics` | Métricas y estadísticas |
| **Reports** | Tools | `/mcp/reports` | Generación de reportes |
| **Task Prompts** | Prompts | Local | Templates para tareas |
| **AI Prompts** | Prompts | Local | Biblioteca de prompts |

## 🏗️ Arquitectura

El proyecto utiliza **Domain-Driven Design** con tres bounded contexts:

```
📦 TaskMaster AI
├── 🎯 Task Management (Gestión de tareas y proyectos)
├── 🤖 AI Integration (Integración con OpenAI)
└── 📊 Analytics & Reporting (Métricas y reportes)
```

### Stack Tecnológico

- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Livewire 3, Alpine.js, Tailwind CSS
- **Base de Datos**: PostgreSQL 14+
- **IA**: OpenAI GPT-4
- **Testing**: Pest PHP
- **MCP**: Laravel MCP Package
- **Code Quality**: Laravel Pint, Larastan

## 🚀 Inicio Rápido

### Requisitos Previos

```bash
PHP >= 8.2
Composer >= 2.5
Node.js >= 18
PostgreSQL >= 14
```

### Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/snieto/laravelmcp.git
cd laravelmcp

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
DB_CONNECTION=pgsql
DB_DATABASE=taskmaster
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# 5. Configurar OpenAI en .env
OPENAI_API_KEY=sk-tu-api-key

# 6. Ejecutar migraciones y seeders
php artisan migrate --seed

# 7. Compilar assets
npm run dev

# 8. Levantar servidor
php artisan serve
```

Visita: http://localhost:8000

### Usuario de Prueba

```
Email: admin@taskmaster.test
Password: password
```

## 📚 Documentación

- 📘 [**CLAUDE.md**](docs/CLAUDE.md) - Guía completa del proyecto para Claude
- 🤖 [**AGENTS.md**](docs/AGENTS.md) - Referencia de servidores MCP
- 🏛️ [**ARCHITECTURE.md**](docs/ARCHITECTURE.md) - Arquitectura técnica detallada
- ⚙️ [**SETUP.md**](docs/SETUP.md) - Guía de instalación completa
- 🔌 [**MCP_USAGE.md**](docs/MCP_USAGE.md) - Ejemplos de uso de MCP

## 🎓 Casos de Uso MCP

### Ejemplo 1: Crear Tarea desde Claude Desktop

```
Usuario: "Crea una tarea para implementar dark mode en el proyecto E-commerce"

Claude (vía MCP):
→ Ejecuta: create_task(title="Implementar dark mode", project_id=1)
→ Ejecuta: generate_task_description(task_id=42)
→ Responde: "Tarea #42 creada con descripción técnica generada por IA"
```

### Ejemplo 2: Análisis de Productividad

```
Usuario: "¿Cómo va el equipo esta semana?"

Claude (vía MCP):
→ Lee: analytics://productivity
→ Ejecuta: generate_report(type="weekly")
→ Responde: Reporte completo con métricas y recomendaciones
```

### Ejemplo 3: Priorización Inteligente

```
Usuario: "¿Qué tareas debería hacer primero?"

Claude (vía MCP):
→ Ejecuta: suggest_priorities(project_id=1)
→ Responde: Lista ordenada con reasoning de IA
```

## 🧪 Testing

```bash
# Ejecutar todos los tests
php artisan test

# Con Pest
./vendor/bin/pest

# Con cobertura
./vendor/bin/pest --coverage

# Solo tests unitarios
./vendor/bin/pest tests/Unit

# Tests de MCP servers
./vendor/bin/pest tests/Integration/MCP
```

## 📊 Estructura del Proyecto

```
taskmaster-ai/
├── app/
│   ├── Domain/              # Lógica de negocio
│   │   ├── TaskManagement/
│   │   ├── AIIntegration/
│   │   └── Analytics/
│   ├── Application/         # Casos de uso
│   ├── Infrastructure/      # Implementaciones técnicas
│   └── Presentation/        # Interfaces (Livewire, API)
├── docs/                    # Documentación
├── routes/
│   ├── web.php
│   ├── api.php
│   └── ai.php              # Rutas MCP
└── tests/
    ├── Unit/
    ├── Feature/
    └── Integration/
```

## 🔐 Configuración de MCP

### Claude Desktop

Edita `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "taskmaster": {
      "url": "http://localhost:8000/mcp/tasks",
      "transport": "http",
      "headers": {
        "Authorization": "Bearer tu-token-aqui"
      }
    }
  }
}
```

### Obtener Token

```bash
php artisan tinker
>>> $user = User::first();
>>> $token = $user->createToken('claude')->plainTextToken;
>>> echo $token;
```

## 🛠️ Desarrollo

### Comandos Útiles

```bash
# Formatear código
./vendor/bin/pint

# Análisis estático
./vendor/bin/phpstan analyse

# Listar servidores MCP
php artisan mcp:list

# Probar servidor MCP
php artisan mcp:test /mcp/tasks

# Limpiar cache
php artisan optimize:clear
```

### Estructura de Commits

```
feat: Nueva funcionalidad
fix: Corrección de bug
docs: Documentación
test: Tests
refactor: Refactorización
style: Formato de código
```

## 📈 Roadmap

- [x] Setup inicial del proyecto
- [ ] Arquitectura DDD completa
- [ ] Implementación de servidores MCP
- [ ] Integración con OpenAI
- [ ] UI con Livewire
- [ ] Dashboard de analytics avanzado
- [ ] Integración con GitHub Issues
- [ ] API GraphQL
- [ ] Mobile app (Flutter)
- [ ] Deployment en producción

## 🤝 Contribución

Este es un proyecto educativo, pero las contribuciones son bienvenidas:

1. Fork el proyecto
2. Crea una rama (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'feat: Add AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Principios de Desarrollo

### SOLID
✅ Single Responsibility
✅ Open/Closed
✅ Liskov Substitution
✅ Interface Segregation
✅ Dependency Inversion

### DDD
✅ Bounded Contexts
✅ Value Objects
✅ Domain Events
✅ Repository Pattern
✅ Domain Services

### TDD
✅ Test First
✅ Red-Green-Refactor
✅ High Coverage
✅ Integration Tests

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver [LICENSE](LICENSE) para más detalles.

## 👤 Autor

**Tutorial Laravel MCP**

- GitHub: [@snieto](https://github.com/snieto)

## 🙏 Agradecimientos

- [Laravel](https://laravel.com) - Framework PHP
- [Laravel MCP](https://laravel.com/docs/12.x/mcp) - Model Context Protocol
- [OpenAI](https://openai.com) - API de IA
- [Livewire](https://livewire.laravel.com) - Framework reactivo
- [Pest](https://pestphp.com) - Testing framework

## 📞 Soporte

- 📚 Consulta la [documentación](docs/)
- 🐛 Reporta bugs en [Issues](https://github.com/snieto/laravelmcp/issues)

---

**⭐ Si este proyecto te ayudó a aprender Laravel MCP, dale una estrella en GitHub!**

---

Hecho con ❤️ usando Laravel y MCP
