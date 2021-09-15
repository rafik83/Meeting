<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Security;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter
{
    public const CHANGE_PASSWORD = 'change_password';
    public const CHANGE_EMAIL = 'change_email';

    protected function supports($attribute, $subject): bool
    {
        if (!\in_array($attribute, [self::CHANGE_EMAIL, self::CHANGE_PASSWORD], true)) {
            return false;
        }

        if (!$subject instanceof Event) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User || !$subject instanceof Event) {
            return false;
        }

        switch ($attribute) {
            case self::CHANGE_PASSWORD:
                return !$subject->isDisabledPasswordChanging();
            case self::CHANGE_EMAIL:
                return !$subject->isDisabledEmailChanging();
        }

        return false;
    }
}
