<?php

namespace Proximum\Vimeet\Domain\Model\User;

use Proximum\Vimeet\Domain\Helper\NameCleaner;

class Account
{
    /**
     * @var null|string
     */
    private $firstName;

    /**
     * @var null|string
     */
    private $lastName;

    /**
     * @var null|string
     */
    private $phone;

    /**
     * @var null|string
     */
    private $mobile;

    /**
     * @var null|string
     */
    private $avatar;

    /**
     * @var null|string
     */
    private $position;

    /**
     * @var null|string
     */
    private $company;

    /**
     * @var null|string
     */
    private $website;

    /**
     * @var null|string
     */
    private $address;

    /**
     * @var null|string
     */
    private $zipCode;

    /**
     * @var null|string
     */
    private $city;

    /**
     * @var null|string
     */
    private $country;

    /**
     * @var null|string
     */
    private $gender;

    /**
     * @var null|string
     */
    private $companyAddress;

    /**
     * @var null|string
     */
    private $companyPhone;

    /**
     * @var null|string
     */
    private $companyZipCode;

    /**
     * @var null|string
     */
    private $companyCity;

    /**
     * @var null|string
     */
    private $companyCountry;

    /**
     * @var null|string
     */
    private $companyWebsite;

    /**
     * @return null|string
     */
    public function getFirstName()
    {
        return NameCleaner::cleanFirstName($this->firstName);
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return null|string
     */
    public function getLastName()
    {
        return NameCleaner::cleanLastName($this->lastName);
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
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
     * @return null|string
     */
    public function getCompleteName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }
}
