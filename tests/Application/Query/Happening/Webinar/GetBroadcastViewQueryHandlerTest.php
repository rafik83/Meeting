<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar;

use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetBroadcastViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetBroadcastViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\HappeningBroadcast;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;

class GetBroadcastViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $happening;

    /** @var ObjectProphecy */
    private $happeningBroadcastRepository;

    /** @var GetBroadcastViewQueryHandler */
    private $getBroadcastViewQueryHandler;

    protected function setUp()
    {
        $this->happening = $this->prophesize(Happening::class);
        $this->happening->isStreamOpenToPublic()->willReturn(true);

        $this->happeningBroadcastRepository = $this->prophesize(HappeningBroadcastRepositoryInterface::class);

        $this->getBroadcastViewQueryHandler = new GetBroadcastViewQueryHandler($this->happeningBroadcastRepository->reveal());
    }

    public function testWhenStreamIsClosed(): void
    {
        $this->happening->isStreamOpenToPublic()->willReturn(false);

        $query = new GetBroadcastViewQuery($this->happening->reveal());
        self::assertNull($this->getBroadcastViewQueryHandler->handle($query));
    }

    public function testHandleWithBroadcast()
    {
        $happeningBroadcast = new HappeningBroadcast(
            $this->happening->reveal(),
            '4321',
            false,
            DateTime::createFromFormat('!Y-m-d H:i', '2012-12-12 12:12'),
            DateTime::createFromFormat('!Y-m-d H:i', '2012-12-12 14:12'),
            null
        );
        $this->happeningBroadcastRepository->getByHappening($this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn($happeningBroadcast);

        $query = new GetBroadcastViewQuery($this->happening->reveal());
        self::assertNull($this->getBroadcastViewQueryHandler->handle($query));
    }

    public function testHandleWithoutBroadcast()
    {
        $this->happeningBroadcastRepository->getByHappening($this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $query = new GetBroadcastViewQuery($this->happening->reveal());
        self::assertNull($this->getBroadcastViewQueryHandler->handle($query));
    }

}
