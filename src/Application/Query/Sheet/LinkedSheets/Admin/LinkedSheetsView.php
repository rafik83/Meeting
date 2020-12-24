<?php

namespace Proximum\Vimeet\Application\Query\Sheet\LinkedSheets\Admin;

class LinkedSheetsView
{
    /** @var int */
    public $id;

    /** @var string[] */
    public $titles;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var bool */
    public $isRemovable;

    public function __construct(int $id, array $titles, \DateTimeInterface $createdAt, bool $isRemovable)
    {
        $this->id = $id;
        $this->titles = $titles;
        $this->createdAt = $createdAt;
        $this->isRemovable = $isRemovable;
    }
}
