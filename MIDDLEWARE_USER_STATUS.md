# Sistema de Control de Estado de Usuarios

## Descripción General

Se ha implementado un sistema completo de middleware para controlar el acceso de usuarios basado en su estado (activo/inactivo) y estado de eliminación (soft delete). El sistema previene el acceso a usuarios inactivos o eliminados y proporciona una experiencia de usuario mejorada.

## Componentes Implementados

### 1. Middleware: CheckUserStatus
**Archivo**: `app/Http/Middleware/CheckUserStatus.php`

**Funcionalidades**:
- Verifica si el usuario está autenticado
- Valida el estado del usuario (activo/inactivo)
- Verifica si el usuario ha sido eliminado (soft delete)
- Desconecta automáticamente usuarios inactivos o eliminados
- Redirige a login con mensajes apropiados
- Soporte para respuestas JSON (APIs)

**Registro**: El middleware se registra globalmente en `bootstrap/app.php` para todas las rutas web.

### 2. Eventos y Listeners

#### Evento: UserStatusChanged
**Archivo**: `app/Events/UserStatusChanged.php`
- Se dispara cuando cambia el estado de un usuario
- Contiene información del usuario, estado anterior y nuevo estado

#### Listener: HandleUserStatusChanged  
**Archivo**: `app/Listeners/HandleUserStatusChanged.php`
- Escucha el evento UserStatusChanged
- Invalida automáticamente todas las sesiones del usuario cuando es desactivado
- Registra la acción en el log de actividades

### 3. Actualizaciones del Modelo User
**Archivo**: `app/Models/User.php`
- Agregado campo 'status' a los fillable
- Soporte para estados: 'active', 'inactive'

### 4. Migración de Base de Datos
**Archivo**: `database/migrations/0001_01_01_000000_create_users_table.php`
- Campo `status` como ENUM('active', 'inactive') con valor por defecto 'active'
- Mantenimiento de soft deletes existente

### 5. Formularios Actualizados

#### UserFormObject
**Archivo**: `app/Livewire/Forms/Users/UserFormObject.php`
- Agregado campo `status` con validación
- Valor por defecto 'active' para nuevos usuarios

### 6. Componente Livewire Mejorado
**Archivo**: `app/Livewire/Users/ListUsers.php`

**Nuevas funcionalidades**:
- Método `toggleUserStatus()` para cambiar estado de usuarios
- Filtro de usuarios por estado
- Disparo de eventos al cambiar estado
- Protección para evitar que usuarios cambien su propio estado
- Inclusión del campo status en consultas

### 7. Vistas Mejoradas

#### Lista de Usuarios
**Archivo**: `resources/views/livewire/users/list-users.blade.php`

**Mejoras de diseño**:
- Avatares con iniciales del usuario
- Indicadores visuales mejorados para estados
- Botones de acción con iconos y colores apropiados
- Filtro de estado con diseño moderno
- Roles con badges mejorados
- Botones de cambio de estado tipo toggle

#### Modal de Usuario
**Archivo**: `resources/views/livewire/users/modals.blade.php`

**Mejoras**:
- Selector de estado visual con radio buttons
- Indicadores claros de qué hace cada estado
- Diseño más intuitivo y moderno

#### Página de Login
**Archivo**: `resources/views/livewire/pages/auth/login.blade.php`

**Mejoras**:
- Mensajes de error mejorados con iconos
- Información adicional para usuarios sobre contactar administrador
- Diseño más profesional para errores de acceso

## Flujo de Funcionamiento

### 1. Usuario Activo
- Acceso normal al sistema
- Todas las funcionalidades disponibles

### 2. Usuario Inactivo
- El middleware detecta el estado 'inactive'
- Cierra la sesión automáticamente
- Redirige a login con mensaje explicativo
- El listener invalida todas las sesiones del usuario

### 3. Usuario Eliminado (Soft Delete)
- El middleware verifica usuarios con deleted_at no nulo
- Cierra la sesión automáticamente
- Redirige a login con mensaje apropiado

### 4. Cambio de Estado por Administrador
- Administrador puede cambiar estado desde la interfaz
- Se dispara evento UserStatusChanged
- Se invalidan sesiones si el usuario es desactivado
- Se registra la acción en logs de actividad

## Características de Seguridad

1. **Middleware Global**: Aplicado automáticamente a todas las rutas web
2. **Verificación en Tiempo Real**: Cada petición verifica el estado del usuario
3. **Invalidación de Sesiones**: Las sesiones se invalidan automáticamente al desactivar usuario
4. **Logs de Auditoría**: Todas las acciones se registran con Spatie ActivityLog
5. **Protección de Automodificación**: Los usuarios no pueden cambiar su propio estado
6. **Mensajes Claros**: Información clara para usuarios sobre el motivo del bloqueo

## Configuración de Estados

### Estados Disponibles:
- `active`: Usuario puede acceder normalmente
- `inactive`: Usuario bloqueado temporalmente
- `deleted` (soft delete): Usuario eliminado del sistema

### Flujo de Estados:
```
Nuevo Usuario → active (por defecto)
     ↓
active ↔ inactive (cambio manual por admin)
     ↓
soft delete (eliminación por admin)
```

## Testing y Validación

Para validar el funcionamiento:

1. Crear usuario en estado activo - debe poder acceder
2. Cambiar usuario a inactivo - debe ser desconectado inmediatamente
3. Eliminar usuario (soft delete) - debe ser desconectado
4. Verificar mensajes en página de login
5. Verificar que las sesiones se invaliden correctamente

## Notas Técnicas

- Compatible con modo oscuro/claro
- Responsive design
- Integración con sistema de toasts existente
- Uso de Alpine.js para interactividad
- Compatible con Livewire y navegación SPA
- Soporte para APIs con respuestas JSON
