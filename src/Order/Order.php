<?php

namespace Weble\LaravelEcommerce\Order;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Customer\Customer;
use Weble\LaravelEcommerce\Order\Concern\InteractsWithStateMachine;
use Weble\LaravelEcommerce\Order\Concern\Payable;
use Weble\LaravelEcommerce\Support\CurrencyCast;
use Weble\LaravelEcommerce\Support\DTOCast;
use Weble\LaravelEcommerce\Support\MoneyCast;

class Order extends Model
{
    use InteractsWithStateMachine, Payable;

    protected $guarded = [];

    protected $casts = [
        'customer'           => DTOCast::class . ':' . Customer::class,
        'currency'           => CurrencyCast::class,
        'discounts'          => 'collection',
        'discounts_subtotal' => MoneyCast::class . ':,currency',
        'items_subtotal'     => MoneyCast::class . ':,currency',
        'items_total'        => MoneyCast::class . ':,currency',
        'subtotal'           => MoneyCast::class . ':,currency',
        'tax'                => MoneyCast::class . ':,currency',
        'total'              => MoneyCast::class . ':,currency',
    ];

    protected $keyType = 'uuid';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.orders', 'orders'));
    }

    public static function fromCart(Cart $cart): Order
    {
        return (new static())
            ->fill([
                'id'                 => Str::orderedUuid(),
                'user_id'            => $cart->customer()->user ? $cart->customer()->user->id : null,
                'customer_id'        => $cart->customer()->getId() ?: null,
                'customer'           => $cart->customer(),
                'currency'           => $cart->total()->getMoney()->getCurrency()->getCode(),
                'discounts'          => $cart->discounts(),
                'discounts_subtotal' => $cart->discount(),
                'items_subtotal'     => $cart->itemsSubtotal(),
                'items_total'        => $cart->items(),
                'subtotal'           => $cart->subTotal(),
                'tax'                => $cart->tax(),
                'total'              => $cart->total(),
            ]);
    }

    public function items(): HasMany
    {
        return $this->hasMany(config('ecommerce.classes.orderItemModel', OrderItem::class));
    }

    public function user(): BelongsTo
    {
        $this->belongsTo(Authenticatable::class);
    }
}
