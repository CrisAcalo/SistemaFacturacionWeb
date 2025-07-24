# API Endpoints - Documentación para Postman

## 🚀 Endpoint: Verificar Token

### **GET** `/api/token-info`

Valida un bearer token y retorna información básica del token y usuario asociado.

---

## 📋 Configuración en Postman

### 1. Configuración Básica
- **Método**: `GET`
- **URL**: `http://tu-dominio.test/api/token-info`
- **Headers**:
  - `Accept`: `application/json`
  - `Content-Type`: `application/json`

### 2. Autenticación
- **Tipo**: `Bearer Token`
- **Token**: `[tu-token-generado-desde-la-interfaz]`

---

## 📤 Ejemplo de Respuesta Exitosa (200)

```json
{
    "success": true,
    "message": "Token válido",
    "data": {
        "token_info": {
            "id": 1,
            "name": "API Token de Prueba",
            "description": "Token para pruebas con Postman",
            "abilities": ["*"],
            "created_at": "2025-07-24 10:30:00",
            "expires_at": null,
            "last_used_at": "2025-07-24 11:45:23",
            "is_active": true,
            "status": "active"
        },
        "user_info": {
            "id": 1,
            "name": "Usuario Admin",
            "email": "admin@example.com",
            "status": "active",
            "roles": ["admin"]
        },
        "request_info": {
            "timestamp": "2025-07-24 11:45:23",
            "ip_address": "127.0.0.1",
            "user_agent": "PostmanRuntime/7.32.2"
        }
    }
}
```

---

## ❌ Ejemplos de Respuestas de Error

### Sin Token (401)
```json
{
    "success": false,
    "message": "Token no proporcionado"
}
```

### Token Inválido (401)
```json
{
    "success": false,
    "message": "Token no válido"
}
```

### Token Inactivo/Expirado (401)
```json
{
    "success": false,
    "message": "Token inactivo o expirado"
}
```

### Error Interno (500)
```json
{
    "success": false,
    "message": "Error interno del servidor",
    "error": "Descripción del error (solo en modo debug)"
}
```

---

## 🧪 Pasos para Probar en Postman

### 1. Crear un Token
1. Ve a la interfaz web: `http://tu-dominio.test/api-tokens`
2. Crea un nuevo token con:
   - **Nombre**: "API Token de Prueba"
   - **Usuario**: Selecciona un usuario
   - **Descripción**: "Token para pruebas con Postman"
3. Copia el token generado

### 2. Configurar Postman
1. Crea una nueva request
2. Método: `GET`
3. URL: `http://tu-dominio.test/api/token-info`
4. En Authorization:
   - Type: `Bearer Token`
   - Token: Pega el token copiado

### 3. Enviar Request
1. Haz clic en "Send"
2. Deberías recibir una respuesta 200 con toda la información

---

## 🔧 Funcionalidades del Endpoint

### ✅ Validaciones Incluidas:
- Verificación de presencia del token
- Validación del token en base de datos
- Verificación de estado activo/inactivo
- Verificación de fecha de expiración
- Actualización automática de `last_used_at`

### 📊 Información Retornada:
- **Token Info**: ID, nombre, descripción, permisos, fechas, estado
- **User Info**: Datos básicos del usuario propietario del token
- **Request Info**: Timestamp, IP, User-Agent de la request

### 🔒 Seguridad:
- Middleware `auth:sanctum` para autenticación
- Verificación de estado del token
- Manejo de errores sin exponer información sensible
- Logging automático de uso del token

---

## 🎯 Casos de Uso

1. **Validación de Token**: Verificar si un token es válido antes de hacer otras requests
2. **Información de Usuario**: Obtener datos del usuario sin hacer requests adicionales
3. **Debugging**: Verificar permisos, estado y metadata del token
4. **Auditoría**: El endpoint actualiza automáticamente `last_used_at`

---

## 📝 Notas Importantes

- El endpoint actualiza la fecha de último uso automáticamente
- Los tokens con `is_active = false` serán rechazados
- Los tokens expirados serán rechazados
- En modo debug se muestran detalles de errores, en producción se ocultan
- El endpoint respeta el middleware de autenticación de Sanctum

¡Listo para probar! 🚀
