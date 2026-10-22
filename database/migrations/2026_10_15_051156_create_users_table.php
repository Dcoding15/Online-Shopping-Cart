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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');					// user id (primary key)
            $table->string('user_name', 100)->nullable();				// user name
            $table->string('phone_no', 150)->unique();				// user phone number
            $table->string('user_addr')->nullable();				// user address
            $table->binary('user_profile_pic')->nullable();				// user profile picture
            $table->date('date_of_birth')->nullable();				// user date of birth to find age
            $table->enum('user_type', ['admin', 'seller', 'buyer'])->default('buyer');	// user type has option of admin, seller, buyer
            $table->integer('user_cart_quantity')->default(0);				// how many product user added to its cart
            $table->boolean('userpayment_confirmantion')->default(false);		// If customer paid for its own cart then false if customer still not paid
            $table->decimal('user_wallet_amount', 12, 2)->default(0);		// user wallet amount
            $table->string('password');						// user password

            $table->rememberToken();						// "Remember Me" persistant authentication sessions.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
