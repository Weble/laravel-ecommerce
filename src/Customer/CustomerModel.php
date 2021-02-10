<?php

namespace Weble\LaravelEcommerce\Customer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\DataTransferObject\DataTransferObject;
use Weble\LaravelEcommerce\Address\Address;
use Weble\LaravelEcommerce\Storage\StoresEcommerceData;
use Weble\LaravelEcommerce\Support\DTOCast;

/**
 * @property string $id
 * @property int $user_id
 * @property Address $billing_address
 * @property Address $shipping_address
 */
class CustomerModel extends Model implements StoresEcommerceData
{
    protected $guarded = [];

    protected $keyType   = 'string';
    public $incrementing = false;

    protected $casts = [
        'billing_address'  => DTOCast::class . ':' . Address::class,
        'shipping_address' => DTOCast::class . ':' . Address::class,
    ];

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
                    'user_id'          => $customer->user ? $customer->user->getKey() : null,
                    'shipping_address' => $customer->shippingAddress,
                    'billing_address'  => $customer->billingAddress,
                ]);
        } catch (ModelNotFoundException $e) {
            return (new self([
                'id'               => $customer->getId(),
                'user_id'          => $customer->user ? $customer->user->getKey() : null,
                'shipping_address' => $customer->shippingAddress,
                'billing_address'  => $customer->billingAddress,
            ]));
        }
    }

    public function toCartValue(): DataTransferObject
    {
        $userModel = config('ecommerce.classes.user', '\\App\\Models\\User');

        return new Customer([
            'id'              => $this->getKey(),
            'user'            => $this->user_id ? $userModel::find($this->user_id) : null,
            'shippingAddress' => $this->shipping_address,
            'billingAddress'  => $this->billing_address,
        ]);
    }
}
