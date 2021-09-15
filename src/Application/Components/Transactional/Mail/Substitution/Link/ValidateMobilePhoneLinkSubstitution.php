<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Participant;

class ValidateMobilePhoneLinkSubstitution implements SubstituteInterface
{
    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(EventUrlGeneratorInterface $eventUrlGenerator)
    {
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail->hasSheet()) {
            return '';
        }

        $userParticipant = $prepareMail->sheet->getUserParticipant($prepareMail->user);

        if (!$userParticipant instanceof Participant) {
            return '';
        }

        return $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $prepareMail->event,
            'event_user_phone_validate',
            [
                'sheet'       => $prepareMail->sheet->getId(),
                'participant' => $userParticipant->getId(),
                '_locale'     => $prepareMail->event->getAvailableLocale($prepareMail->locale),
            ]
        );
    }
}
