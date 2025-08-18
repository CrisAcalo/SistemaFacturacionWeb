<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Users\CreateUserRequest;
use App\Http\Requests\Api\Users\UpdateUserRequest;
use App\Http\Requests\Api\Users\AssignRolesRequest;
use App\Http\Resources\Api\UserResource;
use App\Http\Resources\Api\Collections\UserCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    /**
     * Listar usuarios con filtros y paginación
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Verificar permisos
            if (!Gate::allows('manage users')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver usuarios'
                ], 403);
            }

            $perPage = min($request->get('per_page', 15), 100);
            $search = $request->get('search');
            $status = $request->get('status');
            $role = $request->get('role');
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            $query = User::query()
                ->with(['roles:name', 'permissions:name'])
                ->withTrashed($request->boolean('include_deleted'));

            // Filtro por búsqueda
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filtro por estado
            if ($status && in_array($status, ['active', 'inactive'])) {
                $query->where('status', $status);
            }

            // Filtro por rol
            if ($role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            }

            // Ordenamiento
            $allowedSortFields = ['name', 'email', 'created_at', 'last_login_at', 'status'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
            }

            $users = $query->paginate($perPage);

            // Actualizar última actividad del token
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->update(['last_used_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuarios obtenidos exitosamente',
                'data' => new UserCollection($users)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crear nuevo usuario
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            // Verificar permisos
            if (!Gate::allows('manage users')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para crear usuarios'
                ], 403);
            }

            $validated = $request->validated();
            $validated['password'] = Hash::make($validated['password']);

            // Crear usuario
            $user = User::create($validated);

            // Asignar roles si se especificaron
            if (isset($validated['roles']) && !empty($validated['roles'])) {
                $user->assignRole($validated['roles']);
            }

            // Log de actividad
            activity()
                ->performedOn($user)
                ->causedBy($request->user())
                ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
                ->log('Usuario creado vía API');

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => new UserResource($user->load(['roles', 'permissions']))
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Ver detalles de un usuario específico
     */
    public function show(Request $request, User $user): JsonResponse
    {
        try {
            // Verificar permisos
            if (!Gate::allows('manage users') && $request->user()->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver este usuario'
                ], 403);
            }

            $user->load(['roles', 'permissions']);

            // Actualizar última actividad del token
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->update(['last_used_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario obtenido exitosamente',
                'data' => new UserResource($user)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Actualizar usuario
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            // Verificar permisos
            if (!Gate::allows('manage users') && $request->user()->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para actualizar este usuario'
                ], 403);
            }

            $validated = $request->validated();

            // Si se incluye contraseña, encriptarla
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            // Actualizar usuario
            $user->update($validated);

            // Actualizar roles si se especificaron y el usuario tiene permisos
            if (isset($validated['roles']) && Gate::allows('manage users')) {
                $user->syncRoles($validated['roles']);
            }

            // Log de actividad
            activity()
                ->performedOn($user)
                ->causedBy($request->user())
                ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
                ->log('Usuario actualizado vía API');

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => new UserResource($user->fresh(['roles', 'permissions']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Eliminar usuario (soft delete)
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        try {
            // Verificar permisos
            if (!Gate::allows('manage users')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar usuarios'
                ], 403);
            }

            // No permitir auto-eliminación
            if ($request->user()->id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminarte a ti mismo'
                ], 422);
            }

            // Soft delete
            $user->delete();

            // Log de actividad
            activity()
                ->performedOn($user)
                ->causedBy($request->user())
                ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
                ->log('Usuario eliminado vía API');

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Restaurar usuario eliminado
     */
    public function restore(Request $request, int $userId): JsonResponse
    {
        try {
            // Verificar permisos
            if (!Gate::allows('manage users')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para restaurar usuarios'
                ], 403);
            }

            $user = User::withTrashed()->findOrFail($userId);

            if (!$user->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no está eliminado'
                ], 422);
            }

            $user->restore();

            // Log de actividad
            activity()
                ->performedOn($user)
                ->causedBy($request->user())
                ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
                ->log('Usuario restaurado vía API');

            return response()->json([
                'success' => true,
                'message' => 'Usuario restaurado exitosamente',
                'data' => new UserResource($user->load(['roles', 'permissions']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Cambiar estado del usuario (activo/inactivo)
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        try {
            // Verificar permisos
            if (!Gate::allows('manage users')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar estado de usuarios'
                ], 403);
            }

            $request->validate([
                'status' => 'required|in:active,inactive'
            ]);

            // No permitir desactivar cuenta propia
            if ($request->user()->id === $user->id && $request->status === 'inactive') {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes desactivar tu propia cuenta'
                ], 422);
            }

            $oldStatus = $user->status;
            $user->update(['status' => $request->status]);

            // Log de actividad
            activity()
                ->performedOn($user)
                ->causedBy($request->user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ])
                ->log('Estado de usuario cambiado vía API');

            return response()->json([
                'success' => true,
                'message' => 'Estado del usuario actualizado exitosamente',
                'data' => new UserResource($user->fresh(['roles', 'permissions']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Ver roles de un usuario
     */
    public function getRoles(Request $request, User $user): JsonResponse
    {
        try {
            // Verificar permisos
            if (!Gate::allows('manage users') && $request->user()->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver roles de este usuario'
                ], 403);
            }

            $roles = $user->roles()->with('permissions')->get();

            return response()->json([
                'success' => true,
                'message' => 'Roles obtenidos exitosamente',
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'roles' => $roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'permissions' => $role->permissions->pluck('name')
                        ];
                    })
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles del usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Asignar/actualizar roles de un usuario
     */
    public function assignRoles(AssignRolesRequest $request, User $user): JsonResponse
    {
        try {
            // Verificar permisos
            if (!Gate::allows('manage users')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para asignar roles'
                ], 403);
            }

            $validated = $request->validated();

            // Obtener roles actuales
            $oldRoles = $user->roles->pluck('name')->toArray();

            // Sincronizar roles
            $user->syncRoles($validated['roles']);

            // Log de actividad
            activity()
                ->performedOn($user)
                ->causedBy($request->user())
                ->withProperties([
                    'old_roles' => $oldRoles,
                    'new_roles' => $validated['roles'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ])
                ->log('Roles de usuario actualizados vía API');

            return response()->json([
                'success' => true,
                'message' => 'Roles asignados exitosamente',
                'data' => new UserResource($user->fresh(['roles', 'permissions']))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar roles',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Listar todos los roles disponibles
     */
    public function getRolesAvailable(Request $request): JsonResponse
    {
        try {
            // Verificar permisos
            if (!Gate::allows('manage users')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver roles'
                ], 403);
            }

            $roles = Role::with('permissions')->get();

            return response()->json([
                'success' => true,
                'message' => 'Roles disponibles obtenidos exitosamente',
                'data' => $roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'permissions' => $role->permissions->pluck('name')
                    ];
                })
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles disponibles',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Listar todos los permisos disponibles
     */
    public function getPermissionsAvailable(Request $request): JsonResponse
    {
        try {
            // Verificar permisos
            if (!Gate::allows('manage users')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver permisos'
                ], 403);
            }

            $permissions = Permission::all();

            return response()->json([
                'success' => true,
                'message' => 'Permisos disponibles obtenidos exitosamente',
                'data' => $permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name
                    ];
                })
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permisos disponibles',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}
