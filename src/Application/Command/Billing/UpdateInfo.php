<?php

namespace Proximum\Vimeet\Application\Command\Billing;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\BillingInfo;

class UpdateInfo implements Command
{
    /**
     * @var BillingInfo
     */
    public $billingInfo;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $function;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var string
     */
    public $mobile;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $company;

    /**
     * @var string
     */
    public $street;

    /**
     * @var string
     */
    public $zipcode;

    /**
     * @var string
     */
    public $city;

    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $vatNumber;

    /**
     * @var string
     */
    public $gender;

    /**
     * @var string
     */
    public $reference;

    /**
     * UpdateInfo constructor.
     *
     * @param BillingInfo $billingInfo
     */
    public function __construct(BillingInfo $billingInfo)
    {
        $this->billingInfo = $billingInfo;
        $this->lastname    = $billingInfo->getLastname();
        $this->firstname   = $billingInfo->getFirstname();
        $this->function    = $billingInfo->getFunction();
        $this->phone       = $billingInfo->getPhone();
        $this->mobile      = $billingInfo->getMobile();
        $this->email       = $billingInfo->getEmail();
        $this->company     = $billingInfo->getCompany();
        $this->street      = $billingInfo->getAddress()->getStreet();
        $this->zipcode     = $billingInfo->getAddress()->getZipcode();
        $this->city        = $billingInfo->getAddress()->getCity();
        $this->country     = $billingInfo->getAddress()->getCountry();
        $this->vatNumber   = $billingInfo->getVatNumber();
        $this->gender      = $billingInfo->getGender();
        $this->reference   = $billingInfo->getReference();
    }
}
