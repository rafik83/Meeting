<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Command\User\Batch\Batch;
use Proximum\Vimeet\Application\Command\User\Batch\BatchCampaign;
use Proximum\Vimeet\Application\Command\User\Batch\BatchCampaignHandler;
use Proximum\Vimeet\Application\Command\User\Batch\BatchCampaignResult;
use Proximum\Vimeet\Application\Command\User\Batch\BatchHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;

class BatchHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $campaign = $this->prophesize(Campaign::class);

        $batchCampaignHandler = $this->prophesize(BatchCampaignHandler::class);
        $batchCampaignHandler->handle(new BatchCampaign($event->reveal(), 'fr', [1, 2], 'test'))
            ->shouldBeCalled()
            ->willReturn(new BatchCampaignResult($campaign->reveal()));

        $batch = new Batch($event->reveal(), 'fr');
        $batch->campaignTitle = 'test';
        $batch->ids = [1, 2];
        $batch->locale = 'fr';

        $handler = new BatchHandler($batchCampaignHandler->reveal());
        $result = $handler->handle($batch);

        $this->assertEquals($result, new BatchCampaignResult($campaign->reveal()));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No handler found
     */
    public function testNoHandlerFound(): void
    {
        $event = $this->prophesize(Event::class);

        $batchCampaignHandler = $this->prophesize(BatchCampaignHandler::class);
        $batchCampaignHandler->handle(new BatchCampaign($event->reveal(), 'fr', [1, 2], 'test'))
            ->shouldNotBeCalled();

        $batch = new Batch($event->reveal(), 'fr');
        $batch->ids = [1, 2];
        $batch->locale = 'fr';

        $handler = new BatchHandler($batchCampaignHandler->reveal());
        $handler->handle($batch);
    }
}
