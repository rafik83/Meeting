<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class Comment
{
    /** @var int */
    private $id;

    /** @var Sheet */
    private $sheet;

    /** @var Admin */
    private $author;

    /** @var string|null */
    private $text;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var string|null */
    private $commercialStatus;

    /**
     * @param Sheet              $sheet
     * @param Admin              $author
     * @param string|null        $text
     * @param null|string        $commercialStatus
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Sheet $sheet,
        Admin $author,
        ?string $text = null,
        ?string $commercialStatus = null,
        \DateTimeInterface $createdAt
    ) {
        $this->sheet = $sheet;
        $this->author = $author;
        $this->createdAt = $createdAt;
        $this->text = $text;
        $this->commercialStatus = $commercialStatus;
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
    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    /**
     * @return Admin
     */
    public function getAuthor(): Admin
    {
        return $this->author;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return null|string
     */
    public function getCommercialStatus(): ?string
    {
        return $this->commercialStatus;
    }
}
