<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Unavailability;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Proximum\Vimeet\Domain\Model\User;

class UnavailabilityVoter extends Voter
{
    const CREATE = 'create';
    const REMOVE = 'remove';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::CREATE, self::REMOVE])) {
            return false;
        }

        // only vote on Sheet objects inside this voter
        if (!$subject instanceof Unavailability && !$subject instanceof Sheet) {
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

        // $subject is a Unavailability or Sheet object, thanks to supports method
        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($subject);
            case self::REMOVE:
                return $this->canRemove($subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    private function canCreate(Sheet $sheet)
    {
        return $this->doesSheetAttendEvent($sheet);
    }

    /**
     * @param Unavailability $unavailability
     *
     * @return bool
     */
    private function canRemove(Unavailability $unavailability)
    {
        return $this->doesSheetAttendEvent($unavailability->getParticipant()->getSheet());
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    private function doesSheetAttendEvent(Sheet $sheet)
    {
        if ($sheet->attend()) {
            return true;
        }

        return false;
    }
}
