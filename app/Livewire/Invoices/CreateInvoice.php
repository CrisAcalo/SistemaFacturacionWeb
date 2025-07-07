<?php

namespace App\Livewire\Invoices;

use App\Livewire\Forms\Invoices\InvoiceFormObject;
use App\Livewire\Forms\Shared\ConfirmationFormObject;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Crear Factura - FacturaPro')]
#[Layout('layouts.app')]
class CreateInvoice extends Component
{
    // Form Objects
    public InvoiceFormObject $form;
    public ConfirmationFormObject $confirmation;

    // Propiedades para la búsqueda
    public string $clientSearch = '';
    public string $productSearch = '';
    public $clientSearchResults = [];
    public $productSearchResults = [];

    // Estado de la factura actual
    public ?User $selectedClient = null;
    public array $invoiceItems = [];

    // Propiedades calculadas
    public float $subtotal = 0.00;
    public float $taxAmount = 0.00;
    public float $total = 0.00;
    public float $taxRate = 0.12; // Tasa de impuesto (12%)

    // Control de modales y temas para confirmation-modal
    public bool $showConfirmationModal = false;
    public string $confirmationTitle = '';
    public string $confirmationButtonText = '';
    public string $confirmationButtonColor = '';
    public string $actionType = ''; // 'save' o 'delete'
    public bool $isEditing = false; // Para el modal, aunque aquí siempre es creación
    public array $modalTheme = [];

    // --- MÉTODOS DEL CICLO DE VIDA (HOOKS) ---

    public function updatedClientSearch($value)
    {
        if (strlen($value) >= 2) {
            $this->clientSearchResults = User::where(
                fn($q) =>
                $q->where('name', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%")
            )->limit(5)->get();
        } else {
            $this->clientSearchResults = [];
        }
    }

    public function updatedProductSearch($value)
    {
        if (strlen($value) >= 2) {
            $this->productSearchResults = Product::where(
                fn($q) =>
                $q->where('name', 'like', "%{$value}%")
                    ->orWhere('sku', 'like', "%{$value}%")
            )->where('stock', '>', 0)->limit(5)->get();
        } else {
            $this->productSearchResults = [];
        }
    }

    // Se ejecuta cada vez que el array de items cambia
    public function updatedInvoiceItems()
    {
        $this->calculateTotals();
    }

    // --- MANIPULACIÓN DE LA FACTURA ---

    public function selectClient(User $client)
    {
        $this->selectedClient = $client;
        $this->form->clientId = $client->id;
        $this->clientSearch = '';
        $this->clientSearchResults = [];
    }

    public function deselectClient()
    {
        $this->selectedClient = null;
        $this->form->clientId = null;
    }

    public function addProduct(Product $product)
    {
        // Verificar si el producto ya está en la lista
        $existingItemIndex = collect($this->invoiceItems)->search(fn($item) => $item['product_id'] === $product->id);

        if ($existingItemIndex !== false) {
            // Si ya existe, incrementar la cantidad si hay stock
            if ($this->invoiceItems[$existingItemIndex]['quantity'] < $product->stock) {
                $this->invoiceItems[$existingItemIndex]['quantity']++;
            }
        } else {
            // Si no existe, añadirlo a la lista
            $this->invoiceItems[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'quantity' => 1,
                'price' => $product->price,
                'total' => $product->price,
            ];
        }

        $this->productSearch = '';
        $this->productSearchResults = [];
        $this->calculateTotals();
    }

    public function removeItem(int $index)
    {
        unset($this->invoiceItems[$index]);
        $this->invoiceItems = array_values($this->invoiceItems); // Re-indexar el array
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = collect($this->invoiceItems)->sum(function ($item) {
            $item['quantity'] = (float) $item['quantity'];
            $item['price'] = (float) $item['price'];
            $item['total'] = $item['quantity'] * $item['price'];
            $this->invoiceItems[array_search($item, $this->invoiceItems)]['total'] = $item['total'];
            return $item['total'];
        });

        $this->taxAmount = $this->subtotal * $this->taxRate;
        $this->total = $this->subtotal + $this->taxAmount;
    }

    // --- LÓGICA DE GUARDADO Y CONFIRMACIÓN ---

    public function requestConfirmation()
    {
        // Validación de cliente seleccionado
        if (!$this->selectedClient || !$this->form->clientId) {
            $this->dispatch('show-toast', message: 'Debes seleccionar un cliente.', type: 'error');
            return;
        }

        // Validación de productos en la factura
        if (empty($this->invoiceItems)) {
            $this->dispatch('show-toast', message: 'Debes agregar al menos un producto a la factura.', type: 'error');
            return;
        }

        // Validación de cantidades y stock
        foreach ($this->invoiceItems as $item) {
            if (!is_numeric($item['quantity']) || $item['quantity'] < 1) {
                $this->dispatch('show-toast', message: "La cantidad para el producto '{$item['name']}' debe ser mayor a 0.", type: 'error');
                return;
            }
            $product = Product::find($item['product_id']);
            if (!$product) {
                $this->dispatch('show-toast', message: "El producto '{$item['name']}' ya no está disponible.", type: 'error');
                return;
            }
            if ($item['quantity'] > $product->stock) {
                $this->dispatch('show-toast', message: "No hay suficiente stock para '{$item['name']}'. Disponible: {$product->stock}", type: 'error');
                return;
            }
        }

        // Llenar el form object y validarlo
        $this->form->items = $this->invoiceItems;
        $this->form->subtotal = $this->subtotal;
        $this->form->taxAmount = $this->taxAmount;
        $this->form->total = $this->total;

        $this->form->validate([
            'clientId' => 'required',
            'items' => 'required|array|min:1'
        ]);

        $this->setModalTheme('create');
        $this->setupConfirmationModal(
            title: 'Confirmar Generación de Factura',
            buttonText: 'Confirmar y Guardar',
            buttonColor: 'bg-primary hover:bg-primary/90'
        );
        $this->showConfirmationModal = true;
    }

    public function executeAction()
    {
        $this->confirmation->validate();

        try {
            DB::transaction(function () {
                // 1. Crear la factura
                $invoice = Invoice::create([
                    'invoice_number' => 'INV-' . time(), // Generador de número simple
                    'user_id' => auth()->id(),
                    'client_id' => $this->form->clientId,
                    'subtotal' => $this->form->subtotal,
                    'tax' => $this->form->taxAmount,
                    'total' => $this->form->total,
                    'status' => 'Pagada',
                    'notes' => $this->confirmation->reason,
                ]);

                // 2. Añadir los items y actualizar stock
                foreach ($this->form->items as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['total'],
                    ]);

                    // Actualizar el stock del producto
                    $product = Product::find($item['product_id']);
                    $product->decrement('stock', $item['quantity']);
                }

                // 3. Registrar auditoría
                activity()
                    ->performedOn($invoice)
                    ->causedBy(auth()->user())
                    ->withProperty('reason', $this->confirmation->reason)
                    ->log('Factura creada: ' . $invoice->invoice_number);
            });

            $this->dispatch('show-toast', message: 'Factura creada exitosamente.', type: 'success');
            // Redirigir a una página de visualización de factura o al listado
            $this->redirectRoute('invoices.index', navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', message: 'Error al crear la factura: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->resetActionState();
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
    }

    private function setModalTheme(string $type)
    { /* Opcional para este modal */
    }

    public function render()
    {
        if (!auth()->user()->can('manage invoices')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
        return view('livewire.invoices.create-invoice');
    }
}
