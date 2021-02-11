<?php

namespace Weble\LaravelEcommerce\Discount;

use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\DataTransferObject\DataTransferObject;
use Weble\LaravelEcommerce\Storage\StoresEcommerceData;
use Weble\LaravelEcommerce\Support\CurrencyCast;
use Weble\LaravelEcommerce\Support\DTOCast;

class DiscountModel extends Model implements StoresEcommerceData
{
    protected $guarded = [];

    protected $casts = [
        'target'   => DTOCast::class . ':' . DiscountTarget::class,
        'type'     => DTOCast::class . ':' . DiscountType::class,
        'currency' => CurrencyCast::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.discounts', 'discounts'));
    }

    protected function getValueAttribute($value)
    {
        if (DiscountType::value()->equals($this->type)) {
            return new Money($value, $this->currency);
        }

        return $value;
    }

    public function toCartValue(): DataTransferObject
    {
        return new Discount([
            'type'   => $this->type,
            'target' => $this->target,
            'value'  => $this->value,
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
                ->where($this->getKeyName(), '=', $discount->id)
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
            'id'         => $discount->id,
            'user_id'    => auth()->user() ? auth()->user()->getAuthIdentifier() : null,
            'session_id' => session()->getId(),
            'instance'   => $instanceName,
            'value'      => $discount->value->getAmount(),
            'currency'   => $discount->value->getCurrency(),
            'type'       => $discount->type->value,
            'target'     => $discount->target->value,
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
