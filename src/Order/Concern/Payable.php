<?php

namespace Weble\LaravelEcommerce\Order\Concern;

use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Omnipay;
use Weble\LaravelEcommerce\Order\Order;
use Weble\LaravelEcommerce\Order\PaymentData;

/**
 * @mixin Order
 */
trait Payable
{
    public function paymentGateway(): GatewayInterface
    {
        return Omnipay::create($this->payment_gateway);
    }

    public function pay(): ResponseInterface
    {
        $this->apply('readyForPayment');
        $this->save();

        return $this->paymentGateway()->purchase([
            'amount'    => $this->total->getAmount() / 100,
            'currency'  => $this->currency->getCode(),
            'card'      => PaymentData::fromCustomer($this->customer),
            'returnUrl' => '/',
            'cancelUrl' => '/',
        ])->send();
    }
}
