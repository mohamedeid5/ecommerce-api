<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Actions\Category\CreateCategoryAction;
use App\Actions\Category\DeleteCategoryAction;
use App\Actions\Category\GetCategoriesAction;
use App\Actions\Category\UpdateCategoryAction;
use App\DTOs\Category\CategoryDTO;
use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends BaseApiController
{
    public function index(GetCategoriesAction $action)
    {
        $categories = $action->execute();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    public function store(StoreCategoryRequest $request, CreateCategoryAction $action)
    {
        $dto = CategoryDTO::fromRequest($request);

        $category = $action->execute($dto);

        return $this->createdResponse(
            new CategoryResource($category),
            'Category created successfully'
        );
    }

    public function show(Category $category)
    {
        return $this->successResponse(
            new CategoryResource($category),
            'Category retrieved seccessfully'
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategoryAction $action)
    {
        $dto = CategoryDTO::fromRequest($request);
        $category = $action->execute($category, $dto);

        return $this->successResponse(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    public function destroy(Category $category, DeleteCategoryAction $action)
    {
        try {
            $action->execute($category);
            return $this->successResponse(
                null,
                'Category deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
            );
        }
    }
}
