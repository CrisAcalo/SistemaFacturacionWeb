<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tokens\CreateTokenRequest;
use App\Http\Resources\Api\TokenResource;
use App\Http\Resources\Api\TokenCollection;
use App\Models\PersonalAccessToken;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class TokenController extends Controller
{
    /**
     * Display a listing of tokens for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewOwnTokens', PersonalAccessToken::class);

        $user = $request->user();
        $query = PersonalAccessToken::where('tokenable_id', $user->id)
                                  ->where('tokenable_type', get_class($user));

        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true)
                      ->where(function ($q) {
                          $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                      });
            } elseif ($status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)
                      ->orWhere('expires_at', '<=', now());
                });
            } elseif ($status === 'expired') {
                $query->where('expires_at', '<=', now());
            }
        }

        // Filter by name
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Include revoked tokens if requested
        if ($request->boolean('include_revoked')) {
            // Already included by default since we're not filtering them out
        } else {
            $query->whereNull('deleted_at');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $tokens = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Tokens obtenidos exitosamente',
            'data' => new TokenCollection($tokens),
            'meta' => [
                'total' => $tokens->total(),
                'per_page' => $tokens->perPage(),
                'current_page' => $tokens->currentPage(),
                'last_page' => $tokens->lastPage(),
                'from' => $tokens->firstItem(),
                'to' => $tokens->lastItem()
            ]
        ]);
    }

    /**
     * Create a new API token
     */
    public function store(CreateTokenRequest $request): JsonResponse
    {
        Gate::authorize('createToken', PersonalAccessToken::class);

        $user = $request->user();
        $data = $request->validated();

        // Create the token
        $token = $user->createToken(
            $data['name'],
            $data['abilities'] ?? ['*'],
            $data['expires_at'] ?? null
        );

        // Update additional fields if provided
        $tokenModel = $token->accessToken;
        $tokenModel->update([
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'metadata' => $data['metadata'] ?? null,
            'created_by_role' => $user->roles->first()?->name ?? 'Unknown',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token creado exitosamente',
            'data' => [
                'token' => new TokenResource($tokenModel),
                'plain_text_token' => $token->plainTextToken,
                'warning' => 'Guarda este token de forma segura. No se mostrará nuevamente.'
            ]
        ], 201);
    }

    /**
     * Display the specified token
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $token = PersonalAccessToken::where('tokenable_id', $user->id)
                                  ->where('tokenable_type', get_class($user))
                                  ->findOrFail($id);

        Gate::authorize('viewOwnToken', $token);

        return response()->json([
            'success' => true,
            'message' => 'Token obtenido exitosamente',
            'data' => new TokenResource($token)
        ]);
    }

    /**
     * Revoke (delete) the specified token
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $token = PersonalAccessToken::where('tokenable_id', $user->id)
                                  ->where('tokenable_type', get_class($user))
                                  ->findOrFail($id);

        Gate::authorize('revokeOwnToken', $token);

        // Don't allow revoking the current token being used
        $currentToken = $user->currentAccessToken();
        if ($currentToken && $currentToken->id === $token->id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes revocar el token que estás usando actualmente'
            ], 422);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token revocado exitosamente'
        ]);
    }

    /**
     * Activate or deactivate a token
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $user = $request->user();
        $token = PersonalAccessToken::where('tokenable_id', $user->id)
                                  ->where('tokenable_type', get_class($user))
                                  ->findOrFail($id);

        Gate::authorize('updateOwnTokenStatus', $token);

        $oldStatus = $token->is_active;
        $token->update(['is_active' => $request->get('is_active')]);

        return response()->json([
            'success' => true,
            'message' => 'Estado del token actualizado exitosamente',
            'data' => [
                'token' => new TokenResource($token->fresh()),
                'status_change' => [
                    'previous_status' => $oldStatus ? 'active' : 'inactive',
                    'current_status' => $token->is_active ? 'active' : 'inactive'
                ]
            ]
        ]);
    }

    /**
     * Get audit trail for tokens
     */
    public function auditTrail(Request $request): JsonResponse
    {
        Gate::authorize('viewTokenAudit', PersonalAccessToken::class);

        $user = $request->user();

        $query = Activity::where('subject_type', PersonalAccessToken::class)
                        ->whereHas('subject', function ($q) use ($user) {
                            $q->where('tokenable_id', $user->id)
                              ->where('tokenable_type', get_class($user));
                        });

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Filter by event type
        if ($request->has('event')) {
            $query->where('event', $request->get('event'));
        }

        // Sorting
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy('created_at', $sortDirection);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $activities = $query->with(['subject', 'causer'])->paginate($perPage);

        $formattedActivities = $activities->getCollection()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'event' => $activity->event,
                'description' => $activity->description,
                'subject' => [
                    'type' => $activity->subject_type,
                    'id' => $activity->subject_id,
                    'name' => $activity->subject?->name ?? 'Token eliminado',
                ],
                'causer' => [
                    'type' => $activity->causer_type,
                    'id' => $activity->causer_id,
                    'name' => $activity->causer?->name ?? 'Sistema',
                ],
                'properties' => $activity->properties,
                'created_at' => $activity->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Auditoría de tokens obtenida exitosamente',
            'data' => [
                'activities' => $formattedActivities,
                'stats' => [
                    'total_activities' => $activities->total(),
                    'events_summary' => Activity::where('subject_type', PersonalAccessToken::class)
                                              ->whereHas('subject', function ($q) use ($user) {
                                                  $q->where('tokenable_id', $user->id);
                                              })
                                              ->selectRaw('event, COUNT(*) as count')
                                              ->groupBy('event')
                                              ->get()
                                              ->pluck('count', 'event')
                                              ->toArray()
                ]
            ],
            'meta' => [
                'total' => $activities->total(),
                'per_page' => $activities->perPage(),
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'from' => $activities->firstItem(),
                'to' => $activities->lastItem()
            ]
        ]);
    }
    public function info(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $token = $user->currentAccessToken();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener información del token'
                ], 500);
            }

            $token->update(['last_used_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Token válido',
                'data' => [
                    'token_info' => [
                        'id' => $token->id,
                        'name' => $token->name,
                        'abilities' => $token->abilities,
                        'created_at' => $token->created_at->format('Y-m-d H:i:s'),
                        'expires_at' => $token->expires_at?->format('Y-m-d H:i:s'),
                        'last_used_at' => $token->last_used_at?->format('Y-m-d H:i:s'),
                    ],
                    'user_info' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'status' => $user->status ?? 'active',
                        'roles' => method_exists($user, 'roles') ? $user->roles->pluck('name')->toArray() : [],
                    ],
                    'request_info' => [
                        'timestamp' => now()->format('Y-m-d H:i:s'),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    public function getInvoices(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);
            $status = $request->get('status');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            if ($perPage > 100) {
                $perPage = 100;
            }

            $query = Invoice::where('client_id', $user->id)
                ->with(['items'])
                ->orderBy('created_at', 'desc');

            if ($status) {
                $query->where('status', $status);
            }

            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            $invoices = $query->paginate($perPage, ['*'], 'page', $page);

            $token = $user->currentAccessToken();
            if ($token) {
                $token->update(['last_used_at' => now()]);
            }

            $statistics = [
                'total_invoices' => Invoice::where('client_id', $user->id)->count(),
                'total_amount' => Invoice::where('client_id', $user->id)->sum('total'),
                'paid_invoices' => Invoice::where('client_id', $user->id)->where('status', 'Pagada')->count(),
                'pending_invoices' => Invoice::where('client_id', $user->id)->whereIn('status', ['Pendiente'])->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Facturas obtenidas exitosamente',
                'data' => [
                    'invoices' => $invoices->items(),
                    'pagination' => [
                        'current_page' => $invoices->currentPage(),
                        'last_page' => $invoices->lastPage(),
                        'per_page' => $invoices->perPage(),
                        'total' => $invoices->total(),
                        'from' => $invoices->firstItem(),
                        'to' => $invoices->lastItem(),
                    ],
                    'statistics' => $statistics,
                    'filters_applied' => [
                        'status' => $status,
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    public function test(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'API funcionando correctamente',
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    }
}
