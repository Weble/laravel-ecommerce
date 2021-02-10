<?php

namespace Weble\LaravelEcommerce\Tests\Cart;

use Illuminate\Foundation\Testing\WithFaker;
use Weble\LaravelEcommerce\Address\Address;
use Weble\LaravelEcommerce\Address\AddressType;
use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Customer\Customer;
use Weble\LaravelEcommerce\Customer\CustomerModel;
use Weble\LaravelEcommerce\Tests\TestCase;

class CustomerTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     */
    public function can_add_to_cart_storing_in_db()
    {
        config()->set('ecommerce.cart.instances.cart.storage', 'eloquent');

        $name = $this->faker->firstName;

        $surname = $this->faker->lastName;

        $customer = new Customer([
            'billingAddress' => new Address([
                'type'    => AddressType::billing(),
                'name'    => $name,
                'surname' => $surname,
            ]),
        ]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->forCustomer($customer);

        /** @var CustomerModel $storedCustomer */
        $storedCustomer = CustomerModel::query()->latest()->first();
        $this->assertEquals($customer->id, $storedCustomer->getKey());
        $this->assertEquals($name, $storedCustomer->billing_address->name);
        $this->assertEquals($surname, $storedCustomer->billing_address->surname);
    }
}
