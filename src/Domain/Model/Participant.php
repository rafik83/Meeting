<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

/**
 * "Participant".
 */
class Participant
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
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var int
     */
    private $registrationStep;

    /**
     * @var bool
     */
    private $registrationComplete = false;

    /**
     * @param Sheet $sheet
     * @param User  $user
     * @param array $data
     * @param bool  $active
     */
    public function __construct(Sheet $sheet, User $user, array $data, $active)
    {
        $this->sheet  = $sheet;
        $this->user   = $user;
        $this->data   = $data;
        $this->active = $active;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getUser()->getLocale();
    }

    /**
     * Get sheet.
     *
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * Is owner.
     *
     * @deprecated
     * @return bool
     */
    public function isOwner()
    {
        return false;
    }

    /**
     * Is owner.
     *
     * @return bool
     */
    public function isOwnerParticipant()
    {
        return $this->sheet->getOwner() === $this->user;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data.
     *
     * @param array $data
     *
     * @return Participant
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }


    /**
     * @return int
     */
    public function getRegistrationStep()
    {
        return $this->registrationStep;
    }

    /**
     * @param int $registrationStep
     *
     * @return Participant
     */
    public function setRegistrationStep($registrationStep)
    {
        $this->registrationStep = $registrationStep;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRegistrationComplete()
    {
        return $this->registrationComplete;
    }

    /**
     * @param boolean $registrationComplete
     *
     * @return Participant
     */
    public function setRegistrationComplete($registrationComplete)
    {
        $this->registrationComplete = $registrationComplete;

        return $this;
    }
}
