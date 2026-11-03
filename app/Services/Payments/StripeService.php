<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeService
{
    protected $client;

    public function __construct()
    {
        $secret = config('services.stripe.secret');

        if (empty($secret)) {
            throw new RuntimeException('Stripe is not configured. Set STRIPE_SECRET_KEY.');
        }

        $this->client = Http::asForm()->withToken($secret);
    }

    public function createProduct(array $attributes, string $imageUrl): string
    {
        $response = $this->client->post('https://api.stripe.com/v1/products', [
            'name' => $attributes['name'],
            'description' => $attributes['description'],
            'images' => [$imageUrl],
        ]);

        $this->ensureSuccessful($response->json('error.message') ?: 'Unable to create the Stripe product.', $response->successful());
        $id = $response->json('id');

        if (empty($id)) {
            throw new RuntimeException('Stripe did not return a product ID.');
        }

        return $id;
    }

    public function createPrice(string $productId, int $unitAmount): string
    {
        $response = $this->client->post('https://api.stripe.com/v1/prices', [
            'unit_amount' => $unitAmount,
            'currency' => 'usd',
            'product' => $productId,
        ]);

        $this->ensureSuccessful($response->json('error.message') ?: 'Unable to create the Stripe price.', $response->successful());
        $id = $response->json('id');

        if (empty($id)) {
            throw new RuntimeException('Stripe did not return a price ID.');
        }

        return $id;
    }

    public function deactivatePrice(string $priceId): void
    {
        $response = $this->client->post('https://api.stripe.com/v1/prices/'.$priceId, [
            'active' => 'false',
        ]);

        $this->ensureSuccessful($response->json('error.message') ?: 'Unable to deactivate the Stripe price.', $response->successful());
    }

    public function deactivateProduct(string $productId): void
    {
        $response = $this->client->post('https://api.stripe.com/v1/products/'.$productId, [
            'active' => 'false',
        ]);

        $this->ensureSuccessful($response->json('error.message') ?: 'Unable to archive the Stripe product.', $response->successful());
    }

    protected function ensureSuccessful(string $message, bool $successful): void
    {
        if (!$successful) {
            throw new RuntimeException($message);
        }
    }
}
