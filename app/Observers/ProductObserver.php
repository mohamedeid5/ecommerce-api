<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProductObserver
{
    public function created(Product $product): void
    {
        $this->clearProductCache($product);
    }

    public function updated(Product $product): void
    {
        $this->clearProductCache($product);
    }

    public function deleted(Product $product): void
    {
        $this->clearProductCache($product);
    }

    public function restored(Product $product): void
    {
        $this->clearProductCache($product);
    }

    public function forceDeleted(Product $product): void
    {
        Storage::disk('public')->deleteDirectory("products/{$product->id}");
    }

    private function clearProductCache(Product $product): void
    {
        Cache::tags(['products'])->flush();
    }
}
