<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     title="Order",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="total_amount", type="number", format="float", example=59.97),
 *     @OA\Property(property="status", type="string", enum={"pending","paid","failed","cancelled"}, example="pending"),
 *     @OA\Property(property="currency", type="string", example="usd"),
 *     @OA\Property(property="shipping_address", type="string", example="Col. Escalón, San Salvador", nullable=true),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OrderItem")),
 *     @OA\Property(property="payment", ref="#/components/schemas/Payment"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="OrderRequest",
 *     type="object",
 *     required={"items"},
 *     @OA\Property(property="shipping_address", type="string", example="Col. Escalón, San Salvador"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             required={"product_id", "quantity"},
 *             @OA\Property(property="product_id", type="integer", example=1),
 *             @OA\Property(property="quantity", type="integer", example=2)
 *         )
 *     )
 * )
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'currency',
        'shipping_address',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}