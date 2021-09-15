<?php

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class DisableBatchHandler
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
     * @param DisableBatch $disableBatch
     */
    public function handle(DisableBatch $disableBatch)
    {
        $this->spotRepository->disableBatchSpot($disableBatch->ids, $disableBatch->event);
    }
}
