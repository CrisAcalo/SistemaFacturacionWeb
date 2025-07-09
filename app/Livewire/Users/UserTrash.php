<?php

namespace App\Livewire\Users;

use App\Livewire\Forms\Shared\ConfirmationFormObject;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Papelera de Clientes - FacturaPro')]
#[Layout('layouts.app')]
class UserTrash extends Component
{
    use WithPagination;

    public ConfirmationFormObject $confirmation;

    public string $search = '';

    // Estado de la acción
    public ?int $userIdToRestore = null;
    public ?int $userIdToForceDelete = null;
    public string $actionType = ''; // 'restore' o 'forceDelete'

    // Control de modales y temas
    public bool $showConfirmationModal = false;
    public string $confirmationTitle = '';
    public string $confirmationButtonText = '';
    public string $confirmationButtonColor = '';
    public bool $isEditing = false; // Necesario para el modal compartido

    public function render()
    {
        if (!auth()->user()->can('manage users')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        // --- onlyTrashed() es la clave aquí ---
        $query = User::onlyTrashed()
            ->when(
                $this->search,
                fn($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
            );

        $trashedUsers = $query->latest('deleted_at')->paginate(10);

        return view('livewire.users.user-trash', [
            'trashedUsers' => $trashedUsers,
            // Lista de usuarios para el filtro del modal de auditoría si fuera necesario
            'usersForFilter' => User::select(['id', 'name'])->get(),
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // --- LÓGICA DE ACCIONES DE PAPELERA ---

    public function confirmRestore(int $userId)
    {
        $this->userIdToRestore = $userId;
        $this->actionType = 'create';
        $this->setupConfirmationModal(
            title: 'Confirmar Restauración de Cliente',
            buttonText: 'Sí, Restaurar',
            buttonColor: 'bg-green-600 hover:bg-green-700'
        );
        $this->showConfirmationModal = true;
    }

    public function confirmForceDelete(int $userId)
    {
        $this->userIdToForceDelete = $userId;
        $this->actionType = 'delete';
        $this->setupConfirmationModal(
            title: 'Confirmar Eliminación PERMANENTE',
            buttonText: 'Eliminar Definitivamente',
            buttonColor: 'bg-red-600 hover:bg-red-700 focus:ring-red-600'
        );
        $this->showConfirmationModal = true;
    }

    public function executeAction()
    {
        $this->confirmation->validate();

        if ($this->actionType === 'create' && $this->userIdToRestore) {
            $this->restoreUser();
        } elseif ($this->actionType === 'delete' && $this->userIdToForceDelete) {
            $this->forceDeleteUser();
        }

        $this->resetActionState();
    }

    private function restoreUser()
    {
        $user = User::onlyTrashed()->find($this->userIdToRestore);
        $user->restore();

        activity()->performedOn($user)->causedBy(auth()->user())
            ->withProperty('reason', $this->confirmation->reason)
            ->log('Usuario restaurado desde la papelera: ' . $user->name);

        $this->dispatch('show-toast', message: 'Cliente restaurado correctamente.', type: 'success');
    }

    private function forceDeleteUser()
    {
        $user = User::onlyTrashed()->find($this->userIdToForceDelete);
        $userName = $user->name; // Guardamos el nombre para el log
        $user->forceDelete();

        activity()->causedBy(auth()->user())
            ->withProperty('reason', $this->confirmation->reason)
            ->log('Usuario eliminado permanentemente: ' . $userName);

        $this->dispatch('show-toast', message: 'Cliente eliminado permanentemente.', type: 'success');
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
        $this->userIdToRestore = null;
        $this->userIdToForceDelete = null;
        $this->actionType = '';
    }
}
