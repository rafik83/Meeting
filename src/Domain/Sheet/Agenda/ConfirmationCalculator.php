<?php

namespace Proximum\Vimeet\Domain\Sheet\Agenda;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

/**
 * This class is used to calculate if a sheet has all its users agenda confirmed if concerned
 */
class ConfirmationCalculator
{
    /** @var UserEventTokenRepositoryInterface */
    private $userEventTokenRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /**
     * @param UserEventTokenRepositoryInterface         $userEventTokenRepository
     * @param MeetingRepositoryInterface                $meetingRepository
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     */
    public function __construct(
        UserEventTokenRepositoryInterface $userEventTokenRepository,
        MeetingRepositoryInterface $meetingRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository
    ) {
        $this->userEventTokenRepository = $userEventTokenRepository;
        $this->meetingRepository = $meetingRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getConfirmationStatusForSheet(Sheet $sheet): string
    {
        $users = array_map(function (Participant $participant) {
            return $participant->getUser();
        }, $sheet->getParticipants()->toArray());

        $event = $sheet->getEvent();

        $tokens = $this
            ->userEventTokenRepository
            ->getForEventTypeAndUsers($event, UserEventTokenType::AGENDA_CONFIRMATION, $users)
        ;

        $concerned    = 0;
        $confirmed    = 0;
        $notConfirmed = 0;

        foreach ($tokens as $token) {
            $user = $token->getUser();

            $hasMeeting = $this->meetingRepository->hasMeetingForUserAndEvent($user, $event);

            if (true === $hasMeeting) {
                ++$concerned;

                if ($token->isConfirmed()) {
                    ++$confirmed;
                } else {
                    ++$notConfirmed;
                }

                continue;
            }

            $hasHappeningParticipation = $this
                ->happeningParticipationRepository
                ->hasParticipationForUserAndEvent($user, $event)
            ;

            if (true === $hasHappeningParticipation) {
                ++$concerned;

                if ($token->isConfirmed()) {
                    ++$confirmed;
                } else {
                    ++$notConfirmed;
                }
            }
        }

        if (0 !== $concerned) {
            if ($concerned === $confirmed) {
                return Sheet::AGENDA_ALL_CONFIRMED;
            } elseif ($concerned === $notConfirmed) {
                return Sheet::AGENDA_NONE_CONFIRMED;
            }

            return Sheet::AGENDA_PARTLY_CONFIRMED;
        }

        return Sheet::AGENDA_NOT_CONCERNED;
    }
}
