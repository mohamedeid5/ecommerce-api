<?php

namespace App\Queries;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductQuery
{
    private const CACHE_TTL = 3600;
    private const CACHE_TAG = 'products';

    private array $relations = ['category', 'primaryImage', 'galleryImages'];

    public function getAll(int $perPage = 10)
    {
        $cacheKey = $this->buildListCacheKey('products:admin:list', $perPage);

        return Cache::tags([self::CACHE_TAG])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function() use ($perPage) {
                return QueryBuilder::for(Product::class)
                    ->with($this->relations)
                    ->allowedFilters(
                        'name',
                        AllowedFilter::exact('status'),
                        AllowedFilter::exact('category_id'),
                        AllowedFilter::scope('price_between'),
                        AllowedFilter::scope('in_stock'),
                        AllowedFilter::scope('search'),
                        AllowedFilter::callback('trashed', function ($query, $value) {
                            if ($value === 'with') {
                                $query->withTrashed();
                            }
                            if ($value === 'only') {
                                $query->onlyTrashed();
                            }
                        }),
                    )
                    ->allowedSorts('name', 'price', 'created_at')
                    ->defaultSort('-created_at')
                    ->paginate($perPage);
        });
    }

    public function getPublicProducts(int $perPage)
    {
        $cacheKey = $this->buildListCacheKey('products:public:list', $perPage);

        return Cache::tags([self::CACHE_TAG])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($perPage) {
                return QueryBuilder::for(Product::class)
                ->where('status', ProductStatus::ACTIVE->value)
                ->with($this->relations)
                ->allowedFilters(
                    'name',
                    AllowedFilter::exact('category_id'),
                    AllowedFilter::scope('price_between'),
                    AllowedFilter::scope('in_stock'),
                    AllowedFilter::scope('search'),
                )
                ->allowedSorts('name', 'price', 'created_at')
                ->defaultSort('-created_at')
                ->paginate($perPage);
            });
    }

    private function buildListCacheKey(string $prefix, int $perPage): string
    {
        $queryParams = request()->query();

        $this->sortRecursive($queryParams);

        $hash = md5(json_encode($queryParams) . ":{$perPage}");

        return "{$prefix}:{$hash}";
    }

    private function sortRecursive(array &$array): void
    {
        foreach($array as &$value) {
            if(is_array($value)) {
                $this->sortRecursive($value);
            }
        }
        ksort($array);
    }
}
