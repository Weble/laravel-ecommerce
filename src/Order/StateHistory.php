<?php

namespace Weble\LaravelEcommerce\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User;

class StateHistory extends Model
{
    protected $guarded = [];

    protected $casts = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.state_history', 'ecommerce_state_history'));
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function statable()
    {
        return $this->model();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
