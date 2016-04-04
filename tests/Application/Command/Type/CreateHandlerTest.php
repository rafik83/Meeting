<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Type;

use Proximum\Vimeet\Application\Command\Type\Create;
use Proximum\Vimeet\Application\Command\Type\CreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\TypeTranslation;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        //Context
        $event = new Event();
        $event->setLocales([]);
        $template = new Template('test', [], [], [], '', '');

        //Expected
        $expectedType = new Type($event);
        $expectedType->setTemplate($template);
        $expectedType->getTranslations()->set('fr', new TypeTranslation($expectedType, 'fr', 'toto'));
        $expectedType->getValidationCriteria()->setSheetAccepted(true);

        //Command
        $create = new Create($event);
        $create->template = $template;
        $create->translations['fr']['title'] = 'toto';
        $create->validationCriteria['sheetAccepted'] = true;

        //Mock
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->add($expectedType)->shouldBeCalled();

        //Handler
        $handler = new CreateHandler($typeRepository->reveal());
        $handler->handle($create);
    }
}
