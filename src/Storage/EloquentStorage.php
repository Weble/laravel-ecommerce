<?php

namespace Weble\LaravelEcommerce\Storage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EloquentStorage implements StorageInterface
{
    protected array $modelClasses = [

    ];

    protected StorageInterface $fallbackStorage;
    protected string $modelKey;
    protected string $instanceName;

    public function __construct(array $config = [])
    {
        $models = $config['models'] ?? [];
        foreach ($models as $key => $class) {
            $this->modelClasses[$key] = $class;
        }

        $this->fallbackStorage = storageManager()->store($config['fallback'] ?? 'session');

        $sessionKey = $config['session_key'] ?? 'ecommerce.store.eloquent.';
        $modelKey = session()->get($sessionKey);
        if (! $modelKey) {
            $modelKey = Str::uuid();
            session()->put($sessionKey, $modelKey);
        }

        $this->modelKey = $modelKey;
    }

    public function setInstanceName(string $name): StorageInterface
    {
        $this->instanceName = $name;

        return $this;
    }

    public function set(string $key, $value): StorageInterface
    {
        if (! $this->hasModelFor($key)) {
            return $this->fallbackStorage->set($key, $value);
        }

        if ($value instanceof Collection) {
            $model = $this->modelFor($key);
            $oldKeys = $model->get()->pluck($model->getKeyName());
            $newKeys = $value->pluck($model->getKeyName());
            $keysToDelete = $oldKeys->except($newKeys);

            if ($keysToDelete->count() > 0) {
                $this->modelFor($key)->whereIn($model->getKeyName(), $keysToDelete->toArray())->delete();
            }

            $value->each(function ($item) use ($key) {
                $this->modelFor($key)->fromCartValue($item, $key, $this->instanceName)->save();
            });

            return $this;
        }

        $this->modelFor($key)->fromCartValue($value, $key, $this->instanceName)->save();

        return $this;
    }

    public function get(string $key, $default = null)
    {
        if (! $this->hasModelFor($key)) {
            return $this->fallbackStorage->get($key, $default);
        }

        return $this->modelFor($key)->get()->map(function ($model) {
            return $model->toCartValue();
        });
    }

    public function has(string $key): bool
    {
        if (! $this->hasModelFor($key)) {
            return $this->fallbackStorage->has($key);
        }

        return $this->modelFor($key)->count() > 0;
    }

    public function remove(string $key): StorageInterface
    {
        if (! $this->hasModelFor($key)) {
            return $this->fallbackStorage->remove($key);
        }

        $this->modelFor($key)->delete();

        return $this;
    }

    protected function hasModelFor(string $key) : bool
    {
        return isset($this->modelClasses[str_replace("cart.", "", $key)]);
    }

    protected function modelFor(string $key): Model
    {
        return (new $this->modelClasses[str_replace("cart.", "", $key)])->withCartKey($this->modelKey);
    }
}
