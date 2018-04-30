<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetViewed
{
    /** @var int */
    private $id;

    /** @var Sheet */
    private $sheet;

    /** @var User */
    private $user;

    /** @var \DateTimeInterface */
    private $createdAt;

    /**
     * SheetViewed constructor.
     *
     * @param Sheet              $sheet
     * @param User               $user
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Sheet $sheet, User $user, \DateTimeInterface $createdAt)
    {
        $this->sheet     = $sheet;
        $this->user      = $user;
        $this->createdAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
