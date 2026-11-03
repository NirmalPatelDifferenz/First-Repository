<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        Storage::fake('public');
        config(['services.stripe.secret' => 'sk_test_fake']);
        config(['app.url' => 'https://example.test']);
    }

    public function test_valid_product_is_created_with_stripe_product_and_price()
    {
        Http::fake([
            'https://api.stripe.com/v1/products' => Http::response(['id' => 'prod_test_123'], 200),
            'https://api.stripe.com/v1/prices' => Http::response(['id' => 'price_test_123'], 200),
        ]);

        $this->postJson(route('product.store'), [
            'productName' => 'Test product',
            'productPrice' => '12.50',
            'productQuantity' => '4',
            'productDescription' => 'A test product',
            'productImage' => UploadedFile::fake()->image('product.jpg'),
        ])
            ->assertOk()
            ->assertJsonPath('flag', true);

        $this->assertDatabaseHas('products', [
            'product_name' => 'Test product',
            'product_price' => '12.50',
            'stripe_product_id' => 'prod_test_123',
            'stripe_price_id' => 'price_test_123',
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.stripe.com/v1/prices'
                && $request['unit_amount'] == 1250
                && $request['currency'] === 'usd';
        });
    }

    public function test_invalid_product_input_is_rejected_before_calling_stripe()
    {
        Http::fake();

        $this->postJson(route('product.store'), [
            'productName' => '',
            'productPrice' => '-1',
            'productQuantity' => 'not-a-number',
            'productDescription' => '',
        ])->assertStatus(422);

        Http::assertNothingSent();
        $this->assertSame(0, Product::count());
    }

    public function test_stripe_price_failure_does_not_create_local_product()
    {
        Http::fake([
            'https://api.stripe.com/v1/products' => Http::response(['id' => 'prod_partial_123'], 200),
            'https://api.stripe.com/v1/prices' => Http::response([
                'error' => ['message' => 'Price creation failed'],
            ], 400),
            'https://api.stripe.com/v1/products/prod_partial_123' => Http::response(['id' => 'prod_partial_123', 'active' => false], 200),
        ]);

        $this->postJson(route('product.store'), [
            'productName' => 'Partial product',
            'productPrice' => '9.99',
            'productQuantity' => '1',
            'productDescription' => 'Stripe failure test',
            'productImage' => UploadedFile::fake()->image('product.jpg'),
        ])
            ->assertStatus(502)
            ->assertJsonPath('flag', false);

        $this->assertSame(0, Product::count());
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.stripe.com/v1/products/prod_partial_123'
                && $request['active'] === 'false';
        });
    }
}
