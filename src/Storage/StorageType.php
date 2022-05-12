<?php

namespace Weble\LaravelEcommerce\Storage;

enum StorageType: string
{
    case Items     = 'items';
    case Customer  = 'customer';
    case Discounts = 'discounts';
}
