<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            $table->string('name',100);
            $table->decimal('price',10,2);
            $table->text('description')->nullable();
            $table->string('currency')->default('USD');
            $table->string('interval')->nullable()->comment('month,year');
            $table->unsignedTinyInteger('interval_count')->default(1);
            
            // Stripe 
            $table->string('stripe_product_id')->nullable()->unique();
            $table->string('stripe_price_id')->nullable()->unique();
            $table->unsignedBigInteger('stripe_unit_amount')->nullable();
            $table->string('stripe_product_type')->nullable()->comment('recurring,one_time');

            $table->tinyInteger('is_active')->default(0)->comment('0=inactive, 1=active');

            $table->timestamps();
            $table->softDeletes();

            // index
            $table->index('is_active');
            $table->index('stripe_product_id');
            $table->index('stripe_price_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
}
