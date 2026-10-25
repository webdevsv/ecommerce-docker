<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;
use Throwable;

class OrderController extends Controller
{
    public function __construct(protected StripeService $stripe)
    {
    }

    #[OA\Get(
        path: "/orders",
        tags: ["Ordenes"],
        summary: "Historial de compras del cliente autenticado",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Listado de ordenes del usuario"),
            new OA\Response(response: 401, description: "No autenticado"),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    #[OA\Post(
        path: "/orders",
        tags: ["Ordenes"],
        summary: "Crear una orden de compra y generar el intento de pago en Stripe",
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["items"],
                properties: [
                    new OA\Property(property: "shipping_address", type: "string", example: "Colonia Escalon, San Salvador"),
                    new OA\Property(
                        property: "items",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "product_id", type: "integer", example: 1),
                                new OA\Property(property: "quantity", type: "integer", example: 2),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Orden creada. Incluye el client_secret de Stripe"),
            new OA\Response(response: 422, description: "Error de validacion o stock insuficiente"),
            new OA\Response(response: 402, description: "Error al procesar el pago con Stripe"),
        ]
    )]
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $order = DB::transaction(function () use ($validated, $request) {
                $total = 0;
                $itemsToCreate = [];

                foreach ($validated['items'] as $item) {
                    $product = Product::where('id', $item['product_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (! $product->is_active) {
                        abort(422, "El producto '{$product->name}' ya no esta disponible.");
                    }

                    if ($product->stock < $item['quantity']) {
                        abort(422, "Stock insuficiente para el producto '{$product->name}'. Disponible: {$product->stock}.");
                    }

                    $subtotal = $product->price * $item['quantity'];
                    $total += $subtotal;

                    $itemsToCreate[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                        'subtotal' => $subtotal,
                    ];
                }

                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'total_amount' => $total,
                    'status' => 'pending',
                    'currency' => config('services.stripe.currency', 'usd'),
                    'shipping_address' => $validated['shipping_address'] ?? null,
                ]);

                foreach ($itemsToCreate as $data) {
                    $order->items()->create([
                        'product_id' => $data['product']->id,
                        'quantity' => $data['quantity'],
                        'unit_price' => $data['unit_price'],
                        'subtotal' => $data['subtotal'],
                    ]);

                    $data['product']->decrement('stock', $data['quantity']);
                }

                return $order;
            });
        } catch (Throwable $e) {
            if (method_exists($e, 'getStatusCode') && $e->getStatusCode() === 422) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            Log::error('Error creando la orden: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error al crear la orden.',
            ], 500);
        }

        try {
            $intent = $this->stripe->createPaymentIntent(
                (float) $order->total_amount,
                $order->currency,
                ['order_id' => $order->id, 'user_id' => $order->user_id]
            );

            Payment::create([
                'order_id' => $order->id,
                'stripe_payment_intent_id' => $intent->id,
                'stripe_client_secret' => $intent->client_secret,
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'status' => 'requires_payment',
                'raw_response' => $intent->toArray(),
            ]);
        } catch (Throwable $e) {
            Log::error('Error al generar el pago: '.$e->getMessage());
            $this->releaseStock($order);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el intento de pago con Stripe.',
            ], 402);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden creada exitosamente. Complete el pago usando el client_secret proporcionado.',
            'data' => [
                'order' => $order->load(['items.product', 'payment']),
                'client_secret' => $order->payment->stripe_client_secret,
            ],
        ], 201);
    }

    #[OA\Get(
        path: "/orders/{id}",
        tags: ["Ordenes"],
        summary: "Consultar el detalle de una orden propia",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Detalle de la orden"),
            new OA\Response(response: 403, description: "No autorizado para ver esta orden"),
            new OA\Response(response: 404, description: "Orden no encontrada"),
        ]
    )]
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id && ! $request->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para ver esta orden.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $order->load(['items.product', 'payment']),
        ]);
    }

    /**
     * Devuelve el stock de una orden y la marca como fallida.
     * Es idempotente: si la orden ya no esta pendiente, no hace nada.
     */
    private function releaseStock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $order || $order->status !== 'pending') {
                return;
            }

            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)
                    ->increment('stock', $item->quantity);
            }

            $order->update(['status' => 'failed']);
        });
    }
}