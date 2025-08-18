<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreatePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * Obtener todos los pagos del cliente autenticado
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = min($request->get('per_page', 15), 100);
            $status = $request->get('status');

            $query = Payment::with(['invoice', 'validator'])
                ->where('client_id', $user->id)
                ->orderBy('created_at', 'desc');

            if ($status && in_array($status, ['pendiente', 'validado', 'rechazado'])) {
                $query->where('status', $status);
            }

            $payments = $query->paginate($perPage);

            // Actualizar última vez usado del token
            $token = $user->currentAccessToken();
            if ($token) {
                $token->update(['last_used_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pagos obtenidos exitosamente',
                'data' => [
                    'payments' => $payments->items(),
                    'pagination' => [
                        'current_page' => $payments->currentPage(),
                        'last_page' => $payments->lastPage(),
                        'per_page' => $payments->perPage(),
                        'total' => $payments->total(),
                        'from' => $payments->firstItem(),
                        'to' => $payments->lastItem(),
                    ],
                    'filters_applied' => [
                        'status' => $status,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los pagos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Registrar un nuevo pago para una factura
     */
    public function store(CreatePaymentRequest $request): JsonResponse
    {
        try {
            // Los datos ya están validados por CreatePaymentRequest
            $validatedData = $request->validated();

            // Obtener el usuario autenticado
            $user = $request->user();

            // Verificar que la factura existe y pertenece al cliente
            // $invoice = Invoice::with(['client', 'user', 'items.product'])->find($validatedData['invoice_id']);
            $invoice = Invoice::with(['client', 'user', 'items.product'])->where('invoice_number', $validatedData['invoice_id'])->first();

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada'
                ], 404);
            }

            // Verificar que el usuario autenticado es el cliente de la factura
            if ($invoice->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para registrar pagos en esta factura'
                ], 403);
            }

            // Verificar que la factura esté en estado que permita pagos
            if ($invoice->status === 'Anulada') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden registrar pagos en facturas anuladas'
                ], 422);
            }

            // Verificar que el monto no exceda el total de la factura
            $totalPaid = $invoice->payments()->where('status', '!=', 'rechazado')->sum('amount');
            $remainingAmount = $invoice->total - $totalPaid;

            if ($validatedData['amount'] > $remainingAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto del pago excede el saldo pendiente de la factura',
                    'data' => [
                        'total_factura' => $invoice->total,
                        'total_pagado' => $totalPaid,
                        'saldo_pendiente' => $remainingAmount,
                        'monto_solicitado' => $validatedData['amount']
                    ]
                ], 422);
            }

            // Crear el registro de pago
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'client_id' => $user->id,
                'payment_type' => $validatedData['payment_type'],
                'transaction_number' => $validatedData['transaction_number'],
                'amount' => $validatedData['amount'],
                'observations' => $validatedData['observations'],
                'status' => 'pendiente'
            ]);

            // Cargar las relaciones
            $payment->load(['invoice', 'client']);

            // Actualizar la última vez usado del token
            $token = $user->currentAccessToken();
            if ($token) {
                $token->update(['last_used_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente. Está pendiente de validación.',
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'invoice_id' => $payment->invoice_id,
                        'invoice_number' => $payment->invoice->invoice_number,
                        'payment_type' => $payment->payment_type,
                        'transaction_number' => $payment->transaction_number,
                        'amount' => number_format((float) $payment->amount, 2),
                        'observations' => $payment->observations,
                        'status' => $payment->status,
                        'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                    ],
                    'invoice_summary' => [
                        'total_factura' => number_format($invoice->total, 2),
                        'total_pagado_validado' => number_format($totalPaid, 2),
                        'saldo_pendiente' => number_format($remainingAmount - $payment->amount, 2),
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el pago',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener los pagos de una factura específica
     */
    public function show(Request $request, int $invoiceId): JsonResponse
    {
        try {
            $user = $request->user();

            // Verificar que la factura existe y pertenece al cliente
            $invoice = Invoice::with(['payments.validator'])->find($invoiceId);

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada'
                ], 404);
            }

            // Verificar que el usuario autenticado es el cliente de la factura
            if ($invoice->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver los pagos de esta factura'
                ], 403);
            }

            $payments = $invoice->payments()->orderBy('created_at', 'desc')->get();

            // Calcular estadísticas
            $totalValidated = $payments->where('status', 'validado')->sum('amount');
            $totalPending = $payments->where('status', 'pendiente')->sum('amount');
            $totalRejected = $payments->where('status', 'rechazado')->sum('amount');
            $remainingAmount = $invoice->total - $totalValidated;

            // Actualizar última vez usado del token
            $token = $user->currentAccessToken();
            if ($token) {
                $token->update(['last_used_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pagos obtenidos exitosamente',
                'data' => [
                    'invoice' => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'total' => number_format($invoice->total, 2),
                        'status' => $invoice->status,
                    ],
                    'payments' => $payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'payment_type' => $payment->payment_type,
                            'transaction_number' => $payment->transaction_number,
                            'amount' => number_format((float) $payment->amount, 2),
                            'observations' => $payment->observations,
                            'status' => $payment->status,
                            'validated_at' => $payment->validated_at?->format('Y-m-d H:i:s'),
                            'validated_by' => $payment->validator?->name,
                            'validation_notes' => $payment->validation_notes,
                            'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                        ];
                    }),
                    'summary' => [
                        'total_factura' => number_format($invoice->total, 2),
                        'total_validado' => number_format($totalValidated, 2),
                        'total_pendiente' => number_format($totalPending, 2),
                        'total_rechazado' => number_format($totalRejected, 2),
                        'saldo_pendiente' => number_format($remainingAmount, 2),
                        'count_payments' => $payments->count(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los pagos',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Validar un pago (aprobar o rechazar) - Solo administradores
     */
    public function validatePayment(Request $request, int $paymentId): JsonResponse
    {
        try {
            $user = $request->user();

            // Verificar que el usuario tenga permisos de administrador para gestionar pagos
            if (!$user->hasAnyPermission(['manage payments', 'admin.full'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para validar pagos'
                ], 403);
            }

            // Validar datos de entrada
            $validatedData = $request->validate([
                'action' => 'required|string|in:approve,reject',
                'validation_notes' => 'nullable|string|max:1000|required_if:action,reject'
            ], [
                'action.required' => 'La acción es requerida.',
                'action.in' => 'La acción debe ser "approve" o "reject".',
                'validation_notes.required_if' => 'Las notas de validación son requeridas para rechazar un pago.',
                'validation_notes.max' => 'Las notas no pueden tener más de 1000 caracteres.'
            ]);

            // Buscar el pago
            $payment = Payment::with(['invoice', 'client'])->find($paymentId);

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pago no encontrado'
                ], 404);
            }

            // Verificar que el pago esté pendiente
            if (!$payment->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden validar pagos en estado pendiente',
                    'data' => [
                        'payment_id' => $payment->id,
                        'current_status' => $payment->status
                    ]
                ], 422);
            }

            // Ejecutar la acción
            $success = false;
            $message = '';

            if ($validatedData['action'] === 'approve') {
                $success = $payment->approve($user, $validatedData['validation_notes'] ?? null);
                $message = $success ? 'Pago aprobado exitosamente' : 'Error al aprobar el pago';
                
                // Log de actividad
                activity()
                    ->performedOn($payment)
                    ->causedBy($user)
                    ->withProperties(['notes' => $validatedData['validation_notes'] ?? 'Aprobado vía API'])
                    ->log('Pago aprobado vía API');

            } else { // reject
                $success = $payment->reject($user, $validatedData['validation_notes']);
                $message = $success ? 'Pago rechazado exitosamente' : 'Error al rechazar el pago';
                
                // Log de actividad
                activity()
                    ->performedOn($payment)
                    ->causedBy($user)
                    ->withProperties(['notes' => $validatedData['validation_notes']])
                    ->log('Pago rechazado vía API');
            }

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar la validación del pago'
                ], 500);
            }

            // Recargar el pago con las relaciones actualizadas
            $payment->refresh();
            $payment->load(['invoice', 'client', 'validator']);

            // Calcular estadísticas actualizadas de la factura
            $invoice = $payment->invoice;
            $totalValidated = $invoice->payments()->where('status', 'validado')->sum('amount');
            $totalPending = $invoice->payments()->where('status', 'pendiente')->sum('amount');
            $totalRejected = $invoice->payments()->where('status', 'rechazado')->sum('amount');
            $remainingAmount = $invoice->total - $totalValidated;

            // Actualizar última vez usado del token
            $token = $user->currentAccessToken();
            if ($token) {
                $token->update(['last_used_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'invoice_id' => $payment->invoice_id,
                        'invoice_number' => $payment->invoice->invoice_number,
                        'client_name' => $payment->client->name,
                        'client_email' => $payment->client->email,
                        'payment_type' => $payment->payment_type,
                        'transaction_number' => $payment->transaction_number,
                        'amount' => number_format((float) $payment->amount, 2),
                        'observations' => $payment->observations,
                        'status' => $payment->status,
                        'validated_at' => $payment->validated_at?->format('Y-m-d H:i:s'),
                        'validated_by' => $payment->validator?->name,
                        'validation_notes' => $payment->validation_notes,
                        'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $payment->updated_at->format('Y-m-d H:i:s'),
                    ],
                    'invoice_summary' => [
                        'invoice_number' => $invoice->invoice_number,
                        'total_factura' => number_format($invoice->total, 2),
                        'total_validado' => number_format($totalValidated, 2),
                        'total_pendiente' => number_format($totalPending, 2),
                        'total_rechazado' => number_format($totalRejected, 2),
                        'saldo_pendiente' => number_format($remainingAmount, 2),
                        'status_factura' => $invoice->status,
                    ],
                    'validation_info' => [
                        'action_performed' => $validatedData['action'],
                        'validated_by' => $user->name,
                        'validated_by_email' => $user->email,
                        'validation_date' => now()->format('Y-m-d H:i:s'),
                        'notes' => $validatedData['validation_notes'] ?? null,
                    ]
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al validar el pago',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}
