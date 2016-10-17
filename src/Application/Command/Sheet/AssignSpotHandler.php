<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Exception\Spot\SpotNotFoundException;
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
     * @throws SpotNotFoundException
     */
    public function handle(AssignSpot $assignSpot)
    {
        if ($assignSpot->spotCode === null || $assignSpot->spotCode === '') {
            $assignSpot->sheet->removeSpot(null);
            $numberOfSheet = 0;
        } else {
            $spot = $this->spotRepository->findByReference($assignSpot->event, $assignSpot->spotCode);

            if (null === $spot) {
                throw new SpotNotFoundException();
            }

            $assignSpot->sheet->setSpot($spot);
            $numberOfSheet = $spot->countSheets();
        }

        $this->sheetRepository->set($assignSpot->sheet);

        return new AssignSpotResult($numberOfSheet);
    }
}
