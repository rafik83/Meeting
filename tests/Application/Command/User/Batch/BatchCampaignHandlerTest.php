<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Command\User\Batch\BatchCampaign;
use Proximum\Vimeet\Application\Command\User\Batch\BatchCampaignHandler;
use Proximum\Vimeet\Application\Command\User\Batch\BatchCampaignResult;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class BatchCampaignHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $campaign = $this->prophesize(Campaign::class);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findOneById(1)
            ->shouldBeCalled()
            ->willReturn($user1->reveal());
        $userRepository->findOneById(2)
            ->shouldBeCalled()
            ->willReturn($user2->reveal());
        $campaign->addUser($user1->reveal())->shouldBeCalled();
        $campaign->addUser($user2->reveal())->shouldBeCalled();

        $campaignRepository = $this->prophesize(CampaignRepositoryInterface::class);
        $campaignRepository->add($campaign->reveal())->shouldBeCalled();
        $date = new \DateTime();

        $handler = new BatchCampaignHandler($userRepository->reveal(), $campaignRepository->reveal(), $date);
        $result = $handler->handle(new BatchCampaign($event->reveal(), 'fr', [1, 2], 'test'));

        $this->assertEquals($result, new BatchCampaignResult($campaign->reveal()));
    }
}
