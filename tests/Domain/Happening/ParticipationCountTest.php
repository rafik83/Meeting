<?php

namespace Proximum\Vimeet\Tests\Domain\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Happening\ParticipationCount;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ParticipationCountTest extends TestCase
{
    /** @var Event */
    private $event;

    /** @var Happening */
    private $happening;

    /**
     * @var ObjectProphecy
     */
    private $happeningParticipationRepository;

    public function setUp()
    {
        $this->event     = EventFactory::createEvent();
        $category        = new Happening\Category($this->event, '', 0, '', '');
        $this->happening = new Happening(
            $this->event,
            new \DateTime(),
            new \DateTime(),
            $category,
            []
        );

        $this->happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
    }

    public static function provideGetRemaining()
    {
        return [
            [3, 10, 7, false],
            [0, 10, 10, true],
            [0, 10, 12, true],
            [10, 10, 0, false],
        ];
    }

    /**
     * @dataProvider provideGetRemaining
     *
     * @param int  $expected The expected remaining count
     * @param int  $limit    The happening participant limit
     * @param int  $count    The happening participant count
     * @param bool $isFull   Is the happening full
     */
    public function testGetRemaining(int $expected, int $limit, int $count, bool $isFull)
    {
        $this->happening->setLimitParticipant($limit);

        $this->happeningParticipationRepository->countParticipationByHappening($this->happening)->shouldBeCalled()->willReturn($count);

        $service = new ParticipationCount($this->happeningParticipationRepository->reveal());

        $this->assertEquals($expected, $service->getRemaining($this->happening));
        $this->assertEquals($isFull, $service->isFull($this->happening));
    }

    public function testGetRemainingIfHappeningIsNull()
    {
        $this->happening->setLimitParticipant(null);

        $this->happeningParticipationRepository->countParticipationByHappening($this->happening)->shouldNotBeCalled();

        $service = new ParticipationCount($this->happeningParticipationRepository->reveal());

        $this->assertEquals(INF, $service->getRemaining($this->happening));
        $this->assertEquals(false, $service->isFull($this->happening));
    }
}
