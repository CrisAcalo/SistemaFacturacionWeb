# ENDPOINTS API - FASE 3: GESTIÓN DE FACTURAS

## Documentación de Endpoints Implementados

### 📄 **GESTIÓN DE FACTURAS (Protegidos - Requieren Bearer Token)**

#### 1. **GET /api/invoices**
- **Descripción**: Obtener lista de facturas con filtros y paginación
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `invoices.view`, `invoices.manage`, `admin.full`
- **Query Parameters**:
  ```
  ?search=INV-202501
  &status=Pagada|Pendiente|Anulada
  &client_id=5
  &user_id=2
  &date_from=2025-01-01
  &date_to=2025-12-31
  &min_total=100.00
  &max_total=1000.00
  &per_page=15
  &sort_by=created_at
  &sort_direction=desc
  &include_deleted=false
  ```

#### 2. **POST /api/invoices**
- **Descripción**: Crear una nueva factura
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `invoices.create`, `invoices.manage`, `admin.full`
```json
{
    "client_id": 5,
    "status": "Pendiente",
    "notes": "Factura de productos tecnológicos",
    "tax_rate": 0.19,
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "price": 29.99
        },
        {
            "product_id": 2,
            "quantity": 1,
            "price": 149.99
        }
    ]
}
```

#### 3. **GET /api/invoices/{id}**
- **Descripción**: Obtener una factura específica con detalles completos
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `invoices.view`, `invoices.manage`, `admin.full` (o ser el creador/cliente)

#### 4. **PUT /api/invoices/{id}**
- **Descripción**: Actualizar una factura existente (solo status y notes)
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `invoices.edit`, `invoices.manage`, `admin.full`
- **Nota**: Solo se pueden editar facturas en estado "Pendiente"
```json
{
    "status": "Pagada",
    "notes": "Factura pagada en efectivo"
}
```

#### 5. **DELETE /api/invoices/{id}**
- **Descripción**: Eliminar una factura (soft delete) y restaurar stock
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `invoices.delete`, `invoices.manage`, `admin.full`
- **Restricciones**: No se puede eliminar si tiene pagos registrados

#### 6. **POST /api/invoices/{id}/restore**
- **Descripción**: Restaurar una factura eliminada y descontar stock nuevamente
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `invoices.restore`, `invoices.manage`, `admin.full`

#### 7. **PUT /api/invoices/{id}/status**
- **Descripción**: Actualizar el estado de una factura
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `invoices.status`, `invoices.manage`, `admin.full`
```json
{
    "status": "Pagada"
}
```

#### 8. **GET /api/invoices/statistics**
- **Descripción**: Obtener estadísticas de facturas
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `invoices.statistics`, `invoices.view`, `invoices.manage`, `admin.full`
- **Query Parameters**: `?date_from=2025-01-01&date_to=2025-12-31`

---

## 🔐 **PERMISOS DE FACTURAS**

### Permisos Granulares:
- `invoices.view` - Ver facturas
- `invoices.create` - Crear facturas
- `invoices.edit` - Editar facturas (solo estado Pendiente)
- `invoices.delete` - Eliminar facturas (sin pagos)
- `invoices.restore` - Restaurar facturas eliminadas
- `invoices.status` - Cambiar estado de facturas
- `invoices.statistics` - Ver estadísticas
- `invoices.manage` - Gestión completa (incluye todos los anteriores)
- `admin.full` - Acceso total del administrador

### Roles con Permisos:
- **Administrador**: Todos los permisos
- **Ventas**: Gestión completa de facturas
- **Cliente**: Solo puede ver sus propias facturas (como cliente o creador)

---

## 📊 **RESPUESTAS DE LA API**

### Estructura de Respuesta de Factura:
```json
{
    "success": true,
    "message": "Factura obtenida exitosamente",
    "data": {
        "id": 1,
        "invoice_number": "INV-20250101001",
        "user": {
            "id": 2,
            "name": "Vendedor Demo",
            "email": "vendedor@example.com"
        },
        "client": {
            "id": 5,
            "name": "Cliente Demo",
            "email": "cliente@example.com"
        },
        "subtotal": "209.97",
        "tax": "39.89",
        "total": "249.86",
        "formatted_subtotal": "$209.97",
        "formatted_tax": "$39.89",
        "formatted_total": "$249.86",
        "status": "Pendiente",
        "notes": "Factura de productos tecnológicos",
        "items": [
            {
                "id": 1,
                "product": {
                    "id": 1,
                    "name": "Producto Demo",
                    "sku": "PROD-001",
                    "current_stock": 98
                },
                "quantity": 2,
                "price": "29.99",
                "total": "59.98",
                "formatted_price": "$29.99",
                "formatted_total": "$59.98"
            }
        ],
        "payments_summary": {
            "total_paid": "0.00",
            "formatted_total_paid": "$0.00",
            "pending_balance": "249.86",
            "formatted_pending_balance": "$249.86",
            "is_fully_paid": false,
            "has_partial_payments": false,
            "payments_count": 0
        },
        "is_deleted": false,
        "created_at": "2025-08-18T10:00:00.000000Z",
        "updated_at": "2025-08-18T10:00:00.000000Z"
    }
}
```

### Estructura de Respuesta de Lista (Collection):
```json
{
    "success": true,
    "message": "Facturas obtenidas exitosamente",
    "data": {
        "invoices": [...],
        "stats": {
            "total_invoices": 50,
            "total_amount": 12567.89,
            "average_amount": 251.36,
            "by_status": {
                "paid": 30,
                "pending": 15,
                "cancelled": 5
            },
            "amounts_by_status": {
                "paid": 8456.78,
                "pending": 3567.89,
                "cancelled": 543.22
            },
            "total_items": 125
        }
    },
    "meta": {
        "total": 50,
        "per_page": 15,
        "current_page": 1,
        "last_page": 4,
        "from": 1,
        "to": 15
    }
}
```

### Estructura de Respuesta de Estadísticas:
```json
{
    "success": true,
    "message": "Estadísticas obtenidas exitosamente",
    "data": {
        "total_invoices": 150,
        "total_amount": 45678.90,
        "by_status": {
            "paid": 120,
            "pending": 25,
            "cancelled": 5
        },
        "amounts_by_status": {
            "paid": 38967.45,
            "pending": 6234.56,
            "cancelled": 476.89
        }
    }
}
```

---

## 🧪 **EJEMPLOS DE USO**

### Crear una factura:
```bash
curl -X POST "http://localhost/api/invoices" \
-H "Authorization: Bearer your_token_here" \
-H "Content-Type: application/json" \
-d '{
    "client_id": 5,
    "status": "Pendiente",
    "notes": "Factura de prueba",
    "tax_rate": 0.19,
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "price": 29.99
        },
        {
            "product_id": 2,
            "quantity": 1,
            "price": 149.99
        }
    ]
}'
```

### Buscar facturas:
```bash
curl -X GET "http://localhost/api/invoices?search=INV-2025&status=Pendiente&min_total=100&per_page=10" \
-H "Authorization: Bearer your_token_here"
```

### Actualizar estado de factura:
```bash
curl -X PUT "http://localhost/api/invoices/1/status" \
-H "Authorization: Bearer your_token_here" \
-H "Content-Type: application/json" \
-d '{
    "status": "Pagada"
}'
```

### Obtener estadísticas:
```bash
curl -X GET "http://localhost/api/invoices/statistics?date_from=2025-01-01&date_to=2025-12-31" \
-H "Authorization: Bearer your_token_here"
```
