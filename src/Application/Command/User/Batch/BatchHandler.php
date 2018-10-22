<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

class BatchHandler
{
    /** @var BatchCampaignHandler */
    private $batchCampaignHandler;

    public function __construct(BatchCampaignHandler $batchCampaignHandler)
    {
        $this->batchCampaignHandler = $batchCampaignHandler;
    }

    public function handle(Batch $batch): BatchResultInterface
    {
        if ($batch->campaignTitle) {
            return $this->batchCampaignHandler->handle(
                new BatchCampaign(
                    $batch->event,
                    $batch->locale,
                    $batch->ids,
                    $batch->campaignTitle
                )
            );
        }

        throw new \Exception('Invalid batch handler');
    }
}
