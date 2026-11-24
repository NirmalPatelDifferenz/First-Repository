<?php

namespace Tests\Feature\Payments;

use App\Models\StripeOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

class StripeOperationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operation_is_created_with_a_uuid_and_persisted_lifecycle_fields()
    {
        $operation = $this->createPendingOperation();

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

    public function test_operation_can_move_from_pending_to_processing_to_succeeded()
    {
        $operation = $this->createPendingOperation();

        $operation->markProcessing();

        $this->assertSame(StripeOperation::STATUS_PROCESSING, $operation->status);
        $this->assertSame(1, $operation->attempt_count);
        $this->assertNotNull($operation->started_at);

        $operation->markSucceeded([
            'product' => 'prod_lifecycle_123',
            'price' => 'price_lifecycle_123',
        ]);

        $this->assertSame(StripeOperation::STATUS_SUCCEEDED, $operation->status);
        $this->assertSame('prod_lifecycle_123', $operation->stripe_product_id);
        $this->assertSame('price_lifecycle_123', $operation->stripe_price_id);
        $this->assertNotNull($operation->completed_at);
    }

    public function test_operation_can_be_retried_after_a_known_failure()
    {
        $operation = $this->createPendingOperation();

        $operation->markProcessing();
        $operation->markFailed('stripe_unavailable', 'Stripe returned a temporary error.');

        $this->assertSame(StripeOperation::STATUS_FAILED, $operation->status);
        $this->assertSame('stripe_unavailable', $operation->last_error_code);
        $this->assertNotNull($operation->completed_at);

        $operation->markProcessing();

        $this->assertSame(StripeOperation::STATUS_PROCESSING, $operation->status);
        $this->assertSame(2, $operation->attempt_count);
        $this->assertNull($operation->last_error_code);
        $this->assertNull($operation->completed_at);
    }

    public function test_ambiguous_operation_requires_reconciliation_and_cannot_be_retried_directly()
    {
        $operation = $this->createPendingOperation();

        $operation->markProcessing();
        $operation->markReconciliationRequired(
            'stripe_outcome_unknown',
            'The Stripe request outcome could not be confirmed.'
        );

        $this->assertSame(StripeOperation::STATUS_RECONCILIATION_REQUIRED, $operation->status);

        $this->expectException(LogicException::class);
        $operation->markProcessing();
    }

    public function test_operation_cannot_be_completed_without_first_being_claimed()
    {
        $operation = $this->createPendingOperation();

        $this->expectException(LogicException::class);
        $operation->markSucceeded();
    }

    protected function createPendingOperation()
    {
        return StripeOperation::create([
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
    }
}
