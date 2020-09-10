<?php

namespace Proximum\Vimeet\Application\Adapter\Happening\Webinar;

use DateTimeInterface;

interface RecordJobQueueInterface
{
    public function removeReconciliation(int $happeningId): void;

    public function prepareReconciliation(
        int $happeningId,
        DateTimeInterface $reconciliationDate
    ): void;
}
