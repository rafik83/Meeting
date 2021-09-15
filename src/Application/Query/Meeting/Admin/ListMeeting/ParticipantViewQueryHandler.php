<?php

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting;

use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\ParticipantView;
use Proximum\Vimeet\Domain\Meeting\IsParticipantPresentToMeeting;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantViewQueryHandler
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var ScanRepositoryInterface */
    private $scanRepository;

    /** @var IsParticipantPresentToMeeting */
    private $isParticipantPresentToMeeting;

    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        ScanRepositoryInterface $scanRepository,
        IsParticipantPresentToMeeting $isParticipantPresentToMeeting,
        IsParticipantVisio $isParticipantVisio
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->scanRepository = $scanRepository;
        $this->isParticipantPresentToMeeting = $isParticipantPresentToMeeting;
        $this->isParticipantVisio = $isParticipantVisio;
    }

    public function handle(ParticipantViewQuery $query): ParticipantView
    {
        $present = $this->isParticipantPresentToMeeting->isSatisfiedBy($query->participant, $query->meeting);

        return new ParticipantView(
            $query->participant->getId(),
            $this->participantInfoGuesser->guessParticipantCompleteName($query->participant, $query->locale),
            $this->scanRepository->isUserCheckinByEventAndSlot(
                $query->participant->getUser(),
                $query->event,
                $query->meetingSlot
            ),
            $this->isParticipantVisio->isSatisfiedBy($query->participant),
            $present
        );
    }
}
