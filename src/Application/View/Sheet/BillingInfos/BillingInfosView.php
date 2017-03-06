<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\BillingInfos;

use Proximum\Vimeet\Domain\Model\Address;

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

    /** @var string */
    public $country;

    /** @var string */
    public $reference;

    /**
     * @param string  $gender
     * @param string  $lastname
     * @param string  $firstname
     * @param string  $function
     * @param string  $phone
     * @param string  $mobile
     * @param string  $email
     * @param string  $company
     * @param Address $address
     * @param string  $vatNumber
     * @param string  $reference
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
        Address $address,
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
        $this->street    = $address->getStreet();
        $this->zipcode   = $address->getZipcode();
        $this->city      = $address->getCity();
        $this->country   = $address->getCountry();
        $this->vatNumber = $vatNumber;
        $this->reference = $reference;
    }
}
