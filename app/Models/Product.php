<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\SlugOptions;
use Spatie\Sluggable\HasSlug;
use App\Observers\ProductObserver;
#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'sku',
        'sale_price',
        'stock',
        'status',
    ];

    protected $casts = [
        'status' => ProductStatus::class,
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
    ];

     /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getIsInStockAttribute()
    {
        return $this->stock > 0;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function galleryImages()
    {
        return $this->hasMany(ProductImage::class)
            ->where('is_primary', false)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
             $q->where('name', 'LIKE', "%{$term}%")
                ->orWhere('description', 'LIKE', "%{$term}%");
        });

    }

    public function scopePriceBetween($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', ProductStatus::ACTIVE)
            ->where('stock', '>', 0);
    }

    public function scopeInStock($query, $value)
    {
        return $value ?
            $query->where('stock', '>', 0)
            : $query->where('stock', '=', 0);
    }
}
