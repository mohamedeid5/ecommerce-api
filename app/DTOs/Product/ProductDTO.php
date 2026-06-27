<?php

namespace App\DTOs\Product;

use App\Enums\ProductStatus;
use Illuminate\Http\UploadedFile;

class ProductDTO
{
    public function __construct(
        public readonly ?int $category_id,
        public readonly ?string $name,
        public readonly ?string $description,
        public readonly ?float $price,
        public readonly ?float $sale_price,
        public readonly ?int $stock,
        public readonly ?string $sku,
        public readonly ?ProductStatus $status,
        public readonly ?UploadedFile $primaryImage = null,
        public readonly ?array $galleryImages = null,

    ){}

    public static function fromRequest($request): self
    {
        return new self(
            category_id: $request->validated('category_id'),
            name: $request->validated('name'),
            description: $request->validated('description'),
            price: $request->validated('price'),
            sale_price: $request->validated('sale_price'),
            stock: $request->validated('stock'),
            sku: $request->validated('sku'),
            status: $request->validated('status'),
            primaryImage: ProductStatus::tryFrom($request->validated('primary_image')),
            galleryImages: $request->validated('gallery_images'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'sku' => $this->sku,
            'category_id' => $this->category_id,
            'status' => $this->status,
        ];
    }
}
