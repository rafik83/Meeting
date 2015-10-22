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
 * "Participant"
 */
class Participant
{
    /**
     * @var integer
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
     * @var Type
     */
    private $type;

    /**
     * @var string
     */
    private $data;

    /**
     * @param Sheet  $sheet
     * @param User   $user
     * @param Type   $type
     * @param string $data
     */
    public function __construct(Sheet $sheet, User $user, Type $type, $data)
    {
        $this->sheet = $sheet;
        $this->user  = $user;
        $this->type  = $type;
        $this->data  = $data;
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
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
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
     * Get type
     *
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return Participant
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
