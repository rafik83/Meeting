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
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetListViewFactory
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * SheetOwnerView constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetInfoGuesser         $sheetInfoGuesser
     * @param ParticipantInfoGuesser   $participantInfoGuesser
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, SheetInfoGuesser $sheetInfoGuesser, ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->sheetRepository        = $sheetRepository;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param Event  $event
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     *
     * @return PaginatedResult
     */
    public function paginate(Event $event, $page, $limit, $locale)
    {
        $sheets          = $this->sheetRepository->paginate($page, $limit, $event, $locale);
        $sheets->results = array_map(function (Sheet $sheet) use ($locale) {
            return $this->createFromSheet($sheet, $locale);
        }, $sheets->results);

        return $sheets;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return SheetListView
     */
    public function createFromSheet(Sheet $sheet, $locale)
    {
        return new SheetListView(
            $sheet->getId(),
            $this->sheetInfoGuesser->guessSheetInfo($sheet),
            array_map(function (Category $category) use ($locale) { return $category->getTitle($locale); }, $sheet->getType()->getCategories()->toArray()),
            $sheet->getType()->getTitle($locale),
            new SheetParticipantView(
                $this->participantInfoGuesser->guessParticipantFirstName($sheet->getOwner()),
                $this->participantInfoGuesser->guessParticipantLastName($sheet->getOwner()),
                $sheet->getOwner()->getUser()->getEmail()
            ),
            $sheet->getLastLoginAt(),
            $sheet->getCreatedAt()
        );
    }
}
