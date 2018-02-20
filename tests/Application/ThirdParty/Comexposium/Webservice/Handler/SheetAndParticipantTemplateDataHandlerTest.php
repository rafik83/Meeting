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
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\SheetAndParticipantTemplateDataHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantPositionView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\SheetAndParticipantTemplateDataView;
use Proximum\Vimeet\Domain\Template\TemplateData;

class SheetAndParticipantTemplateDataHandlerTest extends TestCase
{
    public function testHandle()
    {
        $templateData = $this->prophesize(TemplateData::class);

        $registrationView = new RegistrationView(
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

        $sheetAndParticipantTemplateDataHandler = new SheetAndParticipantTemplateDataHandler();
        $result = $sheetAndParticipantTemplateDataHandler->handle($registrationView, $templateData->reveal());

        $expectedResult = new SheetAndParticipantTemplateDataView([], []);

        $this->assertEquals($expectedResult, $result);
    }
}
