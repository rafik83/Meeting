<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Sheet\SheetListView;
use Proximum\Vimeet\Application\View\Sheet\SheetParticipantView;
use Proximum\Vimeet\Domain\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate\Impersonate;

class PaginatedSheetListViewQueryHandler
{
    /**
     * @var SheetSearchAdapterInterface
     */
    private $sheetSearchAdapter;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var Impersonate
     */
    private $impersonate;

    /**
     * @var TraceRepositoryInterface
     */
    private $traceRepository;

    /**
     * PaginatedSheetListViewQueryHandler constructor.
     *
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     * @param SheetInfoGuesser            $sheetInfoGuesser
     * @param ParticipantInfoGuesser      $participantInfoGuesser
     * @param Impersonate                 $impersonate
     * @param TraceRepositoryInterface    $traceRepository
     */
    public function __construct(
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        Impersonate $impersonate,
        TraceRepositoryInterface $traceRepository
    ) {
        $this->sheetSearchAdapter     = $sheetSearchAdapter;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->impersonate            = $impersonate;
        $this->traceRepository        = $traceRepository;
    }

    /**
     * @param PaginatedSheetListViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(PaginatedSheetListViewQuery $query)
    {
        $sheets = $this->sheetSearchAdapter->find($query->event, $query->filters, $query->page, $query->limit, $query->locale);

        $sheets->results = array_map(function (Sheet $sheet) use ($query) {
            return $this->createSheetListView($sheet, $query->locale, $query->admin);
        }, $sheets->results);

        return $sheets;
    }


    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param Admin  $admin
     *
     * @return SheetListView
     */
    public function createSheetListView(Sheet $sheet, $locale, Admin $admin)
    {
        $trace = null;

        if ($sheet->isAccepted()) {
            $trace = $this->traceRepository->getLastAcceptBySheet($sheet);
        } elseif ($sheet->isValidated()) {
            $trace = $this->traceRepository->getLastValidateBySheet($sheet);
        }

        return new SheetListView(
            $sheet->getId(),
            $this->sheetInfoGuesser->guessSheetInfo($sheet),
            $sheet->getState(),
            $sheet->isCompleted(),
            array_map(function (Category $category) use ($locale) { return $category->getTitle($locale); }, $sheet->getType()->getCategories()->toArray()),
            $sheet->getType()->getTitle($locale),
            new SheetParticipantView(
                $this->participantInfoGuesser->guessParticipantFirstName($sheet->getOwner()),
                $this->participantInfoGuesser->guessParticipantLastName($sheet->getOwner()),
                $sheet->getOwner()->getUser()->getEmail()
            ),
            $sheet->getFollower() ? $sheet->getFollower()->getDisplayName() : '',
            $sheet->getCreatedAt(),
            $sheet->getLastLoginAt(),
            $this->impersonate->getEncodedToken($admin, $sheet->getOwner()->getUser()),
            $trace
        );
    }
}
