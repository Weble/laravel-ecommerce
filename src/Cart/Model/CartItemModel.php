<?php

namespace Weble\LaravelEcommerce\Cart\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Weble\LaravelEcommerce\Cart\CartItem;
use Weble\LaravelEcommerce\Support\MoneyCast;

class CartItemModel extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => MoneyCast::class,
        'attributes' => 'collection',
        'quantity' => 'float',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.cart.table', 'cart_items'));
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo('purchasable');
    }

    public function user(): BelongsTo
    {
        $this->belongsTo(Authenticatable::class);
    }

    public static function fromCartItem(CartItem $cartItem): self
    {
        try {
            return self::findOrfail($cartItem->getId())
                ->fill([
                    'id' => $cartItem->getId(),
                    'price' => $cartItem->price,
                    'attributes' => $cartItem->attributes,
                    'quantity' => $cartItem->quantity,
                ])->product()->associate($cartItem->product);
        } catch (ModelNotFoundException $e) {
            return (new self([
                'id' => $cartItem->getId(),
                'price' => $cartItem->price,
                'attributes' => $cartItem->attributes,
                'quantity' => $cartItem->quantity,
            ]))->product()->associate($cartItem->product);
        }
    }

    public function toCartItem(): CartItem
    {
        return new CartItem([
            'price' => $this->price,
            'attributes' => collect($this->attributes),
            'product' => $this->product,
            'quantity' => $this->quantity,
        ]);
    }

    public function product(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'purchasable_type', 'purchasable_id');
    }
}
