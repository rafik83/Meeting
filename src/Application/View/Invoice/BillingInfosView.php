<?php

namespace Proximum\Vimeet\Application\View\Invoice;

class BillingInfosView
{
    /** @var string */
    public $gender;

    /** @var string */
    public $lastname;

    /** @var string */
    public $firstname;

    /** @var string */
    public $function;

    /** @var string */
    public $phone;

    /** @var string */
    public $mobile;

    /** @var string */
    public $email;

    /** @var string */
    public $company;

    /** @var string */
    public $vatNumber;

    /** @var string */
    public $street;

    /** @var string */
    public $zipcode;

    /** @var string */
    public $city;

    /** @var string ISO 3166-1 alpha-2 country code */
    public $country;

    /** @var string */
    public $reference;

    /**
     * @param string $gender    from the sheet
     * @param string $lastname  from the sheet
     * @param string $firstname from the sheet
     * @param string $function  from the sheet
     * @param string $phone     from the sheet
     * @param string $mobile    from the sheet
     * @param string $email     from the sheet
     * @param string $company   from the sheet
     * @param string $street    from the sheet
     * @param string $zipcode   from the sheet
     * @param string $city      from the sheet
     * @param string $country   from the invoice
     * @param string $vatNumber from the invoice
     * @param string $reference from the sheet
     */
    public function __construct(
        $gender,
        $lastname,
        $firstname,
        $function,
        $phone,
        $mobile,
        $email,
        $company,
        $street,
        $zipcode,
        $city,
        $country,
        $vatNumber,
        $reference
    ) {
        $this->gender    = $gender;
        $this->lastname  = $lastname;
        $this->firstname = $firstname;
        $this->function  = $function;
        $this->phone     = $phone;
        $this->mobile    = $mobile;
        $this->email     = $email;
        $this->company   = $company;
        $this->street    = $street;
        $this->zipcode   = $zipcode;
        $this->city      = $city;
        $this->country   = $country;
        $this->vatNumber = $vatNumber;
        $this->reference = $reference;
    }
}
