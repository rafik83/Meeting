<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Request;

use Proximum\Vimeet\Application\View\Agenda\Admin\Request\RequestSheetsView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class RequestSheetsViewQueryHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var RequestSheetViewQueryHandler
     */
    private $requestSheetViewQueryHandler;

    /**
     * RequestSheetsViewQueryHandler constructor.
     *
     * @param RequestRepositoryInterface   $requestRepository
     * @param RequestSheetViewQueryHandler $requestSheetViewQueryHandler
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        RequestSheetViewQueryHandler $requestSheetViewQueryHandler
    ) {
        $this->requestRepository            = $requestRepository;
        $this->requestSheetViewQueryHandler = $requestSheetViewQueryHandler;
    }

    /**
     * @param RequestSheetsViewQuery $query
     *
     * @return RequestSheetsView
     */
    public function handle(RequestSheetsViewQuery $query)
    {
        $request = $this->requestRepository->getRequest($query->meetingRequest);

        $fromSheetView = $this->requestSheetViewQueryHandler->handle(new RequestSheetViewQuery(
            $request->getFromSheet(),
            $query->meetingRequest,
            $query->locale
        ));

        $toSheetView = $this->requestSheetViewQueryHandler->handle(new RequestSheetViewQuery(
            $request->getToSheet(),
            $query->meetingRequest,
            $query->locale
        ));

        return new RequestSheetsView($fromSheetView, $toSheetView);
    }
}
