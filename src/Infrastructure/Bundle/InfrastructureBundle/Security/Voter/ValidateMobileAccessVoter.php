<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Application\Security\ValidateMobileProcessAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ValidateMobileAccessVoter extends Voter
{
    /**
     * @var ValidateMobileProcessAccessChecker
     */
    private $validateMobileProcessAccessChecker;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * ValidateMobileAccessVoter constructor.
     *
     * @param RequestStack                       $requestStack
     * @param ValidateMobileProcessAccessChecker $validateMobileProcessAccessChecker
     */
    public function __construct(
        RequestStack $requestStack,
        ValidateMobileProcessAccessChecker $validateMobileProcessAccessChecker
    ) {
        $this->validateMobileProcessAccessChecker = $validateMobileProcessAccessChecker;
        $this->requestStack                       = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!$subject instanceof Event) {
            return false;
        }

        return $attribute === 'PERMISSION_USER_VALIDATE_MOBILE_ACCESS';
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var Event $event */
        $event = $subject;

        return $this->validateMobileProcessAccessChecker->allowToAccess(
            $event,
            $token->getUser(),
            $this->requestStack->getCurrentRequest()->getLocale()
        );
    }
}
