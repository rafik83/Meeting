<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupVoter extends Voter
{
    const MANAGE = 'manage';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::MANAGE])) {
            return false;
        }

        // only vote on Group objects inside this voter
        if (!$subject instanceof Group) {
            return false;
        }

        return true;
    }

    /**
     * @param string         $attribute
     * @param Group          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // the user must be logged in
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::MANAGE:
                return $this->canManage($subject, $user);
        }

        return false;
    }

    /**
     * @param Group $group
     * @param User  $user
     *
     * @return bool
     */
    private function canManage(Group $group, User $user)
    {
        return $group->getManager() === $user;
    }
}
