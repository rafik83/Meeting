<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Type;

use Proximum\Vimeet\Application\Command\Type\Update;
use Proximum\Vimeet\Application\Command\Type\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\TypeTranslation;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
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
        $expectedType->getTranslations()->set('fr', new TypeTranslation($expectedType, 'fr', 'truc'));
        $expectedType->getValidationCriteria()->setSheetAccepted(false);

        //Command
        $type = new Type($event);
        $type->setTemplate($template);
        $type->getTranslations()->set('fr', new TypeTranslation($expectedType, 'fr', 'toto'));
        $type->getValidationCriteria()->setSheetAccepted(true);
        $create = new Update($type);
        $create->translations['fr']['title'] = 'truc';
        $create->validationCriteria['sheetAccepted'] = false;

        //Mock
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->set($expectedType)->shouldBeCalled();

        //Handler
        $handler = new UpdateHandler($typeRepository->reveal());
        $handler->handle($create);
    }
}
