<?php

namespace App\Livewire\Users;

use App\Livewire\Forms\Shared\ConfirmationFormObject;
use App\Livewire\Forms\Users\UserFormObject;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
// use Livewire\Attributes\Url;
use Spatie\Permission\Models\Role;

#[Title('Gestión de Clientes - FacturaPro')]
#[Layout('layouts.app')]
class ListUsers extends Component
{
    use WithPagination;

    // Form Objects para encapsular la lógica de los formularios
    public UserFormObject $form;
    public ConfirmationFormObject $confirmation;

    // Propiedades para la búsqueda y paginación
    //#[Url(history: true)]
    public string $search = '';
    //#[Url(history: true)]
    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50, 100];
    public string $statusFilter = 'all'; // Filtro de estado
    public array $statusOptions = [
        'all' => 'Todos',
        'active' => 'Activos',
        'inactive' => 'Inactivos'
    ];

    // Propiedades para el control de los modales
    public bool $showFormModal = false;
    public bool $showConfirmationModal = false;

    // Propiedades para el estado de la acción actual
    public bool $isEditing = false;
    public ?User $userToDelete = null;
    public string $actionType = ''; // 'save' o 'delete'

    // Propiedades para la personalización del modal de confirmación
    public string $confirmationTitle = '';
    public string $confirmationButtonText = '';
    public string $confirmationButtonColor = '';
    public array $modalTheme = [];

    // --- MÉTODOS DEL CICLO DE VIDA Y RENDERIZADO ---

    public function render()
    {
        if (!auth()->user()->can('manage users')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $query = User::query()
            ->select(['id', 'name', 'email', 'status'])
            ->with(['roles' => fn($q) => $q->select('name')])
            ->when($this->search, function ($query) {
                $query->where(fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            });

        $users = $query->latest()->paginate($this->perPage);
        $allRoles = Role::pluck('name')->all();

        return view('livewire.users.list-users', [
            'users' => $users,
            'allRoles' => $allRoles,
            'statusOptions' => $this->statusOptions,
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
    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    // --- LÓGICA DEL CRUD ---

    public function create()
    {
        $this->isEditing = false;
        $this->form->reset();
        $this->setModalTheme('create');
        $this->showFormModal = true;
    }

    public function edit(User $user)
    {
        $this->isEditing = true;
        $this->form->setUser($user);
        $this->setModalTheme('edit');
        $this->showFormModal = true;
    }

    public function confirmDelete(User $user)
    {
        if ($user->id === auth()->id()) {
            $this->dispatch('show-toast', message: 'No puedes eliminarte a ti mismo.', type: 'error');
            return;
        }
        $this->setModalTheme('delete');
        $this->userToDelete = $user;
        $this->actionType = 'delete';
        $this->setupConfirmationModal(
            title: 'Confirmar Eliminación de Cliente '.$user->name,
            buttonText: 'Sí, Eliminar',
            buttonColor: 'bg-red-600 hover:bg-red-700 focus:ring-red-600'
        );
        $this->showConfirmationModal = true;
    }

    public function toggleUserStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            $this->dispatch('show-toast', message: 'No puedes cambiar tu propio estado.', type: 'error');
            return;
        }

        $oldStatus = $user->status;
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        // Disparar evento para manejar la invalidación de sesiones
        event(new \App\Events\UserStatusChanged($user, $oldStatus, $newStatus));

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ])
            ->log('Estado de usuario cambiado');

        $statusText = $newStatus === 'active' ? 'activado' : 'desactivado';
        $this->dispatch('show-toast', message: "Cliente {$statusText} correctamente.", type: 'success');
    }

    // --- LÓGICA DE CONFIRMACIÓN DE DOS PASOS ---

    public function requestConfirmation()
    {
        $this->form->validate();
        $this->actionType = 'save';
        $this->setupConfirmationModal(
            title: $this->isEditing ? 'Confirmar Actualización de Cliente' : 'Confirmar Creación de Cliente',
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
            $this->saveUser();
        } elseif ($this->actionType === 'delete') {
            $this->deleteUser();
        }

        $this->resetActionState();
    }

    // --- MÉTODOS PRIVADOS DE ACCIÓN ---

    private function saveUser()
    {
        $user = $this->form->editingUser;
        $data = $this->form->only(['name', 'email', 'status']);
        if ($this->form->password) {
            $data['password'] = Hash::make($this->form->password);
        }

        if ($this->isEditing) {
            $user->update($data);
            $logMessage = 'Usuario actualizado';
            $toastMessage = 'Cliente actualizado correctamente.';
        } else {
            $user = User::create($data);
            $logMessage = 'Usuario creado';
            $toastMessage = 'Cliente creado correctamente.';
        }

        $user->syncRoles($this->form->userRoles);

        activity()
            ->performedOn($user)->causedBy(auth()->user())
            ->withProperty('reason', $this->confirmation->reason)
            ->log($logMessage);

        $this->dispatch('show-toast', message: $toastMessage, type: 'success');
    }

    private function deleteUser()
    {
        activity()
            ->performedOn($this->userToDelete)->causedBy(auth()->user())
            ->withProperty('reason', $this->confirmation->reason)
            ->log('Usuario eliminado');

        $this->userToDelete->delete();
        $this->dispatch('show-toast', message: 'Cliente eliminado correctamente.', type: 'success');
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
        $this->showFormModal = false;
        $this->showConfirmationModal = false;
        $this->form->reset();
        $this->confirmation->reset();
        $this->reset(['isEditing', 'userToDelete', 'actionType']);
    }
    private function setModalTheme(string $type)
    {
        switch ($type) {
            case 'edit':
                $this->modalTheme = [
                    'header' => 'bg-amber-400/20 dark:bg-amber-500/30 border-amber-400/40',
                    'title' => 'text-amber-700 dark:text-amber-200',
                    'icon' => 'bi-pencil-square text-amber-500',
                ];
                break;
            case 'delete':
                $this->modalTheme = [
                    'header' => 'bg-red-600/20 dark:bg-red-700/30 border-red-600/40',
                    'title' => 'text-red-700 dark:text-red-300',
                    'icon' => 'bi-trash3-fill text-red-600',
                ];
                break;
            case 'create':
            default:
                $this->modalTheme = [
                    'header' => 'bg-blue-500/20 dark:bg-blue-600/30 border-blue-500/40',
                    'title' => 'text-blue-700 dark:text-blue-300',
                    'icon' => 'bi-plus-circle-fill text-blue-600',
                ];
                break;
        }
    }
}
