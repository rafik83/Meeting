<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Exception\Spot\SpotNotActiveException;
use Proximum\Vimeet\Application\Exception\Spot\SpotNotFoundException;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class AssignSpotHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param SpotRepositoryInterface  $spotRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, SpotRepositoryInterface $spotRepository)
    {
        $this->sheetRepository = $sheetRepository;
        $this->spotRepository = $spotRepository;
    }

    /**
     * @param AssignSpot $assignSpot
     *
     * @return AssignSpotResult
     *
     * @throws SpotNotActiveException
     * @throws SpotNotFoundException
     */
    public function handle(AssignSpot $assignSpot)
    {
        $oldSpot = null;

        if ($assignSpot->spotCode === null || $assignSpot->spotCode === '') {
            $oldSpot = $assignSpot->sheet->getSpot();

            $assignSpot->sheet->removeSpot();
            $numberOfSheet = 0;

            // Call the sheetRepository to detach the sheet from the spot
            $this->sheetRepository->set($assignSpot->sheet);

            if ($oldSpot instanceof Spot) {
                $oldSpot->removeSheet($assignSpot->sheet);

                if ($oldSpot->countSheets() === 0) {
                    $oldSpot->setPriority(12);
                }

                $this->spotRepository->set($oldSpot);
            }
        } else {
            $spot = $this->spotRepository->findByReference($assignSpot->event, $assignSpot->spotCode);

            if (null === $spot) {
                throw new SpotNotFoundException();
            }

            if (!$spot->isActive()) {
                throw new SpotNotActiveException();
            }

            if ($assignSpot->sheet->getSpot() !== $spot) {
                $oldSpot = $assignSpot->sheet->getSpot();

                if ($oldSpot instanceof Spot) {
                    $oldSpot->removeSheet($assignSpot->sheet);

                    if ($oldSpot->countSheets() === 0) {
                       $oldSpot->setPriority(12);
                    }

                    $this->spotRepository->set($oldSpot);
                }
            }

            $assignSpot->sheet->setSpot($spot);
            $spot->addSheet($assignSpot->sheet);

            // A call to the sheetRepository is not necessary as the spot will carry the link with the sheet
            $this->spotRepository->set($spot->setPriority(8));

            $numberOfSheet = $spot->countSheets();
        }

        return new AssignSpotResult($numberOfSheet);
    }
}
