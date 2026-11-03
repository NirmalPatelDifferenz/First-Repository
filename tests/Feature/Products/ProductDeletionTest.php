<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_is_soft_deleted_after_stripe_deactivation()
    {
        $this->actingAs(User::factory()->create());
        config(['services.stripe.secret' => 'sk_test_fake']);
        Storage::fake('public');

        $product = Product::create([
            'product_name' => 'Delete me',
            'product_description' => 'Delete test',
            'product_price' => 5,
            'product_quantity' => 1,
            'product_image' => 'delete-me.jpg',
            'stripe_product_id' => 'prod_delete_123',
            'stripe_price_id' => 'price_delete_123',
        ]);
        Storage::disk('public')->put('images/delete-me.jpg', 'image');

        Http::fake([
            'https://api.stripe.com/v1/prices/price_delete_123' => Http::response(['id' => 'price_delete_123', 'active' => false], 200),
            'https://api.stripe.com/v1/products/prod_delete_123' => Http::response(['id' => 'prod_delete_123', 'active' => false], 200),
        ]);

        $this->postJson(route('product.delete'), ['id' => $product->id])
            ->assertOk()
            ->assertJsonPath('flag', true);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing('images/delete-me.jpg');
    }

    public function test_stripe_failure_keeps_product_active_locally()
    {
        $this->actingAs(User::factory()->create());
        config(['services.stripe.secret' => 'sk_test_fake']);

        $product = Product::create([
            'product_name' => 'Keep me',
            'product_description' => 'Failure test',
            'product_price' => 5,
            'product_quantity' => 1,
            'stripe_product_id' => 'prod_keep_123',
            'stripe_price_id' => 'price_keep_123',
        ]);

        Http::fake([
            'https://api.stripe.com/v1/prices/price_keep_123' => Http::response([
                'error' => ['message' => 'Stripe unavailable'],
            ], 500),
        ]);

        $this->postJson(route('product.delete'), ['id' => $product->id])
            ->assertStatus(502)
            ->assertJsonPath('flag', false);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);
    }
}
