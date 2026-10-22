<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $table = 'users';

    protected $primaryKey = 'user_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'user_name',
        'phone_no',
        'user_addr',
        'user_profile_pic',
        'date_of_birth',
        'user_type',
        'user_cart_quantity',
        'userpayment_confirmation',
        'user_wallet_amount',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'userpayment_confirmation' => 'boolean',
            'user_wallet_amount' => 'decimal:2',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'user_id', 'user_id');
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class, 'user_id', 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    /**
     * Recalculates and persists user_cart_quantity to the users table.
     */
    public function updateCartQuantity(): int
    {
        $total = (int) $this->cartItems()->sum('quantity');
        $this->user_cart_quantity = $total;
        $this->save();

        return $total;
    }

    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    public function isSeller()
    {
        return $this->user_type === 'seller';
    }

    public function isBuyer()
    {
        return $this->user_type === 'buyer';
    }

    /**
     * Backward compatibility accessor for renamed column.
     */
    public function getUserpaymentConfirmantionAttribute(): ?bool
    {
        return $this->userpayment_confirmation;
    }
}
