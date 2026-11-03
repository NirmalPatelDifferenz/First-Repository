<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_returns_correct_total_and_filtered_counts()
    {
        $this->actingAs(User::factory()->create());

        Product::create([
            'product_name' => 'Alpha',
            'product_description' => 'First item',
            'product_price' => 10,
            'product_quantity' => 2,
        ]);

        Product::create([
            'product_name' => 'Beta',
            'product_description' => 'Second item',
            'product_price' => 20,
            'product_quantity' => 3,
        ]);

        $this->getJson(route('product.getData', [
            'draw' => 3,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'Alpha'],
        ]))
            ->assertOk()
            ->assertJsonPath('draw', 3)
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_listing_excludes_soft_deleted_products()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::create([
            'product_name' => 'Archived',
            'product_description' => 'Hidden item',
            'product_price' => 10,
            'product_quantity' => 1,
        ]);
        $product->delete();

        $this->getJson(route('product.getData'))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 0);
    }
}
