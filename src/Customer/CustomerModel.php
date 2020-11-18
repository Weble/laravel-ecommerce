<?php

namespace Weble\LaravelEcommerce\Customer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;
use Spatie\DataTransferObject\DataTransferObject;
use Weble\LaravelEcommerce\Storage\StoresEcommerceData;

class CustomerModel extends Model implements StoresEcommerceData
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.customers', 'cart_customers'));
    }

    /*    public function user(): BelongsTo
        {
            $this->belongsTo(config('auth.providers.users.model', User::class));
        }*/

    public function scopeWithCartKey(Builder $query, string $key): self
    {
        $query->where('id', '=', $key);

        return $this;
    }

    /**
     * @param Customer $customer
     * @param string $key
     * @param string $instanceName
     * @return StoresEcommerceData
     */
    public function fromCartValue($customer, string $key, string $instanceName): StoresEcommerceData
    {
        try {
            return self::where($this->getKeyName(), '=', $customer->getId())->firstOrFail()
                ->fill([
                    'shipping_address' => json_encode($customer->shippingAddress),
                    'billing_address'  => json_encode($customer->billingAddress),
                ]);
        } catch (ModelNotFoundException $e) {
            return (new self([
                'id'               => $key,
                'shipping_address' => json_encode($customer->shippingAddress),
                'billing_address'  => json_encode($customer->billingAddress),
            ]));
        }
    }

    public function toCartValue(): DataTransferObject
    {
        return new Customer([
            'id'              => $this->getKey(),
            'shippingAddress' => json_decode($this->shipping_address, true),
            'billingAddress'  => json_decode($this->billing_address, true),
        ]);
    }
}
