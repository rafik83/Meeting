<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Application\Components\Security\AdminEventAccess;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminEventAccessVoter extends Voter
{
    /**
     * @var AdminEventAccess
     */
    private $adminEventAccess;

    /**
     * @param AdminEventAccess $adminEventAccess
     */
    public function __construct(AdminEventAccess $adminEventAccess)
    {
        $this->adminEventAccess = $adminEventAccess;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return 'PERMISSION_EVENT_ACCESS' === $attribute && $subject instanceof Event;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttribute($attribute, $event, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof Admin) {
            return false;
        }

        return $this->adminEventAccess->canAccess($user, $event);
    }
}
