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
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class ImportSheetHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $expectedRawRegistration = new \stdClass();
        $expectedResponse = [$expectedRawRegistration];

        $expectedRegistrationView = new RegistrationView(
            '9996666',
            'Nintendo',
            'VALIDE',
            'Yoshi road',
            '315135',
            'Tokyo',
            'JP',
            '0 000 16534046',
            'http://www.nintendo.com',
            new ParticipantView(),
            ['20020', '20021']
        );

        $rawRegistrationToRegistrationViewConverter = $this->prophesize(RawRegistrationToRegistrationViewConverter::class);
        $rawRegistrationToRegistrationViewConverter
            ->convert($expectedRawRegistration)
            ->shouldBeCalled()
            ->willReturn($expectedRegistrationView)
        ;

        $extraParameter = $this->prophesize(ExtraParameter::class);
        $extraParameter->getValue()->shouldBeCalled()->willReturn('999666');

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType($event->reveal(), Type::TYPE_COMEXPOSIUM_EVENT)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;

        $comexposiumWebservice = $this->prophesize(ComexposiumWebservice::class);
        $comexposiumWebservice->getRegistrations('999666', ['111222333'])->shouldBeCalled()->willReturn($expectedResponse);

        $importSheetHandler = new ImportSheetHandler(
            $comexposiumWebservice->reveal(),
            $extraParameterRepository->reveal(),
            $rawRegistrationToRegistrationViewConverter->reveal()
        );

        $importSheetHandler->handle($event->reveal(), ['111222333']);
    }
}
