# 🔐 FASE 4: TOKENS API - GESTIÓN DE TOKENS DE ACCESO

## 📋 Resumen de la Fase

**Funcionalidad**: Sistema completo de gestión de tokens de acceso API con funcionalidades de seguridad, auditoría y control granular.

**Características principales**:
- ✅ Gestión completa de tokens personales
- ✅ Control de permisos granular por rol
- ✅ Auditoría completa de uso de tokens
- ✅ Seguridad avanzada (tokens nunca se exponen)
- ✅ Control de estado sin modificación de tokens
- ❌ No incluye reportes (por solicitud del usuario)
- ❌ No permite actualización de tokens (solo revocación)
- ❌ No incluye logs detallados (solo auditoría)

---

## 🛠️ Endpoints Implementados

### **1. Listar Tokens del Usuario** 
**`GET /api/tokens`**

**Descripción**: Obtiene todos los tokens del usuario autenticado con información de seguridad.

**Headers requeridos**:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Permisos**: `tokens.view`

**Respuesta exitosa (200)**:
```json
{
    "status": "success",
    "message": "Tokens obtenidos correctamente",
    "data": [
        {
            "id": 1,
            "name": "API Mobile App",
            "abilities": ["*"],
            "last_used_at": "2025-08-18T10:30:00.000000Z",
            "expires_at": null,
            "created_at": "2025-08-18T08:00:00.000000Z",
            "security_analysis": {
                "is_expired": false,
                "days_since_last_use": 0,
                "has_broad_permissions": true,
                "security_level": "high_risk"
            }
        }
    ],
    "meta": {
        "total": 1,
        "active_tokens": 1,
        "expired_tokens": 0
    }
}
```

---

### **2. Crear Nuevo Token**
**`POST /api/tokens`**

**Descripción**: Crea un nuevo token de acceso personal con habilidades específicas.

**Headers requeridos**:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Permisos**: `tokens.create`

**Body de la petición**:
```json
{
    "name": "API Mobile App",
    "abilities": ["users.view", "invoices.view"],
    "expires_at": "2025-12-31T23:59:59Z"
}
```

**Validaciones**:
- `name`: Requerido, string, máximo 255 caracteres, único por usuario
- `abilities`: Array opcional, cada habilidad debe ser válida
- `expires_at`: Fecha opcional, debe ser futura

**Respuesta exitosa (201)**:
```json
{
    "status": "success",
    "message": "Token creado correctamente",
    "data": {
        "id": 2,
        "name": "API Mobile App",
        "abilities": ["users.view", "invoices.view"],
        "last_used_at": null,
        "expires_at": "2025-12-31T23:59:59.000000Z",
        "created_at": "2025-08-18T10:35:00.000000Z",
        "security_analysis": {
            "is_expired": false,
            "days_since_last_use": null,
            "has_broad_permissions": false,
            "security_level": "low_risk"
        },
        "plainTextToken": "2|abc123def456ghi789..."
    }
}
```

**⚠️ Importante**: El `plainTextToken` **solo se muestra una vez** al crear el token.

---

### **3. Ver Detalles de Token**
**`GET /api/tokens/{id}`**

**Descripción**: Obtiene los detalles de un token específico del usuario.

**Headers requeridos**:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Permisos**: `tokens.view`

**Parámetros de URL**:
- `{id}`: ID del token a consultar

**Respuesta exitosa (200)**:
```json
{
    "status": "success",
    "message": "Token obtenido correctamente",
    "data": {
        "id": 1,
        "name": "API Mobile App",
        "abilities": ["*"],
        "last_used_at": "2025-08-18T10:30:00.000000Z",
        "expires_at": null,
        "created_at": "2025-08-18T08:00:00.000000Z",
        "updated_at": "2025-08-18T08:00:00.000000Z",
        "security_analysis": {
            "is_expired": false,
            "days_since_last_use": 0,
            "has_broad_permissions": true,
            "security_level": "high_risk",
            "usage_frequency": "daily",
            "risk_factors": [
                "Permisos amplios (*)",
                "Uso frecuente"
            ]
        }
    }
}
```

---

### **4. Revocar Token**
**`DELETE /api/tokens/{id}`**

**Descripción**: Revoca permanentemente un token específico del usuario.

**Headers requeridos**:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Permisos**: `tokens.revoke`

