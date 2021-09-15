<?php

namespace Proximum\Vimeet\Application\Adapter\ThirdParty\Vianeo;

use Proximum\Vimeet\Domain\Model\Sheet;

interface VianeoApiCallJobQueueInterface
{
    /**
     * @param Sheet $sheet
     */
    public function createJob(Sheet $sheet);
}
