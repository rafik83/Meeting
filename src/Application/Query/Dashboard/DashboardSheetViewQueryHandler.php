<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\View\Dashboard\DashboardSheetTypeView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardSheetView;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class DashboardSheetViewQueryHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * DashboardSheetViewQueryHandler constructor.
     *
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
    public function handle(DashboardSheetViewQuery $query)
    {
        $totalEnabledSheets = $this->sheetRepository->countEnabledSheetsByEvent($query->event);
        $totalParticipants  = $this->participantRepository->countByEnabledSheet($query->event);
        $sheetsType         = $this->sheetRepository->countEnabledSheetsTypeByEvent($query->event, $query->locale);
        
        $sheetsTypeView     = [];

        foreach ($sheetsType as $sheetType) {
            $sheetsTypeView[] = new DashboardSheetTypeView($sheetType['id'], $sheetType['total'], $sheetType['title']);
        }

        return new DashboardSheetView(
            $totalEnabledSheets,
            $totalParticipants,
            $sheetsTypeView
        );
    }
}
