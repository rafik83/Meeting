<?php

namespace Proximum\Vimeet\Application\View\OMZ;

class OmzUserView
{
    /** @var string */
    public $participantId;

    /** @var string */
    public $companyName;

    /** @var string */
    public $description;

    /** @var string */
    public $participationType;

    /** @var string */
    public $gender;

    /** @var string */
    public $firstname;

    /** @var string */
    public $lastname;

    /** @var string */
    public $position;

    /** @var string */
    public $phonePrefix;

    /** @var string */
    public $phoneNumber;

    /** @var string */
    public $email;

    /** @var string */
    public $mobilePhonePrefix;

    /** @var string */
    public $mobilePhoneNumber;

    /** @var string */
    public $planning;

    /**
     * OmzUserView constructor.
     *
     * @param string $participantId
     * @param string $companyName
     * @param string $description
     * @param string $participationType
     * @param string $gender
     * @param string $firstname
     * @param string $lastname
     * @param string $position
     * @param string $phonePrefix
     * @param string $phoneNumber
     * @param string $email
     * @param string $mobilePhonePrefix
     * @param string $mobilePhoneNumber
     * @param string $planning
     */
    public function __construct(
        $participantId,
        $companyName,
        $description,
        $participationType,
        $gender,
        $firstname,
        $lastname,
        $position,
        $phonePrefix,
        $phoneNumber,
        $email,
        $mobilePhonePrefix,
        $mobilePhoneNumber,
        $planning
    ) {
        $this->participantId = $participantId;
        $this->companyName = $companyName;
        $this->description = $description;
        $this->participationType = $participationType;
        $this->gender = $gender;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->position = $position;
        $this->phonePrefix = $phonePrefix;
        $this->phoneNumber = $phoneNumber;
        $this->email = $email;
        $this->mobilePhonePrefix = $mobilePhonePrefix;
        $this->mobilePhoneNumber = $mobilePhoneNumber;
        $this->planning = $planning;
    }
}
