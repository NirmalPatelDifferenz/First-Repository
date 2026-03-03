<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlans extends Model
{
    use HasFactory,SoftDeletes;
    protected $table="subscription_plans";
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'price',
        'description',
        'currency',
        'interval',
        'interval_count',
        'stripe_product_id',
        'stripe_price_id',
        'stripe_unit_amount',
        'stripe_product_type',
        'is_active'
    ];
    protected $casts = [
        'price' => 'decimal:2'
    ];
}
