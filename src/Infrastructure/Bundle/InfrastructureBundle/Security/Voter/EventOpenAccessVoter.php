<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventOpenAccessVoter extends Voter
{
    const PERMISSION_EVENT_OPEN_ACCESS = 'PERMISSION_EVENT_OPEN_ACCESS';

    /**
     * @var EventOpenAccessChecker
     */
    private $accessChecker;

    /**
     * EventOpenAccessVoter constructor.
     *
     * @param EventOpenAccessChecker $accessChecker
     */
    public function __construct(EventOpenAccessChecker $accessChecker)
    {
        $this->accessChecker = $accessChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $attribute === self::PERMISSION_EVENT_OPEN_ACCESS && $subject instanceof Event;
    }

    /**
     * {@inheritdoc}
     *
     * @param Event $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $this->accessChecker->allowedToAccess($subject);
    }
}
