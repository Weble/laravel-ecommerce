<?php

namespace Weble\LaravelEcommerce\Storage;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Cart\CartItem;

class EloquentStorage implements StorageInterface
{
    protected array $modelClasses = [];
    protected StorageInterface $fallbackStorage;
    protected string $instanceName;

    public function __construct(array $config = [], array $modelClasses = [])
    {
        $this->modelClasses    = $modelClasses;
        $this->fallbackStorage = storageManager()->store($config['fallback'] ?? 'session');
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
            $model   = $this->modelFor($key);
            $oldKeys = $this->modelQueryFor($key)
                ->get()
                ->pluck($model->getKeyName());
            $newKeys      = $value->pluck($model->getKeyName());
            $keysToDelete = $oldKeys->except($newKeys);

            if ($keysToDelete->count() > 0) {
                $this
                    ->modelQueryFor($key)
                    ->whereIn($model->getKeyName(), $keysToDelete->toArray())
                    ->delete();
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

        if ($key !== StorageType::Customer->value) {
            $items = $this
                ->modelQueryFor($key)
                ->get();

            if ($items->count() <= 0) {
                return $default;
            }

            return $items->mapWithKeys(function (Model|StoresEcommerceData $model) {
                /** @var CartItem $cartItem */
                $cartItem = $model->toCartValue();
                return [$cartItem->getId() => $cartItem];
            });
        }

        try {
            return $this
                ->modelQueryFor($key)
                ->firstOrFail()
                ->toCartValue();
        } catch (ModelNotFoundException $e) {
            return $default;
        }
    }

    public function has(string $key): bool
    {
        if (! $this->hasModelFor($key)) {
            return $this->fallbackStorage->has($key);
        }

        return $this->modelQueryFor($key)->count() > 0;
    }

    public function remove(string $key): StorageInterface
    {
        if (! $this->hasModelFor($key)) {
            return $this->fallbackStorage->remove($key);
        }

        if ($key !== StorageType::Customer->value) {
            $items = $this->modelQueryFor($key)->get();

            if ($items->count() <= 0) {
                return $this->fallbackStorage->remove($key);
            }

            $items->each->delete();

            return $this;
        }

        try {
            $this->modelQueryFor($key)->firstOrFail()->delete();
        } catch (ModelNotFoundException $e) {
            $this->fallbackStorage->remove($key);
        }

        return $this;
    }

    protected function hasModelFor(string $key): bool
    {
        return $this->modelClassFor($key) !== null;
    }

    protected function modelClassFor(string $key): ?string
    {
        switch ($key) {
            case StorageType::Customer->value:
                return $this->modelClasses['customerModel'] ?? null;

            case StorageType::Discounts->value:
                return $this->modelClasses['discountModel'] ?? null;

            case StorageType::Items->value:
                return $this->modelClasses['cartItemModel'] ?? null;
        }

        return null;
    }

    protected function modelFor(string $key): ?StoresEcommerceData
    {
        $class = $this->modelClassFor($key);
        if (! $class) {
            return null;
        }

        return (new $class);
    }

    protected function modelQueryFor(string $key): ?Builder
    {
        $model = $this->modelFor($key);
        if (! $model) {
            return null;
        }

        if ($model instanceof StoresDifferentInstances) {
            $model = $model->forInstance($this->instanceName);
        }

        return $model->forCurrentUser();
    }
}
