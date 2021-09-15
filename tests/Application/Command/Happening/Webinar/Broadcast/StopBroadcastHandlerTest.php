<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Broadcast;

use OpenTok\Exception\BroadcastDomainException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\StopBroadcast;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\StopBroadcastHandler;
use Proximum\Vimeet\Application\Command\Happening\Webinar\StreamCommand;
use Proximum\Vimeet\Application\Command\Happening\Webinar\StreamCommandHandler;
use Proximum\Vimeet\Application\View\Happening\Webinar\StreamDTO;
use Proximum\Vimeet\Domain\Happening\Webinar\Stream;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\HappeningBroadcast;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Tokbox\Broadcast\Broadcast;

class StopBroadcastHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $videoConferenceAdapter;

    /** @var ObjectProphecy */
    private $broadcastRepository;

    /** @var ObjectProphecy */
    private $streamCommandHandler;

    /** @var StopBroadcastHandler */
    private $stopBroadcastHandler;

    private const WEBINAR_SESSION_ID = '2_MX40Njg1Mzc4NH5-MTYwMDE2NTQ0MDEzNH41bVVVVTFpVXRvuXc2VjhhZkRRTFozdHZ-fg';
    private const HLS_URL = 'https://example.com/video.hls';
    private const BROADCAST_ID = '1234-5678-9012';
    private const STREAM_ID = 'aa7bc86a-e0bc-4352-b34c-9754b491a6be';

    public function setUp(): void
    {
        $this->videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $this->broadcastRepository = $this->prophesize(HappeningBroadcastRepositoryInterface::class);
        $this->streamCommandHandler = $this->prophesize(StreamCommandHandler::class);

        $this->stopBroadcastHandler = new StopBroadcastHandler(
            $this->videoConferenceAdapter->reveal(),
            $this->broadcastRepository->reveal(),
            $this->streamCommandHandler->reveal()
        );
    }

    public function testHandleStopBroadcast()
    {
        $happening = $this->getHappening();

        $happeningBroadcast = new HappeningBroadcast(
            $happening->reveal(),
            self::BROADCAST_ID,
            false,
            \DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 02:00'),
            $happening->reveal()->getEnd(),
            self::HLS_URL
        );
        $this->broadcastRepository->getByHappening($happening->reveal())->shouldBeCalled()->willReturn($happeningBroadcast);

        $existingBroadcast = new Broadcast(self::BROADCAST_ID, self::WEBINAR_SESSION_ID, false, self::HLS_URL, null);
        $this->videoConferenceAdapter->getSessionStreamCount(self::WEBINAR_SESSION_ID)->shouldBeCalled()->willReturn(0);

        $this->videoConferenceAdapter->stopBroadcast(self::BROADCAST_ID)->shouldBeCalled()->willReturn($existingBroadcast);
        $this->broadcastRepository->deleteForHappening($happening->reveal())->shouldBeCalled();

        $command = new StopBroadcast($happening->reveal(), 'video', self::STREAM_ID);
        $this->stopBroadcastHandler->handle($command);
    }

    public function testHandleAlreadyStoppedBroadcast()
    {
        $happening = $this->getHappening();

        $happeningBroadcast = new HappeningBroadcast(
            $happening->reveal(),
            self::BROADCAST_ID,
            false,
            \DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 02:00'),
            $happening->reveal()->getEnd(),
            self::HLS_URL
        );
        $this->broadcastRepository->getByHappening($happening->reveal())->shouldBeCalled()->willReturn($happeningBroadcast);

        $this->videoConferenceAdapter->getSessionStreamCount(self::WEBINAR_SESSION_ID)->shouldBeCalled()->willReturn(0);

        $this->videoConferenceAdapter->stopBroadcast(self::BROADCAST_ID)->shouldBeCalled()->willThrow(new BroadcastDomainException());

        $otherBroadcastId = '111-2222-3333';
        $this->videoConferenceAdapter->getBroadcastsForSession(self::WEBINAR_SESSION_ID)
            ->shouldBeCalled()
            ->willReturn([new Broadcast($otherBroadcastId, self::WEBINAR_SESSION_ID, false, null, null)]);
        $this->videoConferenceAdapter->stopBroadcast($otherBroadcastId)->shouldBeCalled();
        $this->broadcastRepository->deleteForHappening($happening->reveal())->shouldBeCalled();

        $command = new StopBroadcast($happening->reveal(), 'video', self::STREAM_ID);
        $this->stopBroadcastHandler->handle($command);
    }

    public function testHandleNoHappeningBroadcast()
    {
        $happening = $this->getHappening();

        $this->broadcastRepository->getByHappening($happening->reveal())->shouldBeCalled()->willReturn(null);

        $this->videoConferenceAdapter->getSessionStreamCount(Argument::any())->shouldNotBeCalled();
        $this->videoConferenceAdapter->stopBroadcast(Argument::any())->shouldNotBeCalled();
        $this->broadcastRepository->deleteForHappening($happening->reveal())->shouldNotBeCalled();

        $command = new StopBroadcast($happening->reveal(), 'video', self::STREAM_ID);
        $this->stopBroadcastHandler->handle($command);
    }

    public function testHandleScreenStreamStopped()
    {
        $happening = $this->getHappening();

        $happeningBroadcast = new HappeningBroadcast(
            $happening->reveal(),
            self::BROADCAST_ID,
            false,
            \DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 02:00'),
            $happening->reveal()->getEnd(),
            self::HLS_URL
        );
        $this->broadcastRepository->getByHappening($happening->reveal())->shouldBeCalled()->willReturn($happeningBroadcast);
        $this->videoConferenceAdapter->getSessionStreamCount(self::WEBINAR_SESSION_ID)->shouldBeCalled()->willReturn(1);

        $this->videoConferenceAdapter->resetBroadcastFocus(self::BROADCAST_ID)->shouldBeCalled();

        $this->videoConferenceAdapter->stopBroadcast(Argument::any())->shouldNotBeCalled();
        $this->broadcastRepository->deleteForHappening(Argument::any())->shouldNotBeCalled();

        $this->streamCommandHandler->handle(new StreamCommand(
            $happening->reveal(),
            new StreamDTO(self::STREAM_ID, 'screen', Stream::ACTION_STOP)
        ))->shouldBeCalled();

        $command = new StopBroadcast($happening->reveal(), 'screen', self::STREAM_ID);
        $this->stopBroadcastHandler->handle($command);
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
