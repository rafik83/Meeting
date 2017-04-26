<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Application\Command\Group\ParticipantDayViewsBuilderCache;
use Proximum\Vimeet\Application\View\Sheet\Group\Participant\AgendaDayView;
use Proximum\Vimeet\Application\View\Sheet\Group\Participant\ParticipantView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantViewQueryHandler
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @var AgendaDayViewQueryHandler
     */
    private $agendaDayViewQueryHandler;

    /**
     * @var ParticipantDayViewsBuilderCache
     */
    private $participantDayViewsBuilderCache;

    /**
     * @var AgendaDayView[]
     */
    private $dayViewsSkeleton;

    /**
     * ParticipantViewQueryHandler constructor.
     *
     * @param ParticipantInfoGuesser          $participantInfoGuesser
     * @param MeetingSlotRepositoryInterface  $meetingSlotRepository
     * @param AgendaDayViewQueryHandler       $agendaDayViewQueryHandler
     * @param ParticipantDayViewsBuilderCache $participantDayViewsBuilderCache
     */
    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        AgendaDayViewQueryHandler $agendaDayViewQueryHandler,
        ParticipantDayViewsBuilderCache $participantDayViewsBuilderCache
    ) {
        $this->participantInfoGuesser          = $participantInfoGuesser;
        $this->meetingSlotRepository           = $meetingSlotRepository;
        $this->agendaDayViewQueryHandler       = $agendaDayViewQueryHandler;
        $this->participantDayViewsBuilderCache = $participantDayViewsBuilderCache;
    }

    /**
     * @param ParticipantViewQuery $query
     *
     * @return ParticipantView
     */
    public function handle(ParticipantViewQuery $query)
    {
        $firstName = $this
            ->participantInfoGuesser
            ->guessParticipantFirstName($query->participant, $query->participant->getLocale());

        $lastName = $this
            ->participantInfoGuesser
            ->guessParticipantLastName($query->participant, $query->participant->getLocale());

        $dayViews = $this
            ->participantDayViewsBuilderCache
            ->buildDayViewsByUserAndEventFromSkeleton(
                $query->participant->getUser(),
                $query->event,
                $this->buildDayViewsSkeleton($query->event, $query->eventDays)
            );

        return new ParticipantView($firstName, $lastName, $dayViews);
    }

    /**
     * @param Event $event
     * @param Day[] $eventDays
     *
     * @return AgendaDayView[]
     */
    private function buildDayViewsSkeleton(Event $event, array $eventDays)
    {
        $dayViews = [];

        foreach ($eventDays as $day) {
            $dayViews[] = $this->agendaDayViewQueryHandler->handle(
                new AgendaDayViewQuery(
                    $event,
                    $day
                )
            );
        }

        return $dayViews;
    }
}
