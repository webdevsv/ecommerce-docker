<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_payment_intent_id')->unique();
            $table->string('stripe_client_secret')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('usd');
            $table->enum('status', ['requires_payment', 'processing', 'succeeded', 'failed', 'cancelled'])
                ->default('requires_payment');
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};