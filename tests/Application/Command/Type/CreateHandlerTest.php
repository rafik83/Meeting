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
use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\TypeTranslation;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        //Context
        $event    = EventFactory::createEvent();
        $dateTime = new \DateTime();
        $package  = new Package($event, 'title', $dateTime);
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
        $expectedType->setHidden(true);
        $expectedType->setRegistrationTemplate($expectedRegistrationTemplate);
        $expectedType->setPackage($package);

        //Command
        $create = new Create($event, 'fr');
        $create->translations['fr']['title'] = 'toto';
        $create->validationCriteria['sheetAccepted'] = true;
        $create->sheetTemplate        = $sheetTemplate;
        $create->registrationTemplate = $registrationTemplate;
        $create->package              = $package;
        $create->hidden               = true;

        //Mock
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->add($expectedType)->shouldBeCalled();
        $typeRepository->typeExists($event, 'fr', 'toto')->willReturn(false);

        $sheetTemplateCloner = $this->prophesize(SheetTemplateCloner::class);
        $sheetTemplateCloner->duplicate($sheetTemplate, $event, 'toto')->shouldBeCalled()->willReturn($expectedSheetTemplate);

        $registrationTemplateCloner = $this->prophesize(RegistrationTemplateCloner::class);
        $registrationTemplateCloner->duplicate($registrationTemplate, $event, 'toto')->shouldBeCalled()->willReturn($expectedRegistrationTemplate);

        //Handler
        $handler = new CreateHandler($typeRepository->reveal(), $sheetTemplateCloner->reveal(), $registrationTemplateCloner->reveal());
        $handler->handle($create);
    }
}
