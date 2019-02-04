<?php

namespace Proximum\Vimeet\Tests\Application\Command\Rooming\Stay;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignAccommodation;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignAccommodationHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Rooming\Accommodation\HasNoRemainingOvernightException;
use Proximum\Vimeet\Domain\Rooming\Accommodation\HasRemainingOvernight;
use Proximum\Vimeet\Domain\Rooming\Stay\HasStayForPeriod;
use Proximum\Vimeet\Domain\Rooming\Stay\HasStayForPeriodException;
use Proximum\Vimeet\Domain\Rooming\Stay\RoommateHasStayForPeriodException;

class AssignAccommodationHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private
        $accommodation,
        $stayRepository,
        $hasRemainingOvernight,
        $hasStayForPeriod,
        $user,
        $event
    ;

    public function setUp()
    {
        $this->accommodation = $this->prophesize(Accommodation::class);
        $this->stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $this->hasRemainingOvernight = $this->prophesize(HasRemainingOvernight::class);
        $this->hasStayForPeriod = $this->prophesize(HasStayForPeriod::class);
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
    }

    public function test_handle(): void
    {
        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');

        $expected = new Stay(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            $this->accommodation->reveal(),
            'single',
            ''
        );

        $this->stayRepository
            ->add($expected)
            ->shouldBeCalled();

        $assignAccommodation = new AssignAccommodation(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            null
        );
        $assignAccommodation->accommodation = $this->accommodation->reveal();

        $this->hasRemainingOvernight
            ->isSatisfiedBy($this->accommodation->reveal(), $arrivalDate, $departureDate)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $this->user->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $handler = new AssignAccommodationHandler(
            $this->stayRepository->reveal(),
            $this->hasRemainingOvernight->reveal(),
            $this->hasStayForPeriod->reveal()
        );
        $handler->handle($assignAccommodation);
    }

    public function test_handle_with_room_number(): void
    {
        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');

        $expected = new Stay(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            $this->accommodation->reveal(),
            'single',
            'A123'
        );

        $this->stayRepository
            ->add($expected)
            ->shouldBeCalled();

        $assignAccommodation = new AssignAccommodation(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            null
        );
        $assignAccommodation->accommodation = $this->accommodation->reveal();
        $assignAccommodation->roomNumber = 'A123';

        $this->hasRemainingOvernight
            ->isSatisfiedBy($this->accommodation->reveal(), $arrivalDate, $departureDate)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $this->user->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $handler = new AssignAccommodationHandler(
            $this->stayRepository->reveal(),
            $this->hasRemainingOvernight->reveal(),
            $this->hasStayForPeriod->reveal()
        );
        $handler->handle($assignAccommodation);
    }

    public function test_handle_no_remaining_overnight(): void
    {
        $this->expectException(HasNoRemainingOvernightException::class);

        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');

        $assignAccommodation = new AssignAccommodation(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            null
        );
        $assignAccommodation->accommodation = $this->accommodation->reveal();

        $this->hasRemainingOvernight
            ->isSatisfiedBy($this->accommodation->reveal(), $arrivalDate, $departureDate)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $this->user->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldNotBeCalled();

        $this->stayRepository
            ->add(Argument::any())
            ->shouldNotBeCalled();

        $handler = new AssignAccommodationHandler(
            $this->stayRepository->reveal(),
            $this->hasRemainingOvernight->reveal(),
            $this->hasStayForPeriod->reveal()
        );
        $handler->handle($assignAccommodation);
    }

    public function test_handle_has_stay_on_period(): void
    {
        $this->expectException(HasStayForPeriodException::class);

        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');

        $assignAccommodation = new AssignAccommodation(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            null
        );
        $assignAccommodation->accommodation = $this->accommodation->reveal();

        $this->hasRemainingOvernight
            ->isSatisfiedBy($this->accommodation->reveal(), $arrivalDate, $departureDate)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $this->user->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $this->stayRepository
            ->add(Argument::any())
            ->shouldNotBeCalled();

        $handler = new AssignAccommodationHandler(
            $this->stayRepository->reveal(),
            $this->hasRemainingOvernight->reveal(),
            $this->hasStayForPeriod->reveal()
        );
        $handler->handle($assignAccommodation);
    }

    public function test_handle_double(): void
    {
        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');

        $expected = new Stay(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            $this->accommodation->reveal(),
            'double',
            ''
        );

        $this->stayRepository
            ->add($expected)
            ->shouldBeCalled()
        ;

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $this->user->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $assignAccommodation = new AssignAccommodation(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            null
        );
        $assignAccommodation->accommodation = $this->accommodation->reveal();
        $assignAccommodation->roommate = null;
        $assignAccommodation->roomType = 'double';

        $this->hasRemainingOvernight
            ->isSatisfiedBy($this->accommodation->reveal(), $arrivalDate, $departureDate)
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new AssignAccommodationHandler(
            $this->stayRepository->reveal(),
            $this->hasRemainingOvernight->reveal(),
            $this->hasStayForPeriod->reveal()
        );
        $handler->handle($assignAccommodation);
    }

    public function test_handle_double_with_roommate(): void
    {
        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');
        $roommate = $this->prophesize(User::class);

        $expected = new Stay(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            $this->accommodation->reveal(),
            'double',
            ''
        );
        $expected->addUser($roommate->reveal());

        $this->stayRepository
            ->add($expected)
            ->shouldBeCalled()
        ;

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $this->user->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $roommate->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $assignAccommodation = new AssignAccommodation(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            null
        );
        $assignAccommodation->accommodation = $this->accommodation->reveal();
        $assignAccommodation->roommate = $roommate->reveal();
        $assignAccommodation->roomType = 'double';

        $this->hasRemainingOvernight
            ->isSatisfiedBy($this->accommodation->reveal(), $arrivalDate, $departureDate)
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new AssignAccommodationHandler(
            $this->stayRepository->reveal(),
            $this->hasRemainingOvernight->reveal(),
            $this->hasStayForPeriod->reveal()
        );
        $handler->handle($assignAccommodation);
    }

    public function test_handle_double_with_invalid_roommate(): void
    {
        $this->expectException(RoommateHasStayForPeriodException::class);

        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');
        $roommate = $this->prophesize(User::class);

        $expected = new Stay(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            $this->accommodation->reveal(),
            'double',
            ''
        );
        $expected->addUser($roommate->reveal());

        $this->stayRepository
            ->add($expected)
            ->shouldNotBeCalled()
        ;

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $this->user->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $roommate->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $assignAccommodation = new AssignAccommodation(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            null
        );
        $assignAccommodation->accommodation = $this->accommodation->reveal();
        $assignAccommodation->roommate = $roommate->reveal();
        $assignAccommodation->roomType = 'double';

        $this->hasRemainingOvernight
            ->isSatisfiedBy($this->accommodation->reveal(), $arrivalDate, $departureDate)
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new AssignAccommodationHandler(
            $this->stayRepository->reveal(),
            $this->hasRemainingOvernight->reveal(),
            $this->hasStayForPeriod->reveal()
        );
        $handler->handle($assignAccommodation);
    }

    public function test_handle_twin(): void
    {
        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');

        $expected = new Stay(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            $this->accommodation->reveal(),
            'twin',
            ''
        );

        $this->stayRepository
            ->add($expected)
            ->shouldBeCalled()
        ;

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $this->user->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $assignAccommodation = new AssignAccommodation(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            null
        );
        $assignAccommodation->accommodation = $this->accommodation->reveal();
        $assignAccommodation->roommate = null;
        $assignAccommodation->roomType = 'twin';

        $this->hasRemainingOvernight
            ->isSatisfiedBy($this->accommodation->reveal(), $arrivalDate, $departureDate)
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new AssignAccommodationHandler(
            $this->stayRepository->reveal(),
            $this->hasRemainingOvernight->reveal(),
            $this->hasStayForPeriod->reveal()
        );
        $handler->handle($assignAccommodation);
    }

    public function test_handle_twin_with_roommate(): void
    {
        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');
        $roommate = $this->prophesize(User::class);

        $expected = new Stay(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            $this->accommodation->reveal(),
            'twin',
            ''
        );
        $expected->addUser($roommate->reveal());

        $this->stayRepository
            ->add($expected)
            ->shouldBeCalled()
        ;

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $this->user->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $roommate->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $assignAccommodation = new AssignAccommodation(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            null
        );
        $assignAccommodation->accommodation = $this->accommodation->reveal();
        $assignAccommodation->roommate = $roommate->reveal();
        $assignAccommodation->roomType = 'twin';

        $this->hasRemainingOvernight
            ->isSatisfiedBy($this->accommodation->reveal(), $arrivalDate, $departureDate)
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new AssignAccommodationHandler(
            $this->stayRepository->reveal(),
            $this->hasRemainingOvernight->reveal(),
            $this->hasStayForPeriod->reveal()
        );
        $handler->handle($assignAccommodation);
    }

    public function test_handle_twin_with_invalid_roommate(): void
    {
        $this->expectException(RoommateHasStayForPeriodException::class);

        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');
        $roommate = $this->prophesize(User::class);

        $expected = new Stay(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            $this->accommodation->reveal(),
            'twin',
            ''
        );
        $expected->addUser($roommate->reveal());

        $this->stayRepository
            ->add($expected)
            ->shouldNotBeCalled()
        ;

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $this->user->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(false);

        $this->hasStayForPeriod
            ->isSatisfiedBy(
                $this->event->reveal(),
                $roommate->reveal(),
                $arrivalDate,
                $departureDate
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $assignAccommodation = new AssignAccommodation(
            $this->event->reveal(),
            $this->user->reveal(),
            $arrivalDate,
            $departureDate,
            null
        );
        $assignAccommodation->accommodation = $this->accommodation->reveal();
        $assignAccommodation->roommate = $roommate->reveal();
        $assignAccommodation->roomType = 'twin';

        $this->hasRemainingOvernight
            ->isSatisfiedBy($this->accommodation->reveal(), $arrivalDate, $departureDate)
            ->shouldBeCalled()
            ->willReturn(true);

        $handler = new AssignAccommodationHandler(
            $this->stayRepository->reveal(),
            $this->hasRemainingOvernight->reveal(),
            $this->hasStayForPeriod->reveal()
        );
        $handler->handle($assignAccommodation);
    }
}
