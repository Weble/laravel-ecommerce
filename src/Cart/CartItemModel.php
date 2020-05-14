<?php

namespace Weble\LaravelEcommerce\Cart;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\DataTransferObject\DataTransferObject;
use Weble\LaravelEcommerce\Storage\StoresEcommerceData;
use Weble\LaravelEcommerce\Support\MoneyCast;

class CartItemModel extends Model implements StoresEcommerceData
{
    protected $guarded = [];

    protected $casts = [
        'cart_key' => 'uuid',
        'price' => MoneyCast::class,
        'product_attributes' => 'collection',
        'quantity' => 'float',
    ];

    protected $keyType = 'uuid';

    /**
     * @var mixed|string
     */
    protected $cartKey;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.items', 'cart_items'));
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo('purchasable');
    }

    public function user(): BelongsTo
    {
        $this->belongsTo(Authenticatable::class);
    }

    public function product(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'purchasable_type', 'purchasable_id');
    }

    public function scopeWithCartKey(Builder $query, string $key): self
    {
        $query->where('cart_key', '=', $key);

        return $this;
    }

    /**
     * @param CartItem $cartItem
     * @param string $key
     * @param string $instanceName
     * @return StoresEcommerceData
     */
    public function fromCartValue($cartItem, string $key, string $instanceName): StoresEcommerceData
    {
        try {
            return self::where($this->getKeyName(), '=', $cartItem->getId())->firstOrFail()
                ->fill([
                    'id' => $cartItem->getId(),
                    'cart_key' => $key,
                    'instance' => $instanceName,
                    'price' => $cartItem->price,
                    'product_attributes' => $cartItem->attributes,
                    'quantity' => $cartItem->quantity,
                ])->product()->associate($cartItem->product);
        } catch (ModelNotFoundException $e) {
            return (new self([
                'id' => $cartItem->getId(),
                'cart_key' => $key,
                'instance' => $instanceName,
                'price' => $cartItem->price,
                'product_attributes' => $cartItem->attributes,
                'quantity' => $cartItem->quantity,
            ]))->product()->associate($cartItem->product);
        }
    }

    public function toCartValue(): DataTransferObject
    {
        return new CartItem([
            'price' => $this->price,
            'attributes' => $this->product_attributes,
            'product' => $this->product,
            'quantity' => $this->quantity,
        ]);
    }
}
