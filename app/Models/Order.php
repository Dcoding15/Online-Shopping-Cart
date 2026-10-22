<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'orders';

    protected $primaryKey = 'order_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'order_total_amount',
        'order_status',
        'payment_confirmed',
        'shipping_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'order_total_amount' => 'decimal:2',
            'payment_confirmed' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Buyer who placed the order.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Items included in this order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }
}
