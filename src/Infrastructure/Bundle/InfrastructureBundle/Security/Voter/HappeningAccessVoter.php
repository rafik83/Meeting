<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class HappeningAccessVoter extends Voter
{
    /**
     * @var HappeningsAccessChecker
     */
    private $happeningsAccessChecker;

    /**
     * HappeningAccessVoter constructor.
     *
     * @param HappeningsAccessChecker $happeningsAccessChecker
     */
    public function __construct(HappeningsAccessChecker $happeningsAccessChecker)
    {
        $this->happeningsAccessChecker = $happeningsAccessChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $attribute === 'PERMISSION_HAPPENING_ACCESS' && $subject instanceof Event;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->happeningsAccessChecker->allowedToAccess($subject);
    }
}
