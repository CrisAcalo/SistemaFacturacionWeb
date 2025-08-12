# API de Pagos - Documentación

## Autenticación
Todos los endpoints requieren autenticación con token Sanctum:
```
Authorization: Bearer {token}
```

## Endpoints

### 1. Registrar un nuevo pago
**POST** `/api/payments`

Permite a un cliente registrar un pago para una de sus facturas.

#### Request Body (JSON):
```json
{
    "invoice_id": 1,
    "payment_type": "transferencia",
    "transaction_number": "TRX123456789",
    "amount": 150.50,
    "observations": "Pago parcial de la factura"
}
```

#### Campos:
- `invoice_id` (integer, requerido): ID de la factura a pagar
- `payment_type` (string, requerido): Tipo de pago
  - Valores permitidos: `efectivo`, `tarjeta`, `transferencia`, `cheque`
- `transaction_number` (string, opcional): Número de transacción o comprobante
  - Requerido para todos los tipos excepto `efectivo`
  - Máximo 255 caracteres
- `amount` (decimal, requerido): Monto del pago
  - Mínimo: 0.01
  - Máximo: 999,999.99
- `observations` (string, opcional): Observaciones adicionales
  - Máximo 1000 caracteres

#### Response Success (201):
```json
{
    "success": true,
    "message": "Pago registrado exitosamente. Está pendiente de validación.",
    "data": {
        "payment": {
            "id": 5,
            "invoice_id": 1,
            "invoice_number": "FAC-2025-0001",
            "payment_type": "transferencia",
            "transaction_number": "TRX123456789",
            "amount": "150.50",
            "observations": "Pago parcial de la factura",
            "status": "pendiente",
            "created_at": "2025-08-07 15:30:00"
        },
        "invoice_summary": {
            "total_factura": "500.00",
            "total_pagado_validado": "200.00",
            "saldo_pendiente": "149.50"
        }
    }
}
```

#### Response Error (422):
```json
{
    "success": false,
    "message": "El monto del pago excede el saldo pendiente de la factura",
    "data": {
        "total_factura": 500.00,
        "total_pagado": 450.00,
        "saldo_pendiente": 50.00,
        "monto_solicitado": 100.00
    }
}
```

### 2. Obtener todos los pagos del cliente
**GET** `/api/payments`

Obtiene todos los pagos registrados por el cliente autenticado.

#### Query Parameters:
- `per_page` (integer, opcional): Número de elementos por página (máximo 100, default 15)
- `page` (integer, opcional): Número de página (default 1)
- `status` (string, opcional): Filtrar por estado
  - Valores: `pendiente`, `validado`, `rechazado`

#### Response Success (200):
```json
{
    "success": true,
    "message": "Pagos obtenidos exitosamente",
    "data": {
        "payments": [
            {
                "id": 5,
                "payment_type": "transferencia",
                "transaction_number": "TRX123456789",
                "amount": "150.50",
                "observations": "Pago parcial de la factura",
                "status": "pendiente",
                "validated_at": null,
                "validated_by": null,
                "validation_notes": null,
                "created_at": "2025-08-07 15:30:00",
                "invoice": {
                    "id": 1,
                    "invoice_number": "FAC-2025-0001"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 15,
            "total": 5,
            "from": 1,
            "to": 5
        },
        "filters_applied": {
            "status": null
        }
    }
}
```

### 3. Obtener pagos de una factura específica
**GET** `/api/payments/invoice/{invoiceId}`

Obtiene todos los pagos registrados para una factura específica.

#### Response Success (200):
```json
{
    "success": true,
    "message": "Pagos obtenidos exitosamente",
    "data": {
        "invoice": {
            "id": 1,
            "invoice_number": "FAC-2025-0001",
            "total": "500.00",
            "status": "Pendiente"
        },
        "payments": [
            {
                "id": 5,
                "payment_type": "transferencia",
                "transaction_number": "TRX123456789",
                "amount": "150.50",
                "observations": "Pago parcial de la factura",
                "status": "pendiente",
                "validated_at": null,
                "validated_by": null,
                "validation_notes": null,
                "created_at": "2025-08-07 15:30:00"
            }
        ],
        "summary": {
            "total_factura": "500.00",
            "total_validado": "200.00",
            "total_pendiente": "150.50",
            "total_rechazado": "0.00",
            "saldo_pendiente": "149.50",
            "count_payments": 3
        }
    }
}
```

## Códigos de Estado HTTP

- `200 OK`: Solicitud exitosa
- `201 Created`: Pago creado exitosamente
- `400 Bad Request`: Datos inválidos en la solicitud
- `401 Unauthorized`: Token de autenticación inválido o faltante
- `403 Forbidden`: Sin permisos para realizar la acción
- `404 Not Found`: Recurso no encontrado
- `422 Unprocessable Entity`: Error de validación
- `500 Internal Server Error`: Error interno del servidor

## Validaciones y Reglas de Negocio

1. **Autorización**: Solo el cliente de la factura puede registrar pagos
2. **Estado de factura**: No se pueden registrar pagos en facturas anuladas
3. **Monto**: El monto del pago no puede exceder el saldo pendiente de la factura
4. **Estado inicial**: Todos los pagos se registran con estado `pendiente`
5. **Número de transacción**: Requerido para todos los tipos de pago excepto `efectivo`
6. **Cálculo de saldo**: Se considera solo pagos validados para el saldo pendiente

## Tipos de Pago Soportados

- `efectivo`: Pago en efectivo (no requiere número de transacción)
- `tarjeta`: Pago con tarjeta de crédito/débito
- `transferencia`: Transferencia bancaria
- `cheque`: Pago con cheque
