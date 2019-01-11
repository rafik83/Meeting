<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Type;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Type\Update;
use Proximum\Vimeet\Application\Command\Type\UpdateHandler;
use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\TypeTranslation;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        //Context
        $event = EventFactory::createEvent();
        $event->setLocales(['fr'], 'fr');

        //Expected
        $expectedType = new Type($event);
        $expectedType->getTranslations()->set('fr', new TypeTranslation($expectedType, 'fr', 'truc'));
        $expectedType->getValidationCriteria()->setSheetAccepted(false);

        //Command
        $type = new Type($event);
        $type->getTranslations()->set('fr', new TypeTranslation($expectedType, 'fr', 'toto'));
        $type->getValidationCriteria()->setSheetAccepted(true);
        $type->setDisableUnavailabilityManagement(false);

        $update                                      = new Update($type, 'fr');
        $update->translations['fr']['title']         = 'truc';
        $update->validationCriteria['sheetAccepted'] = false;
        $update->enableUnavailabilityManagement = true;

        //Mock
        $typeRepository             = $this->prophesize(TypeRepositoryInterface::class);
        $sheetTemplateCloner        = $this->prophesize(SheetTemplateCloner::class);
        $registrationTemplateCloner = $this->prophesize(RegistrationTemplateCloner::class);

        $typeRepository->set($expectedType)->shouldBeCalled();
        $typeRepository->typeExists($event, 'fr', 'truc', $type)->willReturn(false);

        //Handler
        $handler = new UpdateHandler(
            $typeRepository->reveal(),
            $sheetTemplateCloner->reveal(),
            $registrationTemplateCloner->reveal()
        );
        $handler->handle($update);
    }
}
