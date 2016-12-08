<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Application\Components\Security\HappeningAccess;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class HappeningAccessVoter extends Voter
{

    /**
     * @var HappeningAccess
     */
    private $happeningAccess;

    /**
     * HappeningAccessVoter constructor.
     *
     * @param HappeningAccess $happeningAccess
     */
    public function __construct(HappeningAccess $happeningAccess)
    {
        $this->happeningAccess = $happeningAccess;
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
        return $this->happeningAccess->canAccess($subject);
    }
}
