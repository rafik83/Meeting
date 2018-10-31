<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Domain\Model\Messaging\Campaign;

class BatchCampaignResult implements BatchResultInterface
{
    /** @var Campaign */
    public $campaign;

    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }
}
