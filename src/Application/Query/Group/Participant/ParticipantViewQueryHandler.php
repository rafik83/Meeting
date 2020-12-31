<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Application\Command\Group\UserAvailabilitiesBuilderCache;
use Proximum\Vimeet\Application\View\Sheet\Group\Participant\AgendaDayView;
use Proximum\Vimeet\Application\View\Sheet\Group\Participant\ParticipantView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantViewQueryHandler
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var AgendaDayViewQueryHandler
     */
    private $agendaDayViewQueryHandler;

    /**
     * @var UserAvailabilitiesBuilderCache
     */
    private $userAvailabilitiesBuilderCache;

    /**
     * ParticipantViewQueryHandler constructor.
     *
     * @param ParticipantInfoGuesser         $participantInfoGuesser
     * @param AgendaDayViewQueryHandler      $agendaDayViewQueryHandler
     * @param UserAvailabilitiesBuilderCache $userAvailabilitiesBuilderCache
     */
    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        AgendaDayViewQueryHandler $agendaDayViewQueryHandler,
        UserAvailabilitiesBuilderCache $userAvailabilitiesBuilderCache
    ) {
        $this->participantInfoGuesser         = $participantInfoGuesser;
        $this->agendaDayViewQueryHandler      = $agendaDayViewQueryHandler;
        $this->userAvailabilitiesBuilderCache = $userAvailabilitiesBuilderCache;
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
            ->userAvailabilitiesBuilderCache
            ->buildAvailabilitiesByUserAndEventFromSkeleton(
                $query->participant->getUser(),
                $query->event,
                $this->buildDayViewsSkeleton($query->eventDays)
            );

        return new ParticipantView($firstName, $lastName, $dayViews);
    }

    /**
     * @param Day[] $eventDays
     *
     * @return AgendaDayView[]
     */
    private function buildDayViewsSkeleton(array $eventDays)
    {
        $dayViews = [];

        foreach ($eventDays as $day) {
            $dayViews[] = $this->agendaDayViewQueryHandler->handle(
                new AgendaDayViewQuery($day)
            );
        }

        return $dayViews;
    }
}
