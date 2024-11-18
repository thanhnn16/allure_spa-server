<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasTranslations;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="Product",
 *     required={"name", "price", "category_id", "quantity"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="price", type="number", format="float"),
 *     @OA\Property(property="category_id", type="integer"),
 *     @OA\Property(property="quantity", type="integer"),
 *     @OA\Property(property="brand_description", type="string", nullable=true),
 *     @OA\Property(property="usage", type="string", nullable=true),
 *     @OA\Property(property="benefits", type="string", nullable=true),
 *     @OA\Property(property="key_ingredients", type="string", nullable=true),
 *     @OA\Property(property="ingredients", type="string", nullable=true),
 *     @OA\Property(property="directions", type="string", nullable=true),
 *     @OA\Property(property="storage_instructions", type="string", nullable=true),
 *     @OA\Property(property="product_notes", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="media",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Media")
 *     ),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class Product extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'name',
        'price',
        'category_id',
        'quantity',
        'brand_description',
        'usage',
        'benefits',
        'key_ingredients',
        'ingredients',
        'directions',
        'storage_instructions',
        'product_notes'
    ];

    protected $morphClass = 'product';

    protected $appends = ['media', 'rating_summary', 'is_favorite', 'translations_array'];

    protected $translatable = [
        'name',
        'brand_description',
        'usage',
        'benefits',
        'key_ingredients',
        'ingredients',
        'directions',
        'storage_instructions',
        'product_notes'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function priceHistory()
    {
        return $this->hasMany(ProductPriceHistory::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot('attribute_value');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'item_id')
            ->where('favorite_type', 'product');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'item', 'rating_type', 'item_id');
    }

    public function approvedRatings()
    {
        return $this->ratings()
            ->where('status', 'approved');
    }

    public function getRatingSummaryAttribute()
    {
        $approvedRatings = $this->ratings()
            ->where('status', 'approved');

        return [
            'average_rating' => round($this->average_rating, 1) ?? 0,
            'total_ratings' => $this->total_ratings ?? 0,
            'rating_distribution' => [
                5 => (clone $approvedRatings)->where('stars', 5)->count(),
                4 => (clone $approvedRatings)->where('stars', 4)->count(),
                3 => (clone $approvedRatings)->where('stars', 3)->count(),
                2 => (clone $approvedRatings)->where('stars', 2)->count(),
                1 => (clone $approvedRatings)->where('stars', 1)->count(),
            ]
        ];
    }

    public function getIsFavoriteAttribute()
    {
        if (!Auth::check()) {
            return false;
        }

        $userId = Auth::id();
        
        // Add logging
        Log::info('Checking is_favorite:', [
            'user_id' => $userId,
            'favorites_loaded' => $this->relationLoaded('favorites'),
            'favorites_count_exists' => isset($this->favorites_count),
            'favorites' => $this->relationLoaded('favorites') ? $this->favorites->toArray() : null
        ]);

        if ($this->relationLoaded('favorites')) {
            return $this->favorites
                ->where('user_id', $userId)
                ->where('favorite_type', 'product')
                ->isNotEmpty();
        }

        if (isset($this->favorites_count)) {
            return $this->favorites_count > 0;
        }

        return $this->favorites()
            ->where('user_id', $userId)
            ->where('favorite_type', 'product')
            ->exists();
    }

    /**
     * Check if product has sufficient stock
     */
    public function hasStock(int $quantity): bool
    {
        return $this->quantity >= $quantity;
    }

    /**
     * Get latest stock movement
     */
    public function latestStockMovement()
    {
        return $this->stockMovements()->latest()->first();
    }

    /**
     * Get stock movements between dates
     */
    public function getStockMovementsBetween($startDate, $endDate)
    {
        return $this->stockMovements()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    public function getCurrentStock(): int
    {
        return $this->quantity;
    }

    public function getStockMovementHistory()
    {
        return $this->stockMovements()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLastStockMovement()
    {
        return $this->stockMovements()
            ->latest()
            ->first();
    }

    public function getStockHistory($startDate = null, $endDate = null)
    {
        $query = $this->stockMovements()
            ->select([
                'created_at',
                'type',
                'quantity',
                'stock_after_movement',
                'note'
            ]);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->orderBy('created_at')->get();
    }

    public function getMediaAttribute()
    {
        return $this->media()->get();
    }

    public function validateStockConsistency()
    {
        $lastMovement = $this->stockMovements()
            ->latest()
            ->first();

        if ($lastMovement) {
            return $this->quantity === $lastMovement->stock_after_movement;
        }

        return $this->quantity === 0;
    }

    /**
     * Get the attributes that can be translated.
     *
     * @return array
     */
    public function getTranslatableAttributes(): array
    {
        return $this->translatable ?? [];
    }

    public function translations()
    {
        return $this->morphMany(\App\Models\Translation::class, 'translatable');
    }

    public function getTranslationsArrayAttribute()
    {
        return $this->attributes['translations_array'] ?? [];
    }
}
