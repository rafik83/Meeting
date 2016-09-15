<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class ChangeTypeHandler
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
     * @param ChangeType $changeType
     */
    public function handle(ChangeType $changeType)
    {
        if (null === $changeType->type) {
            return;
        }

        $changeType->sheet->updateType($changeType->type);
        $this->sheetRepository->set($changeType->sheet);
    }
}
