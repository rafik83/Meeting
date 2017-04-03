<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\tests\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet;

use FOS\ElasticaBundle\Persister\ObjectPersister;
use Proximum\Vimeet\Application\Event\Template\SheetTemplate\SheetTemplateUpdatedEvent;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet\SheetPopulateEventSubscriber;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SheetPopulateEventSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testOnSheetTemplateUpdated()
    {
        $sheet1 = SheetFactory::create();
        $sheet2 = SheetFactory::create();
        $objectPersister = $this->prophesize(ObjectPersister::class);

        $objectPersister->replaceMany([$sheet1, $sheet2])->shouldBeCalled();

        $sheetPopulateEventSubscriber = new SheetPopulateEventSubscriber($objectPersister->reveal());
        $sheetPopulateEventSubscriber->onSheetTemplateUpdated(new SheetTemplateUpdatedEvent([$sheet1, $sheet2]));
    }
}
