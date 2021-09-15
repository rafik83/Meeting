<?php

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Participant\ParticipantGuesser;
use Proximum\Vimeet\Application\Exception\Participant\ParticipantNotFoundException;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;

class GetTimezoneHelper
{
    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    /** @var ParticipantGuesser */
    private $participantGuesser;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        IsParticipantVisio $isParticipantVisio,
        ParticipantGuesser $participantGuesser,
        TranslatorInterface $translator
    ) {
        $this->isParticipantVisio = $isParticipantVisio;
        $this->participantGuesser = $participantGuesser;
        $this->translator = $translator;
    }

    public function getTimezoneByEventAndParticipant(Event $event, Participant $participant): string
    {
        if ($this->isParticipantVisio->isSatisfiedBy($participant) && $participant->getTimezone()) {
            return $participant->getTimezone();
        }

        return $event->getTimeZone();
    }

    public function getTimezoneByEventAndUser(Event $event, User $user): string
    {
        try {
            $participant = $this->participantGuesser->getUserEventParticipant($user, $event);

            return $this->getTimezoneByEventAndParticipant($event, $participant);
        } catch (ParticipantNotFoundException $exception) {
        } catch (SheetNotFoundException $exception) {
        }

        return $event->getTimeZone();
    }

    public function getTimezoneTranslated(string $timezone): string
    {
        return $this->translator->trans('timezone.' . strtolower(str_replace('/', '-', $timezone)));
    }
}
