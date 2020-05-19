<?php

namespace Weble\LaravelEcommerce\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderHistory extends Model
{
    protected $guarded = [];

    protected $casts = [];

    protected $keyType = 'uuid';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.order_history', 'order_history'));

        $this->fill([
            'id' => Str::orderedUuid(),
        ]);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.classes.orderModel', Order::class));
    }

    public function statable()
    {
        return $this->order();
    }
}
