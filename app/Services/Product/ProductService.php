<?php

namespace App\Services\Product;

use App\Actions\Product\UploadProductImagesAction;
use App\DTOs\Product\ProductDTO;
use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductService
{
    private array $relations = ['category', 'primaryImage', 'galleryImages'];

    public function __construct(
        private UploadProductImagesAction $uploadImagesAction
    ) {}

    public function getAll(int $perPage = 10)
    {
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
    }

    public function getPublicProducts(int $perPage = 10)
    {
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
    }

    public function createProduct(ProductDTO $dto)
    {

        return DB::transaction(function () use ($dto) {

            $product = Product::create([
                'category_id' => $dto->category_id,
                'name' => $dto->name,
                'description' => $dto->description,
                'price' => $dto->price,
                'sale_price' => $dto->sale_price,
                'stock' => $dto->stock,
                'sku' => $dto->sku,
                'status' => $dto->status,
            ]);

            if ($dto->primaryImage) {
                $this->uploadImagesAction->uploadPrimary($product, $dto->primaryImage);
            }

            if ($dto->galleryImages) {
                $this->uploadImagesAction->uploadGallery($product, $dto->galleryImages);
            }

            return $product->load($this->relations);
        });

    }

    public function updateProduct(Product $product, ProductDTO $dto)
    {
        return DB::transaction(function () use ($product, $dto) {

            $data = array_filter([
                'category_id' => $dto->category_id,
                'name' => $dto->name,
                'description' => $dto->description,
                'price' => $dto->price,
                'sale_price' => $dto->sale_price,
                'stock' => $dto->stock,
                'sku' => $dto->sku,
                'status' => $dto->status,
            ], fn ($value) => ! is_null($value));

            $product->update($data);

            if ($dto->primaryImage) {
                $this->uploadImagesAction->uploadPrimary($product, $dto->primaryImage);
            }

            if ($dto->galleryImages) {
                $this->uploadImagesAction->uploadGallery($product, $dto->galleryImages);
            }

            return $product->refresh()->load($this->relations);
        });
    }

    public function deleteProduct(Product $product)
    {
        return $product->delete();
    }

    public function trashedProducts()
    {
        return Product::onlyTrashed()
            ->with($this->relations)
            ->paginate(10);
    }

    public function restoreProduct($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        return $product->restore();
    }

    public function forceDeleteProduct($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        return $product->forceDelete();
    }
}
