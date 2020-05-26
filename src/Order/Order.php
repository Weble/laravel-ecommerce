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

    public $incrementing = false;
    protected $keyType = 'uuid';

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

        static::updating(function (Order $order) {

            $order->generateUniqueHash();
        });
    }

    public static function fromCart(Cart $cart): OrderBuilder
    {
        return (new OrderBuilder())->fromCart($cart);
    }

    public function items(): HasMany
    {
        return $this->hasMany(config('ecommerce.classes.orderItemModel', OrderItem::class));
    }

    public function user(): BelongsTo
    {
        $this->belongsTo(Authenticatable::class);
    }

    protected function generateUniqueHash()
    {
        if ($this->hash) {
            return;
        }

        $hash = $this->generateHash();
        while (self::whereHash($hash)->count() > 0) {
            $hash = $this->generateHash();
        }

        $this->hash = $hash;
    }

    protected function generateHash(): string
    {
        return Str::random(config('ecommerce.order.hash_length', 8));
    }
}