**Parámetros de URL**:
- `{id}`: ID del token a revocar

**Respuesta exitosa (200)**:
```json
{
    "status": "success",
    "message": "Token revocado correctamente",
    "data": {
        "revoked_token_id": 1,
        "revoked_at": "2025-08-18T10:40:00.000000Z",
        "revoked_by": "user_action"
    }
}
```

**Notas importantes**:
- ❌ No se puede revocar el token que se está usando actualmente
- ✅ La revocación es permanente e irreversible
- ✅ Se registra automáticamente en la auditoría

---

### **5. Actualizar Estado de Token**
**`PATCH /api/tokens/{id}/status`**

**Descripción**: Actualiza el estado del token (suspender/reactivar) sin modificar el token en sí.

**Headers requeridos**:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Permisos**: `tokens.status`

**Parámetros de URL**:
- `{id}`: ID del token a modificar

**Body de la petición**:
```json
{
    "status": "suspended",
    "reason": "Actividad sospechosa detectada"
}
```

**Estados válidos**:
- `active`: Token activo y funcional
- `suspended`: Token suspendido temporalmente

**Respuesta exitosa (200)**:
```json
{
    "status": "success",
    "message": "Estado del token actualizado correctamente",
    "data": {
        "id": 1,
        "name": "API Mobile App",
        "old_status": "active",
        "new_status": "suspended",
        "updated_at": "2025-08-18T10:45:00.000000Z",
        "reason": "Actividad sospechosa detectada"
    }
}
```

---

### **6. Auditoría de Token**
**`GET /api/tokens/{id}/audit`**

**Descripción**: Obtiene el historial completo de auditoría para un token específico.

**Headers requeridos**:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Permisos**: `tokens.audit`

**Parámetros de URL**:
- `{id}`: ID del token para auditar

**Parámetros de consulta opcionales**:
```
?limit=50&offset=0&event_type=created&date_from=2025-01-01&date_to=2025-12-31
```

**Respuesta exitosa (200)**:
```json
{
    "status": "success",
    "message": "Auditoría obtenida correctamente",
    "data": {
        "token_id": 1,
        "token_name": "API Mobile App",
        "audit_summary": {
            "total_events": 15,
            "created_at": "2025-08-18T08:00:00.000000Z",
            "last_activity": "2025-08-18T10:30:00.000000Z",
            "status_changes": 2,
            "usage_count": 145
        },
        "events": [
            {
                "id": 101,
                "event": "token_created",
                "description": "Token creado por el usuario",
                "properties": {
                    "token_name": "API Mobile App",
                    "abilities": ["*"],
                    "created_by": "user@example.com"
                },
                "created_at": "2025-08-18T08:00:00.000000Z"
            },
            {
                "id": 102,
                "event": "token_used",
                "description": "Token utilizado para acceso API",
                "properties": {
                    "endpoint": "/api/users",
                    "ip_address": "192.168.1.100",
                    "user_agent": "Mobile App v1.0"
                },
                "created_at": "2025-08-18T10:30:00.000000Z"
            }
        ]
    },
    "pagination": {
        "current_page": 1,
        "per_page": 50,
        "total": 15,
        "last_page": 1
    }
}
```

---

## 🔐 Sistema de Permisos

### **Permisos Implementados**:

| Permiso | Descripción | Roles que lo tienen |
|---------|-------------|-------------------|
| `tokens.view` | Ver tokens propios | Cliente, Ventas, Bodega, Pagos, Admin |
| `tokens.create` | Crear nuevos tokens | Cliente, Ventas, Bodega, Pagos, Admin |
| `tokens.revoke` | Revocar tokens propios | Cliente, Ventas, Bodega, Pagos, Admin |
| `tokens.status` | Cambiar estado de tokens | Cliente, Ventas, Bodega, Pagos, Admin |
| `tokens.audit` | Ver auditoría de tokens | Cliente, Ventas, Bodega, Pagos, Admin |
| `tokens.manage` | Gestión completa (todos los tokens) | Solo Admin |

### **Autorización por Endpoint**:

