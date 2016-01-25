<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Proforma;

class BillingView
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $address;

    /**
     * @var string
     */
    public $city;

    /**
     * @var string
     */
    public $zipcode;

    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $organization;

    /**
     * @var string
     */
    public $vat_number;

    /**
     * @var string
     */
    public $extra;

    /**
     * BillingView constructor.
     *
     * @param string $name
     * @param string $address
     * @param string $city
     * @param string $zipcode
     * @param string $country
     * @param string $phone
     * @param string $email
     * @param string $organization
     * @param string $vat_number
     * @param string $extra
     */
    public function __construct($name, $address, $city, $zipcode, $country, $phone, $email, $organization, $vat_number, $extra)
    {
        $this->name         = $name;
        $this->address      = $address;
        $this->city         = $city;
        $this->zipcode      = $zipcode;
        $this->country      = $country;
        $this->phone        = $phone;
        $this->email        = $email;
        $this->organization = $organization;
        $this->vat_number   = $vat_number;
        $this->extra        = $extra;
    }
}
