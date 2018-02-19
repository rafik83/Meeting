<?php

/**
 * Created by PhpStorm.
 * User: richard
 * Date: 19/02/2018
 * Time: 11:46
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Handler;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ImportSheetHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantPositionView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class ImportSheetHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

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

        $importSheetHandler = new ImportSheetHandler($dateTime);
        $importSheetHandler->handle($event->reveal(), $type->reveal(), $registrationView);
    }
}
