<?php

namespace Proximum\Vimeet\Tests\Application\Query\Analytic\MeetingSolution\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet\SheetSatisfactionViewQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet\SheetSatisfactionViewQueryHandler;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class SheetSatisfactionViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $type;

    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->type = $this->prophesize(Type::class);
        $this->type->getId()->willReturn(2);
        $this->sheet->getId()->willReturn(1);
        $this->sheet->getTitle()->willReturn('title1');
        $this->type->getTitle('fr')->willReturn('typeTitle1');
        $this->sheet->getType()->willReturn($this->type->reveal());
    }

    /**
     * @dataProvider provideData
     *
     * @param int                   $request
     * @param int                   $meeting
     * @param SheetSatisfactionView $expected
     */
    public function testHandle(int $request, int $meeting, SheetSatisfactionView $expected)
    {
        $handler = new SheetSatisfactionViewQueryHandler();
        $result = $handler->handle(new SheetSatisfactionViewQuery($this->sheet->reveal(), $request, $meeting, 'fr'));

        $this->assertEquals($expected, $result);
    }

    public function provideData()
    {
        return [
            [
                10,
                8,
                new SheetSatisfactionView(1, 'title1', 2, 'typeTitle1', 80),
            ],
            [
                7,
                10,
                new SheetSatisfactionView(1, 'title1', 2, 'typeTitle1', 143),
            ],
            [
                7,
                0,
                new SheetSatisfactionView(1, 'title1', 2, 'typeTitle1', 0),
            ],
            [
                0,
                10,
                new SheetSatisfactionView(1, 'title1', 2, 'typeTitle1', 100),
            ],
        ];
    }
}
