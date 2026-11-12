<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['userpayment_confirmation', 'user_wallet_amount']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_provider', 32)->nullable();
            $table->string('payment_method', 32)->nullable();
            $table->string('razorpay_order_id', 100)->nullable()->unique();
            $table->string('razorpay_payment_id', 100)->nullable()->unique();
            $table->string('razorpay_refund_id', 100)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['razorpay_order_id']);
            $table->dropUnique(['razorpay_payment_id']);
            $table->dropUnique(['razorpay_refund_id']);
            $table->dropColumn([
                'payment_provider',
                'payment_method',
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_refund_id',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('userpayment_confirmation')->default(false);
            $table->decimal('user_wallet_amount', 12, 2)->default(0);
        });
    }
};
