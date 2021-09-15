<?php

namespace Proximum\Vimeet\Domain\Model;

class Address
{
    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $zipcode;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * Address constructor.
     *
     * @param string $street
     * @param string $zipcode
     * @param string $city
     * @param string $country
     */
    public function __construct($street, $zipcode, $city, $country)
    {
        $this->street  = $street;
        $this->zipcode = $zipcode;
        $this->city    = $city;
        $this->country = $country;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
}
