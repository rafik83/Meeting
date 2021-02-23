<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ThirdParty\Vianeo;

use Proximum\Vimeet\Application\Adapter\ThirdParty\Vianeo\VianeoApiCallJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\LongJob;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Vianeo\VianeoApiCallCommand;

class VianeoApiCallJobQueue extends AbstractJobQueueAdapter implements VianeoApiCallJobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function createJob(Sheet $sheet)
    {
        // create a low priority job
        $job = new LongJob(VianeoApiCallCommand::NAME, [VianeoApiCallCommand::SHEET_ID => $sheet->getId()]);
        $this->setJob($job);
    }
}
