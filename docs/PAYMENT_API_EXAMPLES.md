# Ejemplos de Pruebas de la API de Pagos

## Requisitos Previos
1. Tener un usuario con rol `cliente` creado
2. Tener facturas asignadas a ese cliente
3. Obtener un token de autenticación válido

## 1. Obtener Token de Autenticación
```bash
POST http://localhost/api/login
Content-Type: application/json

{
    "email": "cliente@example.com",
    "password": "password"
}
```

## 2. Registrar un Pago con Transferencia Bancaria
```bash
POST http://localhost/api/payments
Authorization: Bearer tu-token-aqui
Content-Type: application/json

{
    "invoice_id": 1,
    "payment_type": "transferencia",
    "transaction_number": "TRX-20250807-001",
    "amount": 250.00,
    "observations": "Pago de la primera cuota de la factura FAC-001"
}
```

## 3. Registrar un Pago en Efectivo
```bash
POST http://localhost/api/payments
Authorization: Bearer tu-token-aqui
Content-Type: application/json

{
    "invoice_id": 1,
    "payment_type": "efectivo",
    "amount": 100.00,
    "observations": "Pago parcial en efectivo"
}
```

## 4. Registrar un Pago con Tarjeta
```bash
POST http://localhost/api/payments
Authorization: Bearer tu-token-aqui
Content-Type: application/json

{
    "invoice_id": 2,
    "payment_type": "tarjeta",
    "transaction_number": "CARD-123456-7890",
    "amount": 500.00,
    "observations": "Pago completo con tarjeta de crédito"
}
```

## 5. Obtener Todos los Pagos del Cliente
```bash
GET http://localhost/api/payments
Authorization: Bearer tu-token-aqui
```

## 6. Obtener Pagos con Filtros
```bash
GET http://localhost/api/payments?status=pendiente&per_page=10&page=1
Authorization: Bearer tu-token-aqui
```

## 7. Obtener Pagos de una Factura Específica
```bash
GET http://localhost/api/payments/invoice/1
Authorization: Bearer tu-token-aqui
```

## 8. Obtener Información del Token
```bash
GET http://localhost/api/token-info
Authorization: Bearer tu-token-aqui
```

## 9. Obtener Facturas del Cliente
```bash
GET http://localhost/api/invoices
Authorization: Bearer tu-token-aqui
```

## Casos de Error Comunes

### Error: Monto excede el saldo pendiente
```bash
POST http://localhost/api/payments
Authorization: Bearer tu-token-aqui
Content-Type: application/json

{
    "invoice_id": 1,
    "payment_type": "transferencia",
    "transaction_number": "TRX-ERROR-001",
    "amount": 9999.99,
    "observations": "Monto muy alto"
}
```

Respuesta esperada (422):
```json
{
    "success": false,
    "message": "El monto del pago excede el saldo pendiente de la factura",
    "data": {
        "total_factura": 500.00,
        "total_pagado": 200.00,
        "saldo_pendiente": 300.00,
        "monto_solicitado": 9999.99
    }
}
```

### Error: Factura de otro cliente
```bash
POST http://localhost/api/payments
Authorization: Bearer tu-token-aqui
Content-Type: application/json

{
    "invoice_id": 999,
    "payment_type": "efectivo",
    "amount": 100.00
}
```

Respuesta esperada (403):
```json
{
    "success": false,
    "message": "No tienes permisos para registrar pagos en esta factura"
}
```

### Error: Datos de validación
```bash
POST http://localhost/api/payments
Authorization: Bearer tu-token-aqui
Content-Type: application/json

{
    "invoice_id": "abc",
    "payment_type": "bitcoin",
    "amount": -100
}
```

Respuesta esperada (422):
```json
{
    "success": false,
    "message": "Datos de pago inválidos",
    "errors": {
        "invoice_id": ["El ID de la factura debe ser un número entero."],
        "payment_type": ["El tipo de pago debe ser: efectivo, tarjeta, transferencia o cheque."],
        "amount": ["El monto mínimo es $0.01."]
    }
}
```

## Estados de los Pagos

- **pendiente**: Estado inicial de todos los pagos
- **validado**: Pago aprobado por un usuario con permisos
- **rechazado**: Pago rechazado por un usuario con permisos

Solo los pagos con estado `validado` se consideran para el cálculo del saldo pendiente de la factura.

## Flujo Completo de Pago

1. **Cliente registra pago** → Estado: `pendiente`
2. **Usuario con permisos revisa** → Puede aprobar o rechazar
3. **Si se aprueba** → Estado: `validado` (se cuenta hacia el total pagado)
4. **Si se rechaza** → Estado: `rechazado` (no se cuenta hacia el total pagado)
