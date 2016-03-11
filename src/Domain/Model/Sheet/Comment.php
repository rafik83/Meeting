<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class Comment
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
     * @var Admin
     */
    private $author;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @param Sheet              $sheet
     * @param Admin              $author
     * @param string             $text
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Sheet $sheet, Admin $author, $text, \DateTimeInterface $createdAt)
    {
        $this->sheet     = $sheet;
        $this->author    = $author;
        $this->createdAt = $createdAt;
        $this->text      = $text;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Admin
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
