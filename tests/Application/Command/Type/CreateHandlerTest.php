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
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
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
        $event->setLocales(['fr'], 'fr');

        $sheetTemplate        = new SheetTemplate('base toto', [], ['fr'], 'fr', $dateTime);
        $registrationTemplate = new RegistrationTemplate('base tata', [], ['fr'], 'fr', $dateTime);

        //Expected
        $expectedSheetTemplate         = new SheetTemplate('toto', [], ['fr'], 'fr', $dateTime);
        $expectedRegistrationTemplate  = new RegistrationTemplate('toto', [], ['fr'], 'fr', $dateTime);
        $expectedSheetTemplate->setEvent($event);
        $expectedRegistrationTemplate->setEvent($event);

        $expectedType = new Type($event);
        $expectedType->getTranslations()->set('fr', new TypeTranslation($expectedType, 'fr', 'toto'));
        $expectedType->getValidationCriteria()->setSheetAccepted(true);
        $expectedType->setSheetTemplate($expectedSheetTemplate);
        $expectedType->setRegistrationTemplate($expectedRegistrationTemplate);

        //Command
        $create = new Create($event, 'fr');
        $create->translations['fr']['title'] = 'toto';
        $create->validationCriteria['sheetAccepted'] = true;
        $create->sheetTemplate        = $sheetTemplate;
        $create->registrationTemplate = $registrationTemplate;

        //Mock
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->add($expectedType)->shouldBeCalled();
        $typeRepository->typeExists($event, 'fr', 'toto')->willReturn(false);

        //Handler
        $handler = new CreateHandler($typeRepository->reveal(), $dateTime);
        $handler->handle($create);
    }
}
