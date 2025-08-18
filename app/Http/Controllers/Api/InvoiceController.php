<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Invoices\CreateInvoiceRequest;
use App\Http\Requests\Api\Invoices\UpdateInvoiceRequest;
use App\Http\Resources\Api\InvoiceResource;
use App\Http\Resources\Api\InvoiceCollection;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Invoice::class);

        $query = Invoice::with(['user', 'client', 'items.product']);

        // Search by invoice number, client name, or notes
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by client
        if ($request->has('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        // Filter by user (seller)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Filter by total range
        if ($request->has('min_total')) {
            $query->where('total', '>=', $request->get('min_total'));
        }

        if ($request->has('max_total')) {
            $query->where('total', '<=', $request->get('max_total'));
        }

        // Include soft deleted records if requested
        if ($request->boolean('include_deleted')) {
            Gate::authorize('viewDeleted', Invoice::class);
            $query->withTrashed();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $invoices = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Facturas obtenidas exitosamente',
            'data' => new InvoiceCollection($invoices),
            'meta' => [
                'total' => $invoices->total(),
                'per_page' => $invoices->perPage(),
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'from' => $invoices->firstItem(),
                'to' => $invoices->lastItem()
            ]
        ]);
    }

    /**
     * Store a newly created invoice
     */
    public function store(CreateInvoiceRequest $request): JsonResponse
    {
        Gate::authorize('create', Invoice::class);

        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Generate unique invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => Auth::id(),
                'client_id' => $data['client_id'],
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'status' => $data['status'] ?? 'Pendiente',
                'notes' => $data['notes'] ?? null,
            ]);

            $subtotal = 0;

            // Create invoice items and update product stock
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                // Check stock availability
                if ($product->stock < $itemData['quantity']) {
                    throw new \Exception("Stock insuficiente para el producto: {$product->name}. Stock disponible: {$product->stock}");
                }

                $itemTotal = $itemData['quantity'] * $itemData['price'];

                // Create invoice item
                $invoice->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'total' => $itemTotal,
                ]);

                // Update product stock
                $product->decrement('stock', $itemData['quantity']);

                $subtotal += $itemTotal;
            }

            // Calculate tax and total
            $taxRate = $data['tax_rate'] ?? 0.19; // 19% by default
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $tax;

            // Update invoice totals
            $invoice->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Factura creada exitosamente',
                'data' => new InvoiceResource($invoice->load(['user', 'client', 'items.product']))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la factura: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(string $id): JsonResponse
    {
        $invoice = Invoice::with(['user', 'client', 'items.product', 'payments'])
                         ->withTrashed()
                         ->findOrFail($id);

        Gate::authorize('view', $invoice);

        return response()->json([
            'success' => true,
            'message' => 'Factura obtenida exitosamente',
            'data' => new InvoiceResource($invoice)
        ]);
    }

    /**
     * Update the specified invoice
     */
    public function update(UpdateInvoiceRequest $request, string $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);
        Gate::authorize('update', $invoice);

        // Only allow updating certain fields for existing invoices
        $allowedFields = ['status', 'notes'];
        $updateData = $request->only($allowedFields);

        $invoice->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Factura actualizada exitosamente',
            'data' => new InvoiceResource($invoice->fresh(['user', 'client', 'items.product']))
        ]);
    }

    /**
     * Remove the specified invoice (soft delete)
     */
    public function destroy(string $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);
        Gate::authorize('delete', $invoice);

        // Check if invoice can be deleted (no payments)
        if ($invoice->payments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una factura que tiene pagos registrados'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Restore product stock
            foreach ($invoice->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            $invoice->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Factura eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted invoice
     */
    public function restore(string $id): JsonResponse
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        Gate::authorize('restore', $invoice);

        DB::beginTransaction();

        try {
            // Check stock availability before restoring
            foreach ($invoice->items as $item) {
                if ($item->product->stock < $item->quantity) {
                    throw new \Exception("Stock insuficiente para restaurar la factura. Producto: {$item->product->name}");
                }
            }

            // Remove stock again
            foreach ($invoice->items as $item) {
                $item->product->decrement('stock', $item->quantity);
            }

            $invoice->restore();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Factura restaurada exitosamente',
                'data' => new InvoiceResource($invoice->fresh(['user', 'client', 'items.product']))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar la factura: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update invoice status
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:Pagada,Pendiente,Anulada'
        ]);

        $invoice = Invoice::findOrFail($id);
        Gate::authorize('updateStatus', $invoice);

        $oldStatus = $invoice->status;
        $invoice->update(['status' => $request->get('status')]);

        return response()->json([
            'success' => true,
            'message' => 'Estado de factura actualizado exitosamente',
            'data' => [
                'invoice' => new InvoiceResource($invoice->fresh(['user', 'client', 'items.product'])),
                'status_change' => [
                    'previous_status' => $oldStatus,
                    'current_status' => $invoice->status
                ]
            ]
        ]);
    }

    /**
     * Get invoice statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        Gate::authorize('viewStatistics', Invoice::class);

        $query = Invoice::query();

        // Apply date filter if provided
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $stats = [
            'total_invoices' => $query->count(),
            'total_amount' => $query->sum('total'),
            'by_status' => [
                'paid' => $query->where('status', 'Pagada')->count(),
                'pending' => $query->where('status', 'Pendiente')->count(),
                'cancelled' => $query->where('status', 'Anulada')->count(),
            ],
            'amounts_by_status' => [
                'paid' => $query->where('status', 'Pagada')->sum('total'),
                'pending' => $query->where('status', 'Pendiente')->sum('total'),
                'cancelled' => $query->where('status', 'Anulada')->sum('total'),
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Estadísticas obtenidas exitosamente',
            'data' => $stats
        ]);
    }

    /**
     * Generate a unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-';
        $year = date('Y');
        $month = date('m');

        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}{$year}{$month}%")
                            ->orderBy('invoice_number', 'desc')
                            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $year . $month . $newNumber;
    }
}
