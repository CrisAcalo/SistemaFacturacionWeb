<?php

namespace App\Livewire\Audits;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Spatie\Activitylog\Models\Activity;

#[Title('Auditoría del Sistema - FacturaPro')]
#[Layout('layouts.app')]
class AuditLog extends Component
{
    use WithPagination;

    // Propiedades para los filtros
    public array $filters = [
        'user_id' => '',
        'event' => '',
        'subject_type' => '',
    ];

    // Propiedades para el modal de detalles
    public ?Activity $selectedLog = null;
    public bool $showDetailsModal = false;

    // --- RENDERIZADO Y LÓGICA DE CONSULTA ---

    public function render()
    {
        if (!auth()->user()->can('view audits')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $query = Activity::query()
            // Cargar la relación del 'causer' (el usuario que realizó la acción)
            // para evitar problemas de N+1 queries en la vista.
            ->with('causer:id,name,email')
            // Aplicar filtros dinámicamente
            ->when($this->filters['user_id'], fn($q) => $q->where('causer_id', $this->filters['user_id']))
            ->when($this->filters['event'], fn($q) => $q->where('event', $this->filters['event']))
            ->when($this->filters['subject_type'], fn($q) => $q->where('subject_type', $this->filters['subject_type']));

        $logs = $query->latest()->paginate(20);

        // Obtener la lista de usuarios para poblar el dropdown de filtro
        $usersForFilter = User::select(['id', 'name'])->orderBy('name')->get();

        return view('livewire.audits.audit-log', [
            'logs' => $logs,
            'usersForFilter' => $usersForFilter,
        ]);
    }

    // --- MANEJO DE FILTROS ---

    /**
     * Se ejecuta cada vez que el array de filtros cambia.
     * Reinicia la paginación a la primera página.
     */
    public function updatedFilters()
    {
        $this->resetPage();
    }

    /**
     * Limpia todos los filtros y vuelve a la primera página.
     */
    public function resetFilters()
    {
        $this->reset('filters');
        $this->resetPage();
    }

    // --- MANEJO DEL MODAL ---

    /**
     * Carga el log seleccionado y abre el modal de detalles.
     * Usa Route Model Binding para inyectar el modelo Activity.
     */
    public function showDetails(Activity $log)
    {
        $this->selectedLog = $log;
        $this->showDetailsModal = true;
    }

    /**
     * Cierra el modal y resetea la propiedad del log seleccionado.
     */
    public function closeModal()
    {
        $this->showDetailsModal = false;
        $this->selectedLog = null;
    }
}
