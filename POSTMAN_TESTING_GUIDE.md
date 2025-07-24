# 🚀 Guía Paso a Paso - Probar API en Postman

## 📋 Pasos Previos

### 1. Verificar que el servidor esté ejecutándose
- Si usas Laragon, asegúrate de que esté iniciado
- O ejecuta: `php artisan serve` en la terminal
- La URL base será: `http://tu-dominio.test` o `http://localhost:8000`

### 2. Crear un Token desde la Interfaz Web
1. Ve a: `http://tu-dominio.test/api-tokens`
2. Haz clic en **"Crear Token"**
3. Completa el formulario:
   - **Nombre**: "API Test Token"
   - **Usuario**: Selecciona un usuario
   - **Descripción**: "Token para pruebas con Postman"
4. Haz clic en **"Crear Token"**
5. **IMPORTANTE**: Copia el token completo que aparece en la lista (columna "Valor del Token")

---

## 🔧 Configuración en Postman

### Crear Nueva Request
1. Abre Postman
2. Crea una nueva request
3. Configura los siguientes valores:

#### Método y URL
- **Método**: `GET`
- **URL**: `http://tu-dominio.test/api/token-info`
  - Reemplaza `tu-dominio.test` por tu dominio local
  - O usa `http://localhost:8000/api/token-info` si usas `php artisan serve`

#### Headers
Agrega estos headers manualmente:
- **Key**: `Accept` | **Value**: `application/json`
- **Key**: `Content-Type` | **Value**: `application/json`

#### Authorization
1. Ve a la pestaña **"Authorization"**
2. **Type**: Selecciona `Bearer Token`
3. **Token**: Pega aquí el token que copiaste de la interfaz web

---

## ✅ Prueba 1: Token Válido

### Enviar Request
1. Haz clic en el botón **"Send"**
2. Deberías recibir una respuesta **200 OK**

### Respuesta Esperada
```json
{
    "success": true,
    "message": "Token válido",
    "data": {
        "token_info": {
            "id": 1,
            "name": "API Test Token",
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
            "name": "Nombre del Usuario",
            "email": "usuario@example.com",
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

## ❌ Prueba 2: Token Inválido

### Modificar Token
1. En Authorization, cambia algunas letras del token
2. Envía la request nuevamente

### Respuesta Esperada (401)
```json
{
    "success": false,
    "message": "Token no válido"
}
```

---

## ❌ Prueba 3: Sin Token

### Quitar Authorization
1. En Authorization, selecciona **"No Auth"**
2. Envía la request

### Respuesta Esperada (401)
```json
{
    "success": false,
    "message": "Token no proporcionado"
}
```

---

## 🔍 Verificaciones Adicionales

### 1. Verificar en la Base de Datos
Después de una request exitosa, verifica que `last_used_at` se actualizó:
- Ve a la interfaz web de tokens
- Busca tu token en la lista
- La columna "Última Actividad" debería mostrar "hace unos segundos"

### 2. Verificar Estados del Token
En la interfaz web, puedes:
- **Desactivar** el token (botón toggle)
- Intentar usar el token desactivado en Postman
- Debería retornar error de "Token inactivo o expirado"

---

## 🐛 Solución de Problemas

### Error de Conexión
- Verifica que el servidor esté ejecutándose
- Verifica la URL (puerto, dominio)
- Verifica que las rutas API estén habilitadas

### Error 404
- Verifica que la URL sea exactamente: `/api/token-info`
- Verifica que las rutas API estén registradas: `php artisan route:list --path=api`

### Error 401 con Token Válido
- Verifica que copiaste el token completo
- Verifica que el token no esté desactivado en la interfaz
- Verifica que el token no haya expirado

### Error 500
- Revisa los logs: `storage/logs/laravel.log`
- Verifica que la base de datos esté conectada
- Verifica que las migraciones estén ejecutadas

---

## 🎯 Colección de Postman (Opcional)

Puedes crear una colección con estos valores predefinidos:

### Variables de Colección
- `base_url`: `http://tu-dominio.test`
- `api_token`: `tu-token-aqui`

### Request con Variables
- **URL**: `{{base_url}}/api/token-info`
- **Authorization**: Bearer Token `{{api_token}}`

¡Esto te permitirá cambiar fácilmente entre diferentes tokens y entornos! 🚀
