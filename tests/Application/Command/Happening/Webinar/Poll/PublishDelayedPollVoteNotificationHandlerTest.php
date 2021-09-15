<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Poll;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\PublishDelayedPollVoteNotificationHandler;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\PublishDelayedPollVoteNotificationMessage;
use Proximum\Vimeet\Application\Exception\Happening\PollNotFoundException;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;

class PublishDelayedPollVoteNotificationHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // fixtures
        $poll = $this->prophesize(Poll::class);
        $poll->getId()->willReturn(314);

        // dependencies
        $pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $pollRepository->findById(314)->shouldBeCalled()->willReturn($poll->reveal());
        $notificationPublisher->publishedPollVoteNotification($poll->reveal())->shouldBeCalled();

        // run
        $message = new PublishDelayedPollVoteNotificationMessage($poll->reveal());
        $handler = new PublishDelayedPollVoteNotificationHandler(
            $notificationPublisher->reveal(),
            $pollRepository->reveal()
        );

        $handler->handle($message);
    }

    public function testPollNotFoundException(): void
    {
        // fixtures
        $poll = $this->prophesize(Poll::class);
        $poll->getId()->willReturn(314);

        // dependencies
        $pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $pollRepository->findById(314)->shouldBeCalled()->willReturn(null);
        $notificationPublisher->publishedPollVoteNotification($poll->reveal())->shouldNotBeCalled();

        $this->expectException(PollNotFoundException::class);

        // run
        $message = new PublishDelayedPollVoteNotificationMessage($poll->reveal());
        $handler = new PublishDelayedPollVoteNotificationHandler(
            $notificationPublisher->reveal(),
            $pollRepository->reveal()
        );

        $handler->handle($message);
    }
}
