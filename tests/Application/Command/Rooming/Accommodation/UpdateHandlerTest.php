<?php

namespace Proximum\Vimeet\Tests\Application\Command\Rooming\Accommodation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Rooming\Accommodation\AccommodationOvernightCapacityView;
use Proximum\Vimeet\Application\Command\Rooming\Accommodation\Update;
use Proximum\Vimeet\Application\Command\Rooming\Accommodation\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\AccommodationOvernightCapacity;
use Proximum\Vimeet\Domain\Repository\Rooming\AccommodationRepositoryInterface;

class UpdateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $date1 = new \DateTime('2018-10-10 10:00:00.000');
        $date2 = new \DateTime('2018-10-11 10:00:00.000');
        $date3 = new \DateTime('2018-10-12 10:00:00.000');
        $accommodation = new Accommodation($event->reveal(), 'Mariott');
        $accommodation->addOvernightCapacity(new AccommodationOvernightCapacity($accommodation, $date1, 100));
        $accommodation->addOvernightCapacity(new AccommodationOvernightCapacity($accommodation, $date2, 90));

        $expected = new Accommodation($event->reveal(), 'Old Mariott');
        $expected->addOvernightCapacity(
            new AccommodationOvernightCapacity($expected, $date1, 80)
        );
        $expected->addOvernightCapacity(
            new AccommodationOvernightCapacity($expected, $date2, 120)
        );
        $expected->addOvernightCapacity(
            new AccommodationOvernightCapacity($expected, $date3, 100)
        );

        $accommodationRepository = $this->prophesize(AccommodationRepositoryInterface::class);
        $accommodationRepository
            ->update($expected)
            ->shouldBeCalled()
        ;

        $updateHandler = new UpdateHandler($accommodationRepository->reveal());
        $update = new Update($accommodation);
        $update->title = 'Old Mariott';
        $update->overnightCapacities = [
            new AccommodationOvernightCapacityView($date1, 80),
            new AccommodationOvernightCapacityView($date2, 120),
            new AccommodationOvernightCapacityView($date3, 100),
        ];
        $updateHandler->handle($update);
    }
}
