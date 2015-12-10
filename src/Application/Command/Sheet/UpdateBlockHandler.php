<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\BaseHandler;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateBlockHandler extends BaseHandler
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
     * @param UpdateBlock $updateBlock
     *
     * @throws RequiredDataEmptyException
     */
    public function handle(UpdateBlock $updateBlock)
    {
        $data = $updateBlock->sheet->getData();
        $data[$updateBlock->block] = $updateBlock->data;

        $sheetTemplate = $updateBlock->sheet->getType()->getSheetTemplate();

        // Check the constraint on the data (required) before
        $this->checkDataConstraint($updateBlock->data, $sheetTemplate[$updateBlock->block]['template']);

        $updateBlock->sheet->setData($data);

        $this->sheetRepository->set($updateBlock->sheet);
    }
}
