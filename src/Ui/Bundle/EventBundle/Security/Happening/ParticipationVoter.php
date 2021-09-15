<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Happening;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ParticipationVoter extends Voter
{
    const PARTICIPATE = 'participate';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::PARTICIPATE])) {
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

        // $subject is a Unavailability or Sheet object, thanks to supports method
        switch ($attribute) {
            case self::PARTICIPATE:
                return $this->canParticipate($subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    private function canParticipate(Sheet $sheet)
    {
        return $sheet->attend();
    }
}
