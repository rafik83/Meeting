<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Handler;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Converter\RawRegistrationToRegistrationViewConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ComexposiumWebservice;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ImportSheetHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ImportSheetsHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\RemoveAlreadyImportedReferences;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantPositionView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class ImportSheetsHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $expectedRawRegistration = new \stdClass();
        $expectedResponse = [$expectedRawRegistration];

        $expectedRegistrationView = new RegistrationView(
            '5556666',
            'Nintendo',
            'VALIDE',
            '61 rue de l\'Odyssée',
            '75008',
            'Paris',
            'FR',
            '33 (0)1 40 69 80 00',
            'https://www.nintendo.com',
            new ParticipantView(
                'man',
                'Takashi',
                'Kitano',
                'takashi.kitano@nintendo.com',
                'fr',
                null,
                'Nintendo Europe',
                [
                    new ParticipantPositionView('Directeur Export', 'fr'),
                    new ParticipantPositionView('Export Director', 'en'),
                ]
            ),
            ['666', '777', '88898']
        );

        $rawRegistrationToRegistrationViewConverter = $this->prophesize(RawRegistrationToRegistrationViewConverter::class);
        $rawRegistrationToRegistrationViewConverter
            ->convert($expectedRawRegistration)
            ->shouldBeCalled()
            ->willReturn($expectedRegistrationView)
        ;

        $eventReferenceExtraParameter = $this->prophesize(ExtraParameter::class);
        $eventReferenceExtraParameter->getValue()->shouldBeCalled()->willReturn('999666');

        $typeIdExtraParameter = $this->prophesize(ExtraParameter::class);
        $typeIdExtraParameter->getValue()->shouldBeCalled()->willReturn('113');

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType($event->reveal(), ExtraParameterType::TYPE_COMEXPOSIUM_EVENT)
            ->shouldBeCalled()
            ->willReturn($eventReferenceExtraParameter->reveal())
        ;
        $extraParameterRepository
            ->findByEventAndType($event->reveal(), ExtraParameterType::TYPE_COMEXPOSIUM_TYPE_ID)
            ->shouldBeCalled()
            ->willReturn($typeIdExtraParameter->reveal())
        ;

        $type = $this->prophesize(Type::class);
        $type->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->getById(113)->shouldBeCalled()->willReturn($type->reveal());

        $comexposiumWebservice = $this->prophesize(ComexposiumWebservice::class);
        $comexposiumWebservice->getRegistrations('999666', ['111222333'])->shouldBeCalled()->willReturn($expectedResponse);

        $templateData = $this->prophesize(TemplateData::class);

        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory
            ->createRegistrationFromType($type->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $importSheetHandler = $this->prophesize(ImportSheetHandler::class);
        $importSheetHandler
            ->handle($event->reveal(), $type->reveal(), $expectedRegistrationView, $templateData->reveal())
            ->shouldBeCalled()
        ;

        $removeAlreadyImportedReferences = $this->prophesize(RemoveAlreadyImportedReferences::class);
        $removeAlreadyImportedReferences
            ->handle($event->reveal(), ['111222333', '3334444'])
            ->shouldBeCalled()
            ->willReturn(['111222333'])
        ;

        $importSheetsHandler = new ImportSheetsHandler(
            $comexposiumWebservice->reveal(),
            $extraParameterRepository->reveal(),
            $typeRepository->reveal(),
            $rawRegistrationToRegistrationViewConverter->reveal(),
            $importSheetHandler->reveal(),
            $templateDataFactory->reveal(),
            $removeAlreadyImportedReferences->reveal()
        );

        $importSheetsHandler->handle($event->reveal(), ['111222333', '3334444']);
    }
}
