<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Indicator\SheetIndicatorsViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Indicator\SheetIndicatorsViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\SheetListViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\SheetListViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\Indicator\SheetIndicatorsView;
use Proximum\Vimeet\Application\View\Agenda\Admin\SheetView;
use Proximum\Vimeet\Application\View\Agenda\AgendaParticipantView;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SheetListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $package = new Package($event, 'title', new DateTime());
        $type->setPackage($package);
        $sheet = SheetFactory::create($event, null, null, $type);

        $participant = $this->createParticipantMock($sheet, $user, 1);
        $sheet->addParticipant($participant);

        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $property = $reflectionSheet->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet, 12);
        $property->setAccessible(false);

        $sheetRepository   = $this->prophesize(SheetRepositoryInterface::class);
        $sheetInfoGuesser  = $this->prophesize(SheetInfoGuesser::class);
        $routerInterface   = $this->prophesize(RouterInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetIndicatorsViewQueryHandler = $this->prophesize(SheetIndicatorsViewQueryHandler::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $meetingRepository->countMeetingsOfEvent($event)->shouldBeCalled()->willReturn([12 => ['countMeetings' => 10]]);

        $product = new Product($event, 'plan', 'name', 'img.png', 10, 20, 1, 1, 1, true);
        $sheet->getPackage()->setPlans([$product]);

        $sheetRepository->getSheetsInCatalogByEvent($event)->shouldBeCalled()->willReturn([$sheet]);
        $sheetInfoGuesser->guessSheetTitle($sheet, 'fr')->shouldBeCalled()->willReturn('Titre fiche');
        $routerInterface->generate('admin_sheet_details', Argument::any())->willReturn('/my-url');

        $participantInfoGuesser->guessParticipantCompleteName($participant, 'fr')->shouldBeCalled();
        $sheetIndicatorsViewQueryHandler->handle(new SheetIndicatorsViewQuery($sheet))->shouldNotBeCalled();

        $query   = new SheetListViewQuery($event, 'fr');
        $handler = new SheetListViewQueryHandler(
            $sheetRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $sheetIndicatorsViewQueryHandler->reveal(),
            $meetingRepository->reveal(),
            $routerInterface->reveal(),
            $participantInfoGuesser->reveal()
        );

        $view = $handler->handle($query);

        $expectedParticipant = new AgendaParticipantView(1, null, 'email@email.com', []);

        $sheetIndicatorView = new SheetIndicatorsView(0, 0, 0, 0, 0, 10);
        $expectedView = new SheetView(
            $sheet->getId(),
            'Titre fiche',
            '',
            1,
            true,
            $sheetIndicatorView,
            false,
            null,
            '/my-url',
            [$expectedParticipant]
        );

        $this->assertEquals($expectedView, $view[0]);
    }

    public function testHandleWithParticipantFullyUnavailable()
    {
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $package = new Package($event, 'title', new DateTime());
        $type->setPackage($package);
        $sheet = SheetFactory::create($event, null, null, $type);

        $participant = $this->createParticipantMock($sheet, $user, 1);
        $participant->setFullyUnavailable(true);
        $participant->setHasRequestAssigned(true);
        $sheet->addParticipant($participant);

        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $property = $reflectionSheet->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet, 12);
        $property->setAccessible(false);

        $sheetRepository   = $this->prophesize(SheetRepositoryInterface::class);
        $sheetInfoGuesser  = $this->prophesize(SheetInfoGuesser::class);
        $routerInterface   = $this->prophesize(RouterInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetIndicatorsViewQueryHandler = $this->prophesize(SheetIndicatorsViewQueryHandler::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $meetingRepository->countMeetingsOfEvent($event)->shouldBeCalled()->willReturn([12 => ['countMeetings' => 10]]);

        $product = new Product($event, 'plan', 'name', 'img.png', 10, 20, 1, 1, 1, true);
        $sheet->getPackage()->setPlans([$product]);

        $sheetRepository->getSheetsInCatalogByEvent($event)->shouldBeCalled()->willReturn([$sheet]);
        $sheetInfoGuesser->guessSheetTitle($sheet, 'fr')->shouldBeCalled()->willReturn('Titre fiche');
        $routerInterface->generate('admin_sheet_details', Argument::any())->willReturn('/my-url');

        $participantInfoGuesser->guessParticipantCompleteName($participant, 'fr')->shouldBeCalled();
        $sheetIndicatorsViewQueryHandler->handle(new SheetIndicatorsViewQuery($sheet))->shouldNotBeCalled();

        $query   = new SheetListViewQuery($event, 'fr');
        $handler = new SheetListViewQueryHandler(
            $sheetRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $sheetIndicatorsViewQueryHandler->reveal(),
            $meetingRepository->reveal(),
            $routerInterface->reveal(),
            $participantInfoGuesser->reveal()
        );

        $view = $handler->handle($query);

        $expectedParticipant = new AgendaParticipantView(1, null, 'email@email.com', []);

        $sheetIndicatorView = new SheetIndicatorsView(0, 0, 0, 0, 0, 10);
        $expectedView = new SheetView(
            $sheet->getId(),
            'Titre fiche',
            '',
            1,
            true,
            $sheetIndicatorView,
            false,
            null,
            '/my-url',
            [$expectedParticipant],
            true
        );

        $this->assertEquals($expectedView, $view[0]);
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     * @param       $id
     *
     * @return Participant
     */
    public function createParticipantMock(Sheet $sheet, User $user, $id)
    {
        $participant = new Participant($sheet, $user, [], false, new \DateTime());
        $reflection  = new \ReflectionClass(Participant::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, $id);
        $property->setAccessible(false);

        return $participant;
    }
}
