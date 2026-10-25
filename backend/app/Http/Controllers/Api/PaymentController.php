<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function __construct(protected StripeService $stripe)
    {
    }

    #[OA\Get(
        path: "/payments/{order}/status",
        tags: ["Pagos"],
        summary: "Consultar el estado del pago de una orden directamente en Stripe",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "order", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Estado actual del pago"),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 404, description: "Orden o pago no encontrado"),
        ]
    )]
    public function status(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id && ! $request->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para ver este pago.',
            ], 403);
        }

        $payment = $order->payment;

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'No existe un pago asociado a esta orden.',
            ], 404);
        }

        try {
            $intent = $this->stripe->retrievePaymentIntent($payment->stripe_payment_intent_id);
            $payment->update([
                'status' => $intent->status === 'succeeded' ? 'succeeded' : $payment->status,
                'raw_response' => $intent->toArray(),
            ]);

            if ($intent->status === 'succeeded' && $order->status !== 'paid') {
                $order->update(['status' => 'paid']);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo sincronizar el estado con Stripe: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'data' => $payment->fresh(),
        ]);
    }

    #[OA\Post(
        path: "/payments/webhook",
        tags: ["Pagos"],
        summary: "Webhook de Stripe para eventos de pago",
        responses: [
            new OA\Response(response: 200, description: "Evento procesado"),
            new OA\Response(response: 400, description: "Firma del webhook invalida"),
        ]
    )]
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException|\UnexpectedValueException $e) {
            Log::warning('Webhook de Stripe invalido: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Firma invalida.'], 400);
        }

        $intent = $event->data->object ?? null;

        if ($intent && isset($intent->id)) {
            $payment = Payment::where('stripe_payment_intent_id', $intent->id)->first();

            if ($payment) {
                match ($event->type) {
                    'payment_intent.succeeded' => tap($payment)->update(['status' => 'succeeded'])
                        ->order()->update(['status' => 'paid']),
                    'payment_intent.payment_failed' => tap($payment)->update(['status' => 'failed'])
                        ->order()->update(['status' => 'failed']),
                    default => null,
                };
            }
        }

        return response()->json(['success' => true]);
    }
}