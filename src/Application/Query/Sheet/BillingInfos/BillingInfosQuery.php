<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\BillingInfos;

use Proximum\Vimeet\Domain\Model\Address;

class BillingInfosQuery
{
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
    
    /** @var Address */
    public $address;
    
    /** @var string */
    public $vatNumber;
    
    /** @var string */
    public $gender;
    
    /**
     * BillingInfosQuery constructor.
     *
     * @param string    $lastname
     * @param string    $firstname
     * @param string    $function
     * @param string    $phone
     * @param string    $mobile
     * @param string    $email
     * @param string    $company
     * @param Address   $address
     * @param string    $vatNumber
     * @param string    $gender
     */
    public function __construct(
        $lastname,
        $firstname,
        $function,
        $phone,
        $mobile,
        $email,
        $company,
        Address $address,
        $vatNumber,
        $gender
    ) {
        $this->lastname     = $lastname;
        $this->firstname    = $firstname;
        $this->function     = $function;
        $this->phone        = $phone;
        $this->mobile       = $mobile;
        $this->email        = $email;
        $this->company      = $company;
        $this->address      = $address;
        $this->vatNumber    = $vatNumber;
        $this->gender       = $gender;
    }
}
