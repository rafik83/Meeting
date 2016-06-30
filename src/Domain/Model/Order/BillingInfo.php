<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Order;

use Proximum\Vimeet\Domain\Model\Address;

class BillingInfo
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $mobile;

    /**
     * @var string
     */
    private $position;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $company;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var string
     */
    private $vatNumber;

    /**
     * BillingInfo constructor.
     *
     * @param string  $lastName
     * @param string  $firstName
     * @param string  $position
     * @param string  $phone
     * @param string  $mobile
     * @param string  $email
     * @param string  $company
     * @param Address $address
     * @param string  $vatNumber
     */
    public function __construct(
        $lastName,
        $firstName,
        $position,
        $phone,
        $mobile,
        $email,
        $company,
        Address $address,
        $vatNumber
    ) {
        $this->lastName  = $lastName;
        $this->firstName = $firstName;
        $this->phone     = $phone;
        $this->mobile    = $mobile;
        $this->position  = $position;
        $this->email     = $email;
        $this->company   = $company;
        $this->address   = $address;
        $this->vatNumber = $vatNumber;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Get address
     *
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Get vatNumber
     *
     * @return string
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }
}
