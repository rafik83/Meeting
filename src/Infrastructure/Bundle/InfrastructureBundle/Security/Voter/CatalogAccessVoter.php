<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CatalogAccessVoter extends Voter
{
    const VIEW = 'view';

    /** @var CatalogAccessChecker */
    private $accessChecker;

    /**
     * CatalogAccessVoter constructor.
     *
     * @param CatalogAccessChecker $accessChecker
     */
    public function __construct(CatalogAccessChecker $accessChecker)
    {
        $this->accessChecker = $accessChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW])) {
            return false;
        }

        if (!$subject instanceof Event) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // the user must be logged in
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject);
        }

        return false;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    private function canView(Event $event)
    {
        return $this->accessChecker->allowedToAccess($event);
    }
}
