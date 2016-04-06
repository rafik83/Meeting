<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Command\Sheet\Template\CreateResult;
use Proximum\Vimeet\Application\Command\Sheet\Template\OrganizerCreate;
use Proximum\Vimeet\Application\Command\Sheet\Template\OrganizerCreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Template;
use Proximum\Vimeet\Domain\Repository\Sheet\TemplateRepositoryInterface;

class OrganizerCreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event    = new Event();
        $create   = new OrganizerCreate($dateTime);
        $create->title = 'Toto';
        $create->event = $event;

        //expected
        $expectedTemplate = new Template('Toto', '', $dateTime);
        $expectedTemplate->setEvent($event);
        $expectedResult   = new CreateResult($expectedTemplate);

        // Mock
        $templateRepository = $this->prophesize(TemplateRepositoryInterface::class);
        $templateRepository->add($expectedTemplate)->shouldBeCalled();

        //Handler
        $handler = new OrganizerCreateHandler($templateRepository->reveal());
        $result = $handler->handle($create);

        $this->assertEquals($expectedResult, $result);
    }
}
