<?php

namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use Cknow\Money\MoneyIntCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;
use Weble\LaravelEcommerce\Discount\Discount;
use Weble\LaravelEcommerce\Discount\DiscountCollection;
use Weble\LaravelEcommerce\Purchasable;
use Weble\LaravelEcommerce\Storage\StoresEcommerceData;
use Weble\LaravelEcommerce\Tests\mocks\User;

/**
 * @method Builder forCurrentUser()
 *
 * @property string $cart_item_id
 * @property Money $price
 * @property Collection $product_attributes
 * @property Collection $discounts
 * @property float $quantity
 * @property User|\App\Models\User|null $user
 * @property Purchasable|Model|null $product
 */
class CartItemModel extends Model implements StoresEcommerceData
{
    protected $guarded = [];

    protected $casts = [
        'price'              => MoneyIntCast::class,
        'product_attributes' => 'collection',
        'discounts'          => 'collection',
        'quantity'           => 'float',
    ];

    /**
     * @var mixed|string
     */
    protected $cartKey;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.items', 'cart_items'));
    }

    public function user(): BelongsTo
    {
        $this->belongsTo(config('ecommerce.classes.user', '\\App\\Models\\User'));
    }

    public function product(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'purchasable_type', 'purchasable_id');
    }

    public function getDiscountsAttribute($discounts): DiscountCollection
    {
        $discounts = $this->castAttribute('discounts', $discounts);

        return DiscountCollection::make($discounts->map(function ($discount) {
            return Discount::fromArray($discount);
        }));
    }

    public function toCartValue(): DataTransferObject
    {
        return new CartItem([
            'price'      => $this->price,
            'attributes' => $this->product_attributes,
            'discounts'  => DiscountCollection::make($this->discounts),
            'product'    => $this->product,
            'quantity'   => $this->quantity,
        ]);
    }

    /**
     * @param CartItem $cartItem
     * @param string $key
     * @param string $instanceName
     * @return self|Model
     */
    public function fromCartValue($cartItem, string $key, string $instanceName): self
    {
        $data = $this->cartItemData($cartItem, $instanceName);

        try {
            $cartItemModel = $this
                ->forCurrentUser()
                ->where('cart_item_id', '=', $cartItem->getId())
                ->firstOrFail()
                ->fill($data);
        } catch (ModelNotFoundException $e) {
            $cartItemModel = (new self($data));
        }

        return $cartItemModel
            ->product()
            ->associate($cartItem->product);
    }

    private function cartItemData(CartItem $cartItem, string $instanceName): array
    {
        return [
            'cart_item_id'       => $cartItem->getId(),
            'user_id'            => auth()->user() ? auth()->user()->getAuthIdentifier() : null,
            'session_id'         => session()->getId(),
            'instance'           => $instanceName,
            'price'              => $cartItem->price,
            'product_attributes' => $cartItem->attributes,
            'discounts'          => $cartItem->discounts->toArray(),
            'quantity'           => $cartItem->quantity,
        ];
    }

    public function scopeForCurrentUser(Builder $query): Builder
    {
        return $query->where(function (Builder $subQuery) {
            $subQuery->where('session_id', '=', session()->getId());

            if (auth()->user()) {
                $subQuery->orWhere('user_id', '=', auth()->user()->getAuthIdentifier());
            }

            return $subQuery;
        });
    }
}
