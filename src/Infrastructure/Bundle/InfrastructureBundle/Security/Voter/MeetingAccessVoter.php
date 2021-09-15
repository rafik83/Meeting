<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Application\Components\Security\MeetingAccess;
use Proximum\Vimeet\Domain\Model\Meeting;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MeetingAccessVoter extends Voter
{
    const PERMISSION = 'PERMISSION_MEETING_ACCESS';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Meeting;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return (new MeetingAccess())->allowedToAccess($subject, $token->getUser());
    }
}