```php
// Solo tokens propios (todos los roles)
GET /api/tokens -> TokenPolicy::viewAny()
POST /api/tokens -> TokenPolicy::create()
GET /api/tokens/{id} -> TokenPolicy::view()

// Operaciones de gestión (solo tokens propios)
DELETE /api/tokens/{id} -> TokenPolicy::delete()
PATCH /api/tokens/{id}/status -> TokenPolicy::updateStatus()
GET /api/tokens/{id}/audit -> TokenPolicy::audit()
```

---

## 🛡️ Características de Seguridad

### **1. Protección de Datos Sensibles**
- ❌ **Nunca se expone el token real** en las respuestas
- ✅ Solo se muestra el `plainTextToken` una vez al crear
- ✅ IDs de token ofuscados en logs de auditoría

### **2. Análisis de Seguridad Automático**
Cada token incluye un análisis de riesgo:

```json
"security_analysis": {
    "is_expired": false,
    "days_since_last_use": 0,
    "has_broad_permissions": true,
    "security_level": "high_risk", // low_risk, medium_risk, high_risk
    "usage_frequency": "daily", // never, rare, weekly, daily
    "risk_factors": [
        "Permisos amplios (*)",
        "Uso frecuente"
    ]
}
```

### **3. Restricciones de Seguridad**
- ❌ No se puede revocar el token actualmente en uso
- ❌ No se pueden actualizar tokens (solo estado)
- ✅ Validación estricta de habilidades permitidas
- ✅ Registro automático de todas las acciones

### **4. Auditoría Completa**
- ✅ Creación, uso, modificación y revocación
- ✅ Metadatos de IP y User-Agent
- ✅ Historial completo por token
- ✅ Filtros avanzados de consulta

---

## 📊 Códigos de Respuesta HTTP

| Código | Descripción | Cuándo ocurre |
|--------|-------------|---------------|
| `200` | OK | Operación exitosa |
| `201` | Created | Token creado exitosamente |
| `400` | Bad Request | Datos de entrada inválidos |
| `401` | Unauthorized | Token de autenticación inválido |
| `403` | Forbidden | Sin permisos para la acción |
| `404` | Not Found | Token no encontrado |
| `422` | Unprocessable Entity | Errores de validación |
| `500` | Internal Server Error | Error interno del servidor |

---

## 🔧 Validaciones Implementadas

### **Crear Token (POST /api/tokens)**:
```php
'name' => 'required|string|max:255|unique:personal_access_tokens,name,NULL,id,tokenable_id,' . auth()->id(),
'abilities' => 'sometimes|array',
'abilities.*' => 'string|in:*,users.view,users.create,users.update,users.delete,products.view,products.create,products.update,products.delete,invoices.view,invoices.create,invoices.update,invoices.delete',
'expires_at' => 'sometimes|date|after:now'
```

### **Actualizar Estado (PATCH /api/tokens/{id}/status)**:
```php
'status' => 'required|in:active,suspended',
'reason' => 'sometimes|string|max:500'
```

---

## 📁 Archivos Implementados

### **1. Controlador**
- `app/Http/Controllers/Api/TokenController.php` - Lógica completa de tokens

### **2. Política de Autorización**
- `app/Policies/TokenPolicy.php` - Control granular de permisos

### **3. Recursos API**
- `app/Http/Resources/Api/TokenResource.php` - Formato de respuesta individual
- `app/Http/Resources/Api/TokenCollection.php` - Formato de respuesta de colección

### **4. Validaciones**
- `app/Http/Requests/Api/Tokens/CreateTokenRequest.php` - Validación para creación

### **5. Configuración**
- `database/seeders/RolesAndPermissionsSeeder.php` - Permisos y roles actualizados
- `app/Providers/AppServiceProvider.php` - Registro de políticas

---

## 🧪 Ejemplos de Uso

### **Ejemplo 1: Crear Token para Aplicación Móvil**
```bash
curl -X POST http://localhost:8000/api/tokens \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mobile App Production",
    "abilities": ["users.view", "invoices.view", "products.view"],
    "expires_at": "2025-12-31T23:59:59Z"
  }'
```

### **Ejemplo 2: Suspender Token Sospechoso**
```bash
curl -X PATCH http://localhost:8000/api/tokens/1/status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "suspended",
    "reason": "Actividad sospechosa desde IP desconocida"
  }'
```

### **Ejemplo 3: Revisar Auditoría de Token**
```bash
curl -X GET "http://localhost:8000/api/tokens/1/audit?limit=10&event_type=token_used" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---
