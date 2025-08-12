<?php

namespace App\Livewire\Payments;

use App\Livewire\Forms\Shared\ConfirmationFormObject;
use App\Models\Payment;
use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

#[Title('Gestión de Pagos - FacturaPro')]
#[Layout('layouts.app')]
class ListPayments extends Component
{
    use WithPagination;

    // Form Object para confirmación
    public ConfirmationFormObject $confirmation;

    // Búsqueda y Paginación
    #[Url(history: true)]
    public string $search = '';
    #[Url(history: true)]
    public string $status = '';
    #[Url(history: true)]
    public int $perPage = 15;
    public array $perPageOptions = [15, 25, 50, 100];

    // Control de Modales
    public bool $showApproveModal = false;
    public bool $showRejectModal = false;
    public bool $showConfirmationModal = false;

    // Estado de la Acción
    public ?Payment $selectedPayment = null;
    public string $actionType = ''; // 'approve', 'reject'

    // Notas de validación
    public string $validationNotes = '';

    // Personalización del Modal de Confirmación
    public string $confirmationTitle = '';
    public string $confirmationButtonText = '';
    public string $confirmationButtonColor = '';

    public function boot()
    {
        // La inicialización automática de Form Objects en Livewire v3
    }

    public function mount()
    {
        // Configuración inicial del componente
    }

    public function render()
    {
        $payments = Payment::query()
            ->with(['invoice.user', 'client'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('invoice', function ($invoice) {
                        $invoice->where('invoice_number', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('client', function ($client) {
                        $client->where('name', 'like', '%' . $this->search . '%')
                               ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('amount', 'like', '%' . $this->search . '%')
                    ->orWhere('payment_type', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status && $this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $stats = [
            'pendiente' => Payment::where('status', 'pendiente')->count(),
            'validado' => Payment::where('status', 'validado')->count(),
            'rechazado' => Payment::where('status', 'rechazado')->count(),
            'total_pendiente_amount' => Payment::where('status', 'pendiente')->sum('amount'),
        ];

        return view('livewire.payments.list-payments', compact('payments', 'stats'));
    }

    // --- BÚSQUEDA Y FILTROS ---

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // --- SELECCIÓN DE PAGOS --- (REMOVIDO)
    // Se eliminó la funcionalidad de selección masiva según solicitud del usuario

    // --- ACCIONES INDIVIDUALES ---

    public function requestApproval($paymentId)
    {
        $payment = Payment::find($paymentId);

        if (!$payment || !$payment->isPending()) {
            $this->dispatch('show-toast', message: 'Solo se pueden aprobar pagos pendientes.', type: 'error');
            return;
        }

        $this->selectedPayment = $payment;
        $this->validationNotes = '';
        $this->showApproveModal = true;
    }

    public function requestRejection($paymentId)
    {
        $payment = Payment::find($paymentId);

        if (!$payment || !$payment->isPending()) {
            $this->dispatch('show-toast', message: 'Solo se pueden rechazar pagos pendientes.', type: 'error');
            return;
        }

        $this->selectedPayment = $payment;
        $this->validationNotes = '';
        $this->showRejectModal = true;
    }

    public function confirmApproval()
    {
        if (!$this->selectedPayment) {
            $this->dispatch('show-toast', message: 'No hay pago seleccionado.', type: 'error');
            return;
        }

        $this->actionType = 'approve';
        $this->setupConfirmationModal(
            'Confirmar Aprobación de Pago',
            'Aprobar Pago',
            'bg-green-500 hover:bg-green-700'
        );
        $this->confirmation->reason = $this->validationNotes;
        $this->showApproveModal = false;
        $this->showConfirmationModal = true;
    }

    public function confirmRejection()
    {
        if (!$this->selectedPayment) {
            $this->dispatch('show-toast', message: 'No hay pago seleccionado.', type: 'error');
            return;
        }

        if (empty($this->validationNotes)) {
            $this->dispatch('show-toast', message: 'El motivo del rechazo es obligatorio.', type: 'error');
            return;
        }

        $this->actionType = 'reject';
        $this->setupConfirmationModal(
            'Confirmar Rechazo de Pago',
            'Rechazar Pago',
            'bg-red-500 hover:bg-red-700'
        );
        $this->confirmation->reason = $this->validationNotes;
        $this->showRejectModal = false;
        $this->showConfirmationModal = true;
    }

    // --- ACCIONES MASIVAS --- (REMOVIDO)
    // Se eliminó la funcionalidad de acciones masivas según solicitud del usuario

    // --- EJECUCIÓN DE ACCIONES ---

    public function executeAction()
    {
        $this->confirmation->validate();

        if (!Hash::check($this->confirmation->password, Auth::user()->password)) {
            $this->addError('confirmation.password', 'La contraseña no es correcta.');
            return;
        }

        try {
            switch ($this->actionType) {
                case 'approve':
                    $this->executeApproval();
                    break;
                case 'reject':
                    $this->executeRejection();
                    break;
            }

            $this->closeModals();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }

    private function executeApproval()
    {
        if (!$this->selectedPayment) return;

        $this->selectedPayment->approve(Auth::user(), $this->confirmation->reason);

        activity()
            ->performedOn($this->selectedPayment)
            ->causedBy(Auth::user())
            ->withProperties(['notes' => $this->confirmation->reason])
            ->log('Pago aprobado manualmente');

        $this->dispatch('show-toast', message: 'Pago aprobado exitosamente. Se ha actualizado el estado de la factura.', type: 'success');
        
        // Refrescar la vista para mostrar cambios
        $this->resetPage();
    }

    private function executeRejection()
    {
        if (!$this->selectedPayment) return;

        $this->selectedPayment->reject(Auth::user(), $this->confirmation->reason);

        activity()
            ->performedOn($this->selectedPayment)
            ->causedBy(Auth::user())
            ->withProperties(['notes' => $this->confirmation->reason])
            ->log('Pago rechazado manualmente');

        $this->dispatch('show-toast', message: 'Pago rechazado exitosamente. Se ha actualizado el estado de la factura.', type: 'success');
        
        // Refrescar la vista para mostrar cambios
        $this->resetPage();
    }

    // --- CONTROL DE MODALES ---

    public function closeModals()
    {
        $this->showApproveModal = false;
        $this->showRejectModal = false;
        $this->showConfirmationModal = false;
        $this->selectedPayment = null;
        $this->validationNotes = '';
        $this->confirmation->reset();
    }

    // --- MÉTODOS DE UTILIDAD ---

    private function setupConfirmationModal(string $title, string $buttonText, string $buttonColor)
    {
        $this->confirmationTitle = $title;
        $this->confirmationButtonText = $buttonText;
        $this->confirmationButtonColor = $buttonColor;
    }
}
