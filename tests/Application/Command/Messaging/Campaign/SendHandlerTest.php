<?php

namespace Proximum\Vimeet\Tests\Application\Command\Messaging\Campaign;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\Send;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\SendHandler;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SendHandlerTest extends TestCase
{
    public function testSend()
    {
        $event     = EventFactory::createEvent();
        $createdAt = new \DateTime();
        $datetime  = new \DateTime();
        $receiver  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet     = SheetFactory::create($event, $receiver);
        $message   = new Message($event, $createdAt, 'test', 'test subject', 'test content');

        $campaign  = new Campaign($event, 'amazing campaign', [], $createdAt);
        $campaign->setMessage($message);
        $campaign->addRecipient(Campaign::RECIPIENT_PARTICIPANTS);
        $campaign->addSheet($sheet);

        $expectedCampaign = new Campaign($event, 'amazing campaign', [], $createdAt);
        $expectedCampaign->setMessage($message);
        $expectedCampaign->addRecipient(Campaign::RECIPIENT_PARTICIPANTS);
        $expectedCampaign->addSheet($sheet);
        $expectedCampaign->markAsSent($datetime);

        // Mock
        $campaignRepository = $this->prophesize(CampaignRepositoryInterface::class);
        $campaignRepository->set($expectedCampaign)->shouldBeCalled();
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $handler = new SendHandler(
            $campaignRepository->reveal(),
            $jobQueue->reveal(),
            $datetime
        );

        $handler->handle(new Send($campaign));
        $this->assertInstanceOf(\DateTimeInterface::class, $campaign->getSentAt());
    }
}
