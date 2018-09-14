<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Service\SheetsGroup;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GroupNameResolver
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    public function resolve(Event $event, User $user, array $sheets = []): string
    {
        if (empty($sheets)) {
            $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

            if (empty($sheets)) {
                throw new SheetNotFoundException('Sheet not found.');
            }
        }

        foreach ($sheets as $sheet) {
            if (null !== $sheet->getGroup()) {
                return $sheet->getGroup()->getTitle();
            }
        }

        $sheet = reset($sheets);

        if (!$sheet instanceof Sheet) {
            throw new SheetNotFoundException('Sheet not found.');
        }

        return $sheet->getTitle();
    }
}
