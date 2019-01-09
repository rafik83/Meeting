<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Batch;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\User\Batch\Batch;
use Proximum\Vimeet\Application\Command\User\Batch\BatchCampaign;
use Proximum\Vimeet\Application\Command\User\Batch\BatchCampaignHandler;
use Proximum\Vimeet\Application\Command\User\Batch\BatchCampaignResult;
use Proximum\Vimeet\Application\Command\User\Batch\BatchExportFormTemplate;
use Proximum\Vimeet\Application\Command\User\Batch\BatchExportFormTemplateHandler;
use Proximum\Vimeet\Application\Command\User\Batch\BatchHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\User\Batch\BatchNoResult;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView\GetUserIdsByEvent;

class BatchHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event, $admin;

    /** @var ObjectProphecy */
    private $batchCampaignHandler, $batchExportFormTemplateHandler;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->batchCampaignHandler = $this->prophesize(BatchCampaignHandler::class);
        $this->batchExportFormTemplateHandler = $this->prophesize(BatchExportFormTemplateHandler::class);
    }

    public function testHandleCampaign(): void
    {
        $campaign = $this->prophesize(Campaign::class);

        $getUserIdsByEvent = $this->prophesize(GetUserIdsByEvent::class);
        $this->batchCampaignHandler->handle(new BatchCampaign($this->event->reveal(), 'fr', [1, 2], 'test'))
            ->shouldBeCalled()
            ->willReturn(new BatchCampaignResult($campaign->reveal()));

        $this->batchExportFormTemplateHandler
            ->handle(Argument::any())
            ->shouldNotBeCalled()
        ;

        $batch = new Batch($this->event->reveal(), $this->admin->reveal(), 'fr');
        $batch->campaignTitle = 'test';
        $batch->isCampaignCreation = true;
        $batch->ids = [1, 2];
        $batch->locale = 'fr';

        $handler = new BatchHandler(
            $this->batchCampaignHandler->reveal(),
            $this->batchExportFormTemplateHandler->reveal(),
            $getUserIdsByEvent->reveal()
        );
        $result = $handler->handle($batch);

        $this->assertEquals($result, new BatchCampaignResult($campaign->reveal()));
    }

    public function testHandleFormTemplate(): void
    {
        $formTemplate = $this->prophesize(FormTemplate::class);

        $getUserIdsByEvent = $this->prophesize(GetUserIdsByEvent::class);
        $this->batchCampaignHandler
            ->handle(Argument::any())
            ->shouldNotBeCalled()
        ;
        $this->batchExportFormTemplateHandler
            ->handle(
                new BatchExportFormTemplate(
                    $this->event->reveal(),
                    $formTemplate->reveal(),
                    $this->admin->reveal(),
                    'fr',
                    [1, 2]
                )
            )
            ->shouldBeCalled()
            ->willReturn(new BatchNoResult())
        ;

        $batch = new Batch($this->event->reveal(), $this->admin->reveal(), 'fr');
        $batch->isExportFormTemplate = true;
        $batch->formTemplate = $formTemplate->reveal();
        $batch->ids = [1, 2];
        $batch->locale = 'fr';

        $handler = new BatchHandler(
            $this->batchCampaignHandler->reveal(),
            $this->batchExportFormTemplateHandler->reveal(),
            $getUserIdsByEvent->reveal()
        );
        $result = $handler->handle($batch);

        $this->assertEquals($result, new BatchNoResult());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No handler found
     */
    public function testNoHandlerFound(): void
    {
        $getUserIdsByEvent = $this->prophesize(GetUserIdsByEvent::class);
        $this->batchCampaignHandler->handle(new BatchCampaign($this->event->reveal(), 'fr', [1, 2], 'test'))
            ->shouldNotBeCalled();

        $batch = new Batch($this->event->reveal(), $this->admin->reveal(), 'fr');
        $batch->ids = [1, 2];
        $batch->locale = 'fr';

        $handler = new BatchHandler(
            $this->batchCampaignHandler->reveal(),
            $this->batchExportFormTemplateHandler->reveal(),
            $getUserIdsByEvent->reveal()
        );
        $handler->handle($batch);
    }
}
