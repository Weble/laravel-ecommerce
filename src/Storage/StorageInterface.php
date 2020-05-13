<?php


namespace Weble\LaravelEcommerce\Storage;

interface StorageInterface
{
    public function set(string $key, $value): self;

    public function get(string $key, $default = null);

    public function has(string $key): bool;

    public function remove(string $key): self;
}
