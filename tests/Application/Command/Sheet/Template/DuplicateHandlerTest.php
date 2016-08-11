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
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateRemoveField;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime         = new \DateTime();
        $template         = new SheetTemplate('Toto', [], ['fr'], 'fr', $dateTime);
        $duplicate        = new Duplicate($template, $dateTime);
        $duplicate->title = 'Machin';

        //expected
        $expectedTemplate = new SheetTemplate('Machin', [], ['fr'], 'fr', $dateTime);
        $expectedResult   = new DuplicateResult($expectedTemplate);

        // Mock
        $templateRepository  = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $templateRemoveField = $this->prophesize(TemplateRemoveField::class);
        $templateRepository->add($expectedTemplate)->shouldBeCalled();

        //Handler
        $handler = new DuplicateHandler($templateRepository->reveal(), $dateTime, $templateRemoveField->reveal());
        $result  = $handler->handle($duplicate);

        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleOrganizer()
    {
        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent();
        $event->setLocales(['fr'], 'fr');
        $template = new SheetTemplate('Toto', [], ['fr'], 'fr', $dateTime);
        $template->setEvent($event);

        $duplicate        = new Duplicate($template, $dateTime);
        $duplicate->title = 'Machin';
        $duplicate->event = $event;

        //expected
        $expectedTemplate = new SheetTemplate('Machin', [], ['fr'], 'fr', $dateTime);
        $expectedTemplate->setEvent($event);
        $expectedResult = new DuplicateResult($expectedTemplate);

        // Mock
        $templateRepository  = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $templateRemoveField = $this->prophesize(TemplateRemoveField::class);
        $templateRepository->add($expectedTemplate)->shouldBeCalled();

        //Handler
        $handler = new DuplicateHandler($templateRepository->reveal(), $dateTime, $templateRemoveField->reveal());
        $result  = $handler->handle($duplicate);

        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleRemoveLinkedProduct()
    {
        $event   = EventFactory::createEvent();
        $eventTo = EventFactory::createEvent();
        $event->setLocales(['fr'], 'fr');
        $eventTo->setLocales(['fr'], 'fr');

        $dateTime = new \DateTime();
        $template = new SheetTemplate('Toto', [
            "ee4f2281" =>
                [
                    "component" => "object",
                    "type"      => "image",
                    "config"    =>
                        [
                            "label"       => ["en" => null, "fr" => "Image"],
                            "placeholder" => ["en" => null, "fr" => ""],
                            "help"        => ["en" => null, "fr" => ""],
                            "required"    => false,
                            "style"       => "",
                            "products"    => ["1", "2"],
                        ],
                ],
        ], ['fr'], 'fr', $dateTime, $event);

        //expected
        $expectedTemplate = new SheetTemplate('DuplicateWithoutProduct', [
            "ee4f2281" =>
                [
                    "component" => "object",
                    "type"      => "image",
                    "config"    =>
                        [
                            "label"       => ["en" => null, "fr" => "Image"],
                            "placeholder" => ["en" => null, "fr" => ""],
                            "help"        => ["en" => null, "fr" => ""],
                            "required"    => false,
                            "style"       => "",
                            "products"    => [],
                        ],
                ],
        ], ['fr'], 'fr', $dateTime, $eventTo);
        $expectedResult = new DuplicateResult($expectedTemplate);

        // Command
        $duplicate        = new Duplicate($template, $dateTime);
        $duplicate->title = 'DuplicateWithoutProduct';
        $duplicate->event = $eventTo;

        // Mock
        $templateRepository  = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $templateRemoveField = $this->prophesize(TemplateRemoveField::class);

        $templateRemoveField->remove($template, 'products', [])->shouldBeCalled()->willReturn([
            "ee4f2281" =>
                [
                    "component" => "object",
                    "type"      => "image",
                    "config"    =>
                        [
                            "label"       => ["en" => null, "fr" => "Image"],
                            "placeholder" => ["en" => null, "fr" => ""],
                            "help"        => ["en" => null, "fr" => ""],
                            "required"    => false,
                            "style"       => "",
                            "products"    => [],
                        ],
                ],
        ]);

        $templateRepository->add($expectedTemplate)->shouldBeCalled();

        //Handler
        $handler = new DuplicateHandler($templateRepository->reveal(), $dateTime, $templateRemoveField->reveal());
        $result  = $handler->handle($duplicate);

        $this->assertEquals($expectedResult, $result);
    }
}
