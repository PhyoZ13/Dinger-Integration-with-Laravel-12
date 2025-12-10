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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_id')->unique(); // Unique order identifier
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending'); // pending, processing, completed, cancelled, failed
            $table->string('payment_status')->default('pending'); // pending, success, failed, cancelled
            $table->string('dinger_transaction_id')->nullable();
            $table->string('dinger_provider_name')->nullable(); // AYA Pay, OK$, etc.
            $table->string('dinger_method_name')->nullable(); // QR, PIN, OTP, etc.
            $table->timestamp('payment_completed_at')->nullable();
            $table->timestamp('payment_failed_at')->nullable();
            $table->text('payment_failure_reason')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
