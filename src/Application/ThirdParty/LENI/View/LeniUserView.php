<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\View;

class LeniUserView
{
    /** @var int */
    public $id;

    /** @var string */
    public $sheetName;

    /** @var int|null */
    public $typeId;

    /** @var int|null */
    public $categoryId;

    /** @var string */
    public $email;

    /** @var string */
    public $gender;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $position;

    /** @var string */
    public $phone;

    /** @var string */
    public $mobile;

    /** @var string */
    public $country;

    /** @var string */
    public $locale;

    /** @var LeniPlanningView */
    public $planning;

    /**
     * @param int              $id
     * @param string|null      $sheetName
     * @param int|null         $typeId
     * @param int|null         $categoryId
     * @param string           $email
     * @param string           $gender
     * @param string           $firstName
     * @param string           $lastName
     * @param string           $position
     * @param string           $phone
     * @param string           $mobile
     * @param string           $country
     * @param string           $locale
     * @param LeniPlanningView $planning
     */
    public function __construct(
        int $id,
        string $sheetName = null,
        int $typeId = null,
        int $categoryId = null,
        string $email,
        string $gender,
        string $firstName,
        string $lastName,
        string $position,
        string $phone,
        string $mobile,
        string $country,
        string $locale,
        LeniPlanningView $planning
    ) {
        $this->id = $id;
        $this->sheetName = $sheetName;
        $this->typeId = $typeId;
        $this->categoryId = $categoryId;
        $this->email = $email;
        $this->gender = $gender;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
        $this->phone = $phone;
        $this->mobile = $mobile;
        $this->planning = $planning;
        $this->country = $country;
        $this->locale = $locale;
    }
}
