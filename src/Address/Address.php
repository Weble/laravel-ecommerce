<?php

namespace Weble\LaravelEcommerce\Address;

use CommerceGuys\Addressing\AddressInterface;
use CommerceGuys\Addressing\Country\Country;
use CommerceGuys\Addressing\Country\CountryRepositoryInterface;
use Illuminate\Http\Request;
use Spatie\DataTransferObject\DataTransferObject;

class Address extends DataTransferObject implements AddressInterface
{
    public string $name    = '';
    public string $surname = '';
    public string $company = '';
    public string $vatId   = '';
    public string $country;
    public string $street = '';
    public string $zip    = '';
    public string $state  = '';
    public string $city   = '';
    public AddressType $type;

    public function __construct(array $parameters = [])
    {
        $parameters['country'] ??= config('ecommerce.store.address.country', 'IT');
        $parameters['type'] = AddressType::make($parameters['type'] ?? AddressType::shipping());

        parent::__construct($parameters);
    }

    public function country(): Country
    {
        return app()->make(CountryRepositoryInterface::class)->get($this->country);
    }

    public function getCountryCode()
    {
        return $this->country;
    }

    public function getAdministrativeArea()
    {
        return $this->state;
    }

    public function getLocality()
    {
        return $this->city;
    }

    public function getDependentLocality()
    {
        return '';
    }

    public function getPostalCode()
    {
        return $this->zip;
    }

    public function getSortingCode()
    {
        return $this->zip;
    }

    public function getAddressLine1()
    {
        return $this->street;
    }

    public function getAddressLine2()
    {
        return '';
    }

    public function getOrganization()
    {
        return $this->company;
    }

    public function getGivenName()
    {
        return $this->name;
    }

    public function getAdditionalName()
    {
        return '';
    }

    public function getFamilyName()
    {
        return $this->surname;
    }

    public function getLocale()
    {
        return app()->getLocale();
    }

    public function getVatId(): string
    {
        return $this->vatId;
    }

    public static function fromRequest(Request $request, $type = 'shipping'): self
    {
    }
}
