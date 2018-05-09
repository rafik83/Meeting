<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Security;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter
{
    public const CHANGE_PASSWORD = 'change_password';
    public const CHANGE_EMAIL = 'change_password';

    protected function supports($attribute, $subject): bool
    {
        if (!\in_array($attribute, [self::CHANGE_EMAIL, self::CHANGE_EMAIL], true)) {
            return false;
        }

        if (!$subject instanceof EventDomain) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User || !$subject instanceof EventDomain) {
            return false;
        }

        switch ($attribute) {
            case self::CHANGE_PASSWORD:
                return !$subject->getEvent()->isDisabledPasswordChanging();
            case self::CHANGE_EMAIL:
                return !$subject->getEvent()->isDisabledEmailChanging();
        }

        return false;
    }
}
