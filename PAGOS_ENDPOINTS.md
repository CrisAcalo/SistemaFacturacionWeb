# 💳 GESTIÓN DE PAGOS - ENDPOINTS API

## 📋 Resumen de la Funcionalidad

**Módulo**: Sistema de gestión de pagos para facturas
**Propósito**: Permite a los clientes registrar pagos para sus facturas y consultar el estado de los mismos

**Características principales**:
- ✅ Registro de pagos por parte de los clientes
- ✅ Consulta de pagos por cliente
- ✅ Consulta de pagos específicos por factura
- ✅ Validación/Aprobación de pagos por administradores
- ✅ Validación automática de montos y estados
- ✅ Control de saldo pendiente por factura
- ✅ Estados de pago: pendiente, validado, rechazado
- ✅ Tipos de pago: efectivo, tarjeta, transferencia, cheque

---

## 🛠️ Endpoints Implementados

### **1. Listar Pagos del Cliente**
**`GET /api/payments`**

**Descripción**: Obtiene todos los pagos registrados por el cliente autenticado, con opciones de filtrado y paginación.

**Headers requeridos**:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Parámetros de consulta opcionales**:
```
?per_page=15&status=pendiente&page=1
```

**Parámetros disponibles**:
- `per_page`: Número de elementos por página (máximo 100, por defecto 15)
- `status`: Filtrar por estado (`pendiente`, `validado`, `rechazado`)
- `page`: Número de página

**Respuesta exitosa (200)**:
```json
{
    "success": true,
    "message": "Pagos obtenidos exitosamente",
    "data": {
        "payments": [
            {
                "id": 1,
                "invoice": {
                    "id": 15,
                    "invoice_number": "INV-2025-0015",
                    "total": "1500.00",
                    "status": "Pendiente"
                },
                "client": {
                    "id": 8,
                    "name": "Juan Pérez",
                    "email": "juan@example.com"
                },
                "payment_type": "transferencia",
                "transaction_number": "TRX-20250818-0123",
                "amount": "750.00",
                "observations": "Pago parcial primera quincena",
                "status": "pendiente",
                "validated_at": null,
                "validated_by": null,
                "validation_notes": null,
                "created_at": "2025-08-18T10:30:00.000000Z",
                "updated_at": "2025-08-18T10:30:00.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 3,
            "per_page": 15,
            "total": 42,
            "from": 1,
            "to": 15
        },
        "filters_applied": {
            "status": "pendiente"
        }
    }
}
```

---

### **2. Registrar Nuevo Pago**
**`POST /api/payments`**

**Descripción**: Registra un nuevo pago para una factura específica del cliente autenticado.

**Headers requeridos**:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Body de la petición**:
```json
{
    "invoice_id": "INV-2025-0015",
    "payment_type": "transferencia",
    "transaction_number": "TRX-20250818-0456",
    "amount": 750.50,
    "observations": "Pago parcial de la primera quincena"
}
```

**Validaciones aplicadas**:
- `invoice_id`: Requerido, debe existir en el sistema como `invoice_number`
- `payment_type`: Requerido, debe ser uno de: `efectivo`, `tarjeta`, `transferencia`, `cheque`
- `transaction_number`: Requerido excepto para pagos en efectivo, máximo 255 caracteres
- `amount`: Requerido, numérico, entre $0.01 y $999,999.99
- `observations`: Opcional, máximo 1000 caracteres

**Verificaciones de negocio**:
- ✅ La factura debe existir y pertenecer al cliente autenticado
- ✅ La factura no debe estar anulada
- ✅ El monto no puede exceder el saldo pendiente de la factura
- ✅ Se actualiza automáticamente el token usage

