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
     * @var string
     */
    private $data;

    /**
     * @param Sheet $sheet
     * @param User  $user
     * @param array $data
     */
    public function __construct(Sheet $sheet, User $user, array $data)
    {
        $this->sheet = $sheet;
        $this->user  = $user;
        $this->setData($data);
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
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return json_decode($this->data, true);
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
        $data = json_encode($data);

        if ($data === null) {
            throw new \InvalidArgumentException('Invalid json data');
        }

        $this->data = $data;
    }
}
