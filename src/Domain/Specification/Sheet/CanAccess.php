<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Specification\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Satisfaction\CompositeSpecification;

class CanAccess extends CompositeSpecification
{
    private $user;

    public function isSatisfiedBy($sheet)
    {
        if (!$sheet instanceof Sheet) {
            return false;
        }

        foreach ($sheet->getParticipants() as $participant) {
            if ($participant->getUser() === $this->user) {
                return true;
            }
        }

        return false;
    }

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