**Respuesta exitosa (201)**:
```json
{
    "success": true,
    "message": "Pago registrado exitosamente. Está pendiente de validación.",
    "data": {
        "payment": {
            "id": 25,
            "invoice_id": 15,
            "invoice_number": "INV-2025-0015",
            "payment_type": "transferencia",
            "transaction_number": "TRX-20250818-0456",
            "amount": "750.50",
            "observations": "Pago parcial de la primera quincena",
            "status": "pendiente",
            "created_at": "2025-08-18 14:25:30"
        },
        "invoice_summary": {
            "total_factura": "1,500.00",
            "total_pagado_validado": "0.00",
            "saldo_pendiente": "749.50"
        }
    }
}
```

**Respuesta de error - Monto excedido (422)**:
```json
{
    "success": false,
    "message": "El monto del pago excede el saldo pendiente de la factura",
    "data": {
        "total_factura": 1500.00,
        "total_pagado": 750.00,
        "saldo_pendiente": 750.00,
        "monto_solicitado": 800.00
    }
}
```

**Respuesta de error - Factura no encontrada (404)**:
```json
{
    "success": false,
    "message": "Factura no encontrada"
}
```

**Respuesta de error - Sin permisos (403)**:
```json
{
    "success": false,
    "message": "No tienes permisos para registrar pagos en esta factura"
}
```

---

### **3. Consultar Pagos de una Factura**
**`GET /api/payments/invoice/{invoiceId}`**

**Descripción**: Obtiene todos los pagos asociados a una factura específica del cliente autenticado.

**Headers requeridos**:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Parámetros de URL**:
- `{invoiceId}`: ID numérico de la factura

**Respuesta exitosa (200)**:
```json
{
    "success": true,
    "message": "Pagos obtenidos exitosamente",
    "data": {
        "invoice": {
            "id": 15,
            "invoice_number": "INV-2025-0015",
            "total": "1,500.00",
            "status": "Pendiente"
        },
        "payments": [
            {
                "id": 25,
                "payment_type": "transferencia",
                "transaction_number": "TRX-20250818-0456",
                "amount": "750.50",
                "observations": "Pago parcial de la primera quincena",
                "status": "pendiente",
                "validated_at": null,
                "validated_by": null,
                "validation_notes": null,
                "created_at": "2025-08-18 14:25:30"
            },
            {
                "id": 26,
                "payment_type": "efectivo",
                "transaction_number": null,
                "amount": "749.50",
                "observations": "Pago del saldo restante",
                "status": "validado",
                "validated_at": "2025-08-18 15:30:00",
                "validated_by": "Ana García",
                "validation_notes": "Pago validado correctamente",
                "created_at": "2025-08-18 15:00:00"
            }
        ],
        "summary": {
            "total_factura": "1,500.00",
            "total_validado": "749.50",
            "total_pendiente": "750.50",
            "total_rechazado": "0.00",
            "saldo_pendiente": "0.00",
            "count_payments": 2
        }
    }
}
```

**Respuesta de error - Factura no encontrada (404)**:
```json
{
    "success": false,
    "message": "Factura no encontrada"
}
```

**Respuesta de error - Sin permisos (403)**:
```json
{
    "success": false,
    "message": "No tienes permisos para ver los pagos de esta factura"
}
```

---

### **4. Validar Pago (Aprobar/Rechazar) - Solo Administradores**
**`PATCH /api/payments/{id}/validate`**

**Descripción**: Permite a los administradores aprobar o rechazar pagos pendientes. Esta funcionalidad está restringida a usuarios con permisos administrativos.

