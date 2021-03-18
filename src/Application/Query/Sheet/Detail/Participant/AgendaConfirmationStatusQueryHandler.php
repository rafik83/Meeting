<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Detail\Participant;

use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaConfirmationNotConcernedView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaConfirmationNotSentView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaConfirmationStatusView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaConfirmedView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaNotConfirmedView;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class AgendaConfirmationStatusQueryHandler
{
    /** @var UserEventTokenRepositoryInterface */
    private $userEventTokenRepository;

    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /**
     * AgendaConfirmationStatusQueryHandler constructor.
     *
     * @param UserEventTokenRepositoryInterface         $userEventTokenRepository
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param MeetingRepositoryInterface                $meetingRepository
     */
    public function __construct(
        UserEventTokenRepositoryInterface $userEventTokenRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->userEventTokenRepository = $userEventTokenRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->meetingRepository = $meetingRepository;
    }

    /**
     * @param AgendaConfirmationStatusQuery $query
     *
     * @return AgendaConfirmationStatusView
     */
    public function handle(AgendaConfirmationStatusQuery $query): AgendaConfirmationStatusView
    {
        $user = $query->participant->getUser();

        if (null === $this->happeningParticipationRepository->checkAnyParticipation($user, $query->event)
            && false === $this->meetingRepository->hasScheduledMeetingByParticipant($query->participant)
        ) {
            return new AgendaConfirmationNotConcernedView();
        }

        $userEventToken = $this->userEventTokenRepository->findByEventAndUserAndType(
            $query->event,
            $user,
            UserEventTokenType::AGENDA_CONFIRMATION
        );

        if (null !== $userEventToken) {
            return $userEventToken->isConfirmed() ? new AgendaConfirmedView() : new AgendaNotConfirmedView();
        }

        return new AgendaConfirmationNotSentView();
    }
}
