<?php

namespace Proximum\Vimeet\Tests\Application\Components\Planning\Displayer;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;
use Proximum\Vimeet\Application\Components\Planning\Displayer\SheetPlanningDisplayer;
use Proximum\Vimeet\Application\Components\Planning\Formatter\SheetPlanningFormatter;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetPlanningDisplayerTest extends TestCase
{
    public function testDisplay()
    {
        $locale = 'fr';
        $sheet = $this->prophesize(Sheet::class);
        $currentParticipant = $this->prophesize(Participant::class);

        $planningMarkdown = "Planning:\n\n**Patrick Sebastien**\n\n**Jeudi Y Janvier**\n\n- 10:00 13:00 - TABLE A01 - Truc Muche\n";
        $planningHtml = '<div>Planning:<br><b>Patrick Sebastien</b><br><br><b>Jeudi Y Janvier</b><br><br>- 10:00 13:00 - TABLE A01 - Truc Muche</div>';

        // Mock
        $sheetPlanningFormatter = $this->prophesize(SheetPlanningFormatter::class);
        $markdown = $this->prophesize(MarkdownAdapterInterface::class);

        $sheetPlanningFormatter
            ->formatWithUnallocated($sheet->reveal(), $locale, $currentParticipant->reveal())
            ->shouldBeCalled()
            ->willReturn($planningMarkdown)
        ;
        $markdown
            ->toHtml($planningMarkdown)
            ->shouldBeCalled()
            ->willReturn($planningHtml);

        $sheetPlanningDisplayer = new SheetPlanningDisplayer(
            $sheetPlanningFormatter->reveal(),
            $markdown->reveal()
        );
        $result = $sheetPlanningDisplayer->display($sheet->reveal(), $locale, $currentParticipant->reveal());

        $expected = $planningHtml;

        $this->assertEquals($expected, $result);
    }
}
