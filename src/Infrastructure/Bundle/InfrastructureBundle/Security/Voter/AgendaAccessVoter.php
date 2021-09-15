<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter;

use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AgendaAccessVoter extends Voter
{
    const PERMISSION = 'PERMISSION_AGENDA_ACCESS';

    /**
     * @var AgendaAccessChecker
     */
    private $agendaAccessChecker;

    /**
     * AgendaAccessVoter constructor.
     *
     * @param AgendaAccessChecker $agendaAccessChecker
     */
    public function __construct(AgendaAccessChecker $agendaAccessChecker)
    {
        $this->agendaAccessChecker = $agendaAccessChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Event;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->agendaAccessChecker->allowedToAccess($subject);
    }
}
