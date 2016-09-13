<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
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
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

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
     * @param SheetRepositoryInterface    $sheetRepository
     * @param TraceRepositoryInterface    $traceRepository
     */
    public function __construct(
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        Impersonate $impersonate,
        SheetRepositoryInterface $sheetRepository,
        TraceRepositoryInterface $traceRepository
    ) {
        $this->sheetSearchAdapter     = $sheetSearchAdapter;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->impersonate            = $impersonate;
        $this->sheetRepository        = $sheetRepository;
        $this->traceRepository        = $traceRepository;
    }

    /**
     * @param PaginatedSheetListViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(PaginatedSheetListViewQuery $query)
    {
        $sheets = $this->sheetSearchAdapter->find(
            $query->event,
            $query->filters,
            null,
            $query->page,
            $query->limit,
            $query->locale
        );

        $lastAccepts     = $this->traceRepository->getLastByTraceableObjectsAndAction($sheets->results, Trace::ACCEPT);
        $lastValidates   = $this->traceRepository->getLastByTraceableObjectsAndAction($sheets->results, Trace::VALIDATE);
        $sheets->results = $this->sheetRepository->findFullSheets($sheets->results);

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
        if (null === $sheet->getParticipantOwner()) {
            $firstName = $sheet->getOwner()->getAccount()->getFirstName();
            $lastName  = $sheet->getOwner()->getAccount()->getLastName();
        } else {
            $firstName = $this->participantInfoGuesser->guessParticipantFirstName($sheet->getParticipantOwner(), $locale);
            $lastName  = $this->participantInfoGuesser->guessParticipantLastName($sheet->getParticipantOwner(), $locale);
        }

        return new SheetListView(
            $sheet->getId(),
            $this->sheetInfoGuesser->guessSheetName($sheet, $locale),
            $sheet->getState(),
            $sheet->isCompleted(),
            $sheet->isEnabled(),
            $sheet->isInCatalog(),
            $sheet->getType()->getCategoriesTitles($locale),
            $sheet->getType()->getTitle($locale),
            new SheetParticipantView(
                $firstName,
                $lastName,
                $sheet->getOwner()->getEmail()
            ),
            $sheet->getFollower() ? $sheet->getFollower()->getDisplayName() : '',
            $sheet->getCreatedAt(),
            $sheet->getLastLoginAt(),
            $this->impersonate->getEncodedToken($admin, $sheet->getOwner()),
            $trace
        );
    }
}
