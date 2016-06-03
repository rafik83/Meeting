<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class BillingInfo
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * "nom"
     *
     * @var string
     */
    private $name;

    /**
     * "prénom"
     *
     * @var string
     */
    private $firstname;

    /**
     * "fonction (optionnel)"
     *
     * @var string
     */
    private $function;

    /**
     * "tel mobile (optionnel)"
     *
     * @var string
     */
    private $phone;

    /**
     * "tel fixe (optionnel)"
     *
     * @var string
     */
    private $mobile;

    /**
     * "e-mail"
     *
     * @var string
     */
    private $email;

    /**
     * "Société"
     *
     * @var string
     */
    private $company;

    /**
     * "adresse"
     *
     * @var Address
     */
    private $address;

    /**
     * "N° TVA (optionnel)"
     *
     * @var string
     */
    private $vatNumber;

    /**
     * BillingInfo constructor.
     *
     * @param Sheet   $sheet
     * @param string  $name
     * @param string  $firstname
     * @param string  $function
     * @param string  $phone
     * @param string  $mobile
     * @param string  $email
     * @param string  $company
     * @param Address $address
     * @param string  $vatNumber
     */
    public function __construct(Sheet $sheet, $name, $firstname, $function, $phone, $mobile, $email, $company, Address $address, $vatNumber)
    {
        $this->sheet     = $sheet;
        $this->name      = $name;
        $this->firstname = $firstname;
        $this->function  = $function;
        $this->phone     = $phone;
        $this->mobile    = $mobile;
        $this->email     = $email;
        $this->company   = $company;
        $this->address   = $address;
        $this->vatNumber = $vatNumber;
    }

    /**
     * @param string  $name
     * @param string  $firstname
     * @param string  $function
     * @param string  $phone
     * @param string  $mobile
     * @param string  $email
     * @param string  $company
     * @param Address $address
     * @param string  $vatNumber
     */
    public function update($name, $firstname, $function, $phone, $mobile, $email, $company, Address $address, $vatNumber)
    {
        $this->name      = $name;
        $this->firstname = $firstname;
        $this->function  = $function;
        $this->phone     = $phone;
        $this->mobile    = $mobile;
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
     * Get sheet
     *
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Get function
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
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
