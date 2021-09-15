<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateDayListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\MeetingSolutionView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionListView;
use Proximum\Vimeet\Domain\Repository\Analytic\MeetingSolutionRepositoryInterface;

class MeetingSolutionListQueryHandler
{
    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var MeetingSolutionRepositoryInterface */
    private $meetingSolutionRepository;

    /**
     * @param MeetingSolutionRepositoryInterface $meetingSolutionRepository
     * @param SerializerAdapterInterface         $serializer
     */
    public function __construct(
        MeetingSolutionRepositoryInterface $meetingSolutionRepository,
        SerializerAdapterInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->meetingSolutionRepository = $meetingSolutionRepository;
    }

    /**
     * @param MeetingSolutionListQuery $query
     *
     * @return MeetingSolutionView[]
     */
    public function handle(MeetingSolutionListQuery $query): array
    {
        $meetingSolutionView = [];

        $meetingSolutions = $this->meetingSolutionRepository->findByEvent($query->event);

        foreach ($meetingSolutions as $meetingSolution) {
            $meetingSolutionView[] = new MeetingSolutionView(
                $meetingSolution->getMeetings(),
                $meetingSolution->getRequests(),
                $meetingSolution->getFillingRate(),
                $this->serializer->deserialize(
                    $meetingSolution->getSheetSatisfaction(),
                    SheetSatisfactionListView::class,
                    'json'
                ),
                $this->serializer->deserialize(
                    $meetingSolution->getSpotSatisfaction(),
                    SpotSatisfactionListView::class,
                    'json'
                ),
                $this->serializer->deserialize(
                    $meetingSolution->getSpotFillingGraph(),
                    SpotFillingRateDayListView::class,
                    'json'
                ),
                $meetingSolution->getCreatedAt()
            );
        }

        return $meetingSolutionView;
    }
}