**Headers requeridos**:
```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Permisos requeridos**: `manage payments` o `admin.full`

**Parámetros de URL**:
- `{id}`: ID numérico del pago a validar

**Body de la petición**:
```json
{
    "action": "approve",
    "validation_notes": "Pago verificado y aprobado correctamente"
}
```

**Campos del body**:
- `action`: Requerido, debe ser `approve` o `reject`
- `validation_notes`: Opcional para aprobar, obligatorio para rechazar. Máximo 1000 caracteres

**Respuesta exitosa - Pago Aprobado (200)**:
```json
{
    "success": true,
    "message": "Pago aprobado exitosamente",
    "data": {
        "payment": {
            "id": 25,
            "invoice_id": 15,
            "invoice_number": "INV-2025-0015",
            "client_name": "Juan Pérez",
            "client_email": "juan@example.com",
            "payment_type": "transferencia",
            "transaction_number": "TRX-20250818-0456",
            "amount": "750.50",
            "observations": "Pago parcial de la primera quincena",
            "status": "validado",
            "validated_at": "2025-08-18 16:45:30",
            "validated_by": "Ana García",
            "validation_notes": "Pago verificado y aprobado correctamente",
            "created_at": "2025-08-18 14:25:30",
            "updated_at": "2025-08-18 16:45:30"
        },
        "invoice_summary": {
            "invoice_number": "INV-2025-0015",
            "total_factura": "1,500.00",
            "total_validado": "750.50",
            "total_pendiente": "0.00",
            "total_rechazado": "0.00",
            "saldo_pendiente": "749.50",
            "status_factura": "Pendiente"
        },
        "validation_info": {
            "action_performed": "approve",
            "validated_by": "Ana García",
            "validated_by_email": "ana@empresa.com",
            "validation_date": "2025-08-18 16:45:30",
            "notes": "Pago verificado y aprobado correctamente"
        }
    }
}
```

**Respuesta exitosa - Pago Rechazado (200)**:
```json
{
    "success": true,
    "message": "Pago rechazado exitosamente",
    "data": {
        "payment": {
            "id": 27,
            "invoice_id": 15,
            "invoice_number": "INV-2025-0015",
            "client_name": "Juan Pérez",
            "client_email": "juan@example.com",
            "payment_type": "cheque",
            "transaction_number": "CHQ-1234",
            "amount": "500.00",
            "observations": "Pago con cheque",
            "status": "rechazado",
            "validated_at": "2025-08-18 16:50:00",
            "validated_by": "Ana García",
            "validation_notes": "Cheque sin fondos suficientes",
            "created_at": "2025-08-18 15:00:00",
            "updated_at": "2025-08-18 16:50:00"
        },
        "invoice_summary": {
            "invoice_number": "INV-2025-0015",
            "total_factura": "1,500.00",
            "total_validado": "750.50",
            "total_pendiente": "0.00",
            "total_rechazado": "500.00",
            "saldo_pendiente": "749.50",
            "status_factura": "Pendiente"
        },
        "validation_info": {
            "action_performed": "reject",
            "validated_by": "Ana García",
            "validated_by_email": "ana@empresa.com",
            "validation_date": "2025-08-18 16:50:00",
            "notes": "Cheque sin fondos suficientes"
        }
    }
}
```

**Respuesta de error - Sin permisos (403)**:
```json
{
    "success": false,
    "message": "No tienes permisos para validar pagos"
}
```

**Respuesta de error - Pago no encontrado (404)**:
```json
{
    "success": false,
    "message": "Pago no encontrado"
}
```

**Respuesta de error - Estado inválido (422)**:
```json
{
    "success": false,
    "message": "Solo se pueden validar pagos en estado pendiente",
    "data": {
        "payment_id": 25,
        "current_status": "validado"
    }
}
```

**Respuesta de error - Validación (422)**:
```json
{
    "success": false,
    "message": "Datos de validación inválidos",
    "errors": {
        "action": ["La acción debe ser 'approve' o 'reject'."],
        "validation_notes": ["Las notas de validación son requeridas para rechazar un pago."]
    }
}
```

---

## 📊 Tipos de Pago Soportados

| Tipo | Código | Descripción | Requiere Número de Transacción |
|------|--------|-------------|---------------------------------|
| **Efectivo** | `efectivo` | Pago en efectivo | ❌ No |
| **Tarjeta** | `tarjeta` | Pago con tarjeta de crédito/débito | ✅ Sí |
| **Transferencia** | `transferencia` | Transferencia bancaria | ✅ Sí |
| **Cheque** | `cheque` | Pago con cheque | ✅ Sí |

---

## 🔄 Estados de Pago

| Estado | Descripción | Puede Cambiar A |
|--------|-------------|-----------------|
| **Pendiente** | Pago registrado, esperando validación | Validado, Rechazado |
| **Validado** | Pago aprobado por un administrador | - |
| **Rechazado** | Pago rechazado por un administrador | - |

### **Flujo de Estados**:
1. **Cliente registra pago** → Estado: `pendiente`
2. **Administrador revisa** → Estado: `validado` o `rechazado`
3. **Sistema actualiza factura** → Si total pagado ≥ total factura → Factura: `Pagada`

---

## 🛡️ Validaciones y Reglas de Negocio

### **Validaciones de Entrada**:
```php
// Validaciones aplicadas en CreatePaymentRequest
'invoice_id' => 'required|string|exists:invoices,invoice_number'
'payment_type' => 'required|string|in:efectivo,tarjeta,transferencia,cheque'
'transaction_number' => 'nullable|string|max:255|required_unless:payment_type,efectivo'
'amount' => 'required|numeric|min:0.01|max:999999.99'
'observations' => 'nullable|string|max:1000'
```

### **Reglas de Negocio**:

#### **Control de Acceso**:
- ✅ Solo el cliente propietario puede registrar pagos en sus facturas
- ✅ Solo el cliente propietario puede ver los pagos de sus facturas
- ✅ Token de API válido y activo requerido

#### **Control de Montos**:
- ✅ El monto del pago no puede exceder el saldo pendiente de la factura
- ✅ Se calcula automáticamente el saldo considerando solo pagos validados
- ✅ El monto se redondea automáticamente a 2 decimales

#### **Control de Estados**:
- ❌ No se pueden registrar pagos en facturas anuladas
- ✅ Los pagos se crean siempre en estado `pendiente`
- ✅ Solo administradores pueden cambiar el estado a `validado` o `rechazado`

#### **Control de Duplicados**:
- ✅ Se permite múltiples pagos para la misma factura (pagos parciales)
- ✅ No hay restricción de unicidad en números de transacción (pueden repetirse entre diferentes tipos)

---

## 💰 Cálculos Financieros

### **Fórmulas Aplicadas**:

```php
// Cálculo de total pagado (solo pagos validados)
$totalPagado = $factura->payments()->where('status', 'validado')->sum('amount');

