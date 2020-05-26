<?php


namespace Weble\LaravelEcommerce\Cart\Event;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Weble\LaravelEcommerce\Cart\CartItem;

abstract class CartItemEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CartItem $cartItem;
    public ?string $instance;

    public function __construct(CartItem $cartItem, ?string $instance = null)
    {
        $this->cartItem = $cartItem;
        $this->instance = $instance;
    }
}
