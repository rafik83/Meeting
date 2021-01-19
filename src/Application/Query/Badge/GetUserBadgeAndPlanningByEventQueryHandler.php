<?php

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;

class GetUserBadgeAndPlanningByEventQueryHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var ParticipantPlanningFormatter */
    private $participantPlanningFormatter;

    public function __construct(
        QueryBusInterface $queryBus,
        ParticipantPlanningFormatter $participantPlanningFormatter
    ) {
        $this->queryBus = $queryBus;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
    }

    public function handle(GetUserBadgeAndPlanningByEventQuery $query): UserBadgeAndPlanningByEventView
    {
        $userLocale = $query->event->getAvailableLocale($query->user->getLocale());

        return new UserBadgeAndPlanningByEventView(
            $this->queryBus->handle(new GetUserBadgeByEventQuery($query->event, $query->user)),
            $this->participantPlanningFormatter->formatPlanningFromUserAndEventWithUnallocated(
                $query->user,
                $query->event,
                $userLocale
            )
        );
    }
}
