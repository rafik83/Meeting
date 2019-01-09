<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export;

use Proximum\Vimeet\Application\View\Rooming\ExportList\UserSheetView;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class UserViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->extraDataRepository = $extraDataRepository;
    }

    public function handle(UserViewQuery $query): UserSheetView
    {
        $comment = '';
        $testing = '';
        $extraDataComment = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $query->event,
            Type::ROOMING_COMMENT,
            $query->user
        );

        if ($extraDataComment instanceof ExtraData) {
            $comment = $extraDataComment->getValue();
        }

        $extraDataTesting = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $query->event,
            Type::ROOMING_TESTING,
            $query->user
        );

        if ($extraDataTesting instanceof ExtraData) {
            $testing = $extraDataTesting->getValue();
        }

        $sheetIds = [];
        $sheetTitles = [];
        $typeTitles = [];
        $spotReferences = [];

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent(
            $query->user,
            $query->event
        );

        foreach ($sheets as $sheet) {
            $sheetIds[] = $sheet->getId();
            $sheetTitles[] = $sheet->getTitle();

            $typeTitles[$sheet->getType()->getId()] = $sheet->getTypeTitle($query->locale);

            if ($sheet->getSpot() instanceof Spot) {
                $spotReferences[] = $sheet->getSpot()->getReference();
            }
        }


        $userSheetView = new UserSheetView(
            $query->user->getId(),
            $query->user->getAccount()->getGender(),
            $query->user->getAccount()->getFirstName(),
            $query->user->getAccount()->getLastName(),
            implode(',', $sheetIds),
            implode(',', $sheetTitles),
            implode(',', $typeTitles),
            implode(',', $spotReferences),
            $comment,
            $testing
        );

        return $userSheetView;
    }
}
