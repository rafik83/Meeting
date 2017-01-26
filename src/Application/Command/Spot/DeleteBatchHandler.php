<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
*/

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class DeleteBatchHandler
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * DeleteBatchHandler constructor.
     *
     * @param SpotRepositoryInterface $spotRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository)
    {
        $this->spotRepository = $spotRepository;
    }

    /**
     * @param DeleteBatch $deleteBatch
     *
     * @return array
     */
    public function handle(DeleteBatch $deleteBatch)
    {
        $spots = $this->spotRepository->getSpotsByIds($deleteBatch->ids);
        $spotToAvoid = [
            DeleteBatch::SPOT_WITH_SHEETS  => [],
            DeleteBatch::SPOT_WITH_MEETING => []
        ];
        $spotToDelete = [];
        
        foreach($spots as $spot) {
            

            if ($spot->hasSheets() !== false) {

                $spotToAvoid[DeleteBatch::SPOT_WITH_SHEETS][] = $spot;
                continue;
            }

            if ($this->spotRepository->hasMeeting($spot) !== false) {

                $spotToAvoid[DeleteBatch::SPOT_WITH_MEETING][] = $spot;
                continue;
            }

            $spotToDelete[] = $spot;

        }

        $spotToDelete = $this->getSpotToDelete($spotToDelete, $spotToAvoid);
        
        $this->spotRepository->removeBatchSpot($spotToDelete, $deleteBatch->event);

        return $spotToAvoid;
    }

    /**
     * @param $spotToDelete[]
     * @param $avoidedSpot[]
     *
     * @return array
     */
    private function getSpotToDelete(array $spotToDelete = [], array $avoidedSpot = [])
    {
        return array_diff($spotToDelete, $avoidedSpot[DeleteBatch::SPOT_WITH_MEETING], $avoidedSpot[DeleteBatch::SPOT_WITH_SHEETS]);
    }

}
