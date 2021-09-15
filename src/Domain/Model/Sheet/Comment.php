<?php

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

    /** @var string */
    private $text;

    /** @var \DateTimeInterface */
    private $createdAt;

    /**
     * @param Sheet              $sheet
     * @param Admin              $author
     * @param string             $text
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Sheet $sheet,
        Admin $author,
        string $text,
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
     * @return string
     */
    public function getText(): string
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
