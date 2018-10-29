<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Query\User\UserEventListViews\GetUserIdsByEventQuery;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView\GetUserIdsByEvent;

class BatchHandler
{
    /** @var BatchCampaignHandler */
    private $batchCampaignHandler;

    /** @var GetUserIdsByEvent */
    private $getUserIdsByEvent;

    public function __construct(
        BatchCampaignHandler $batchCampaignHandler,
        GetUserIdsByEvent $getUserIdsByEvent
    ) {
        $this->batchCampaignHandler = $batchCampaignHandler;
        $this->getUserIdsByEvent = $getUserIdsByEvent;
    }

    public function handle(Batch $batch): BatchResultInterface
    {
        if (Batch::SELECTION_TYPE_ALL === $batch->selectionType) {
            $batch->ids = $this->getUserIdsByEvent->handle(
                new GetUserIdsByEventQuery($batch->event, $batch->locale, $batch->condition)
            );
        }

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

        throw new \Exception('No handler found');
    }
}
