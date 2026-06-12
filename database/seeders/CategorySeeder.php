<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Books' => ['Fiction', 'Non-Fiction', 'Academic', 'Children'],
            'Stationery' => ['Pens', 'Pencils', 'Notebooks', 'Erasers', 'Rulers'],
            'Bags' => ['School Bags', 'College Bags', 'Backpacks'],
        ];

        foreach ($data as $categoryName => $subcategories) {
            $category = Category::create(['name' => $categoryName]);

            foreach ($subcategories as $sub) {
                Subcategory::create([
                    'category_id' => $category->id,
                    'name' => $sub,
                ]);
            }
        }
    }
}
