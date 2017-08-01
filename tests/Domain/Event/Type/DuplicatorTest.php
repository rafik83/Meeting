<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Event\Type;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Event\Type\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $date                 = new \DateTime();
        $eventDuplicated      = EventFactory::createEvent('event duplicated');
        $event                = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en',],
            Event::VAT_MODE_ET,
            $eventDuplicated
        );
        $sheetTemplate        = new SheetTemplate(
            'sheet template title',
            [],
            ['fr'],
            'fr',
            $date
        );
        $registrationTemplate = new RegistrationTemplate(
            'registration template',
            [],
            ['fr'],
            'fr',
            $date
        );
        $expectedType         = new Type($event);
        $expectedType->setSheetTemplate($sheetTemplate);
        $expectedType->setRegistrationTemplate($registrationTemplate);
        $expectedType->setPackage(new Package($event, 'package title', $date));

        $type = new Type($eventDuplicated);
        $type->setSheetTemplate($sheetTemplate);
        $type->setRegistrationTemplate($registrationTemplate);
        $type->setPackage(new Package($event, 'package title', $date));

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->getTypesByEvent($eventDuplicated)->shouldBeCalled()->willReturn([$type]);

        $sheetTemplateCloner = $this->prophesize(SheetTemplateCloner::class);
        $sheetTemplateCloner->duplicate(
            $type->getSheetTemplate(),
            $event,
            $type->getSheetTemplate()->getTitle()
        )->shouldBeCalled()->willReturn($sheetTemplate);

        $registrationTemplateCloner = $this->prophesize(RegistrationTemplateCloner::class);
        $registrationTemplateCloner->duplicate(
            $type->getRegistrationTemplate(),
            $event,
            $type->getRegistrationTemplate()->getTitle()
        )->shouldBeCalled()->willReturn($registrationTemplate);

        $typeRepository->add($expectedType)->shouldBeCalled();

        (new Duplicator(
            $typeRepository->reveal(),
            $sheetTemplateCloner->reveal(),
            $registrationTemplateCloner->reveal()
        ))->duplicate($event);
    }
}
