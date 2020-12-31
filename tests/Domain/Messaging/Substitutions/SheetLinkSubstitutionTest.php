<?php

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Messaging\Substitutions\SheetLinkSubstitution;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SheetLinkSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $event  = EventFactory::createEvent('Proximum');
        $locale = 'fr';

        $sheet             = $this->prophesize(Sheet::class);
        $eventUrlGenerator = $this->prophesize(EventUrlGeneratorInterface::class);

        $sheet->getEvent()->shouldBeCalled()->willReturn($event);
        $sheet->getOwnerLocale()->shouldBeCalled()->willReturn($locale);
        $sheet->getId()->shouldBeCalled()->willReturn(1);

        $eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_sheet_default', [
            'sheet'   => 1,
            '_locale' => $locale,
        ])->shouldBeCalled()->willReturn('https://event.vimeet.events/sheet/1');

        $substitution = new SheetLinkSubstitution($eventUrlGenerator->reveal());
        $sheetLink    = $substitution->getValue($sheet->reveal(), $locale);

        $this->assertEquals('https://event.vimeet.events/sheet/1', $sheetLink);
    }
}
