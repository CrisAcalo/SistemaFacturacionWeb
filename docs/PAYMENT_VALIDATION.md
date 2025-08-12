# Gestión de Validación de Pagos

## Descripción General

El módulo de validación de pagos permite a usuarios con rol "Pagos" revisar, aprobar o rechazar los pagos registrados por clientes a través de la API REST.

## Características Implementadas

### 1. Control de Acceso
- **Rol requerido**: `Pagos` o `Administrador`
- **Permiso**: `manage payments`
- **Autenticación**: Laravel Breeze (auth:web)

### 2. Funcionalidades Principales

#### Listado de Pagos
- **Ruta**: `/payments`
- **Vista**: `payments.index`
- **Características**:
  - Filtrado por estado (pendiente, validado, rechazado)
  - Búsqueda por número de factura, cliente o número de transacción
  - Paginación configurable (15, 25, 50 elementos)
  - Estadísticas en tiempo real
  - Selección múltiple para acciones masivas

#### Detalle de Pago
- **Ruta**: `/payments/{payment}`
- **Vista**: `payments.show`
- **Información mostrada**:
  - Detalles completos del pago
  - Información del cliente
  - Información de la factura asociada
  - Historial de otros pagos de la misma factura
  - Resumen de pagos de la factura

#### Aprobación Individual
- **Ruta**: `POST /payments/{payment}/approve`
- **Funcionalidad**:
  - Cambia el estado del pago a "validado"
  - Registra fecha y usuario validador
  - Permite agregar notas opcionales
  - Actualiza automáticamente el estado de la factura si está completamente pagada

#### Rechazo Individual
- **Ruta**: `POST /payments/{payment}/reject`
- **Funcionalidad**:
  - Cambia el estado del pago a "rechazado"
  - Registra fecha y usuario validador
  - Requiere notas obligatorias explicando el motivo
  - La factura mantiene su estado pendiente

#### Acciones Masivas
- **Ruta**: `POST /payments/bulk-action`
- **Funcionalidades**:
  - Aprobación masiva de múltiples pagos
  - Rechazo masivo de múltiples pagos
  - Validaciones de seguridad
  - Notas aplicables a todos los pagos seleccionados

### 3. Reglas de Negocio

#### Al Aprobar un Pago:
1. El estado del pago cambia a "validado"
2. Se registra la fecha de validación
3. Se registra el usuario validador
4. Se verifica si la factura está completamente pagada:
   - Si el total de pagos validados ≥ total de la factura
   - El estado de la factura cambia a "Pagada"

#### Al Rechazar un Pago:
1. El estado del pago cambia a "rechazado"
2. Se registra la fecha de rechazo
3. Se registra el usuario validador
4. Las notas son obligatorias para rechazos
5. La factura mantiene su estado (no se considera el pago rechazado)

### 4. Interfaz de Usuario

#### Características de la Vista Principal:
- **Dashboard con estadísticas**:
  - Cantidad de pagos pendientes, validados, rechazados
  - Monto total pendiente de validación
- **Tabla responsiva** con información clave
- **Filtros avanzados** para búsqueda eficiente
- **Acciones rápidas** desde la tabla
- **Modales interactivos** para confirmaciones

#### Características de la Vista de Detalle:
- **Layout en columnas** para información organizada
- **Códigos de color** para estados y tipos de pago
- **Historial completo** de pagos relacionados
- **Resumen financiero** de la factura
- **Acciones contextuales** según el estado

### 5. Seguridad y Auditoría

#### Controles de Seguridad:
- Solo usuarios autenticados con permisos específicos
- Validación de datos en frontend y backend
- Protección CSRF en todos los formularios
- Sanitización de inputs

#### Registro de Auditoría:
- Todas las acciones se registran con Activity Log
- Trazabilidad completa de cambios
- Información del usuario que realizó la acción
- Timestamp de todas las operaciones

### 6. Datos de Prueba

Se incluye un seeder (`PaymentSeeder`) que crea datos de ejemplo:
- Pagos con diferentes tipos (efectivo, tarjeta, transferencia, cheque)
- Diversos estados (50% pendientes, 35% validados, 15% rechazados)
- Números de transacción realistas según el tipo
- Observaciones y notas de validación contextuales

## Flujo de Trabajo Típico

1. **Cliente registra pago** vía API REST → Estado: "pendiente"
2. **Usuario de Pagos accede** al módulo web de validación
3. **Revisa la lista** de pagos pendientes con filtros si es necesario
4. **Accede al detalle** del pago para información completa
5. **Toma decisión**:
   - **Aprobar**: Agrega notas opcionales y confirma
   - **Rechazar**: Agrega notas obligatorias explicando el motivo
6. **Sistema actualiza** automáticamente:
   - Estado del pago
   - Estado de la factura (si aplica)
   - Registro de auditoría

## Usuarios de Ejemplo

Después del seeding, puedes usar estos usuarios para probar:

### Usuario con rol "Pagos":
- **Email**: `pagos@example.com`
- **Password**: `password`
- **Acceso**: Solo módulo de validación de pagos

### Usuario Administrador:
- **Email**: `admin@example.com`
- **Password**: `password`
- **Acceso**: Todos los módulos incluyendo pagos

## Navegación

El módulo está disponible en el menú lateral como "Gestión de Pagos" con icono de tarjeta de crédito, visible solo para usuarios con el permiso correspondiente.
