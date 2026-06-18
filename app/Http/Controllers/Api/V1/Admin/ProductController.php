<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Product\ProductDTO;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Queries\ProductQuery;
use App\Services\Product\ProductService;
use Illuminate\Support\Facades\Gate;

class ProductController extends BaseApiController
{

    public function __construct(
        protected ProductService $productService,
        protected ProductQuery $productQuery,
    )
    {}

    public function index()
    {
        Gate::authorize('viewAny', Product::class);

        $products = $this->productQuery->getAll(10);

        return $this->successResponse(
            ProductResource::collection($products),
            'Products retrieved successfully'
        );
    }

    public function store(StoreProductRequest $request)
    {
        Gate::authorize('create', Product::class);

        $dto = ProductDTO::fromRequest($request);

        $product = $this->productService->createProduct($dto);

        return $this->createdResponse(
            new ProductResource($product),
            'Product created successfully',
            201
        );
    }

    public function show(Product $product)
    {
        Gate::authorize('view', Product::class);

        $product->load(['category', 'primaryImage', 'galleryImages']);

        return $this->successResponse(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        Gate::authorize('update', Product::class);

        $dto = ProductDTO::fromRequest($request);

        $product = $this->productService->updateProduct($product, $dto);

        return $this->successResponse(
            new ProductResource($product),
            'Product updated successfully',
        );
    }

    public function destroy(Product $product)
    {
        Gate::authorize('delete', Product::class);

        $this->productService->deleteProduct($product);

        return $this->successResponse(
            null,
            'Product deleted successfully'
        );
    }

    public function trashed()
    {
        Gate::authorize('view', Product::class);

        $products = $this->productService->trashedProducts();

        return $this->successResponse(
            ProductResource::collection($products),
            'Trashed Proucts retrieved successfully'
        );
    }

    public function restore($id)
    {
        Gate::authorize('delete', Product::class);

        $this->productService->restoreProduct($id);

        return $this->successResponse(
            null,
            'Product Restored Sucessfully'
        );
    }

    public function forceDelete($id)
    {
        Gate::authorize('delete', Product::class);

        $this->productService->forceDeleteProduct($id);

        return $this->successResponse(
            null,
            'Product Permanently Deleted Sucessfully'
        );
    }
}
