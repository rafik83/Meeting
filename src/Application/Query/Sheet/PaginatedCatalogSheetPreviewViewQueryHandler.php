<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Components\Catalog\GetViewedSheetsFromFilters;
use Proximum\Vimeet\Application\Components\Sheet\Nomenclature\NomenclatureItemsGetter;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewViewQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQueryHandler;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\User\Phone\ValidationRequiredChecker;

class PaginatedCatalogSheetPreviewViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ViewedSheetListViewQueryHandler */
    private $viewedSheetListViewQueryHandler;

    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /** @var SheetPreviewViewQueryHandler */
    private $sheetPreviewViewQueryHandler;

    /** @var MeetingRequestAccessChecker */
    private $meetingRequestAccessChecker;

    /** @var AnsweringMeetingRequestAccessChecker */
    private $answeringMeetingRequestAccessChecker;

    /** @var ValidationRequiredChecker */
    private $validationRequiredChecker;

    /** @var NomenclatureItemsGetter */
    private $nomenclatureItemsGetter;

    /** @var RequestRepositoryInterface */
    private $meetingRequestRepository;

    /** @var GetViewedSheetsFromFilters */
    private $getViewedSheetsFromFilters;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetPreviewViewQueryHandler $sheetPreviewViewQueryHandler,
        ViewedSheetListViewQueryHandler $viewedSheetListViewQueryHandler,
        MeetingRequestAccessChecker $meetingRequestAccessChecker,
        AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker,
        ValidationRequiredChecker $validationRequiredChecker,
        NomenclatureItemsGetter $nomenclatureItemsGetter,
        RequestRepositoryInterface $meetingRequestRepository,
        GetViewedSheetsFromFilters $getViewedSheetsFromFilters
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->sheetSearchAdapter = $sheetSearchAdapter;
        $this->sheetPreviewViewQueryHandler = $sheetPreviewViewQueryHandler;
        $this->viewedSheetListViewQueryHandler = $viewedSheetListViewQueryHandler;
        $this->meetingRequestAccessChecker = $meetingRequestAccessChecker;
        $this->answeringMeetingRequestAccessChecker = $answeringMeetingRequestAccessChecker;
        $this->validationRequiredChecker = $validationRequiredChecker;
        $this->nomenclatureItemsGetter = $nomenclatureItemsGetter;
        $this->meetingRequestRepository = $meetingRequestRepository;
        $this->getViewedSheetsFromFilters = $getViewedSheetsFromFilters;
    }

    public function handle(PaginatedCatalogSheetPreviewViewQuery $query): PaginatedResult
    {
        $paginatedResult = $this->sheetSearchAdapter->paginate(
            $query->event,
            $query->filters,
            $query->filters['orderBy'],
            $query->page,
            $query->limit,
            $query->locale,
            true,
            $this->nomenclatureItemsGetter->getNomenclatureItems(
                $query->viewer,
                $query->locale
            ),
            $query->availableSlotIds,
            $query->sheetsToExclude,
            $this->getViewedSheetsFromFilters->getFilteredByVisitSheetIds($query->filters, $query->user, $query->viewer)
        );

        $paginatedResult->results = $this->sheetRepository->findSheets($paginatedResult->results);
        $seenSheetIndexed = $this->viewedSheetListViewQueryHandler->handle(
            new ViewedSheetListViewQuery($query->user, $paginatedResult->results)
        );

        $isMeetingRequestClosed = !$this->meetingRequestAccessChecker->allowedToAccess($query->event);
        $isAnsweringMeetingRequestClosed = !$this->answeringMeetingRequestAccessChecker->allowedToAccess($query->event);

        $isPhoneValidationRequired = $this->isPhoneValidationRequiredForUser($query);

        $paginatedResult->results = array_map(
            function (Sheet $sheet) use (
                $query,
                $isMeetingRequestClosed,
                $isAnsweringMeetingRequestClosed,
                $seenSheetIndexed,
                $isPhoneValidationRequired
            ) {
                return $this
                    ->sheetPreviewViewQueryHandler
                    ->handle(
                        new SheetPreviewViewQuery(
                            $query->event,
                            $sheet,
                            $query->locale,
                            $query->viewer,
                            $query->user,
                            $isMeetingRequestClosed,
                            $isAnsweringMeetingRequestClosed,
                            isset($seenSheetIndexed[$sheet->getId()]),
                            $isPhoneValidationRequired,
                            $query->showCategory,
                            $this->isPriorityRequest(
                                $query,
                                $this->meetingRequestRepository
                                ->getRequestBetweenSheets($sheet, $query->viewer)
                            )
                        )
                    );
            },
            $paginatedResult->results
        );

        return $paginatedResult;
    }

    private function isPhoneValidationRequiredForUser(PaginatedCatalogSheetPreviewViewQuery $query): bool
    {
        return $this->validationRequiredChecker->handle($query->viewer, $query->user);
    }


    private function isPriorityRequest(PaginatedCatalogSheetPreviewViewQuery $query, ?Request $meetingRequest): bool
    {
        if (null === $meetingRequest) {
            return false;
        }

        return ($meetingRequest->isFromPriority() && $query->viewer === $meetingRequest->getFromSheet())
            || ($meetingRequest->isToPriority() && $query->viewer === $meetingRequest->getToSheet());
    }
}
