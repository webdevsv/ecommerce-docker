<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     title="Payment",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="stripe_payment_intent_id", type="string", example="pi_3Nk..."),
 *     @OA\Property(property="stripe_client_secret", type="string", example="pi_3Nk..._secret_..."),
 *     @OA\Property(property="amount", type="number", format="float", example=59.97),
 *     @OA\Property(property="currency", type="string", example="usd"),
 *     @OA\Property(property="status", type="string", enum={"requires_payment","processing","succeeded","failed","cancelled"}, example="requires_payment"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'stripe_payment_intent_id',
        'stripe_client_secret',
        'amount',
        'currency',
        'status',
        'raw_response',
    ];

    /**
     * Campos que no se exponen en las respuestas JSON de la API.
     * El client_secret se entrega solo al crear la orden (data.client_secret).
     */
    protected $hidden = [
        'stripe_client_secret',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'raw_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}