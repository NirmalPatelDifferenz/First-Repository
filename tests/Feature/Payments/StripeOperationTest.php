<?php

namespace Tests\Feature\Payments;

use App\Models\StripeOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StripeOperationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operation_is_created_with_a_uuid_and_persisted_lifecycle_fields()
    {
        $operation = StripeOperation::create([
            'stripe_product_create_key' => 'stripe-product-create-test-001',
            'stripe_price_create_key' => 'stripe-price-create-test-001',
            'operation_type' => StripeOperation::TYPE_CREATE_PRODUCT,
            'status' => StripeOperation::STATUS_PENDING,
            'payload' => [
                'name' => 'Test product',
                'unit_amount' => 1250,
                'currency' => 'usd',
            ],
        ]);

        $this->assertTrue(Str::isUuid($operation->operation_uuid));
        $this->assertSame(StripeOperation::STATUS_PENDING, $operation->status);
        $this->assertSame(1250, $operation->payload['unit_amount']);
        $this->assertSame(0, $operation->attempt_count);

        $this->assertDatabaseHas('stripe_operations', [
            'id' => $operation->id,
            'operation_uuid' => $operation->operation_uuid,
            'stripe_product_create_key' => 'stripe-product-create-test-001',
            'operation_type' => StripeOperation::TYPE_CREATE_PRODUCT,
            'status' => StripeOperation::STATUS_PENDING,
        ]);
    }
}
