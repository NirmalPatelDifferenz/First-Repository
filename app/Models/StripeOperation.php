<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'idempotency_key',
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
}
