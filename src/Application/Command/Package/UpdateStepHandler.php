<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateStepHandler
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
     * @param UpdateStep $updateStep
     */
    public function handle(UpdateStep $updateStep)
    {
        $packageData = $updateStep->sheet->getPackageData();
        $packageData[$updateStep->step] = $updateStep->packageData;

        $updateStep->sheet->setPackageData($packageData);

        $this->sheetRepository->set($updateStep->sheet);
    }
}
