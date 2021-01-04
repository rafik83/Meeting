<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet;

use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetSatisfactionListQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var SheetSatisfactionViewQueryHandler */
    private $sheetSatisfactionViewQueryHandler;

    /**
     * @param SheetRepositoryInterface          $sheetRepository
     * @param RequestRepositoryInterface        $requestRepository
     * @param MeetingRepositoryInterface        $meetingRepository
     * @param SheetSatisfactionViewQueryHandler $sheetSatisfactionViewQueryHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingRepositoryInterface $meetingRepository,
        SheetSatisfactionViewQueryHandler $sheetSatisfactionViewQueryHandler
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->requestRepository = $requestRepository;
        $this->meetingRepository = $meetingRepository;
        $this->sheetSatisfactionViewQueryHandler = $sheetSatisfactionViewQueryHandler;
    }

    /**
     * @param SheetSatisfactionListQuery $query
     *
     * @return array
     */
    public function handle(SheetSatisfactionListQuery $query): array
    {
        $sheets = $this->sheetRepository->getSheetsInCatalogByEvent($query->event);
        $meetingCount = $this->meetingRepository->countMeetingBySheets($query->event, $sheets);
        $requestCount = $this->requestRepository->countApprovedRequestBySheets($query->event, $sheets);

        $views = [];

        foreach ($sheets as $sheet) {
            $sheetSatisfactionView = $this->sheetSatisfactionViewQueryHandler->handle(
                new SheetSatisfactionViewQuery(
                    $sheet,
                    $requestCount[$sheet->getId()]['countRequest'] ?? 0,
                    $meetingCount[$sheet->getId()]['countMeetings'] ?? 0,
                    $query->event->getFallback()
                )
            );

            $views[] = $sheetSatisfactionView;
        }

        return $views;
    }
}
