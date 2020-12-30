<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\View\Agenda\AgendaParticipantView;
use Proximum\Vimeet\Domain\KeyDates\Checker\SmsActivationDateAccessChecker;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class AgendaParticipantViewQueryHandler
{
    /** @var DayRepositoryInterface */
    private $dayRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var AgendaDayViewQueryHandler */
    private $agendaDayViewQueryHandler;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var SmsActivationDateAccessChecker */
    private $smsActivationDateAccessChecker;

    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /**
     * @param DayRepositoryInterface            $dayRepository
     * @param MeetingRepositoryInterface        $meetingRepository
     * @param AgendaDayViewQueryHandler         $agendaDayViewQueryHandler
     * @param ParticipantInfoGuesser            $participantInfoGuesser
     * @param SmsActivationDateAccessChecker    $smsActivationDateAccessChecker
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     */
    public function __construct(
        DayRepositoryInterface $dayRepository,
        MeetingRepositoryInterface $meetingRepository,
        AgendaDayViewQueryHandler $agendaDayViewQueryHandler,
        ParticipantInfoGuesser $participantInfoGuesser,
        SmsActivationDateAccessChecker $smsActivationDateAccessChecker,
        UserEventPhoneRepositoryInterface $userEventPhoneRepository
    ) {
        $this->dayRepository                  = $dayRepository;
        $this->meetingRepository              = $meetingRepository;
        $this->agendaDayViewQueryHandler      = $agendaDayViewQueryHandler;
        $this->participantInfoGuesser         = $participantInfoGuesser;
        $this->smsActivationDateAccessChecker = $smsActivationDateAccessChecker;
        $this->userEventPhoneRepository       = $userEventPhoneRepository;
    }

    /**
     * @param AgendaParticipantViewQuery $query
     *
     * @return AgendaParticipantView
     */
    public function handle(AgendaParticipantViewQuery $query)
    {
        $eventDays = $this->dayRepository->findByEvent($query->event);

        $meetingsOtherSheets = $this
            ->meetingRepository
            ->findByUserAndEventExceptSheet($query->participant->getUser(), $query->event, $query->sheet);

        $dayViews = [];

        foreach ($eventDays as $dayNumber => $day) {
            $dayViews[] = $this->agendaDayViewQueryHandler->handle(
                new AgendaDayViewQuery(
                    $query->sheet,
                    $day,
                    $dayNumber,
                    $query->participant,
                    $query->locale,
                    $query->happeningParticipations,
                    $query->unavailabilities,
                    $query->masses,
                    $query->meetings,
                    $query->massAssignments,
                    $meetingsOtherSheets
                )
            );
        }

        $completeName = $this->participantInfoGuesser->guessParticipantCompleteName(
            $query->participant,
            $query->locale
        );

        $stopSMS = false;
        $phoneValidated = false;
        $userEventPhone = $this->userEventPhoneRepository->find($query->participant->getUser(), $query->event);

        if ($userEventPhone instanceof UserEventPhone) {
            $stopSMS = $userEventPhone->isStop();
            $phoneValidated = $userEventPhone->isValidated();
        }

        return new AgendaParticipantView(
            $query->participant->getId(),
            $completeName,
            $query->participant->getUser()->getEmail(),
            $dayViews,
            $query->sheet->attend(),
            $this->smsActivationDateAccessChecker->allowedToAccess($query->event),
            $phoneValidated,
            $stopSMS
        );
    }
}
