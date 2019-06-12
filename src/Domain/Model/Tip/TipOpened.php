<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Tip;

use Proximum\Vimeet\Domain\Model\User;

class TipOpened
{
    /** @var User */
    private $user;

    /** @var Tip */
    private $tip;

    /** @var \DateTimeInterface */
    private $openedAt;

    /**
     * @param User               $user
     * @param Tip                $tip
     * @param \DateTimeInterface $openedAt
     */
    public function __construct(User $user, Tip $tip, \DateTimeInterface $openedAt)
    {
        $this->user = $user;
        $this->tip = $tip;
        $this->openedAt = $openedAt;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTip(): Tip
    {
        return $this->tip;
    }

    public function getOpenedAt(): \DateTimeInterface
    {
        return $this->openedAt;
    }
}
