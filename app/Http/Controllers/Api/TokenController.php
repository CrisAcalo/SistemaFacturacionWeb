<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalAccessToken;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TokenController extends Controller
{
    public function info(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $bearerToken = $request->bearerToken();

            if (!$bearerToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token no proporcionado'
                ], 401);
            }

            $token = PersonalAccessToken::where('plain_text_token', $bearerToken)->first();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token no válido'
                ], 401);
            }

            if (!$token->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inactivo o expirado'
                ], 401);
            }

            $token->update(['last_used_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Token válido',
                'data' => [
                    'token_info' => [
                        'id' => $token->id,
                        'name' => $token->name,
                        'description' => $token->description,
                        'abilities' => $token->abilities,
                        'created_at' => $token->created_at->format('Y-m-d H:i:s'),
                        'expires_at' => $token->expires_at?->format('Y-m-d H:i:s'),
                        'last_used_at' => $token->last_used_at?->format('Y-m-d H:i:s'),
                        'is_active' => $token->is_active,
                        'status' => $token->status,
                    ],
                    'user_info' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'status' => $user->status ?? 'active',
                        'roles' => $user->roles->pluck('name')->toArray(),
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

            $bearerToken = $request->bearerToken();
            if ($bearerToken) {
                PersonalAccessToken::where('plain_text_token', $bearerToken)
                    ->update(['last_used_at' => now()]);
            }

            $statistics = [
                'total_invoices' => Invoice::where('user_id', $user->id)->count(),
                'total_amount' => Invoice::where('user_id', $user->id)->sum('total'),
                'paid_invoices' => Invoice::where('user_id', $user->id)->where('status', 'paid')->count(),
                'pending_invoices' => Invoice::where('user_id', $user->id)->whereIn('status', ['draft', 'sent'])->count(),
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
