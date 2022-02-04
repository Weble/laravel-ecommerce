<?php

namespace Weble\LaravelEcommerce\Order;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Weble\LaravelEcommerce\Cart\CartInterface;
use Weble\LaravelEcommerce\Customer\Customer;
use Weble\LaravelEcommerce\Order\Concern\Payable;
use Weble\LaravelEcommerce\Support\CurrencyCast;
use Weble\LaravelEcommerce\Support\DTOCast;
use Weble\LaravelEcommerce\Support\InteractsWithStateMachine;

class Order extends Model
{
    use InteractsWithStateMachine, Payable;

    protected $guarded = [];

    protected $casts = [
        'customer'           => DTOCast::class . ':' . Customer::class,
        'currency'           => CurrencyCast::class,
        'discounts'          => 'collection',
        'discounts_subtotal' => MoneyIntegerCast::class . ':currency',
        'items_subtotal'     => MoneyIntegerCast::class . ':currency',
        'items_total'        => MoneyIntegerCast::class . ':currency',
        'subtotal'           => MoneyIntegerCast::class . ':currency',
        'tax'                => MoneyIntegerCast::class . ':currency',
        'total'              => MoneyIntegerCast::class . ':currency',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.orders', 'orders'));
    }

    protected static function booted()
    {
        static::creating(function (Order $order) {
            $order->generateUniqueHash();
        });

        static::created(function (Order $order) {
            $order->createPayment();
        });

        static::updating(function (Order $order) {
            $order->generateUniqueHash();
        });
    }

    public static function fromCart(CartInterface $cart): OrderBuilder
    {
        return (new OrderBuilder())->fromCart($cart);
    }

    public function items(): HasMany
    {
        return $this->hasMany(config('ecommerce.classes.orderItemModel', OrderItem::class));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.classes.user'));
    }

    protected function generateUniqueHash(): self
    {
        if ($this->hash) {
            return $this;
        }

        $hash = $this->generateHash();
        while (self::query()->where('hash', '=', $hash)->count() > 0) {
            $hash = $this->generateHash();
        }

        $this->hash = $hash;

        return $this;
    }

    protected function generateHash(): string
    {
        return Str::random(config('ecommerce.order.hash_length', 8));
    }

    protected function getGraph(): string
    {
        return config('ecommerce.order.workflow.graph', 'ecommerce-order');
    }
}
