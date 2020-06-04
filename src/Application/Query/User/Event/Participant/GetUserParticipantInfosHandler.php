<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Participant;

use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Exception\Participant\ParticipantNotFoundException;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetUserParticipantInfosHandler
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var SheetGuesser */
    private $sheetGuesser;

    public function __construct(ParticipantInfoGuesser $participantInfoGuesser, SheetGuesser $sheetGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->sheetGuesser = $sheetGuesser;
    }

    public function handle(GetUserParticipantInfos $query): ParticipantView
    {
        $sheet = $this->sheetGuesser->getUserSheet($query->user, $query->event, $query->locale);
        $participant = $sheet->getUserParticipant($query->user);

        if (!$participant) {
            throw new ParticipantNotFoundException('Participant not found in the given sheet');
        }

        $infos = $this->participantInfoGuesser->guessParticipantInfos($participant, $query->locale);

        return new ParticipantView(
            $participant,
            $infos[Tag::PARTICIPANT_FIRSTNAME],
            $infos[Tag::PARTICIPANT_LASTNAME],
            $infos[Tag::PARTICIPANT_POSITION],
            $infos[Tag::PARTICIPANT_AVATAR]
        );
    }
}
