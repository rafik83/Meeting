<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Request;

use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\Request\MeetingRequestListView;
use Proximum\Vimeet\Application\View\Agenda\Admin\RequestView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class MeetingRequestListViewQueryHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var RequestViewQueryHandler
     */
    private $requestViewQueryHandler;

    /**
     * @param RequestRepositoryInterface $requestRepository
     * @param RequestViewQueryHandler    $requestViewQueryHandler
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        RequestViewQueryHandler $requestViewQueryHandler
    ) {
        $this->requestRepository       = $requestRepository;
        $this->requestViewQueryHandler = $requestViewQueryHandler;
    }

    /**
     * @param MeetingRequestListViewQuery $query
     *
     * @return MeetingRequestListView
     */
    public function handle(MeetingRequestListViewQuery $query)
    {
        $unassignedRequests = $this
            ->requestRepository
            ->getUnassignedRequestsBySheetAndEvent(
                $query->sheet,
                Request::STATE_APPROVED
            );

        $requests = [];

        foreach ($unassignedRequests as $request) {
            $requests[] = $this->requestViewQueryHandler->handle(
                new RequestViewQuery(
                    $request,
                    $query->sheet,
                    $query->locale
                )
            );
        }

        usort($requests, function (RequestView $first, RequestView $second) {
            return strcmp($first->sheetMetTitle, $second->sheetMetTitle);
        });

        return new MeetingRequestListView($query->sheet->getId(), $requests);
    }
}
