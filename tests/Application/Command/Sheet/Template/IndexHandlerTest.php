<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\SheetTemplate\SheetTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IndexHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $sheetTemplate = new SheetTemplate(
            'Sheet template',
            [],
            ['fr'],
            'fr',
            new \DateTime(),
            [],
            $event
        );
        $sheet = SheetFactory::create($event);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $sheetRepository->getBySheetTemplate($sheetTemplate)->shouldBeCalled()->willReturn([$sheet]);

        $eventDispatcher
            ->dispatch(
                Events::SHEET_TEMPLATE_UPDATED,
                new SheetTemplateUpdatedEvent([$sheet])
            )
            ->shouldBeCalled();

        // Handler
        $indexHandler = new IndexHandler($sheetRepository->reveal(), $eventDispatcher->reveal());
        $indexHandler->handle(new Index($sheetTemplate));
    }
}
