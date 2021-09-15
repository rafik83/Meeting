<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class SpotSatisfactionListQueryHandler
{
    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var SpotSatisfactionViewQueryHandler */
    private $spotSatisfactionViewQueryHandler;

    /**
     * @param SpotRepositoryInterface          $spotRepository
     * @param MeetingRepositoryInterface       $meetingRepository
     * @param SpotSatisfactionViewQueryHandler $spotSatisfactionViewQueryHandler
     */
    public function __construct(
        SpotRepositoryInterface $spotRepository,
        MeetingRepositoryInterface $meetingRepository,
        SpotSatisfactionViewQueryHandler $spotSatisfactionViewQueryHandler
    ) {
        $this->spotRepository = $spotRepository;
        $this->meetingRepository = $meetingRepository;
        $this->spotSatisfactionViewQueryHandler = $spotSatisfactionViewQueryHandler;
    }

    /**
     * @param SpotSatisfactionListQuery $query
     *
     * @return SpotSatisfactionView[]
     */
    public function handle(SpotSatisfactionListQuery $query): array
    {
        $spots = $this->spotRepository->getActiveByEvent($query->event);
        $meetingBySpot = $this->meetingRepository->countMeetingsBySpots($spots);
        $numberOfAvailableSlots = count($query->slots);

        $spotSatisfactionView = [];

        foreach ($spots as $spot) {
            $spotSatisfactionView[] = $this->spotSatisfactionViewQueryHandler->handle(
                new SpotSatisfactionViewQuery(
                    $spot,
                    $meetingBySpot[$spot->getId()]['countMeetings'] ?? 0,
                    $numberOfAvailableSlots
                )
            );
        }

        return $spotSatisfactionView;
    }
}
