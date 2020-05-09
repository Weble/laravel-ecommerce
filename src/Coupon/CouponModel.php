<?php

namespace Weble\LaravelEcommerce\Coupon;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CouponModel extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.coupon.table', 'coupons'));
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo('purchasable');
    }

    public function user(): BelongsTo
    {
        $this->belongsTo(Authenticatable::class);
    }
}
