<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Query\User\UserEventListViews\GetUserIdsByEventQuery;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView\GetUserIdsByEvent;

class BatchHandler
{
    /** @var BatchCampaignHandler */
    private $batchCampaignHandler;

    /** @var GetUserIdsByEvent */
    private $getUserIdsByEvent;

    /** @var BatchExportFormTemplateHandler */
    private $batchExportFormTemplateHandler;

    public function __construct(
        BatchCampaignHandler $batchCampaignHandler,
        BatchExportFormTemplateHandler $batchExportFormTemplateHandler,
        GetUserIdsByEvent $getUserIdsByEvent
    ) {
        $this->batchCampaignHandler = $batchCampaignHandler;
        $this->getUserIdsByEvent = $getUserIdsByEvent;
        $this->batchExportFormTemplateHandler = $batchExportFormTemplateHandler;
    }

    public function handle(Batch $batch): BatchResultInterface
    {
        if (Batch::SELECTION_TYPE_ALL === $batch->selectionType) {
            $batch->ids = $this->getUserIdsByEvent->handle(
                new GetUserIdsByEventQuery($batch->event, $batch->locale, $batch->condition)
            );
        }

        if ($batch->isCampaignCreation && $batch->campaignTitle) {
            return $this->batchCampaignHandler->handle(
                new BatchCampaign(
                    $batch->event,
                    $batch->locale,
                    $batch->ids,
                    $batch->campaignTitle
                )
            );
        }

        if ($batch->isExportFormTemplate && $batch->formTemplate instanceof FormTemplate) {
            return $this->batchExportFormTemplateHandler->handle(
                new BatchExportFormTemplate(
                    $batch->event,
                    $batch->formTemplate,
                    $batch->admin,
                    $batch->locale,
                    $batch->ids
                )
            );
        }

        throw new \Exception('No handler found');
    }
}