// Cálculo de saldo pendiente
$saldoPendiente = $factura->total - $totalPagado;

// Verificación de monto válido
$montoDisponible = $saldoPendiente; // Para nuevo pago
if ($montoPago > $montoDisponible) {
    // Error: Monto excede saldo pendiente
}
```

### **Actualización Automática de Estado de Factura**:
```php
// Si total pagado >= total factura
if ($totalPagado >= $factura->total) {
    $factura->status = 'Pagada';
} else if ($totalPagado > 0 || hayPagosPendientes) {
    $factura->status = 'Pendiente';
}
```

---

## 🔒 Seguridad Implementada

### **Autenticación y Autorización**:
- 🔐 **Sanctum Token**: Requerido para todos los endpoints
- 👤 **Ownership Verification**: Solo propietarios pueden acceder a sus datos
- 🚫 **Access Control**: Verificación estricta de pertenencia de facturas

### **Validación de Datos**:
- ✅ **Form Request Validation**: Validación centralizada en `CreatePaymentRequest`
- 🛡️ **SQL Injection Protection**: Uso de Eloquent ORM
- 🔍 **Data Sanitization**: Sanitización automática de datos de entrada

### **Logging y Auditoría**:
- 📝 **Activity Log**: Registro automático de todas las operaciones
- 🕒 **Token Usage Tracking**: Actualización de `last_used_at` en cada petición
- 🔍 **Error Tracking**: Logging detallado de errores

---

## 📋 Códigos de Respuesta HTTP

| Código | Descripción | Cuándo Ocurre |
|--------|-------------|---------------|
| `200` | OK | Consulta exitosa |
| `201` | Created | Pago registrado exitosamente |
| `400` | Bad Request | Datos de entrada inválidos |
| `401` | Unauthorized | Token de autenticación inválido o faltante |
| `403` | Forbidden | Sin permisos para acceder a la factura |
| `404` | Not Found | Factura no encontrada |
| `422` | Unprocessable Entity | Errores de validación o reglas de negocio |
| `500` | Internal Server Error | Error interno del servidor |

---

## 🧪 Ejemplos de Uso

### **Ejemplo 1: Registrar Pago con Transferencia**
```bash
curl -X POST http://localhost:8000/api/payments \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "invoice_id": "INV-2025-0015",
    "payment_type": "transferencia",
    "transaction_number": "TRX-20250818-0789",
    "amount": 1500.00,
    "observations": "Pago completo de factura"
  }'
