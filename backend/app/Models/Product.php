<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Camiseta Deportiva"),
 *     @OA\Property(property="slug", type="string", example="camiseta-deportiva"),
 *     @OA\Property(property="description", type="string", example="Camiseta transpirable talla M", nullable=true),
 *     @OA\Property(property="price", type="number", format="float", example=19.99),
 *     @OA\Property(property="stock", type="integer", example=100),
 *     @OA\Property(property="sku", type="string", example="SKU-0001"),
 *     @OA\Property(property="image_url", type="string", example="https://example.com/img.jpg", nullable=true),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ProductRequest",
 *     type="object",
 *     required={"name", "price", "stock", "sku"},
 *     @OA\Property(property="name", type="string", example="Camiseta Deportiva"),
 *     @OA\Property(property="description", type="string", example="Camiseta transpirable talla M"),
 *     @OA\Property(property="price", type="number", format="float", example=19.99),
 *     @OA\Property(property="stock", type="integer", example=100),
 *     @OA\Property(property="sku", type="string", example="SKU-0001"),
 *     @OA\Property(property="image_url", type="string", example="https://example.com/img.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=true)
 * )
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'sku',
        'image_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name).'-'.Str::random(6);
            }
        });
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}