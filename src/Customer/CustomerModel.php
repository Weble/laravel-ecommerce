<?php

namespace Weble\LaravelEcommerce\Customer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\LaravelData\Data;
use Weble\LaravelEcommerce\Address\Address;
use Weble\LaravelEcommerce\Storage\StoresEcommerceData;

/**
 * @property string $id
 * @property int $user_id
 * @property Address $billing_address
 * @property Address $shipping_address
 */
class CustomerModel extends Model implements StoresEcommerceData
{
    protected $guarded = [];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'billing_address'  => Address::class,
        'shipping_address' => Address::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.customers', 'cart_customers'));
    }

    public function user(): BelongsTo
    {
        $this->belongsTo(config('ecommerce.classes.user', '\\App\\Models\\User'));
    }

    public function toCartValue(): Data
    {
        $userModel = config('ecommerce.classes.user', '\\App\\Models\\User');

        return new Customer(
            id: $this->getKey(),
            user: $this->user_id ? $userModel::find($this->user_id) : null,
            billingAddress: $this->billing_address,
            shippingAddress: $this->shipping_address,
        );
    }

    /**
     * @param Customer $customer
     * @param string $key
     * @param string $instanceName
     * @return StoresEcommerceData
     */
    public function fromCartValue($customer, string $key, string $instanceName): StoresEcommerceData
    {
        if ($customer->user) {
            try {
                return self::query()
                    ->where('user_id', '=', $customer->user->getKey())
                    ->firstOrFail()
                    ->fill([
                        'shipping_address' => $customer->shippingAddress,
                        'billing_address'  => $customer->billingAddress,
                    ]);
            } catch (ModelNotFoundException $e) {
            }
        }

        return $this->loadOrCreateFromCustomerId($customer);
    }

    private function loadOrCreateFromCustomerId(Customer $customer): self
    {
        try {
            return self::query()
                ->where($this->getKeyName(), '=', $customer->getId())
                ->firstOrFail()
                ->fill([
                    'session_id'       => session()->getId(),
                    'user_id'          => $customer->user ? $customer->user->getKey() : null,
                    'shipping_address' => $customer->shippingAddress,
                    'billing_address'  => $customer->billingAddress,
                ]);
        } catch (ModelNotFoundException $e) {
            return (new self([
                'id'               => $customer->getId(),
                'session_id'       => session()->getId(),
                'user_id'          => $customer->user ? $customer->user->getKey() : null,
                'shipping_address' => $customer->shippingAddress,
                'billing_address'  => $customer->billingAddress,
            ]));
        }
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
