<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_name',
        'product_description',
        'product_price',
        'product_quantity',
        'product_image',
        'product_status',
        'stripe_product_id',
        'stripe_price_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
