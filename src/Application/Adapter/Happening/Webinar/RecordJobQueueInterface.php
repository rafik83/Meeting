<?php

namespace Proximum\Vimeet\Application\Adapter\Happening\Webinar;

use Proximum\Vimeet\Domain\Model\Happening;

interface RecordJobQueueInterface
{
    public function removeReconciliation(int $happeningId): void;

    public function prepareReconciliation(
        int $happeningId,
        \DateTimeInterface $reconciliationDate
    ): void;
}
