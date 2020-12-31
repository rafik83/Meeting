<?php

namespace Proximum\Vimeet\Tests\Application\Query\Scan\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Scan\Happening\ListViewQuery;
use Proximum\Vimeet\Application\Query\Scan\Happening\ListViewQueryHandler;
use Proximum\Vimeet\Application\View\Scan\Happening\HappeningView;
use Proximum\Vimeet\Application\View\Scan\Happening\ListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class ListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $locale = 'en';
        $event->getAvailableLocale('en')->shouldBeCalled()->willReturn('fr');
        $happening1 = $this->prophesize(Happening::class);
        $happening2 = $this->prophesize(Happening::class);
        $happening3 = $this->prophesize(Happening::class);
        $happening1->getId()->shouldBeCalled()->willReturn(11);
        $happening1->getTitle('fr')->shouldBeCalled()->willReturn('happening1');
        $happening2->getId()->shouldBeCalled()->willReturn(12);
        $happening2->getTitle('fr')->shouldBeCalled()->willReturn('happening2');
        $happening3->getId()->shouldBeCalled()->willReturn(13);
        $happening3->getTitle('fr')->shouldBeCalled()->willReturn('happening3');

        $date1 = new \DateTime('2018-10-10 10:00:00.000');
        $date2 = new \DateTime('2018-10-10 14:00:00.000');
        $date3 = new \DateTime('2018-10-10 09:00:00.000');

        $happening1->getBegin()->shouldBeCalled()->willReturn($date1);
        $happening2->getBegin()->shouldBeCalled()->willReturn($date2);
        $happening3->getBegin()->shouldBeCalled()->willReturn($date3);

        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningRepository->findByEvent($event->reveal())->shouldBeCalled()->willReturn([
            $happening3->reveal(),
            $happening1->reveal(),
            $happening2->reveal(),
        ]);

        $query = new ListViewQuery($event->reveal(), $locale);
        $handler = new ListViewQueryHandler(
            $happeningRepository->reveal()
        );

        $result = $handler->handle($query);

        $views = [
            new HappeningView(13, 'happening3', $date3),
            new HappeningView(11, 'happening1', $date1),
            new HappeningView(12, 'happening2', $date2),
        ];
        $expected = new ListView($views);

        $this->assertEquals($expected, $result);
    }
}
