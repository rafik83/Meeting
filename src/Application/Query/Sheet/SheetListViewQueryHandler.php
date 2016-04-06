<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\TraceRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate\Impersonate;

class SheetListViewQueryHandler
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
     * SheetListViewFactory constructor.
     *
     * @param SheetInfoGuesser            $sheetInfoGuesser
     * @param ParticipantInfoGuesser      $participantInfoGuesser
     * @param Impersonate                 $impersonate
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     * @param TraceRepositoryInterface    $traceRepository
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        Impersonate $impersonate,
        SheetSearchAdapterInterface $sheetSearchAdapter,
        TraceRepositoryInterface $traceRepository
    ) {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->impersonate            = $impersonate;
        $this->traceRepository        = $traceRepository;
        $this->sheetSearchAdapter     = $sheetSearchAdapter;
        $this->traceRepository        = $traceRepository;
    }

    /**
     * @param SheetListViewQuery $query
     *
     * @return SheetListView
     */
    public function handle(SheetListViewQuery $query)
    {
        $trace = null;

        if ($query->sheet->isAccepted()) {
            $trace = $this->traceRepository->getLastAcceptBySheet($query->sheet);
        } elseif ($query->sheet->isValidated()) {
            $trace = $this->traceRepository->getLastValidateBySheet($query->sheet);
        }

        return new SheetListView(
            $query->sheet->getId(),
            $this->sheetInfoGuesser->guessSheetInfo($query->sheet),
            $query->sheet->getState(),
            $query->sheet->isCompleted(),
            array_map(function (Category $category) use ($query) { return $category->getTitle($query->locale); }, $query->sheet->getType()->getCategories()->toArray()),
            $query->sheet->getType()->getTitle($query->locale),
            new SheetParticipantView(
                $this->participantInfoGuesser->guessParticipantFirstName($query->sheet->getOwner()),
                $this->participantInfoGuesser->guessParticipantLastName($query->sheet->getOwner()),
                $query->sheet->getOwner()->getUser()->getEmail()
            ),
            $query->sheet->getFollower() ? $query->sheet->getFollower()->getDisplayName() : '',
            $query->sheet->getCreatedAt(),
            $query->sheet->getLastLoginAt(),
            $this->impersonate->getEncodedToken($query->admin, $query->sheet->getOwner()->getUser()),
            $trace
        );
    }
}
