<?php

namespace Proximum\Vimeet\Domain\Model;

class BillingInfo implements MailRecipientInterface
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
    private $lastname;

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
     * @var string
     */
    private $gender;

    /**
     * @var string
     */
    private $reference;

    /**
     * BillingInfo constructor.
     *
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet   = $sheet;
        $this->address = new Address(null, null, null, null);
    }

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
    public function update(
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
        $this->address   = $address;
        $this->vatNumber = $vatNumber;
        $this->reference = $reference;
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
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
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
     * @return string
     */
    public function getCompleteName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullname()
    {
        return $this->getCompleteName();
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

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string  $firstname
     * @param string  $lastname
     * @param string  $function
     * @param string  $company
     * @param string  $phone
     * @param string  $mobile
     * @param string  $email
     * @param Address $address
     * @param string  $gender
     *
     * @return BillingInfo
     */
    public function prefill(
        $gender,
        $firstname,
        $lastname,
        $function,
        $company,
        $phone,
        $mobile,
        $email,
        Address $address
    ) {
        $this->gender    = $gender;
        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->function  = $function;
        $this->company   = $company;
        $this->phone     = $phone;
        $this->mobile    = $mobile;
        $this->email     = $email;
        $this->address   = $address;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return null !== $this->gender
            && null !== $this->lastname
            && null !== $this->firstname
            && null !== $this->email
            && null !== $this->company
            && null !== $this->address->getStreet()
            && null !== $this->address->getZipcode()
            && null !== $this->address->getCity()
            && null !== $this->address->getCountry();
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getSheet()->getOwnerLocale();
    }
}
