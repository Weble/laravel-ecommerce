<?php

namespace Weble\LaravelEcommerce\Order;

use Cknow\Money\MoneyIntCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Weble\LaravelEcommerce\Cart\CartItem;

class OrderItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'product_attributes' => 'collection',
        'discounts'          => 'collection',
        'quantity'           => 'float',
        'unit_price'         => MoneyIntCast::class,
        'discounts_subtotal' => MoneyIntCast::class,
        'subtotal'           => MoneyIntCast::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.order_items', 'order_items'));
    }

    public static function fromCartItem(CartItem $cartItem): OrderItemBuilder
    {
        return (new OrderItemBuilder())->fromCartItem($cartItem);
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
