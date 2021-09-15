<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\Catalog;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Event\Day\EventOver;
use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type as ParticipationType;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\AvailabilityConfirmationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\AvailabilityConfirmationCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\AvailabilityConfirmationCheckerView;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class AvailabilityConfirmationCheckerHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    public $agendaAccessChecker;

    /** @var ObjectProphecy */
    public $eventOver;

    /** @var ObjectProphecy */
    public $flashBag;

    /** @var ObjectProphecy */
    public $extraDataRepository;

    /** @var ObjectProphecy */
    public $router;

    /** @var string */
    public $featureAvailabilityConfirmationActivated;

    /** @var ObjectProphecy */
    public $sheet;

    /** @var ObjectProphecy */
    public $event;

    /** @var ObjectProphecy */
    public $user;

    /** @var ObjectProphecy */
    public $type;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
        $this->agendaAccessChecker = $this->prophesize(AgendaAccessChecker::class);
        $this->eventOver = $this->prophesize(EventOver::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->featureAvailabilityConfirmationActivated = '1';
        $this->type = $this->prophesize(ParticipationType::class);
    }

    public function testHandleFeatureFlagDisabled()
    {
        $expected = new AvailabilityConfirmationCheckerView(
            AvailabilityConfirmationCheckerView::ALLOWED_TO_ACCESS,
            null
        );

        $handler = new AvailabilityConfirmationCheckerHandler(
            $this->agendaAccessChecker->reveal(),
            $this->eventOver->reveal(),
            $this->flashBag->reveal(),
            $this->extraDataRepository->reveal(),
            $this->router->reveal(),
            ''
        );
        $result = $handler->handle(
            new AvailabilityConfirmationChecker(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                AvailabilityConfirmationChecker::ORIGIN_CATALOG
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleEventOver()
    {
        $expected = new AvailabilityConfirmationCheckerView(
            AvailabilityConfirmationCheckerView::ALLOWED_TO_ACCESS,
            null
        );

        $this->eventOver->isEventOver($this->event->reveal())->shouldBeCalled()->willReturn(true);

        $handler = new AvailabilityConfirmationCheckerHandler(
            $this->agendaAccessChecker->reveal(),
            $this->eventOver->reveal(),
            $this->flashBag->reveal(),
            $this->extraDataRepository->reveal(),
            $this->router->reveal(),
            $this->featureAvailabilityConfirmationActivated
        );
        $result = $handler->handle(
            new AvailabilityConfirmationChecker(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                AvailabilityConfirmationChecker::ORIGIN_CATALOG
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleAgendaClosed()
    {
        $expected = new AvailabilityConfirmationCheckerView(
            AvailabilityConfirmationCheckerView::ALLOWED_TO_ACCESS,
            null
        );

        $this->eventOver->isEventOver($this->event->reveal())->shouldBeCalled()->willReturn(false);
        $this->agendaAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(false);

        $handler = new AvailabilityConfirmationCheckerHandler(
            $this->agendaAccessChecker->reveal(),
            $this->eventOver->reveal(),
            $this->flashBag->reveal(),
            $this->extraDataRepository->reveal(),
            $this->router->reveal(),
            $this->featureAvailabilityConfirmationActivated
        );
        $result = $handler->handle(
            new AvailabilityConfirmationChecker(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                AvailabilityConfirmationChecker::ORIGIN_CATALOG
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleUnavailabilityManagementDisabled(): void
    {
        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->type->getAvailabilityType()->willReturn(ParticipationType::TYPE_MANAGEMENT_NONE);

        $expected = new AvailabilityConfirmationCheckerView(
            AvailabilityConfirmationCheckerView::ALLOWED_TO_ACCESS,
            null
        );

        $this->eventOver->isEventOver($this->event->reveal())->shouldBeCalled()->willReturn(false);
        $this->agendaAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(true);

        $handler = new AvailabilityConfirmationCheckerHandler(
            $this->agendaAccessChecker->reveal(),
            $this->eventOver->reveal(),
            $this->flashBag->reveal(),
            $this->extraDataRepository->reveal(),
            $this->router->reveal(),
            $this->featureAvailabilityConfirmationActivated
        );
        $result = $handler->handle(
            new AvailabilityConfirmationChecker(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                AvailabilityConfirmationChecker::ORIGIN_CATALOG
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleAlreadyConfirmed()
    {
        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->type->getAvailabilityType()->willReturn(ParticipationType::TYPE_MANAGEMENT_UNAVAILABLE);

        $expected = new AvailabilityConfirmationCheckerView(
            AvailabilityConfirmationCheckerView::ALLOWED_TO_ACCESS,
            null
        );

        $this->eventOver->isEventOver($this->event->reveal())->shouldBeCalled()->willReturn(false);
        $this->agendaAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(true);

        $extraData = $this->prophesize(ExtraData::class);
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user->reveal()
            )->shouldBeCalled()
            ->willReturn($extraData->reveal())
        ;

        $handler = new AvailabilityConfirmationCheckerHandler(
            $this->agendaAccessChecker->reveal(),
            $this->eventOver->reveal(),
            $this->flashBag->reveal(),
            $this->extraDataRepository->reveal(),
            $this->router->reveal(),
            $this->featureAvailabilityConfirmationActivated
        );
        $result = $handler->handle(
            new AvailabilityConfirmationChecker(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                AvailabilityConfirmationChecker::ORIGIN_CATALOG
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandle()
    {
        $this->sheet->getId()->willReturn(12);
        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->type->getAvailabilityType()->willReturn(ParticipationType::TYPE_MANAGEMENT_UNAVAILABLE);

        $expected = new AvailabilityConfirmationCheckerView(
            AvailabilityConfirmationCheckerView::REDIRECT,
            'route_to_confirmation'
        );

        $this->eventOver->isEventOver($this->event->reveal())->shouldBeCalled()->willReturn(false);
        $this->agendaAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(true);

        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::AVAILABILITY_CONFIRMATION,
                $this->user->reveal()
            )->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->flashBag->add(AvailabilityConfirmationChecker::ORIGIN_CATALOG, 12)->shouldBeCalled();
        $this->router
            ->generate(
                AvailabilityConfirmationCheckerHandler::ROUTE_AVAILABILITY_CONFIRMATION,
                ['sheet' => 12]
            )
            ->shouldBeCalled()
            ->willReturn('route_to_confirmation')
        ;

        $handler = new AvailabilityConfirmationCheckerHandler(
            $this->agendaAccessChecker->reveal(),
            $this->eventOver->reveal(),
            $this->flashBag->reveal(),
            $this->extraDataRepository->reveal(),
            $this->router->reveal(),
            $this->featureAvailabilityConfirmationActivated
        );
        $result = $handler->handle(
            new AvailabilityConfirmationChecker(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                AvailabilityConfirmationChecker::ORIGIN_CATALOG
            )
        );

        $this->assertEquals($expected, $result);
    }
}
