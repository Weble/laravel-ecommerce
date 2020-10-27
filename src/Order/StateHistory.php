<?php

namespace Weble\LaravelEcommerce\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;
use Weble\LaravelEcommerce\Support\HasUuidPrimaryKey;

class StateHistory extends Model
{
    use HasUuidPrimaryKey;

    protected $guarded = [];

    protected $casts = [];

    public $incrementing = false;
    protected $keyType   = 'string';

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
