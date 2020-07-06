<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\Happening\Webinar;

use Proximum\Vimeet\Application\Adapter\Happening\Webinar\RecordJobQueueInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\AbstractJobQueueAdapter;

class RecordJobQueueAdapter extends AbstractJobQueueAdapter implements RecordJobQueueInterface
{
    public function removeReconciliation(int $happeningId): void
    {
        // TODO: Implement removeReconciliation() method.
    }

    public function prepareReconciliation(int $happeningId, \DateTimeInterface $reconciliationDate): void
    {
        // TODO: Implement prepareReconciliation() method.
    }
}
