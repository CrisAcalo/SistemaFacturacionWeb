<?php

namespace App\Livewire\Products;

use App\Livewire\Forms\Shared\ConfirmationFormObject;
use App\Livewire\Forms\Products\ProductFormObject;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Title('Gestión de Productos - FacturaPro')]
#[Layout('layouts.app')]
class ListProducts extends Component
{
    use WithPagination;

    // Form Objects
    public ProductFormObject $form;
    public ConfirmationFormObject $confirmation;

    // Búsqueda y Paginación
    #[Url(history: true)]
    public string $search = '';
    #[Url(history: true)]
    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50, 100];

    // Control de Modales
    public bool $showFormModal = false;
    public bool $showConfirmationModal = false;

    // Estado de la Acción
    public bool $isEditing = false;
    public ?Product $productToDelete = null;
    public string $actionType = ''; // 'save' o 'delete'

    // Personalización del Modal de Confirmación
    public string $confirmationTitle = '';
    public string $confirmationButtonText = '';
    public string $confirmationButtonColor = '';
    public array $modalTheme = [];

    // --- RENDERIZADO Y CICLO DE VIDA ---

    public function render()
    {
        if (!auth()->user()->can('manage products')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $query = Product::query()
            ->when(
                $this->search,
                fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
            );

        $products = $query->latest()->paginate($this->perPage);
        $this->dispatch('render-barcodes');
        return view('livewire.products.list-products', [
            'products' => $products,
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedFormName(string $value): void
    {
        // Solo autogeneramos si estamos en modo de creación
        if (!$this->isEditing) {
            $sku = '';
            $barcode = '';

            if (trim($value) !== '') {
                $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $value), 0, 3));
                if (strlen($base) < 3) {
                    $base = str_pad($base, 3, 'X');
                }
                $sku = $base . '-' . rand(1000, 9999);
                $barcode = '786' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
            }

            // Actualizamos las propiedades del backend
            $this->form->sku = $sku;
            $this->form->barcode = $barcode;

            // Forzamos la actualización de los inputs en el frontend vía eventos
            $this->dispatch('update-form-fields', [
                'sku' => $sku,
                'barcode' => $barcode,
            ]);
        }
    }

    // --- MÉTODOS DEL CRUD ---

    public function create()
    {
        $this->isEditing = false;
        $this->form->reset();
        $this->setModalTheme('create');
        $this->showFormModal = true;
    }

    public function edit(Product $product)
    {
        $this->isEditing = true;
        $this->form->setProduct($product);
        $this->setModalTheme('edit');
        $this->showFormModal = true;
    }

    public function confirmDelete(Product $product)
    {
        $this->productToDelete = $product;
        $this->actionType = 'delete';
        $this->setModalTheme('delete');
        $this->setupConfirmationModal(
            title: 'Confirmar Eliminación de Producto ' . $product->name,
            buttonText: 'Sí, Eliminar',
            buttonColor: 'bg-red-600 hover:bg-red-700 focus:ring-red-600'
        );
        $this->showConfirmationModal = true;
    }

    // --- LÓGICA DE CONFIRMACIÓN DE DOS PASOS ---

    public function requestConfirmation()
    {
        $this->form->validate();
        $this->actionType = $this->isEditing ? 'save' : 'create';
        $this->setModalTheme($this->isEditing ? 'edit' : 'create');
        $this->setupConfirmationModal(
            title: $this->isEditing ? 'Confirmar Actualización de Producto' : 'Confirmar Creación de Producto',
            buttonText: 'Confirmar',
            buttonColor: $this->isEditing
                ? 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-500'
                : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-600'
        );
        $this->showFormModal = false;
        $this->showConfirmationModal = true;
    }

    public function executeAction()
    {
        $this->confirmation->validate();
        if ($this->actionType === 'save') {
            $this->saveProduct();
        } elseif ($this->actionType === 'delete') {
            $this->deleteProduct();
        }
        $this->resetActionState();
    }

    // --- ACCIONES PRIVADAS ---

    private function saveProduct()
    {
        // Obtenemos todos los campos del formulario.
        $data = $this->form->all();

        if ($this->isEditing) {
            $this->form->editingProduct->update($data);
            $logMessage = 'Producto actualizado: ' . $data['name'];
            $toastMessage = 'Producto actualizado correctamente.';
        } else {
            $product = Product::create($data);
            $logMessage = 'Producto creado: ' . $data['name'];
            $toastMessage = 'Producto creado correctamente.';
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperty('reason', $this->confirmation->reason)
            ->log($logMessage);

        $this->dispatch('show-toast', message: $toastMessage, type: 'success');
    }

    private function deleteProduct()
    {
        activity()
            ->causedBy(auth()->user())
            ->withProperty('reason', $this->confirmation->reason)
            ->log('Producto eliminado: ' . $this->productToDelete->name);

        $this->productToDelete->delete();
        $this->dispatch('show-toast', message: 'Producto eliminado correctamente.', type: 'success');
    }

    // --- MÉTODOS DE UTILIDAD ---

    private function setModalTheme(string $type)
    {
        switch ($type) {
            case 'edit':
                $this->modalTheme = [
                    'header' => 'bg-secondary/10 dark:bg-secondary/20 border-secondary/20',
                    'title' => 'text-secondary-700 dark:text-secondary-300',
                    'icon' => 'bi-pencil-square text-secondary-500',
                ];
                break;
            case 'delete':
                $this->modalTheme = [
                    'header' => 'bg-danger/10 dark:bg-danger/20 border-danger/20',
                    'title' => 'text-danger-700 dark:text-danger-300',
                    'icon' => 'bi-trash3-fill text-danger-500',
                ];
                break;
            case 'create':
            default:
                $this->modalTheme = [
                    'header' => 'bg-primary/10 dark:bg-primary/20 border-primary/20',
                    'title' => 'text-primary-700 dark:text-primary-300',
                    'icon' => 'bi-plus-circle-fill text-primary-500',
                ];
                break;
        }
    }

    private function setupConfirmationModal(string $title, string $buttonText, string $buttonColor)
    {
        $this->confirmationTitle = $title;
        $this->confirmationButtonText = $buttonText;
        $this->confirmationButtonColor = $buttonColor;
    }

    private function resetActionState()
    {
        $this->showFormModal = false;
        $this->showConfirmationModal = false;
        $this->form->reset();
        $this->confirmation->reset();
        $this->reset(['isEditing', 'productToDelete', 'actionType']);
    }
}
