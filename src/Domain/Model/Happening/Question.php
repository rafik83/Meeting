<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Question
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Happening
     */
    private $happening;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var User
     */
    private $createdBy;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var string
     */
    private $content;

    /**
     * Has question been ask during webinar (true), or on registration (false)
     * @var bool
     */
    private $askedDuringWebinar;

    /**
     * Question constructor.
     *
     * @param Happening          $happening
     * @param Sheet              $sheet
     * @param User               $createdBy
     * @param \DateTimeInterface $createdAt
     * @param string             $content
     */
    public function __construct(
        Happening $happening,
        Sheet $sheet,
        User $createdBy,
        \DateTimeInterface $createdAt,
        $content,
        $askedDuringWebinar = false
    ) {
        $this->happening = $happening;
        $this->sheet = $sheet;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->content = $content;
        $this->askedDuringWebinar = $askedDuringWebinar;
    }

    /**
     * @return Happening
     */
    public function getHappening(): Happening
    {
        return $this->happening;
    }

    /**
     * @return Sheet
     */
    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    /**
     * @return User
     */
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getAskedDuringWebinar(): bool
    {
        return $this->askedDuringWebinar;
    }
}
