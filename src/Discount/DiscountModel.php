<?php

namespace Weble\LaravelEcommerce\Discount;

use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Money\Currency;
use Spatie\DataTransferObject\DataTransferObject;
use Weble\LaravelEcommerce\Storage\StoresDifferentInstances;
use Weble\LaravelEcommerce\Storage\StoresEcommerceData;
use Weble\LaravelEcommerce\Support\CurrencyCast;
use Weble\LaravelEcommerce\Support\DTOCast;

/**
 * @property DiscountType $type
 * @property DiscountTarget $target
 * @property Currency $currency
 * @property Money $value
 * @property Collection $discount_attributes
 * @method DiscountModel forCurrentUser()
 */
class DiscountModel extends Model implements StoresEcommerceData, StoresDifferentInstances
{
    protected $guarded = [];

    protected $casts = [
        'target'              => DiscountTarget::class,
        'type'                => DiscountType::class,
        'currency'            => CurrencyCast::class,
        'discount_attributes' => 'collection',
    ];

    public function getId(): string
    {
        return $this->getKey();
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.discounts', 'discounts'));
    }

    public function getValueAttribute($value)
    {
        if ($this->type === DiscountType::Value) {
            return new Money($value, $this->currency);
        }

        return $value;
    }

    public function toCartValue(): DataTransferObject
    {
        return new Discount([
            'type'       => $this->type,
            'target'     => $this->target,
            'value'      => $this->value,
            'attributes' => $this->discount_attributes,
        ]);
    }

    /**
     * @param Discount $discount
     * @param string $key
     * @param string $instanceName
     * @return self|Model
     */
    public function fromCartValue($discount, string $key, string $instanceName): self
    {
        $data = $this->discountData($discount, $instanceName);

        try {
            $discountModel = self::query()
                ->where('discount_id', '=', $discount->id)
                ->firstOrFail()
                ->fill($data);
        } catch (ModelNotFoundException $e) {
            $discountModel = (new self($data));
        }

        return $discountModel;
    }

    private function discountData(Discount $discount, string $instanceName): array
    {
        return [
            'discount_id'         => $discount->id,
            'user_id'             => auth()->user() ? auth()->user()->getAuthIdentifier() : null,
            'session_id'          => session()->getId(),
            'instance'            => $instanceName,
            'value'               => $discount->value instanceof Money ? $discount->value->getAmount() : $discount->value,
            'currency'            => $discount->value instanceof Money ? $discount->value->getCurrency() : null,
            'type'                => $discount->type->value,
            'target'              => $discount->target->value,
            'discount_attributes' => $discount->attributes->toArray(),
        ];
    }

    public function scopeForCurrentUser(Builder $query): Builder
    {
        return $query->where(function (Builder $subQuery) {
            if (auth()->user()) {
                return $subQuery->orWhere('user_id', '=', auth()->user()->getAuthIdentifier());
            }

            return $subQuery->where('session_id', '=', session()->getId());
        });
    }

    public function scopeForInstance(Builder $query, string $instanceName): Builder
    {
        return $query->where('instance', $instanceName);
    }
}
