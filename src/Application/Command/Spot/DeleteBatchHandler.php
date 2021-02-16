<?php

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
        $toDelete        = [];

        foreach ($spots as $spot) {
            if (false !== $spot->hasSheets()) {
                $deleteBatchView->addSpotWithSheets($spot);
            } elseif (false !== $this->spotRepository->hasMeeting($spot)) {
                $deleteBatchView->addSpotWithMeeting($spot);
            } else {
                $deleteBatchView->addDeletedSpot($spot);
                $toDelete[] = $spot;
            }
        }

        $this->spotRepository->removeBatchSpot($toDelete, $deleteBatch->event);

        return $deleteBatchView;
    }
}
