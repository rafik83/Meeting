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

    /**
     * @param Sheet              $sheet
     * @param Admin              $author
     * @param string|null        $text
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Sheet $sheet,
        Admin $author,
        ?string $text = null,
        \DateTimeInterface $createdAt
    ) {
        $this->sheet = $sheet;
        $this->author = $author;
        $this->createdAt = $createdAt;
        $this->text = $text;
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
}
