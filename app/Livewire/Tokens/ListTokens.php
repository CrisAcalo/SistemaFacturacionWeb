<?php

namespace App\Livewire\Tokens;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

#[Title('Gestión de Tokens de API - FacturaPro')]
#[Layout('layouts.app')]
class ListTokens extends Component
{
    use WithPagination;

    // Propiedades para la búsqueda y paginación
    public string $search = '';
    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50, 100];
    public string $statusFilter = 'all';
    public array $statusOptions = [
        'all' => 'Todos',
        'active' => 'Activos',
        'inactive' => 'Inactivos',
        'expired' => 'Expirados'
    ];

    // Propiedades para el modal de creación
    public bool $showCreateModal = false;
    public ?PersonalAccessToken $editingToken = null;
    public array $form = [
        'name' => '',
        'user_id' => '',
        'description' => '',
        'never_expires' => true,
        'expires_at' => '',
        'metadata' => ['notes' => '']
    ];

    // Variables adicionales para la vista
    public ?array $newTokenInfo = null;

    // Propiedades legacy (mantenidas por compatibilidad)
    public string $tokenName = '';
    public string $tokenDescription = '';
    public int $selectedUserId = 0;
    public string $selectedRole = '';
    public array $selectedAbilities = [];
    public string $expiresInDays = '';
    public bool $neverExpires = true;

    // Token recién creado
    public ?string $newTokenValue = null;
    public bool $showTokenModal = false;

    // Propiedades para confirmación (modal compartido)
    public bool $showConfirmationModal = false;
    public bool $isEditing = false;
    public string $confirmationTitle = '';
    public string $confirmationButtonText = '';
    public array $confirmation = [
        'reason' => '',
        'password' => '',
        'confirm' => false
    ];

    // Propiedades para confirmación (legacy - mantener compatibilidad)
    public bool $showConfirmModal = false;
    public ?PersonalAccessToken $tokenToAction = null;
    public string $actionType = '';
    public string $confirmTitle = '';
    public string $confirmMessage = '';

    // Habilidades disponibles
    public array $availableAbilities = [
        'users:read' => 'Ver usuarios',
        'users:write' => 'Gestionar usuarios',
        'products:read' => 'Ver productos',
        'products:write' => 'Gestionar productos',
        'invoices:read' => 'Ver facturas',
        'invoices:write' => 'Gestionar facturas',
        'audits:read' => 'Ver auditorías',
        'api:full' => 'Acceso completo a API'
    ];

    public function mount()
    {
        // El middleware ya maneja la verificación de permisos
    }

    public function render()
    {
        $query = PersonalAccessToken::query()
            ->with(['tokenable'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('description', 'like', "%{$this->search}%")
                      ->orWhereHas('tokenable', function ($userQuery) {
                          $userQuery->where('name', 'like', "%{$this->search}%")
                                   ->orWhere('email', 'like', "%{$this->search}%");
                      });
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                switch ($this->statusFilter) {
                    case 'active':
                        $query->active();
                        break;
                    case 'inactive':
                        $query->inactive();
                        break;
                    case 'expired':
                        $query->expired();
                        break;
                }
            });

        $tokens = $query->latest()->paginate($this->perPage);
        $users = User::select('id', 'name', 'email')->get();
        $roles = Role::pluck('name')->all();

        return view('livewire.tokens.list-tokens', [
            'tokens' => $tokens,
            'users' => $users,
            'roles' => $roles,
            'availableAbilities' => $this->availableAbilities,
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

    public function create()
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function createToken()
    {
        $this->validate([
            'tokenName' => 'required|string|max:255',
            'tokenDescription' => 'nullable|string|max:500',
            'selectedUserId' => 'required|exists:users,id',
            'selectedRole' => 'nullable|string|exists:roles,name',
            'expiresInDays' => 'nullable|integer|min:1|max:3650',
        ]);

        $user = User::findOrFail($this->selectedUserId);

        // Calcular fecha de expiración
        $expiresAt = null;
        if (!$this->neverExpires && $this->expiresInDays) {
            $expiresAt = now()->addDays((int) $this->expiresInDays);
        }

        // Crear el token con habilidades por defecto
        $defaultAbilities = ['*']; // Acceso completo

        $token = $user->createToken(
            $this->tokenName,
            $defaultAbilities,
            $expiresAt
        );

        // Actualizar campos adicionales
        $personalAccessToken = PersonalAccessToken::find($token->accessToken->id);
        $personalAccessToken->update([
            'description' => $this->tokenDescription,
            'plain_text_token' => $token->plainTextToken,
            'expires_at' => $expiresAt,
            'created_by_role' => $this->selectedRole,
            'metadata' => [
                'created_by_user_id' => Auth::id(),
                'created_by_user_name' => Auth::user()->name,
                'created_at_formatted' => now()->format('Y-m-d H:i:s'),
            ]
        ]);

        // Guardar el token para mostrarlo
        $this->newTokenValue = $token->plainTextToken;
        $this->newTokenInfo = [
            'id' => $personalAccessToken->id,
            'name' => $this->tokenName,
            'description' => $this->tokenDescription,
            'expires_at' => $expiresAt ? $expiresAt->format('d/m/Y H:i') : null,
        ];

        // Log de la actividad
        activity('token_created')
            ->performedOn($personalAccessToken)
            ->withProperties([
                'token_name' => $this->tokenName,
                'user_id' => $this->selectedUserId,
                'abilities' => $defaultAbilities,
                'expires_at' => $expiresAt?->format('Y-m-d H:i:s'),
            ])
            ->log('Token de API creado');

        $this->showCreateModal = false;
        $this->showTokenModal = true;
        $this->dispatch('show-toast', message: 'Token creado exitosamente.', type: 'success');
    }

    public function toggleTokenStatus(PersonalAccessToken $token)
    {
        if ($token->is_active) {
            $token->deactivate();
            $message = 'Token desactivado correctamente.';
        } else {
            $token->activate();
            $message = 'Token activado correctamente.';
        }

        $this->dispatch('show-toast', message: $message, type: 'success');
    }

    public function confirmDelete(PersonalAccessToken $token)
    {
        $this->tokenToAction = $token;
        $this->actionType = 'delete';
        $this->confirmationTitle = 'Eliminar Token';
        $this->confirmationButtonText = 'Eliminar';
        $this->isEditing = false;

        // Reset confirmation form
        $this->confirmation = [
            'reason' => '',
            'password' => '',
            'confirm' => false
        ];

        $this->showConfirmationModal = true;
    }

    public function executeAction()
    {
        // Validar confirmación
        $this->validate([
            'confirmation.reason' => 'required|string|min:10',
            'confirmation.password' => 'required|string',
            'confirmation.confirm' => 'accepted',
        ]);

        // Verificar contraseña del usuario
        $user = Auth::user();
        if (!$user || !Hash::check($this->confirmation['password'], $user->getAuthPassword())) {
            $this->addError('confirmation.password', 'La contraseña es incorrecta.');
            return;
        }

        if ($this->actionType === 'delete' && $this->tokenToAction) {
            activity('token_deleted')
                ->performedOn($this->tokenToAction)
                ->withProperties([
                    'token_name' => $this->tokenToAction->name,
                    'user_id' => $this->tokenToAction->tokenable_id,
                    'reason' => $this->confirmation['reason'],
                ])
                ->log('Token de API eliminado');

            $this->tokenToAction->delete();
            session()->flash('message', 'Token eliminado correctamente.');
        }

        $this->resetConfirmation();
    }

    private function resetCreateForm()
    {
        $this->tokenName = '';
        $this->tokenDescription = '';
        $this->selectedUserId = 0;
        $this->selectedRole = '';
        $this->expiresInDays = '';
        $this->neverExpires = true;
    }

    private function resetConfirmation()
    {
        $this->showConfirmationModal = false;
        $this->showConfirmModal = false;
        $this->tokenToAction = null;
        $this->actionType = '';
        $this->confirmationTitle = '';
        $this->confirmationButtonText = '';
        $this->isEditing = false;
        $this->confirmation = [
            'reason' => '',
            'password' => '',
            'confirm' => false
        ];
    }

    public function closeTokenModal()
    {
        $this->showTokenModal = false;
        $this->newTokenValue = null;
        $this->newTokenInfo = null;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->editingToken = null;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.user_id' => 'required|exists:users,id',
            'form.description' => 'nullable|string|max:500',
            'form.expires_at' => $this->form['never_expires'] ? 'nullable' : 'required|date|after:now',
        ]);

        $user = User::findOrFail($this->form['user_id']);

        $expiresAt = $this->form['never_expires'] ? null : $this->form['expires_at'];

        // Asignar habilidades por defecto (acceso completo)
        $defaultAbilities = ['*']; // Wildcard para acceso completo

        $token = $user->createToken(
            $this->form['name'],
            $defaultAbilities,
            $expiresAt ? Carbon::parse($expiresAt) : null
        );

        // Actualizar el token con información adicional
        $personalAccessToken = PersonalAccessToken::find($token->accessToken->id);
        $personalAccessToken->update([
            'description' => $this->form['description'],
            'plain_text_token' => $token->plainTextToken,
            'metadata' => $this->form['metadata'],
            'created_by_role' => Auth::user()->roles->pluck('name')->implode(', '),
        ]);

        // Información para el modal de confirmación
        $this->newTokenValue = $token->plainTextToken;
        $this->newTokenInfo = [
            'id' => $personalAccessToken->id,
            'name' => $this->form['name'],
            'description' => $this->form['description'],
            'expires_at' => $expiresAt ? Carbon::parse($expiresAt)->format('d/m/Y H:i') : null,
        ];

        $this->showCreateModal = false;
        $this->showTokenModal = true;
        $this->resetForm();

        session()->flash('message', 'Token creado exitosamente.');
    }

    private function resetForm()
    {
        $this->form = [
            'name' => '',
            'user_id' => '',
            'description' => '',
            'never_expires' => true,
            'expires_at' => '',
            'metadata' => ['notes' => '']
        ];
    }
}
