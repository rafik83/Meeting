<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Command\Sheet\Template\Duplicate;
use Proximum\Vimeet\Application\Command\Sheet\Template\DuplicateHandler;
use Proximum\Vimeet\Application\Command\Sheet\Template\DuplicateResult;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Template;
use Proximum\Vimeet\Domain\Repository\Sheet\TemplateRepositoryInterface;

class DuplicateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $template = new Template('Toto', [], ['fr'], 'fr', $dateTime);
        $duplicate   = new Duplicate($template, $dateTime);
        $duplicate->title = 'Machin';

        //expected
        $expectedTemplate = new Template('Machin', [], ['fr'], 'fr', $dateTime);
        $expectedResult   = new DuplicateResult($expectedTemplate);

        // Mock
        $templateRepository = $this->prophesize(TemplateRepositoryInterface::class);
        $templateRepository->add($expectedTemplate)->shouldBeCalled();

        //Handler
        $handler = new DuplicateHandler($templateRepository->reveal(), $dateTime);
        $result = $handler->handle($duplicate);

        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleOrganizer()
    {
        $dateTime = new \DateTime();
        $event  = new Event();
        $event->setLocales(['fr'], 'fr');
        $template = new Template('Toto', [], ['fr'], 'fr', $dateTime);
        $template->setEvent($event);

        $duplicate   = new Duplicate($template, $dateTime);
        $duplicate->title = 'Machin';
        $duplicate->event = $event;

        //expected
        $expectedTemplate = new Template('Machin', [], ['fr'], 'fr', $dateTime);
        $expectedTemplate->setEvent($event);
        $expectedResult   = new DuplicateResult($expectedTemplate);

        // Mock
        $templateRepository = $this->prophesize(TemplateRepositoryInterface::class);
        $templateRepository->add($expectedTemplate)->shouldBeCalled();

        //Handler
        $handler = new DuplicateHandler($templateRepository->reveal(), $dateTime);
        $result = $handler->handle($duplicate);

        $this->assertEquals($expectedResult, $result);
    }
}
