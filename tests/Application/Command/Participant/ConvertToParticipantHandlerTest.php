<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\Command\Participant\SheetAndParticipantTemplateDataHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedFromApiEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Event\User\Event\UpdatedEvent;
use Proximum\Vimeet\Application\View\Participant\SheetAndParticipantTemplateDataView;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConvertToParticipantHandlerTest extends TestCase
{
    private $userRepository;
    private $sheetRepository;
    private $participantRepository;
    private $userEventExtraDataRepository;
    private $userEventRepository;
    private $synchronizer;
    private $eventDispatcher;
    private $dateTime;
    private $registrationTemplateData;
    private $sheetTemplateData;
    private $sheetAndParticipantTemplateDataHandler;

    public function setUp(): void
    {
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->userEventExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->userEventRepository = $this->prophesize(UserEventRepositoryInterface::class);
        $this->synchronizer = $this->prophesize(Synchronizer::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->sheetAndParticipantTemplateDataHandler = $this->prophesize(
            SheetAndParticipantTemplateDataHandler::class
        );

        $this->registrationTemplateData = $this->prophesize(TemplateData::class);
        $this->sheetTemplateData = $this->prophesize(TemplateData::class);

        $this->dateTime = new \DateTime();
    }

    public function testHandleNotKnownUser(): void
    {
        $email = 'korben@dallas.us';
        $dataIndexedByTag = ['whatever-data'];

        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale('en')->shouldBeCalled()->willReturn('fr');
        $type = $this->prophesize(Type::class);

        $this->userRepository->findByEmail($email)->shouldBeCalled()->willReturn(null);
        $user = new User($email, '', '', 'fr');
        $user->setAccount(new User\Account());
        $this->userRepository->add($user)->shouldBeCalled();

        $sheet = new Sheet(
            $event->reveal(),
            $type->reveal(),
            ['sheetTemplateData'],
            $user,
            $this->dateTime
        );
        $sheet->setRegistrationData(['sheetRegistrationData']);
        $sheet->setTitle('Korben Dallas Taxi Cie');
        $sheet->setImported(true);
        $sheet->setInCatalogAt($this->dateTime);
        $sheet->setInCatalog(true);

        // Sheet must be created with "validated" state
        $sheet->setState('validated');

        $participant = new Participant($sheet, $user, ['participantRegistrationData'], false, $this->dateTime);
        $participant->setImported(true);

        $this->sheetRepository->add($sheet)->shouldBeCalled();
        $this->participantRepository->add($participant)->shouldBeCalled();
        $this
            ->userEventRepository
            ->add(new UserEvent($user, $event->reveal(), $type->reveal()))
            ->shouldBeCalled()
        ;
        $this
            ->eventDispatcher
            ->dispatch(Events::PARTICIPANT_IMPORTED_FROM_API, new ParticipantImportedFromApiEvent($participant))
            ->shouldBeCalled()
        ;

        $this
            ->sheetAndParticipantTemplateDataHandler->handle(
                $dataIndexedByTag,
                $this->registrationTemplateData->reveal(),
                $this->sheetTemplateData->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                new SheetAndParticipantTemplateDataView(
                    'Korben Dallas Taxi Cie',
                    ['sheetRegistrationData'],
                    ['participantRegistrationData'],
                    ['sheetTemplateData']
                )
            )
        ;

        $this->synchronizer->set($this->registrationTemplateData->reveal(), $user)->shouldBeCalled();
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, new SheetUpdatedEvent($sheet))->shouldBeCalled();

        $convertToParticipantHandler = new ConvertToParticipantHandler(
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->userEventExtraDataRepository->reveal(),
            $this->userEventRepository->reveal(),
            $this->sheetAndParticipantTemplateDataHandler->reveal(),
            $this->synchronizer->reveal(),
            $this->eventDispatcher->reveal(),
            $this->dateTime
        );

        $result = $convertToParticipantHandler->handle(
            new ConvertToParticipant(
                $event->reveal(),
                $type->reveal(),
                $email,
                'en',
                $dataIndexedByTag,
                $this->registrationTemplateData->reveal(),
                $this->sheetTemplateData->reveal(),
                null,
                'validated', // sheet must be created with "validated" state
                true
            )
        );

        $this->assertEquals($participant, $result);
    }

    public function testHandleKnownUserWithNoExtraData(): void
    {
        $email = 'korben@dallas.us';
        $dataIndexedByTag = [];

        $user = $this->prophesize(User::class);

        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

        $this->userRepository->findByEmail($email)->shouldBeCalled()->willReturn($user->reveal());

        $this
            ->userEventExtraDataRepository
            ->getExtraDataForEventNameAndUser($event->reveal(), 'whatever-extra-data', $user->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $sheet = new Sheet(
            $event->reveal(),
            $type->reveal(),
            ['sheetTemplateData'],
            $user->reveal(),
            $this->dateTime
        );
        $sheet->setRegistrationData(['sheetRegistrationData']);
        $sheet->setTitle('Korben Dallas Taxi Cie');
        $sheet->setImported(true);

        $participant = new Participant($sheet, $user->reveal(), ['participantRegistrationData'], false, $this->dateTime);
        $participant->setImported(true);

        $this->sheetRepository->add($sheet)->shouldBeCalled();
        $this->participantRepository->add($participant)->shouldBeCalled();
        $this
            ->userEventRepository
            ->add(new UserEvent($user->reveal(), $event->reveal(), $type->reveal()))
            ->shouldBeCalled()
        ;

        $this->sheetAndParticipantTemplateDataHandler
            ->handle(
                $dataIndexedByTag,
                $this->registrationTemplateData->reveal(),
                $this->sheetTemplateData->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                new SheetAndParticipantTemplateDataView(
                    'Korben Dallas Taxi Cie',
                    ['sheetRegistrationData'],
                    ['participantRegistrationData'],
                    ['sheetTemplateData']
                )
            )
        ;

        $this
            ->eventDispatcher
            ->dispatch(Events::PARTICIPANT_IMPORTED_FROM_API, new ParticipantImportedFromApiEvent($participant))
            ->shouldBeCalled()
        ;
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, new SheetUpdatedEvent($sheet))->shouldBeCalled();

        $this->synchronizer->set($this->registrationTemplateData->reveal(), $user->reveal())->shouldBeCalled();

        $convertToParticipantHandler = new ConvertToParticipantHandler(
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->userEventExtraDataRepository->reveal(),
            $this->userEventRepository->reveal(),
            $this->sheetAndParticipantTemplateDataHandler->reveal(),
            $this->synchronizer->reveal(),
            $this->eventDispatcher->reveal(),
            $this->dateTime
        );

        $result = $convertToParticipantHandler->handle(
            new ConvertToParticipant(
                $event->reveal(),
                $type->reveal(),
                $email,
                'en',
                $dataIndexedByTag,
                $this->registrationTemplateData->reveal(),
                $this->sheetTemplateData->reveal(),
                'whatever-extra-data'
            )
        );

        $this->assertEquals($participant, $result);
    }

    public function testHandleKnownUserWithExtraData(): void
    {
        $email = 'korben@dallas.us';
        $dataIndexedByTag = [];

        $user = $this->prophesize(User::class);

        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

        $this->userRepository->findByEmail($email)->shouldBeCalled()->willReturn($user->reveal());

        $whateverExtraData = $this->prophesize(ExtraData::class);

        $this
            ->userEventExtraDataRepository
            ->getExtraDataForEventNameAndUser($event->reveal(), 'whatever-extra-data', $user->reveal())
            ->shouldBeCalled()
            ->willReturn($whateverExtraData->reveal())
        ;

        $this
            ->eventDispatcher
            ->dispatch(Argument::any())
            ->shouldNotBeCalled()
        ;

        $convertToParticipantHandler = new ConvertToParticipantHandler(
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->userEventExtraDataRepository->reveal(),
            $this->userEventRepository->reveal(),
            $this->sheetAndParticipantTemplateDataHandler->reveal(),
            $this->synchronizer->reveal(),
            $this->eventDispatcher->reveal(),
            $this->dateTime
        );

        $result = $convertToParticipantHandler->handle(
            new ConvertToParticipant(
                $event->reveal(),
                $type->reveal(),
                $email,
                'en',
                $dataIndexedByTag,
                $this->registrationTemplateData->reveal(),
                $this->sheetTemplateData->reveal(),
                'whatever-extra-data'
            )
        );

        $this->assertNull($result);
    }
}
