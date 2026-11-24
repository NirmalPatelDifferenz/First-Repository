<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;

class StripeOperation extends Model
{
    public const TYPE_CREATE_PRODUCT = 'create_product';
    public const TYPE_DELETE_PRODUCT = 'delete_product';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED = 'failed';
    public const STATUS_RECONCILIATION_REQUIRED = 'reconciliation_required';

    protected $fillable = [
        'operation_uuid',
        'stripe_product_create_key',
        'stripe_price_create_key',
        'stripe_price_deactivate_key',
        'stripe_product_deactivate_key',
        'operation_type',
        'status',
        'product_id',
        'stripe_product_id',
        'stripe_price_id',
        'payload',
        'attempt_count',
        'last_error_code',
        'last_error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempt_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (StripeOperation $operation) {
            if (empty($operation->operation_uuid)) {
                $operation->operation_uuid = (string) Str::uuid();
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Claim a pending operation or explicitly retry a known, safe failure.
     */
    public function markProcessing()
    {
        return $this->transition(
            [self::STATUS_PENDING, self::STATUS_FAILED],
            self::STATUS_PROCESSING,
            function (StripeOperation $operation) {
                $operation->attempt_count++;
                $operation->started_at = now();
                $operation->completed_at = null;
                $operation->last_error_code = null;
                $operation->last_error_message = null;
            }
        );
    }

    public function markSucceeded(array $stripeResourceIds = [])
    {
        return $this->transition(
            [self::STATUS_PROCESSING],
            self::STATUS_SUCCEEDED,
            function (StripeOperation $operation) use ($stripeResourceIds) {
                if (array_key_exists('product', $stripeResourceIds)) {
                    $operation->stripe_product_id = $stripeResourceIds['product'];
                }

                if (array_key_exists('price', $stripeResourceIds)) {
                    $operation->stripe_price_id = $stripeResourceIds['price'];
                }

                $operation->completed_at = now();
                $operation->last_error_code = null;
                $operation->last_error_message = null;
            }
        );
    }

    public function markFailed(string $errorCode, string $errorMessage)
    {
        return $this->transition(
            [self::STATUS_PROCESSING],
            self::STATUS_FAILED,
            function (StripeOperation $operation) use ($errorCode, $errorMessage) {
                $operation->last_error_code = $errorCode;
                $operation->last_error_message = $errorMessage;
                $operation->completed_at = now();
            }
        );
    }

    public function markReconciliationRequired(string $errorCode, string $errorMessage)
    {
        return $this->transition(
            [self::STATUS_PROCESSING],
            self::STATUS_RECONCILIATION_REQUIRED,
            function (StripeOperation $operation) use ($errorCode, $errorMessage) {
                $operation->last_error_code = $errorCode;
                $operation->last_error_message = $errorMessage;
                $operation->completed_at = now();
            }
        );
    }

    protected function transition(array $allowedCurrentStatuses, string $nextStatus, callable $applyChanges)
    {
        DB::transaction(function () use ($allowedCurrentStatuses, $nextStatus, $applyChanges) {
            $operation = static::query()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($operation->status, $allowedCurrentStatuses, true)) {
                throw new LogicException(
                    'Cannot transition Stripe operation from '.$operation->status.' to '.$nextStatus.'.'
                );
            }

            $operation->status = $nextStatus;
            $applyChanges($operation);
            $operation->save();

            $this->setRawAttributes($operation->getAttributes(), true);
        });

        return $this;
    }
}
