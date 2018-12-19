<?php

namespace Proximum\Vimeet\Tests\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignAccommodation;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignAccommodationHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;

class AssignAccommodationHandlerTest extends TestCase
{
    public function test_handle(): void
    {
        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $accommodation = $this->prophesize(Accommodation::class);
        $stayRepository = $this->prophesize(StayRepositoryInterface::class);

        $expected = new Stay(
            $event->reveal(),
            $user->reveal(),
            $arrivalDate,
            $departureDate,
            $accommodation->reveal(),
            'single'
        );

        $stayRepository
            ->add($expected)
            ->shouldBeCalled();

        $assignAccommodation = new AssignAccommodation(
            $event->reveal(),
            $user->reveal(),
            $arrivalDate,
            $departureDate
        );
        $assignAccommodation->accommodation = $accommodation->reveal();

        $handler = new AssignAccommodationHandler($stayRepository->reveal());
        $handler->handle($assignAccommodation);
    }

    public function test_handle_double(): void
    {
        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $accommodation = $this->prophesize(Accommodation::class);
        $stayRepository = $this->prophesize(StayRepositoryInterface::class);

        $expected = new Stay(
            $event->reveal(),
            $user->reveal(),
            $arrivalDate,
            $departureDate,
            $accommodation->reveal(),
            'double'
        );

        $stayRepository
            ->add($expected)
            ->shouldBeCalled()
        ;

        $assignAccommodation = new AssignAccommodation(
            $event->reveal(),
            $user->reveal(),
            $arrivalDate,
            $departureDate
        );
        $assignAccommodation->accommodation = $accommodation->reveal();
        $assignAccommodation->roommate = null;
        $assignAccommodation->roomType = 'double';

        $handler = new AssignAccommodationHandler($stayRepository->reveal());
        $handler->handle($assignAccommodation);
    }

    public function test_handle_double_with_roommate(): void
    {
        $arrivalDate = new \DateTime('2018-12-10');
        $departureDate = new \DateTime('2018-12-12');
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $roommate = $this->prophesize(User::class);
        $accommodation = $this->prophesize(Accommodation::class);
        $stayRepository = $this->prophesize(StayRepositoryInterface::class);

        $expected = new Stay(
            $event->reveal(),
            $user->reveal(),
            $arrivalDate,
            $departureDate,
            $accommodation->reveal(),
            'double'
        );
        $expected->addUser($roommate->reveal());

        $stayRepository
            ->add($expected)
            ->shouldBeCalled()
        ;

        $assignAccommodation = new AssignAccommodation(
            $event->reveal(),
            $user->reveal(),
            $arrivalDate,
            $departureDate
        );
        $assignAccommodation->accommodation = $accommodation->reveal();
        $assignAccommodation->roommate = $roommate->reveal();
        $assignAccommodation->roomType = 'double';

        $handler = new AssignAccommodationHandler($stayRepository->reveal());
        $handler->handle($assignAccommodation);
    }
}
