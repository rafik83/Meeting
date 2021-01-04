<?php

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\View\Dashboard\DashboardSheetTypeView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardSheetView;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class DashboardSheetViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param DashboardSheetViewQuery $query
     *
     * @return DashboardSheetView
     */
    public function handle(DashboardSheetViewQuery $query): DashboardSheetView
    {
        $totalEnabledSheets = $this->sheetRepository->countEnabledSheetsByEvent($query->event);
        $totalParticipants  = $this->participantRepository->countByEnabledSheet($query->event);
        $sheetsType         = $this->sheetRepository->countEnabledSheetsTypeByEvent($query->event, $query->locale);
        $participantsType   = $this->participantRepository->countByTypeWithEnabledSheet($query->event, $query->locale);

        $sheetsTypeView       = [];
        $participantsTypeView = [];

        foreach ($sheetsType as $sheetType) {
            $sheetsTypeView[$sheetType['id']] = new DashboardSheetTypeView($sheetType['id'], $sheetType['total'], $sheetType['title']);
        }

        foreach ($participantsType as $participantType) {
            $participantsTypeView[$participantType['id']] = new DashboardSheetTypeView($participantType['id'], $participantType['total'], $participantType['title']);
        }

        return new DashboardSheetView(
            $totalEnabledSheets,
            $totalParticipants,
            $sheetsTypeView,
            $participantsTypeView
        );
    }
}
