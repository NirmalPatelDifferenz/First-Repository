<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStripeOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_operations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('operation_uuid')->unique();
            $table->string('stripe_product_create_key', 191)->nullable()->unique();
            $table->string('stripe_price_create_key', 191)->nullable()->unique();
            $table->string('stripe_price_deactivate_key', 191)->nullable()->unique();
            $table->string('stripe_product_deactivate_key', 191)->nullable()->unique();
            $table->string('operation_type', 32);
            $table->string('status', 32)->default('pending');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->string('last_error_code', 100)->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'operation_type']);
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stripe_operations');
    }
}
