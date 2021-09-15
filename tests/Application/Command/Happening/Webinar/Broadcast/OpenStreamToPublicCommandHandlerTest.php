<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Broadcast;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\HappeningBroadcastForHappeningNotFoundException;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\OpenStreamToPublicCommand;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\OpenStreamToPublicCommandHandler;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\StartBroadcast;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\StartBroadcastHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class OpenStreamToPublicCommandHandlerTest extends TestCase
{
    /**
     * this case shouldn't happen
     */
    public function testHLSButNoHappeningBroadcast(): void
    {
        // data
        $happening = $this->prophesize(Happening::class);
        $happening->openStreamToPublic()->shouldBeCalled();
        $happening->allowWebinarOnHLS()->shouldBeCalled()->willReturn(true);
        $happening->getId()->willReturn(1);

        // dependencies
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $happeningBroadcastRepository = $this->prophesize(HappeningBroadcastRepositoryInterface::class);
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $startBroadcastHandler = $this->prophesize(StartBroadcastHandler::class);

        $notificationPublisher->publishHappeningNotification(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled()
        ;

        $happeningBroadcastRepository->getByHappening($happening->reveal())->shouldBeCalled()->willReturn(null);

        $happeningRepository->set($happening->reveal())->shouldBeCalled();

        $startBroadcastHandler->handle(new StartBroadcast($happening->reveal(), 'foo', 'barr'))
            ->shouldBeCalled();

        // test
        $this->expectException(HappeningBroadcastForHappeningNotFoundException::class);

        $handler = new OpenStreamToPublicCommandHandler(
            $notificationPublisher->reveal(),
            $happeningBroadcastRepository->reveal(),
            $happeningRepository->reveal(),
            $startBroadcastHandler->reveal()
        );

        $command = new OpenStreamToPublicCommand($happening->reveal(), 'foo', 'barr');

        $handler->handle($command);
    }

    public function testHLS(): void
    {
        // data
        $happening = $this->prophesize(Happening::class);
        $happening->openStreamToPublic()->shouldBeCalled();
        $happening->allowWebinarOnHLS()->shouldBeCalled()->willReturn(true);

        $happeningBroadcast = $this->prophesize(Happening\HappeningBroadcast::class);
        $happeningBroadcast->getHlsUrl()->willReturn('http://some.where/over/the/rainbow');

        // dependencies
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $happeningBroadcastRepository = $this->prophesize(HappeningBroadcastRepositoryInterface::class);
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $startBroadcastHandler = $this->prophesize(StartBroadcastHandler::class);

        $notificationPublisher->publishHappeningNotification(
            $happening->reveal(),
            AbstractNotification::TYPE_STREAM,
            [
                'action' => 'stream_started',
                'sessionReference' => 'http://some.where/over/the/rainbow',
            ]
        )
            ->shouldBeCalled()
        ;

        $happeningBroadcastRepository->getByHappening($happening->reveal())->shouldBeCalled()
            ->willReturn($happeningBroadcast->reveal())
        ;

        $happeningRepository->set($happening->reveal())->shouldBeCalled();

        $startBroadcastHandler->handle(new StartBroadcast($happening->reveal(), 'foo', 'barr'))
            ->shouldBeCalled();

        // test
        $handler = new OpenStreamToPublicCommandHandler(
            $notificationPublisher->reveal(),
            $happeningBroadcastRepository->reveal(),
            $happeningRepository->reveal(),
            $startBroadcastHandler->reveal()
        );

        $command = new OpenStreamToPublicCommand($happening->reveal(), 'foo', 'barr');

        $handler->handle($command);
    }

    public function testWebRTC(): void
    {
        // data
        $happening = $this->prophesize(Happening::class);
        $happening->openStreamToPublic()->shouldBeCalled();
        $happening->allowWebinarOnHLS()->shouldBeCalled()->willReturn(false);
        $happening->getWebinarSessionId()->willReturn('x-y-z');

        // dependencies
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $happeningBroadcastRepository = $this->prophesize(HappeningBroadcastRepositoryInterface::class);
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $startBroadcastHandler = $this->prophesize(StartBroadcastHandler::class);

        $notificationPublisher->publishHappeningNotification(
            $happening->reveal(),
            AbstractNotification::TYPE_STREAM,
            [
                'action' => 'stream_started',
                'sessionReference' => 'x-y-z',
            ]
        )
            ->shouldBeCalled()
        ;

        $happeningRepository->set($happening->reveal())->shouldBeCalled();

        // test
        $handler = new OpenStreamToPublicCommandHandler(
            $notificationPublisher->reveal(),
            $happeningBroadcastRepository->reveal(),
            $happeningRepository->reveal(),
            $startBroadcastHandler->reveal()
        );

        $command = new OpenStreamToPublicCommand($happening->reveal(), 'foo', 'barr');

        $handler->handle($command);
    }
}
