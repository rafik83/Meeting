<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Poll;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\UpdateStatus;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\UpdateStatusHandler;
use Proximum\Vimeet\Application\Exception\Happening\PollNotFoundException;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;

class UpdateStatusHandlerTest extends TestCase
{
    public function testPollNotFoundException(): void
    {
        // dependencies
        $pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $pollRepository->findById(314)->willReturn(null);

        // run
        $this->expectException(PollNotFoundException::class);

        $handler = new UpdateStatusHandler($pollRepository->reveal(), $notificationPublisher->reveal());
        $command = new UpdateStatus(314, Poll::STATUS_PUBLISHED);

        $handler->handle($command);
    }

    public function testInvalidArgumentException(): void
    {
        // fixtures
        $poll = $this->prophesize(Poll::class);

        // dependencies
        $pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $pollRepository->findById(314)->willReturn($poll->reveal());

        // run
        $this->expectException(InvalidArgumentException::class);

        $handler = new UpdateStatusHandler($pollRepository->reveal(), $notificationPublisher->reveal());
        $command = new UpdateStatus(314, 'foobar');

        $handler->handle($command);
    }

    public function testPublishedStatusHandle(): void
    {
        // fixtures
        $poll = $this->prophesize(Poll::class);
        $poll->setPublished()->shouldBeCalled();
        $poll->setHidden()->shouldNotBeCalled();

        // dependencies
        $pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $pollRepository->findById(314)->willReturn($poll->reveal());
        $pollRepository->update($poll->reveal())->shouldBeCalled();

        $notificationPublisher->publishNewPublishedPollNotification($poll->reveal())->shouldBeCalled();
        $notificationPublisher->publishHiddenPollNotification($poll->reveal())->shouldNotBeCalled();

        // run
        $handler = new UpdateStatusHandler($pollRepository->reveal(), $notificationPublisher->reveal());
        $command = new UpdateStatus(314, Poll::STATUS_PUBLISHED);

        $handler->handle($command);
    }

    public function testHiddenStatusHandle(): void
    {
        // fixtures
        $poll = $this->prophesize(Poll::class);
        $poll->setPublished()->shouldNotBeCalled();
        $poll->setHidden()->shouldBeCalled();

        // dependencies
        $pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $pollRepository->findById(314)->willReturn($poll->reveal());
        $pollRepository->update($poll->reveal())->shouldBeCalled();

        $notificationPublisher->publishNewPublishedPollNotification($poll->reveal())->shouldNotBeCalled();
        $notificationPublisher->publishHiddenPollNotification($poll->reveal())->shouldBeCalled();

        // run
        $handler = new UpdateStatusHandler($pollRepository->reveal(), $notificationPublisher->reveal());
        $command = new UpdateStatus(314, Poll::STATUS_HIDDEN);

        $handler->handle($command);
    }
}