```

### **Ejemplo 2: Registrar Pago en Efectivo**
```bash
curl -X POST http://localhost:8000/api/payments \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "invoice_id": "INV-2025-0020",
    "payment_type": "efectivo",
    "amount": 750.50,
    "observations": "Pago parcial en efectivo"
  }'
```

### **Ejemplo 3: Consultar Pagos con Filtros**
```bash
curl -X GET "http://localhost:8000/api/payments?status=validado&per_page=25" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### **Ejemplo 4: Ver Pagos de Factura Específica**
```bash
curl -X GET "http://localhost:8000/api/payments/invoice/15" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### **Ejemplo 5: Aprobar Pago (Solo Administradores)**
```bash
curl -X PATCH http://localhost:8000/api/payments/25/validate \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "approve",
    "validation_notes": "Pago verificado y aprobado"
  }'
```

### **Ejemplo 6: Rechazar Pago (Solo Administradores)**
```bash
curl -X PATCH http://localhost:8000/api/payments/27/validate \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "reject",
    "validation_notes": "Cheque sin fondos suficientes"
  }'
```

---

## 📁 Archivos Relacionados

### **Controlador**:
- `app/Http/Controllers/Api/PaymentController.php` - Lógica de endpoints

### **Modelo**:
- `app/Models/Payment.php` - Modelo de datos con relaciones y métodos de utilidad

### **Validación**:
- `app/Http/Requests/Api/CreatePaymentRequest.php` - Validaciones de entrada

### **Migración**:
- `database/migrations/2025_08_07_162200_create_payments_table.php` - Estructura de BD

### **Seeder**:
- `database/seeders/PaymentSeeder.php` - Datos de prueba

---

## 📈 Resumen de Funcionalidad

### **✅ Características Implementadas**:
- 4 endpoints completos de gestión de pagos
- Funcionalidad de aprobación/rechazo para administradores
- Validación exhaustiva de datos y reglas de negocio
- Control de acceso por ownership de facturas y permisos administrativos
- Cálculos automáticos de saldos y estados
- Soporte para 4 tipos de pago diferentes
- Sistema de estados con flujo controlado
- Paginación y filtros en listados
- Logging y auditoría completa

### **🔄 Integración con Otros Módulos**:
- **Facturas**: Actualización automática de estados según pagos
- **Usuarios**: Control de acceso por cliente propietario
- **Tokens**: Tracking de uso de API

### **💡 Características Destacadas**:
- **Prevención de Sobrepagos**: Control automático de montos vs saldo pendiente
- **Flexibilidad de Pagos**: Soporte para pagos parciales múltiples
- **Transparencia**: Información completa de estado y validación
- **Control Administrativo**: Validación manual por administradores con logging completo
- **Seguridad**: Separación de permisos entre clientes y administradores
- **Auditoría**: Registro completo de todas las acciones de validación
- **Usabilidad**: Respuestas estructuradas con información contextual

---

**🎯 Los endpoints de pagos están completamente implementados y funcionando según las especificaciones del sistema actual.**
