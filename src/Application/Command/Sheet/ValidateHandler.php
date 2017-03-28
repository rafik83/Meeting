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

class ValidateHandler
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
     * @param Validate $validate
     */
    public function handle(Validate $validate)
    {
        if ($validate->sheet->isValidated()) {
            return;
        }

        $this->sheetRepository->set($validate->sheet->markAsValidated());
    }
}
