<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use App\Models\CategoryProduct;
use App\Models\Product;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Product::factory(1000)->create();

        $faker = Faker::create();
        $imageUrl = $faker->imageUrl(640,480, null, false);

        foreach($products as $product){
            $product->addMediaFromUrl($imageUrl)->toMediaCollection('products');
            $number_select_categories = rand(1,7);
            for($i = 1; $i < $number_select_categories ; $i++){
                $number_of_categories = rand(1,20);
                CategoryProduct::create([
                    'product_id'    => $product->id,
                    'category_id'   => $number_of_categories
                ]);
            }
            
        }

    }
}
