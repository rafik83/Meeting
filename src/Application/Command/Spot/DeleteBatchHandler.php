<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
*/

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Application\View\Spot\Batch\DeleteBatchView;
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
     * @return DeleteBatchView
     */
    public function handle(DeleteBatch $deleteBatch)
    {
        $spots = $this->spotRepository->getSpotsByIds($deleteBatch->ids);

        $deleteBatchView = new DeleteBatchView();
        
        foreach($spots as $spot) {
            

            if ($spot->hasSheets() !== false) {

                $deleteBatchView->spotsWithSheets[] = $spot->getId();
            }

            if ($this->spotRepository->hasMeeting($spot) !== false) {

                $deleteBatchView->spotsWithMeetings[] = $spot->getId();
            }

            $deleteBatchView->deletedSpots[] = $spot->getId();

        }

        $deleteBatchView = $this->getSpotToDelete($deleteBatchView);
        
        $this->spotRepository->removeBatchSpot($deleteBatchView->deletedSpots, $deleteBatch->event);

        return $deleteBatchView;
    }

    /**
     * @param DeleteBatchView $deleteBatchView
     *
     * @return DeleteBatchView
     */
    private function getSpotToDelete(DeleteBatchView $deleteBatchView)
    {
        $deleteBatchView->deletedSpots = array_diff(
            $deleteBatchView->deletedSpots,
           $deleteBatchView->spotsWithMeetings,
           $deleteBatchView->spotsWithSheets
        );

        return $deleteBatchView;
    }
}
