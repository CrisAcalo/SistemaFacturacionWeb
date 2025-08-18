# ENDPOINTS API - FASE 2: GESTIÓN DE PRODUCTOS

## Documentación de Endpoints Implementados

### 📦 **GESTIÓN DE PRODUCTOS (Protegidos - Requieren Bearer Token)**

#### 1. **GET /api/products**
- **Descripción**: Obtener lista de productos con filtros y paginación
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `products.view`, `products.manage`, `admin.full`
- **Query Parameters**:
  ```
  ?search=producto
  &stock_status=in_stock|out_of_stock|low_stock
  &min_price=10.00
  &max_price=100.00
  &per_page=15
  &sort_by=created_at
  &sort_direction=desc
  &include_deleted=false
  ```

#### 2. **POST /api/products**
- **Descripción**: Crear un nuevo producto
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `products.create`, `products.manage`, `admin.full`
```json
{
    "name": "Producto Ejemplo",
    "sku": "PROD-001",
    "barcode": "1234567890123",
    "description": "Descripción del producto",
    "stock": 100,
    "price": 29.99
}
```

#### 3. **GET /api/products/{id}**
- **Descripción**: Obtener un producto específico
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `products.view`, `products.manage`, `admin.full`

#### 4. **PUT /api/products/{id}**
- **Descripción**: Actualizar un producto existente
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `products.edit`, `products.manage`, `admin.full`
```json
{
    "name": "Producto Actualizado",
    "sku": "PROD-001-UPD",
    "barcode": "1234567890124",
    "description": "Nueva descripción",
    "stock": 150,
    "price": 34.99
}
```

#### 5. **DELETE /api/products/{id}**
- **Descripción**: Eliminar un producto (soft delete)
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `products.delete`, `products.manage`, `admin.full`

#### 6. **POST /api/products/{id}/restore**
- **Descripción**: Restaurar un producto eliminado
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `products.restore`, `products.manage`, `admin.full`

#### 7. **PUT /api/products/{id}/stock**
- **Descripción**: Actualizar el stock de un producto
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `products.stock`, `products.manage`, `admin.full`
```json
{
    "stock": 50,
    "operation": "set|add|subtract"
}
```

#### 8. **GET /api/products/low-stock**
- **Descripción**: Obtener productos con stock bajo
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `products.view`, `products.stock`, `products.manage`, `admin.full`
- **Query Parameters**: `?threshold=10`

#### 9. **POST /api/products/bulk-update**
- **Descripción**: Actualizar múltiples productos en lote
- **Headers**: `Authorization: Bearer {token}`
- **Permisos**: `products.bulk`, `products.manage`, `admin.full`
```json
{
    "products": [
        {
            "id": 1,
            "stock": 100,
            "price": 29.99
        },
        {
            "id": 2,
            "stock": 75,
            "price": 19.99
        }
    ]
}
```

---

## 🔐 **PERMISOS DE PRODUCTOS**

### Permisos Granulares:
- `products.view` - Ver productos
- `products.create` - Crear productos
- `products.edit` - Editar productos
- `products.delete` - Eliminar productos
- `products.restore` - Restaurar productos eliminados
- `products.stock` - Gestionar stock
- `products.bulk` - Operaciones en lote
- `products.manage` - Gestión completa (incluye todos los anteriores)
- `admin.full` - Acceso total del administrador

### Roles con Permisos:
- **Administrador**: Todos los permisos
- **Bodega**: Gestión completa de productos
- **Ventas**: Solo visualización (para crear facturas)

---

## 📊 **RESPUESTAS DE LA API**

### Estructura de Respuesta de Producto:
```json
{
    "success": true,
    "message": "Producto obtenido exitosamente",
    "data": {
        "id": 1,
        "name": "Producto Ejemplo",
        "sku": "PROD-001",
        "barcode": "1234567890123",
        "description": "Descripción del producto",
        "stock": 100,
        "price": "29.99",
        "formatted_price": "$29.99",
        "stock_status": "in_stock",
        "is_deleted": false,
        "created_at": "2025-08-18T10:00:00.000000Z",
        "updated_at": "2025-08-18T10:00:00.000000Z",
        "deleted_at": null
    }
}
```

### Estructura de Respuesta de Lista (Collection):
```json
{
    "success": true,
    "message": "Productos obtenidos exitosamente",
    "data": {
        "products": [...],
        "stats": {
            "total_products": 150,
            "in_stock": 120,
            "out_of_stock": 5,
            "low_stock": 25,
            "average_price": 45.67,
            "total_inventory_value": 15750.50
        }
    },
    "meta": {
        "total": 150,
        "per_page": 15,
        "current_page": 1,
        "last_page": 10,
        "from": 1,
        "to": 15
    }
}
```

### Estados de Stock:
- `in_stock` - En stock (stock > 10)
- `low_stock` - Stock bajo (stock > 0 y <= 10)
- `out_of_stock` - Sin stock (stock <= 0)

---

## 🧪 **EJEMPLOS DE USO**

### Crear un producto:
```bash
curl -X POST "http://localhost/api/products" \
-H "Authorization: Bearer your_token_here" \
-H "Content-Type: application/json" \
-d '{
    "name": "Laptop Gaming",
    "sku": "LAP-GAM-001",
    "barcode": "7890123456789",
    "description": "Laptop para gaming de alta gama",
    "stock": 25,
    "price": 1299.99
}'
```

### Buscar productos:
```bash
curl -X GET "http://localhost/api/products?search=laptop&stock_status=in_stock&min_price=500&per_page=10" \
-H "Authorization: Bearer your_token_here"
```

### Actualizar stock:
```bash
curl -X PUT "http://localhost/api/products/1/stock" \
-H "Authorization: Bearer your_token_here" \
-H "Content-Type: application/json" \
-d '{
    "stock": 10,
    "operation": "add"
}'
```

### Ver productos con stock bajo:
```bash
curl -X GET "http://localhost/api/products/low-stock?threshold=5" \
-H "Authorization: Bearer your_token_here"
```
