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
use Proximum\Vimeet\Bundle\AppBundle\Security\Impersonate\Impersonate;
use Proximum\Vimeet\Domain\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetListViewFactory
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
     * SheetOwnerView constructor.
     *
     * @param SheetInfoGuesser            $sheetInfoGuesser
     * @param ParticipantInfoGuesser      $participantInfoGuesser
     * @param Impersonate                 $impersonate
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        Impersonate $impersonate,
        SheetSearchAdapterInterface $sheetSearchAdapter
    ) {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->impersonate            = $impersonate;
        $this->sheetSearchAdapter     = $sheetSearchAdapter;
    }

    /**
     * @param Event  $event
     * @param array  $filters
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     * @param Admin  $admin
     *
     * @return PaginatedResult
     */
    public function paginate(Event $event, array $filters, $page, $limit, $locale, Admin $admin)
    {
        $sheets = $this->sheetSearchAdapter->find($event, $filters, $page, $limit, $locale);

        $sheets->results = array_map(function (Sheet $sheet) use ($locale, $admin) {
            return $this->createFromSheet($sheet, $locale, $admin);
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
    public function createFromSheet(Sheet $sheet, $locale, Admin $admin)
    {
        return new SheetListView(
            $sheet->getId(),
            $this->sheetInfoGuesser->guessSheetInfo($sheet),
            $sheet->getState(),
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
            $this->impersonate->getEncodedToken($admin, $sheet->getOwner()->getUser())
        );
    }
}
