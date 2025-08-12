<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentValidationController extends Controller
{
    /**
     * Mostrar listado de pagos pendientes
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pendiente');
        $search = $request->get('search');
        $perPage = min($request->get('per_page', 15), 100);

        $query = Payment::with(['invoice', 'client', 'validator'])
            ->orderBy('created_at', 'desc');

        // Filtrar por estado
        if (in_array($status, ['pendiente', 'validado', 'rechazado'])) {
            $query->where('status', $status);
        }

        // Búsqueda por número de factura, cliente o número de transacción
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('invoice', function ($invoiceQuery) use ($search) {
                    $invoiceQuery->where('invoice_number', 'like', "%{$search}%");
                })
                ->orWhereHas('client', function ($clientQuery) use ($search) {
                    $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('transaction_number', 'like', "%{$search}%");
            });
        }

        $payments = $query->paginate($perPage);

        // Estadísticas
        $stats = [
            'pendiente' => Payment::where('status', 'pendiente')->count(),
            'validado' => Payment::where('status', 'validado')->count(),
            'rechazado' => Payment::where('status', 'rechazado')->count(),
            'total_pendiente_amount' => Payment::where('status', 'pendiente')->sum('amount'),
        ];

        return view('payments.index', compact('payments', 'stats', 'status', 'search', 'perPage'));
    }

    /**
     * Mostrar detalles de un pago específico
     */
    public function show(Payment $payment): View
    {
        $payment->load(['invoice.items.product', 'client', 'validator']);

        // Obtener otros pagos de la misma factura
        $relatedPayments = Payment::where('invoice_id', $payment->invoice_id)
            ->where('id', '!=', $payment->id)
            ->with(['validator'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcular resumen de la factura
        $invoicePayments = Payment::where('invoice_id', $payment->invoice_id)->get();
        $totalValidated = $invoicePayments->where('status', 'validado')->sum('amount');
        $totalPending = $invoicePayments->where('status', 'pendiente')->sum('amount');
        $totalRejected = $invoicePayments->where('status', 'rechazado')->sum('amount');
        $remainingAmount = $payment->invoice->total - $totalValidated;

        $invoiceSummary = [
            'total_validated' => $totalValidated,
            'total_pending' => $totalPending,
            'total_rejected' => $totalRejected,
            'remaining_amount' => $remainingAmount,
        ];

        return view('payments.show', compact('payment', 'relatedPayments', 'invoiceSummary'));
    }

    /**
     * Aprobar un pago
     */
    public function approve(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate([
            'validation_notes' => 'nullable|string|max:1000',
        ]);

        if (!$payment->isPending()) {
            return redirect()->back()->with('error', 'Solo se pueden aprobar pagos pendientes.');
        }

        try {
            DB::transaction(function () use ($payment, $request) {
                // Aprobar el pago
                $payment->approve(Auth::user(), $request->validation_notes);

                // Verificar si la factura está completamente pagada
                $invoice = $payment->invoice;
                $totalValidatedPayments = $invoice->payments()
                    ->where('status', 'validado')
                    ->sum('amount');

                // Si el total de pagos validados es igual o mayor al total de la factura, marcarla como pagada
                if ($totalValidatedPayments >= $invoice->total) {
                    $invoice->update(['status' => 'Pagada']);
                }
            });

            return redirect()->back()->with('success', 'Pago aprobado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al aprobar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar un pago
     */
    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate([
            'validation_notes' => 'required|string|max:1000',
        ], [
            'validation_notes.required' => 'Las notas de rechazo son obligatorias.',
        ]);

        if (!$payment->isPending()) {
            return redirect()->back()->with('error', 'Solo se pueden rechazar pagos pendientes.');
        }

        try {
            // Rechazar el pago
            $payment->reject(Auth::user(), $request->validation_notes);

            return redirect()->back()->with('success', 'Pago rechazado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al rechazar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Procesar múltiples pagos (aprobación/rechazo masivo)
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'exists:payments,id',
            'bulk_validation_notes' => 'nullable|string|max:1000',
        ]);

        $paymentIds = $request->payment_ids;
        $action = $request->action;
        $notes = $request->bulk_validation_notes;

        try {
            DB::transaction(function () use ($paymentIds, $action, $notes) {
                $payments = Payment::whereIn('id', $paymentIds)
                    ->where('status', 'pendiente')
                    ->get();

                foreach ($payments as $payment) {
                    if ($action === 'approve') {
                        $payment->approve(Auth::user(), $notes);

                        // Verificar si la factura está completamente pagada
                        $invoice = $payment->invoice;
                        $totalValidatedPayments = $invoice->payments()
                            ->where('status', 'validado')
                            ->sum('amount');

                        if ($totalValidatedPayments >= $invoice->total) {
                            $invoice->update(['status' => 'Pagada']);
                        }
                    } else {
                        $payment->reject(Auth::user(), $notes);
                    }
                }
            });

            $actionText = $action === 'approve' ? 'aprobados' : 'rechazados';
            return redirect()->back()->with('success', count($paymentIds) . " pagos {$actionText} exitosamente.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al procesar los pagos: ' . $e->getMessage());
        }
    }
}
