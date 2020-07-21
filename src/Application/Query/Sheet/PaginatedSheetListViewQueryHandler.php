<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Sheet\SheetListView;
use Proximum\Vimeet\Application\View\Sheet\SheetParticipantView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Trace\TraceableName;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate\Impersonate;

class PaginatedSheetListViewQueryHandler
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var Impersonate */
    private $impersonate;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TraceRepositoryInterface */
    private $traceRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /**
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     * @param SheetInfoGuesser            $sheetInfoGuesser
     * @param ParticipantInfoGuesser      $participantInfoGuesser
     * @param Impersonate                 $impersonate
     * @param SheetRepositoryInterface    $sheetRepository
     * @param TraceRepositoryInterface    $traceRepository
     * @param TypeRepositoryInterface     $typeRepository
     */
    public function __construct(
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        Impersonate $impersonate,
        SheetRepositoryInterface $sheetRepository,
        TraceRepositoryInterface $traceRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->sheetSearchAdapter     = $sheetSearchAdapter;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->impersonate            = $impersonate;
        $this->sheetRepository        = $sheetRepository;
        $this->traceRepository        = $traceRepository;
        $this->typeRepository         = $typeRepository;
    }

    /**
     * @param PaginatedSheetListViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(PaginatedSheetListViewQuery $query): PaginatedResult
    {
        if ($query->admin->hasAllowedTypes() && empty($query->filters['type'])) {
            $query->filters['type'] = $this->typeRepository->getAllowedTypesByEvent($query->admin, $query->event);
        }

        $sheets = $this->sheetSearchAdapter->paginate(
            $query->event,
            $query->filters,
            isset($query->filters['orderBy']) ? $query->filters['orderBy'] : null,
            $query->page,
            $query->limit,
            $query->locale,
            false,
            [],
            [],
            [],
            $query->condition
        );

        $sheets->results = $this->sheetRepository->findFullSheets($sheets->results);
        $lastAccepts = $this->traceRepository->getLastByTraceableObjectsAndAction(
            $sheets->results,
            TraceableName::SHEET_TRACEABLE_NAME,
            Trace::ACCEPT
        );

        $lastValidates = $this->traceRepository->getLastByTraceableObjectsAndAction(
            $sheets->results,
            TraceableName::SHEET_TRACEABLE_NAME,
            Trace::VALIDATE
        );

        $sheets->results = array_map(function (Sheet $sheet) use ($query, $lastAccepts, $lastValidates) {
            if ($sheet->isAccepted()) {
                $trace = Trace::find($lastAccepts, $sheet);
            } elseif ($sheet->isValidated()) {
                $trace = Trace::find($lastValidates, $sheet);
            } else {
                $trace = null;
            }

            return $this->createSheetListView($sheet, $query->locale, $query->admin, $trace);
        }, $sheets->results);

        return $sheets;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param Admin  $admin
     * @param Trace  $trace
     *
     * @return SheetListView
     */
    private function createSheetListView(Sheet $sheet, $locale, Admin $admin, Trace $trace = null)
    {
        $linkedSheetsTitle = [];
        $linkedSheets = $sheet->getLinkedSheets();

        if (null !== $linkedSheets) {
            foreach ($linkedSheets->getSheets() as $linkedSheet) {
                if ($linkedSheet !== $sheet) {
                    $linkedSheetsTitle[] = $linkedSheet->getTitle();
                }
            }
        }

        if (null === $sheet->getParticipantOwner()) {
            $firstName = $sheet->getOwner()->getAccount()->getFirstName();
            $lastName  = $sheet->getOwner()->getAccount()->getLastName();
        } else {
            $firstName = $this->participantInfoGuesser->guessParticipantFirstName($sheet->getParticipantOwner(), $locale);
            $lastName  = $this->participantInfoGuesser->guessParticipantLastName($sheet->getParticipantOwner(), $locale);
        }

        return new SheetListView(
            $sheet->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale),
            $sheet->getState(),
            $sheet->getValidationState(),
            $sheet->getCompleteness(),
            $sheet->isEnabled(),
            $sheet->isInInternalCatalog(),
            $sheet->attend(),
            $sheet->getType()->getCategoriesTitles($locale),
            $linkedSheetsTitle,
            $sheet->getType()->getTitle($locale),
            new SheetParticipantView(
                $firstName,
                $lastName,
                $sheet->getOwner()->getEmail()
            ),
            null !== $sheet->getFollower() ? $sheet->getFollower()->getDisplayName() : '',
            $sheet->getCommercialStatus(),
            $sheet->getReminderDate(),
            $sheet->getCreatedAt(),
            $sheet->getLastLoginAt(),
            $this->impersonate->getEncodedToken($admin, $sheet->getOwner()),
            $sheet->countParticipants(),
            null !== $sheet->getGroup(),
            null !== $sheet->getGroup() ? $sheet->getGroup()->getTitle() : null,
            null !== $sheet->getSpot() ? $sheet->getSpot()->getReference() : null,
            $trace
        );
    }
}
