<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\User;

class Account
{
    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

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
    private $avatar;

    /**
     * @var string
     */
    private $position;

    /**
     * @var string
     */
    private $company;

    /**
     * @var string
     */
    private $website;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $zipCode;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $gender;

    /**
     * @var string
     */
    private $companyAddress;

    /**
     * @var string
     */
    private $companyPhone;

    /**
     * @var string
     */
    private $companyZipCode;

    /**
     * @var string
     */
    private $companyCity;

    /**
     * @var string
     */
    private $companyCountry;

    /**
     * @var string
     */
    private $companyWebsite;

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getCompanyAddress()
    {
        return $this->companyAddress;
    }

    /**
     * @param string $companyAddress
     */
    public function setCompanyAddress($companyAddress)
    {
        $this->companyAddress = $companyAddress;
    }

    /**
     * @return string
     */
    public function getCompanyPhone()
    {
        return $this->companyPhone;
    }

    /**
     * @param string $companyPhone
     */
    public function setCompanyPhone($companyPhone)
    {
        $this->companyPhone = $companyPhone;
    }

    /**
     * @return string
     */
    public function getCompanyZipCode()
    {
        return $this->companyZipCode;
    }

    /**
     * @param string $companyZipCode
     */
    public function setCompanyZipCode($companyZipCode)
    {
        $this->companyZipCode = $companyZipCode;
    }

    /**
     * @return string
     */
    public function getCompanyCity()
    {
        return $this->companyCity;
    }

    /**
     * @param string $companyCity
     */
    public function setCompanyCity($companyCity)
    {
        $this->companyCity = $companyCity;
    }

    /**
     * @return string
     */
    public function getCompanyCountry()
    {
        return $this->companyCountry;
    }

    /**
     * @param string $companyCountry
     */
    public function setCompanyCountry($companyCountry)
    {
        $this->companyCountry = $companyCountry;
    }

    /**
     * @return string
     */
    public function getCompanyWebsite()
    {
        return $this->companyWebsite;
    }

    /**
     * @param string $companyWebsite
     */
    public function setCompanyWebsite($companyWebsite)
    {
        $this->companyWebsite = $companyWebsite;
    }

    /**
     * @return string
     */
    public function getCompleteName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }
}
