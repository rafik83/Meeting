<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Api\Leni;

class LeniUserView
{
    /** @var int */
    public $id;

    /** @var string */
    public $sheetName;

    /** @var string */
    public $typeName;

    /** @var string */
    public $categoryName;

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

    /** @var LeniPlanningView */
    public $planning;

    /** @var array */
    public $serializeContent;

    /**
     * @param int              $id
     * @param string           $sheetName
     * @param string           $typeName
     * @param string           $categoryName
     * @param string           $email
     * @param string           $gender
     * @param string           $firstName
     * @param string           $lastName
     * @param string           $position
     * @param string           $phone
     * @param string           $mobile
     * @param LeniPlanningView $planning
     */
    public function __construct(
        int $id,
        string $sheetName,
        string $typeName,
        string $categoryName,
        string $email,
        $gender,
        $firstName,
        $lastName,
        $position,
        $phone,
        $mobile,
        LeniPlanningView $planning
    ) {
        $this->id               = $id;
        $this->sheetName        = $sheetName;
        $this->typeName         = $typeName;
        $this->categoryName     = $categoryName;
        $this->email            = $email;
        $this->gender           = $gender;
        $this->firstName        = $firstName;
        $this->lastName         = $lastName;
        $this->position         = $position;
        $this->phone            = $phone;
        $this->mobile           = $mobile;
        $this->planning         = $planning;
        $this->serializeContent = [];
    }

    /**
     * @param array $serializeContent
     */
    public function addSerializeContent(array $serializeContent)
    {
        $this->serializeContent = $serializeContent;
    }
}
