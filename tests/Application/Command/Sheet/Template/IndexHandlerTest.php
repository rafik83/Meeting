<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

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
        $sheetIndexerInterface = $this->prophesize(SheetIndexerInterface::class);

        $sheetRepository->getBySheetTemplate($sheetTemplate)->shouldBeCalled()->willReturn([$sheet]);
        $sheetIndexerInterface->updateSheets([$sheet])->shouldBeCalled();

        $indexHandler = new IndexHandler($sheetRepository->reveal(), $sheetIndexerInterface->reveal());
        $indexHandler->handle(new Index($sheetTemplate));
    }
}
