<?php

namespace Weble\LaravelEcommerce\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Weble\LaravelEcommerce\Cart\CartItem;

class OrderItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'product_attributes' => 'collection',
        'discounts' => 'collection',
        'quantity' => 'float',
    ];

    protected $keyType = 'uuid';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.order_items', 'order_items'));
    }

    public static function fromCartItem(CartItem $cartItem): self
    {
        return (new static())->fill([
            'id' => Str::uuid(),
            'quantity' => $cartItem->quantity,
            'product_attributes' => $cartItem->attributes,
            'purchasable_data' => $cartItem->product->toJson(),
            'discounts' => $cartItem->discounts,
            'unit_price' => $cartItem->unitPrice(),
            'discounts_subtotal' => $cartItem->discount(),
            'subtotal' => $cartItem->subTotal(),
        ])
            ->product()
            ->associate($cartItem->product);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.classes.orderModel', Order::class));
    }

    public function product(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'purchasable_type', 'purchasable_id');
    }
}
