<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Security;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SheetVoter extends Voter
{
    const EDIT = 'edit';
    const UNAVAILABILITY_ADD = 'unavailability-add';
    const UNAVAILABILITY_REMOVE = 'unavailability-remove';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT, self::UNAVAILABILITY_ADD, self::UNAVAILABILITY_REMOVE])) {
            return false;
        }

        // only vote on Sheet objects inside this voter
        if (!$subject instanceof Sheet) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // $subject is a Sheet object, thanks to supports method
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::UNAVAILABILITY_ADD:
                return $this->canAddUnavailability($subject, $user);
            case self::UNAVAILABILITY_REMOVE:
                return $this->canRemoveUnavailability($subject, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return bool
     */
    private function canEdit(Sheet $sheet, User $user)
    {
        if ($sheet->hasUser($user)) {
            return true;
        }

        // If the user is not on the sheet but is manager of the sheet's group
        $group = $sheet->getGroup();

        if (null !== $group && $group->getManager() === $user) {
            return true;
        }

        return false;
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return bool
     */
    private function canAddUnavailability(Sheet $sheet, User $user)
    {
        return $this->canUpdateUnavailability($sheet, $user);
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return bool
     */
    private function canRemoveUnavailability(Sheet $sheet, User $user)
    {
        return $this->canUpdateUnavailability($sheet, $user);
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return bool
     */
    private function canUpdateUnavailability(Sheet $sheet, User $user)
    {
        return $this->canEdit($sheet, $user) && $sheet->attend();
    }
}
