<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

abstract class BaseModel extends Model
{
    use SoftDeletes, LogsActivity;

    /**
     * The attributes that should be mutated to dates.
     * SoftDeletes ya añade 'deleted_at' a este array implícitamente.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at'
    ];

    /**
     * Atributos de auditoría que se deben registrar.
     * Queremos registrar todo.
     *
     * @var array
     */
    protected $auditInclude = [
        // Se definirá en los modelos hijos, pero podemos poner aquí los comunes.
        // Si no se define en el hijo, auditará todos los $fillable.
    ];

    /**
     * Eventos de auditoría que queremos registrar.
     * Por defecto son 'created', 'updated', 'deleted', 'restored'.
     * Podemos dejarlos por defecto o ser explícitos.
     *
     * @var array
     */
    protected $auditEvents = [
        'created',
        'updated',
        'deleted',
        'restored',
    ];

    /**
     * Generar comentarios de auditoría automáticamente.
     *
     * @param string $eventName
     * @return string
     */
    public function generateTags(string $eventName): array
    {
        // Esto añadirá una "etiqueta" a cada evento de auditoría
        // para facilitar el filtrado. Por ejemplo: 'client', 'product'.
        return [
            strtolower(class_basename($this))
        ];
    }

    /**
     * Configuración del log de actividad.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Registra todos los atributos ($fillable)
            ->logOnlyDirty() // Registra solo los atributos que cambiaron
            ->dontSubmitEmptyLogs() // No crea logs si no hubo cambios
            ->setDescriptionForEvent(fn(string $eventName) => $this->getLogDescription($eventName))
            ->useLogName(strtolower(class_basename($this))); // Nombra el log con el nombre del modelo
    }

    /**
     * Genera una descripción legible para el log.
     */
    protected function getLogDescription(string $eventName): string
    {
        $modelName = __('app.models.' . strtolower(class_basename($this)));

        switch ($eventName) {
            case 'created':
                return "Se creó el/la {$modelName}";
            case 'updated':
                return "Se actualizó el/la {$modelName}";
            case 'deleted':
                return "Se eliminó el/la {$modelName}";
            case 'restored':
                return "Se restauró el/la {$modelName}";
            default:
                return "Se realizó la acción '{$eventName}' en el/la {$modelName}";
        }
    }
}
