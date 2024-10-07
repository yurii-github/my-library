<?php declare(strict_types=1);

namespace Tests;

use App\Models\Category;

trait PopulateCategoriesTrait
{
    /**
     * @return Category[]
     */
    protected function populateCategories()
    {
        $categories = [];

        $category = new Category();
        $category->title = 'new category';
        $category->save();
        $categories[] = $category;

        $category = new Category();
        $category->title = 'new category 2';
        $category->save();
        $categories[] = $category;

        return $categories;
    }
}
