<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Application\Exception\Package\BoughtParticipantAlreadyAddedException;
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
        $this->sheetRepository    = $sheetRepository;
    }

    /**
     * @param UpdateStep $updateStep
     *
     * @throws BoughtParticipantAlreadyAddedException
     */
    public function handle(UpdateStep $updateStep)
    {
        $packageData = $updateStep->sheet->getPackageData();

        foreach ($updateStep->packageData as $elementKey => $element) {
            if (isset($element['participant'])) {
                if ($element['participant']) {
                    if (($updateStep->packageData[$elementKey]['participant_bought'] + $packageData[$updateStep->step][$elementKey]['participant_bought']) <= $updateStep->sheet->getType()->getMaxParticipant()) {
                        $updateStep->packageData[$elementKey]['participant_bought'] += $packageData[$updateStep->step][$elementKey]['participant_bought'];
                    }
                } else {
                    if (count($updateStep->sheet->getParticipants()) > $updateStep->sheet->getType()->getFreeParticipant())
                    {
                        throw new BoughtParticipantAlreadyAddedException();
                    }
                }
            }
        }

        $packageData[$updateStep->step] = $updateStep->packageData;

        $updateStep->sheet->setPackageData($packageData);

        $this->sheetRepository->set($updateStep->sheet);
    }
}
