<?php

namespace App\Livewire\Invoices;

use App\Livewire\Forms\Shared\ConfirmationFormObject;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Title('Listado de Facturas - FacturaPro')]
#[Layout('layouts.app')]
class ListInvoices extends Component
{
    use WithPagination;

    // Form Object para el modal de confirmación
    public ConfirmationFormObject $confirmation;

    // Propiedades de la UI
    public string $search = '';

    // Estado de la Acción
    public ?Invoice $invoiceToCancel = null;

    // Control de modales y temas
    public bool $showConfirmationModal = false;
    public string $confirmationTitle = '';
    public string $confirmationButtonText = '';
    public string $confirmationButtonColor = '';
    public string $actionType = ''; // 'cancel'
    public bool $isEditing = false; // Necesario para el modal compartido
    public array $modalTheme = [];

    public function render()
    {
        if (!auth()->user()->can('manage invoices')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $query = Invoice::with([
            'user:id,name',
            'client:id,name',
            'items',
            'items.product:id,name'
        ])
            ->when(
                $this->search,
                fn($q) => $q
                    ->where('invoice_number', 'like', "%{$this->search}%")
                    ->orWhereHas('client', fn($sub) => $sub->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('user', fn($sub) => $sub->where('name', 'like', "%{$this->search}%"))
            );

        $invoices = $query->latest()->paginate(15);

        return view('livewire.invoices.list-invoices', [
            'invoices' => $invoices,
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // --- LÓGICA DE ANULACIÓN ---

    public function confirmCancel(Invoice $invoice)
    {
        $this->invoiceToCancel = $invoice;
        $this->actionType = 'delete';
        $this->setupConfirmationModal(
            title: 'Confirmar Anulación de Factura ' . $invoice->invoice_number,
            buttonText: 'Sí, Anular',
            buttonColor: 'bg-red-600 hover:bg-red-700 focus:ring-red-600'
        );
        $this->showConfirmationModal = true;
    }

    public function executeAction()
    {
        $this->confirmation->validate();

        if ($this->actionType === 'delete' && $this->invoiceToCancel) {
            $this->cancelInvoice();
        }

        $this->showConfirmationModal = false;
        $this->confirmation->reset();
        $this->invoiceToCancel = null;
        $this->actionType = '';
    }

    private function cancelInvoice()
    {
        try {
            DB::transaction(function () {
                foreach ($this->invoiceToCancel->items()->with('product')->get() as $item) {
                    // Verificamos que el producto aún exista antes de intentar incrementar el stock
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }

                $this->invoiceToCancel->update(['status' => 'Anulada']);

                activity()
                    ->performedOn($this->invoiceToCancel)
                    ->causedBy(auth()->user())
                    ->withProperty('reason', $this->confirmation->reason)
                    ->log('Factura anulada: ' . $this->invoiceToCancel->invoice_number);
            });

            $this->dispatch('show-toast', message: 'Factura anulada y stock reversado correctamente.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', message: 'Error al anular la factura: ' . $e->getMessage(), type: 'error');
        }
    }

    // --- MÉTODOS DE UTILIDAD ---

    private function setupConfirmationModal(string $title, string $buttonText, string $buttonColor)
    {
        $this->confirmationTitle = $title;
        $this->confirmationButtonText = $buttonText;
        $this->confirmationButtonColor = $buttonColor;
    }

    private function resetActionState()
    {
        $this->showConfirmationModal = false;
        $this->confirmation->reset();
        $this->invoiceToCancel = null;
        $this->actionType = '';
    }
}
