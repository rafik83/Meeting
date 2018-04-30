<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\OMZ;

class OmzUserView
{
    /** @var int */
    public $userId;

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
     * @param int    $userId
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
        $userId,
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
        $this->userId            = $userId;
        $this->companyName       = $companyName;
        $this->description       = $description;
        $this->participationType = $participationType;
        $this->gender            = $gender;
        $this->firstname         = $firstname;
        $this->lastname          = $lastname;
        $this->position          = $position;
        $this->phonePrefix       = $phonePrefix;
        $this->phoneNumber       = $phoneNumber;
        $this->email             = $email;
        $this->mobilePhonePrefix = $mobilePhonePrefix;
        $this->mobilePhoneNumber = $mobilePhoneNumber;
        $this->planning          = $planning;
    }
}
