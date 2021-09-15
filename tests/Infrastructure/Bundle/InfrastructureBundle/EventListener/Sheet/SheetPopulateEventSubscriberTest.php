<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet\SheetPopulateEventSubscriber;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SheetPopulateEventSubscriberTest extends TestCase
{
    public function testOnSheetTemplateUpdated()
    {
        $sheet = SheetFactory::create();
        $sheetIndexerInterface = $this->prophesize(SheetIndexerInterface::class);

        $sheetIndexerInterface->updateSheets([$sheet])->shouldBeCalled();

        $sheetPopulateEventSubscriber = new SheetPopulateEventSubscriber($sheetIndexerInterface->reveal());
        $sheetPopulateEventSubscriber->onPackageStep(new StepDoneEvent($sheet, 'plan'));
    }
}
