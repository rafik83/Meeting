<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Broadcast;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\StartBroadcast;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\StartBroadcastHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\HappeningBroadcast;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Tokbox\Broadcast\Broadcast;

class StartBroadcastHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $videoConferenceAdapter;

    /** @var ObjectProphecy */
    private $broadcastRepository;

    /** @var ObjectProphecy */
    private $notificationPublisher;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var StartBroadcastHandler */
    private $startBroadcastHandler;

    private const WEBINAR_SESSION_ID = '2_MX40Njg1Mzc4NH5-MTYwMDE2NTQ0MDEzNH41bVVVVTFpVXRvuXc2VjhhZkRRTFozdHZ-fg';

    public function setUp(): void
    {
        $this->videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $this->broadcastRepository = $this->prophesize(HappeningBroadcastRepositoryInterface::class);
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $this->dateTime = \DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 03:14');

        $this->startBroadcastHandler = new StartBroadcastHandler(
            $this->videoConferenceAdapter->reveal(),
            $this->broadcastRepository->reveal(),
            $this->notificationPublisher->reveal(),
            $this->dateTime
        );
    }

    public function testHandleStartNewBroadcast()
    {
        $happening = $this->getHappening();

        $this->videoConferenceAdapter->getBroadcastForSession(self::WEBINAR_SESSION_ID)->shouldBeCalled()->willReturn(null);
        $newBroadcast = new Broadcast('1234', self::WEBINAR_SESSION_ID, false, null, null);
        $this->videoConferenceAdapter->startBroadcast(self::WEBINAR_SESSION_ID, 5400)->shouldBeCalled()->willReturn($newBroadcast);

        $this->broadcastRepository->getByHappening($happening->reveal())->shouldBeCalled()->willReturn(null);
        $this->broadcastRepository->deleteForHappening($happening->reveal())->shouldBeCalled();
        $this->broadcastRepository->add(Argument::type(HappeningBroadcast::class))->shouldBeCalled();

        $this->notificationPublisher->publishHappeningNotification($happening, 'stream', [
            'action' => 'stream_started',
            'hlsUrl' => null,
        ])->shouldBeCalled();

        $command = new StartBroadcast($happening->reveal(), 'video', '1234-5678-9012');
        $result = $this->startBroadcastHandler->handle($command);

        self::assertNull($result);
    }

    public function testHandleStartExistingBroadcast()
    {
        $happening = $this->getHappening();

        $hlsUrl = 'https://example.com/stream/video.hls';
        $existingBroadcast = new Broadcast('1234', self::WEBINAR_SESSION_ID, false, $hlsUrl, null);
        $this->videoConferenceAdapter->getBroadcastForSession(self::WEBINAR_SESSION_ID)->shouldBeCalled()->willReturn($existingBroadcast);
        $this->videoConferenceAdapter->startBroadcast(Argument::any(), Argument::any())->shouldNotBeCalled();

        $happeningBroadcast = new HappeningBroadcast(
            $happening->reveal(),
            '1234',
            false,
            \DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 02:00'),
            $happening->reveal()->getEnd(),
            $hlsUrl
        );
        $this->broadcastRepository->getByHappening($happening->reveal())->shouldBeCalled()->willReturn($happeningBroadcast);

        $this->notificationPublisher->publishHappeningNotification($happening, 'stream', [
            'action' => 'stream_started',
            'hlsUrl' => $hlsUrl,
        ])->shouldBeCalled();

        $command = new StartBroadcast($happening->reveal(), 'video', '1234-5678-9012');
        $result = $this->startBroadcastHandler->handle($command);

        self::assertEquals($hlsUrl, $result);
    }

    public function testHandleScreenShareBroadcast()
    {
        $happening = $this->getHappening();

        $streamId = '1234-5678-9012';
        $hlsUrl = 'https://example.com/stream/video.hls';

        $existingBroadcast = new Broadcast('1234', self::WEBINAR_SESSION_ID, false, $hlsUrl, null);
        $this->videoConferenceAdapter->getBroadcastForSession(self::WEBINAR_SESSION_ID)->shouldBeCalled()->willReturn($existingBroadcast);
        $this->videoConferenceAdapter->startBroadcast(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->videoConferenceAdapter->changeBroadcastFocus($existingBroadcast, $streamId)->shouldBeCalled();

        $happeningBroadcast = new HappeningBroadcast(
            $happening->reveal(),
            '1234',
            false,
            \DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 02:00'),
            $happening->reveal()->getEnd(),
            $hlsUrl
        );
        $this->broadcastRepository->getByHappening($happening->reveal())->shouldBeCalled()->willReturn($happeningBroadcast);

        $this->notificationPublisher->publishHappeningNotification($happening, 'stream', [
            'action' => 'stream_started',
            'hlsUrl' => $hlsUrl,
        ])->shouldBeCalled();

        $command = new StartBroadcast($happening->reveal(), 'screen', $streamId);
        $result = $this->startBroadcastHandler->handle($command);

        self::assertEquals($hlsUrl, $result);
    }

    private function getHappening(): ObjectProphecy
    {
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->willReturn(314);
        $happening->getWebinarSessionId()->willReturn(self::WEBINAR_SESSION_ID);
        $happening->getEnd()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 04:14'));

        return $happening;
    }
}
