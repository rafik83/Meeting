<?php

namespace Proximum\Vimeet\Domain\Model\Sheet;

use DateTimeInterface;
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

    /** @var DateTimeInterface */
    private $createdAt;

    public function __construct(Sheet $sheet, User $user, DateTimeInterface $createdAt)
    {
        $this->sheet = $sheet;
        $this->user = $user;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }
}
