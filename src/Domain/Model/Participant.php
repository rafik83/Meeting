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
    private $owner;

    /**
     * @param Sheet $sheet
     * @param User  $user
     * @param array $data
     * @param bool  $owner
     */
    public function __construct(Sheet $sheet, User $user, array $data, $owner)
    {
        $this->sheet = $sheet;
        $this->user  = $user;
        $this->data  = $data;
        $this->owner = $owner;
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
     * @return bool
     */
    public function isOwner()
    {
        return $this->owner;
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
}
