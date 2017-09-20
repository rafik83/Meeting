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
    private $id;

    /** @var string */
    private $sheetName;

    /** @var string */
    private $typeName;

    /** @var string */
    private $email;

    /** @var string */
    private $gender;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $position;

    /** @var string */
    private $phone;

    /** @var string */
    private $mobile;

    /** @var LeniPlanningView */
    private $planning;

    /**
     * @param int              $id
     * @param string           $sheetName
     * @param string           $typeName
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
        string $email,
        $gender,
        $firstName,
        $lastName,
        $position,
        $phone,
        $mobile,
        LeniPlanningView $planning
    ) {
        $this->id = $id;
        $this->sheetName = $sheetName;
        $this->typeName = $typeName;
        $this->email = $email;
        $this->gender = $gender;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
        $this->phone = $phone;
        $this->mobile = $mobile;
        $this->planning = $planning;
    }
}
