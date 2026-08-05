<?php

namespace App\Actions\Category;

use App\Models\Category;

class GetCategoriesAction
{
    public function execute($perPage = 10)
    {
        return Category::with(['parent', 'products'])
            ->latest()
            ->get();
    }
}
