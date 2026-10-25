<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: "/products",
        tags: ["Productos"],
        summary: "Listar catalogo de productos (acceso publico)",
        parameters: [
            new OA\Parameter(name: "search", in: "query", description: "Buscar por nombre", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "per_page", in: "query", description: "Resultados por pagina (entre 1 y 50)", schema: new OA\Schema(type: "integer", default: 15, minimum: 1, maximum: 50)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Listado paginado de productos activos"),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->where('is_active', true);

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Limita per_page entre 1 y 50 para evitar consultas gigantes
        $perPage = min(max($request->integer('per_page', 15), 1), 50);

        $products = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    #[OA\Post(
        path: "/products",
        tags: ["Productos"],
        summary: "Crear un nuevo producto (requiere administrador)",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 201, description: "Producto creado"),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 422, description: "Error de validacion"),
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente.',
            'data' => $product,
        ], 201);
    }

    #[OA\Get(
        path: "/products/{id}",
        tags: ["Productos"],
        summary: "Obtener el detalle de un producto",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Detalle del producto"),
            new OA\Response(response: 404, description: "Producto no encontrado"),
        ]
    )]
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    #[OA\Put(
        path: "/products/{id}",
        tags: ["Productos"],
        summary: "Actualizar un producto (requiere administrador)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Producto actualizado"),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 404, description: "Producto no encontrado"),
            new OA\Response(response: 422, description: "Error de validacion"),
        ]
    )]
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente.',
            'data' => $product->fresh(),
        ]);
    }

    #[OA\Delete(
        path: "/products/{id}",
        tags: ["Productos"],
        summary: "Eliminar un producto (requiere administrador)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Producto eliminado"),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 404, description: "Producto no encontrado"),
        ]
    )]
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
                        'message' => 'Producto eliminado exitosamente.',
        ]);
    }
}