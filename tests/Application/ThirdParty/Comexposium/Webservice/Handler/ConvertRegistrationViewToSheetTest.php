<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Handler;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedFromApiEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ConvertRegistrationViewToSheet;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\SheetAndParticipantTemplateDataHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\SheetAndParticipantTemplateDataView;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Participant\ParticipantOfSheetWithPackageParticipantAndPlanningDisabled;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface as SheetExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface as UserEventExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConvertRegistrationViewToSheetTest extends TestCase
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
                []
            ),
            [],
            []
        );

        $expectedUser = new User('takashi.kitano@nintendo.com', '', '', 'fr');
        $expectedUser->welcome();
        $expectedUser->setAccount(new User\Account());

        $expectedSheet = new Sheet(
            $event->reveal(),
            $type->reveal(),
            ['whateverSheetTemplateData'],
            $expectedUser,
            $dateTime
        );
        $expectedSheet->setRegistrationData(['whateverRegistrationSheetData']);
        $expectedSheet->setTitle('Nintendo');
        $expectedSheet->setImported(true);

        $expectedParticipant = new Participant(
            $expectedSheet, $expectedUser, ['whateverRegistrationParticipantData'], false, $dateTime
        );
        $expectedParticipant->setImported(true);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->add($expectedSheet)->shouldBeCalled();

        $sheetExtraDataRepository = $this->prophesize(SheetExtraDataRepositoryInterface::class);
        $sheetExtraDataRepository
            ->add(new Sheet\ExtraData($expectedSheet, 'comexposium_registration_reference', '5556666', $dateTime))
            ->shouldBeCalled()
        ;

        $userEventExtraDataRepository = $this->prophesize(UserEventExtraDataRepositoryInterface::class);
        $userEventExtraDataRepository
            ->add(
                new User\Event\ExtraData($expectedUser, $event->reveal(), 'imported_from_comexposium', null, $dateTime)
            )
            ->shouldBeCalled()
        ;

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('takashi.kitano@nintendo.com')->shouldBeCalled()->willReturn(null);
        $userRepository->add($expectedUser)->shouldBeCalled();

        $userEventRepository = $this->prophesize(UserEventRepositoryInterface::class);
        $userEventRepository->add(new UserEvent($expectedUser, $event->reveal(), $type->reveal()))->shouldBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $registrationTemplateData = $this->prophesize(TemplateData::class);
        $sheetTemplateData = $this->prophesize(TemplateData::class);

        $synchronizer = $this->prophesize(Synchronizer::class);
        $synchronizer->set($registrationTemplateData->reveal(), $expectedUser)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher
            ->dispatch(Events::SHEET_UPDATED, new SheetUpdatedEvent($expectedSheet))
            ->shouldBeCalled()
        ;
        $eventDispatcher
            ->dispatch(
                Events::PARTICIPANT_IMPORTED_FROM_API,
                new ParticipantImportedFromApiEvent($expectedParticipant)
            )
            ->shouldBeCalled()
        ;

        $expectedSheetAndParticipantTemplateDataView = new SheetAndParticipantTemplateDataView(
            'Nintendo',
            ['whateverRegistrationSheetData'],
            ['whateverRegistrationParticipantData']
        );
        $expectedSheetAndParticipantTemplateDataView->setSheetTemplateData(['whateverSheetTemplateData']);

        $sheetAndParticipantTemplateDataHandler = $this->prophesize(SheetAndParticipantTemplateDataHandler::class);
        $sheetAndParticipantTemplateDataHandler
            ->handle($registrationView, $registrationTemplateData->reveal(), $sheetTemplateData->reveal())
            ->shouldBeCalled()
            ->willReturn($expectedSheetAndParticipantTemplateDataView)
        ;

        $participantOfSheetWithPackageParticipantAndPlanningDisabled = $this->prophesize(
            ParticipantOfSheetWithPackageParticipantAndPlanningDisabled::class
        );
        $participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($expectedParticipant)
            ->shouldBeCalled()
        ;

        $importSheetHandler = new ConvertRegistrationViewToSheet(
            $sheetAndParticipantTemplateDataHandler->reveal(),
            $userRepository->reveal(),
            $sheetRepository->reveal(),
            $participantRepository->reveal(),
            $sheetExtraDataRepository->reveal(),
            $userEventExtraDataRepository->reveal(),
            $userEventRepository->reveal(),
            $synchronizer->reveal(),
            $eventDispatcher->reveal(),
            $participantOfSheetWithPackageParticipantAndPlanningDisabled->reveal(),
            $dateTime
        );
        $importSheetHandler->handle(
            $event->reveal(),
            $type->reveal(),
            $registrationView,
            $registrationTemplateData->reveal(),
            $sheetTemplateData->reveal()
        );
    }
}
