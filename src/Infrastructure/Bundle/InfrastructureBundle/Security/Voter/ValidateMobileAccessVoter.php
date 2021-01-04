<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Application\Security\ValidateMobileProcessAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ValidateMobileAccessVoter extends Voter
{
    const PERMISSION_NAME = 'PERMISSION_USER_VALIDATE_MOBILE_ACCESS';

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

        return self::PERMISSION_NAME === $attribute;
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
