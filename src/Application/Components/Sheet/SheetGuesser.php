<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\View\EventView;

class SheetGuesser
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param User $user
     * @param EventView $eventView
     * @param string $locale
     * @return Sheet
     * @throws \Exception
     */
    public function getUserSheet(User $user, EventView $eventView, $locale)
    {
        $sheets = $this->sheetRepository->getSheetByUserAndEvent($user, $eventView);

        if (empty($sheets)) {
            throw new \Exception('Sheet not found.');
        }

        $sheet = $sheets[array_keys($sheets)[0]];

        if (!$sheet instanceof Sheet) {
            throw new \Exception('Sheet not found.');
        }

        if ($sheet->getEvent()->getId() !== $eventView->getId()) {
            throw new \Exception('Sheet not found');
        }

        if (!$sheet->hasUser($user)) {
            throw new \Exception('No participant for this user is attached on this sheet');
        }

        if (!$eventView->hasLocale($locale)) {
            throw new \Exception('Locale not available for this event.');
        }

        return $sheet;
    }
}
