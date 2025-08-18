<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Products\CreateProductRequest;
use App\Http\Requests\Api\Products\UpdateProductRequest;
use App\Http\Resources\Api\ProductResource;
use App\Http\Resources\Api\ProductCollection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view', Product::class);

        $query = Product::query();

        // Search by name, sku, or description
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by stock availability
        if ($request->has('stock_status')) {
            $stockStatus = $request->get('stock_status');
            if ($stockStatus === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($stockStatus === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            } elseif ($stockStatus === 'low_stock') {
                $query->where('stock', '>', 0)->where('stock', '<=', 10);
            }
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }

        // Include soft deleted records if requested
        if ($request->boolean('include_deleted')) {
            Gate::authorize('viewDeleted', Product::class);
            $query->withTrashed();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Productos obtenidos exitosamente',
            'data' => new ProductCollection($products),
            'meta' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem()
            ]
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        Gate::authorize('create', Product::class);

        $product = Product::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'data' => new ProductResource($product)
        ], 201);
    }

    /**
     * Display the specified product
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        Gate::authorize('view', $product);

        return response()->json([
            'success' => true,
            'message' => 'Producto obtenido exitosamente',
            'data' => new ProductResource($product)
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        Gate::authorize('update', $product);

        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'data' => new ProductResource($product->fresh())
        ]);
    }

    /**
     * Remove the specified product (soft delete)
     */
    public function destroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        Gate::authorize('delete', $product);

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado exitosamente'
        ]);
    }

    /**
     * Restore a soft deleted product
     */
    public function restore(string $id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        Gate::authorize('restore', $product);

        $product->restore();

        return response()->json([
            'success' => true,
            'message' => 'Producto restaurado exitosamente',
            'data' => new ProductResource($product->fresh())
        ]);
    }

    /**
     * Update product stock
     */
    public function updateStock(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
            'operation' => 'required|in:set,add,subtract'
        ]);

        $product = Product::findOrFail($id);
        Gate::authorize('updateStock', $product);

        $oldStock = $product->stock;
        $newStock = $request->get('stock');
        $operation = $request->get('operation');

        switch ($operation) {
            case 'set':
                $product->stock = $newStock;
                break;
            case 'add':
                $product->stock += $newStock;
                break;
            case 'subtract':
                $product->stock = max(0, $product->stock - $newStock);
                break;
        }

        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Stock actualizado exitosamente',
            'data' => [
                'product' => new ProductResource($product),
                'stock_change' => [
                    'previous_stock' => $oldStock,
                    'current_stock' => $product->stock,
                    'operation' => $operation,
                    'amount' => $newStock
                ]
            ]
        ]);
    }

    /**
     * Bulk update products
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.stock' => 'sometimes|integer|min:0',
            'products.*.price' => 'sometimes|numeric|min:0'
        ]);

        Gate::authorize('bulkUpdate', Product::class);

        $updatedProducts = [];

        foreach ($request->get('products') as $productData) {
            $product = Product::findOrFail($productData['id']);

            if (isset($productData['stock'])) {
                $product->stock = $productData['stock'];
            }

            if (isset($productData['price'])) {
                $product->price = $productData['price'];
            }

            $product->save();
            $updatedProducts[] = new ProductResource($product);
        }

        return response()->json([
            'success' => true,
            'message' => 'Productos actualizados exitosamente',
            'data' => $updatedProducts
        ]);
    }

    /**
     * Get low stock products
     */
    public function lowStock(Request $request): JsonResponse
    {
        Gate::authorize('viewLowStock', Product::class);

        $threshold = $request->get('threshold', 10);

        $products = Product::where('stock', '>', 0)
            ->where('stock', '<=', $threshold)
            ->orderBy('stock', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Productos con stock bajo obtenidos exitosamente',
            'data' => ProductResource::collection($products),
            'threshold' => $threshold
        ]);
    }
}
