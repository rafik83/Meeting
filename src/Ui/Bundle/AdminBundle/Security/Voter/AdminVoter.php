<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Security\Voter;

use Proximum\Vimeet\Domain\Model\Admin;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminVoter extends Voter
{
    public const DELETE = 'delete_admin';

    protected function supports($attribute, $subject): bool
    {
        return $attribute === self::DELETE && $subject instanceof Admin;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $loggedAdmin = $token->getUser();

        if (!$subject instanceof Admin || !$loggedAdmin instanceof Admin) {
            return false;
        }

        switch ($attribute) {
            case self::DELETE:
                return $this->canDelete($loggedAdmin, $subject);
            default:
                return false;
        }
    }

    private function canDelete(Admin $loggedAdmin, Admin $adminToRemove): bool
    {
        if ($loggedAdmin === $adminToRemove) {
            return false;
        }

        if ('ROLE_SUPER_ADMIN' === $loggedAdmin->getRole()) {
            return true;
        }

        if ('ROLE_ORGANIZER' === $loggedAdmin->getRole()
            && \in_array($adminToRemove->getRole(), ['ROLE_PARTNER', 'ROLE_OPERATOR'])) {
            return true;
        }

        return false;
    }
}
