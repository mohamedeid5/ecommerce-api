<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProductStatus;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Queries\ProductQuery;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends BaseApiController
{
    public function __construct(
        private ProductService $productService,
        protected ProductQuery $productQuery
    ) {}

    public function index(): JsonResponse
    {
        $products = $this->productQuery->getPublicProducts(10);

        return $this->successResponse(
            ProductResource::collection($products),
            'Products retrieved successfully'
        );
    }

    public function show(Product $product): JsonResponse
    {
        abort_unless($product->status === ProductStatus::ACTIVE, 404);

        $product->load(['category', 'primaryImage', 'galleryImages']);

        return $this->successResponse(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }
}
