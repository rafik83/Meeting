<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Encryption;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Encryption\SheetProtectedKeyPasswordGetterAdapter;

class SheetProtectedKeyPasswordGetterAdapterTest extends TestCase
{
    public function testGetProtectedKeyPasswordBySheet()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(13317);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(963);
        $sheet->getEvent()->willReturn($event->reveal());

        $sheetProtectedKeyPasswordGetterAdapter = new SheetProtectedKeyPasswordGetterAdapter('_my-very_secret_KEY');

        $result = $sheetProtectedKeyPasswordGetterAdapter->getProtectedKeyPasswordBySheet($sheet->reveal());

        $this->assertEquals('9c48920141f130cacdac4250582e9c2ef56fc515518c1783af776f43806d3da4', $result);
    }
}
