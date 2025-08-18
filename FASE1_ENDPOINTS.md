# ENDPOINTS API - FASE 1: AUTENTICACIÓN Y USUARIOS

## Documentación de Endpoints Implementados

### 🔐 **AUTENTICACIÓN (Públicos)**

#### 1. **POST /api/auth/login**
```json
{
    "email": "admin@example.com",
    "password": "password",
    "remember": false
}
```

#### 2. **POST /api/auth/register**
```json
{
    "name": "Usuario Nuevo",
    "email": "nuevo@example.com", 
    "password": "Password123!",
    "password_confirmation": "Password123!"
}
```

#### 3. **POST /api/auth/forgot-password**
```json
{
    "email": "admin@example.com"
}
```

#### 4. **POST /api/auth/reset-password**
```json
{
    "token": "reset_token_here",
    "email": "admin@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
}
```

---

### 🔒 **AUTENTICACIÓN (Protegidos - Requieren Bearer Token)**

#### 5. **POST /api/auth/logout**
- Headers: `Authorization: Bearer {token}`

#### 6. **POST /api/auth/logout-all**
- Headers: `Authorization: Bearer {token}`

#### 7. **POST /api/auth/refresh**
- Headers: `Authorization: Bearer {token}`

#### 8. **GET /api/auth/me**
- Headers: `Authorization: Bearer {token}`

---

### 👥 **GESTIÓN DE USUARIOS (Protegidos)**

#### 9. **GET /api/users**
- Headers: `Authorization: Bearer {token}`
- Query params: `?search=nombre&status=active&role=Admin&per_page=15&sort_by=created_at&sort_direction=desc&include_deleted=false`

#### 10. **POST /api/users**
```json
{
    "name": "Usuario API",
    "email": "usuario@api.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "status": "active",
    "roles": ["Cliente"]
}
```

#### 11. **GET /api/users/{id}**
- Headers: `Authorization: Bearer {token}`

#### 12. **PUT /api/users/{id}**
```json
{
    "name": "Usuario Actualizado",
    "email": "actualizado@api.com",
    "status": "inactive"
}
```

#### 13. **DELETE /api/users/{id}**
- Headers: `Authorization: Bearer {token}`

#### 14. **POST /api/users/{id}/restore**
- Headers: `Authorization: Bearer {token}`

#### 15. **PUT /api/users/{id}/status**
```json
{
    "status": "inactive"
}
```

---

### 🎭 **ROLES Y PERMISOS**

#### 16. **GET /api/users/{id}/roles**
- Headers: `Authorization: Bearer {token}`

#### 17. **PUT /api/users/{id}/roles**
```json
{
    "roles": ["Administrador", "Cliente"]
}
```

#### 18. **GET /api/roles**
- Headers: `Authorization: Bearer {token}`

#### 19. **GET /api/permissions**
- Headers: `Authorization: Bearer {token}`

---
