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

class UpdateBlockHandler
{
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    public function handle(UpdateBlock $updateBlock)
    {
        $data = $updateBlock->sheet->getData();
        $data[$updateBlock->block] = $updateBlock->data;

        $updateBlock->sheet->setData($data);

        $this->sheetRepository->set($updateBlock->sheet);
    }
}
