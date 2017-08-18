<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Template\Registration;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\Index;
use Proximum\Vimeet\Application\Command\Template\Registration\IndexHandler;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class IndexHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $registrationTemplate = new RegistrationTemplate(
            'Registration template',
            [],
            ['fr'],
            'fr',
            new \DateTime(),
            $event
        );
        $sheet = SheetFactory::create($event);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetIndexerInterface = $this->prophesize(SheetIndexerInterface::class);

        $sheetRepository->getByRegistrationTemplate($registrationTemplate)->shouldBeCalled()->willReturn([$sheet]);
        $sheetIndexerInterface->updateSheets([$sheet])->shouldBeCalled();

        $indexHandler = new IndexHandler($sheetRepository->reveal(), $sheetIndexerInterface->reveal());
        $indexHandler->handle(new Index($registrationTemplate));
    }
}
