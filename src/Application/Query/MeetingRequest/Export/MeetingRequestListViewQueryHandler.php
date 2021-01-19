<?php

namespace Proximum\Vimeet\Application\Query\MeetingRequest\Export;

use Proximum\Vimeet\Application\View\MeetingRequest\Export\MeetingRequestListView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class MeetingRequestListViewQueryHandler
{
    const QUERY_LIMIT = 500;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingRequestViewQueryHandler */
    private $requestViewQueryHandler;

    /**
     * @param RequestRepositoryInterface     $requestRepository
     * @param MeetingRequestViewQueryHandler $requestViewQueryHandler
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MeetingRequestViewQueryHandler $requestViewQueryHandler
    ) {
        $this->requestRepository = $requestRepository;
        $this->requestViewQueryHandler = $requestViewQueryHandler;
    }

    /**
     * @param MeetingRequestListViewQuery $query
     *
     * @return MeetingRequestListView
     */
    public function handle(MeetingRequestListViewQuery $query): MeetingRequestListView
    {
        $locale   = $query->event->getFallback();
        $countRequest = $this->requestRepository->countAllByEvent($query->event);

        $requestViews = [];

        if (0 !== $countRequest) {
            $pages = ceil($countRequest / self::QUERY_LIMIT);

            for ($page = 1; $page <= $pages; ++$page) {
                $requests = $this->requestRepository->findByEventWithHydratationOfElement(
                    $query->event,
                    $page,
                    self::QUERY_LIMIT
                );
                $requests = $this->requestRepository->hydrateParticipants($requests);

                foreach ($requests as $request) {
                    $requestViews[] = $this->requestViewQueryHandler->handle(
                        new MeetingRequestViewQuery($request, $locale)
                    );
                }

                unset($requests);
            }
        }

        return new MeetingRequestListView(
            $requestViews,
            $query->event->getTimeZone(),
            $locale
        );
    }
}
