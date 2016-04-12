<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Type;

use Proximum\Vimeet\Application\Command\Type\Create;
use Proximum\Vimeet\Application\Command\Type\CreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template;
use Proximum\Vimeet\Domain\Model\Sheet\Template as SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\TypeTranslation;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        //Context
        $event    = new Event();
        $dateTime = new \DateTime();
        $event->setLocales(['fr']);
        $template      = new Template('test', [], [], [], '', '');
        $sheetTemplate = new SheetTemplate('base toto', [], [], $dateTime);


        //Expected
        $expectedSheetTemplate = new SheetTemplate('toto', [], [], $dateTime);
        $expectedSheetTemplate->setEvent($event);
        $expectedType = new Type($event);
        $expectedType->setTemplate($template);
        $expectedType->getTranslations()->set('fr', new TypeTranslation($expectedType, 'fr', 'toto'));
        $expectedType->getValidationCriteria()->setSheetAccepted(true);
        $expectedType->setSheetTemplate($expectedSheetTemplate);

        //Command
        $create = new Create($event, 'fr');
        $create->template = $template;
        $create->translations['fr']['title'] = 'toto';
        $create->validationCriteria['sheetAccepted'] = true;
        $create->sheetTemplate = $sheetTemplate;

        //Mock
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->add($expectedType)->shouldBeCalled();

        //Handler
        $handler = new CreateHandler($typeRepository->reveal(), $dateTime);
        $handler->handle($create);
    }
}
