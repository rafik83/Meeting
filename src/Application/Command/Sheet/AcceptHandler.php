<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AcceptHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * ValidateHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Accept $accept
     */
    public function handle(Accept $accept)
    {
        if ($accept->sheet->isAccepted()) {
            return;
        }

        $this->sheetRepository->set($accept->sheet->markAsAccepted());
    }
}
