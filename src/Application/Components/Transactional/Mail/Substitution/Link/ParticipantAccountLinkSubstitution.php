<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link;

use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantAccountLinkSubstitution implements SubstituteInterface
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

        $participant = null;

        if (method_exists($prepareMail, 'getParticipant')) {
            $participant = $prepareMail->getParticipant();
        } else {
            $participant = $prepareMail->sheet->getUserParticipant($prepareMail->user);
        }

        if (!$participant instanceof Participant) {
            return '';
        }

        return $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $prepareMail->event,
            Route::PARTICIPANT_ACCOUNT,
            [
                'sheet' => $prepareMail->sheet->getId(),
                'participant' => $participant->getId(),
            ]
        );
    }
}
