<?php

namespace Proximum\Vimeet\Application\View\Order\Export;

class BillingInfoView
{
    /** @var string|null */
    public $gender;

    /** @var string|null */
    public $lastName;

    /** @var string|null */
    public $firstName;

    /** @var string|null */
    public $position;

    /** @var string|null */
    public $phone;

    /** @var string|null */
    public $mobile;

    /** @var string|null */
    public $email;

    /** @var string|null */
    public $company;

    /** @var string|null */
    public $street;

    /** @var string|null */
    public $zipCode;

    /** @var string|null */
    public $city;

    /** @var string|null */
    public $country;

    /** @var string|null */
    public $vatNumber;

    /** @var string|null */
    public $reference;

    /** @var string|null */
    public $countryCode;

    /**
     * @param string|null $gender
     * @param string|null $lastName
     * @param string|null $firstName
     * @param string|null $position
     * @param string|null $phone
     * @param string|null $mobile
     * @param string|null $email
     * @param string|null $company
     * @param string|null $street
     * @param string|null $zipCode
     * @param string|null $city
     * @param string|null $country
     * @param string|null $countryCode
     * @param string|null $vatNumber
     * @param string|null $reference
     */
    public function __construct(
        $gender = null,
        $lastName = null,
        $firstName = null,
        $position = null,
        $phone = null,
        $mobile = null,
        $email = null,
        $company = null,
        $street = null,
        $zipCode = null,
        $city = null,
        $country = null,
        $countryCode = null,
        $vatNumber = null,
        $reference = null
    ) {
        $this->gender    = $gender;
        $this->lastName  = $lastName;
        $this->firstName = $firstName;
        $this->position  = $position;
        $this->phone     = $phone;
        $this->mobile    = $mobile;
        $this->email     = $email;
        $this->company   = $company;
        $this->street    = $street;
        $this->zipCode   = $zipCode;
        $this->city      = $city;
        $this->country   = $country;
        $this->vatNumber = $vatNumber;
        $this->reference = $reference;
        $this->countryCode = $countryCode;
    }
}
