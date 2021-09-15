<?php

namespace Proximum\Vimeet\Tests\Application\Command\Messaging\Campaign;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\SelectMessage;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\SelectMessageHandler;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SelectMessageHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Input context

        $event     = EventFactory::createEvent();
        $createdAt = new \DateTime();
        $message   = new Message($event, $createdAt, 'test', 'test subject', 'test content');

        $campaign  = new Campaign($event, 'amazing campaign', [], $createdAt);

        $expectedCampaign = new Campaign($event, 'amazing campaign', [], $createdAt);
        $expectedCampaign->setMessage($message);

        // Mock

        $campaignRepository = $this->prophesize(CampaignRepositoryInterface::class);

        // Scenario

        $campaignRepository->set($expectedCampaign)->shouldBeCalled();

        // Command

        $command = new SelectMessage($campaign);
        $command->message = $message;

        // Handler

        $handler = new SelectMessageHandler($campaignRepository->reveal());

        $handler->handle($command);
    }
}
