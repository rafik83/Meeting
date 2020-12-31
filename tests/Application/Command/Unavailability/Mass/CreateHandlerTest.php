<?php

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability\Mass;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Create;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\CreateHandler;
use Proximum\Vimeet\Domain\Exception\Unavailability\InvalidTimeSlotException;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event    = EventFactory::createEvent();
        $category = new Category($event, 'Conference', 'title', '#123123', '#312312');
        $begin    = new \DateTime('2016-10-10 10:00');
        $end      = new \DateTime('2016-10-10 12:00');

        // Expected
        $expected = new Mass(
            $event,
            $category,
            'name',
            $begin,
            $end,
            true
        );
        $expected->createTranslation('fr', 'titre', 'description');
        $expected->createTranslation('en', 'title', 'description');

        // Mock
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->create($expected)->shouldBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->aggregateEventUsersFullUnavailability($event)->shouldBeCalled();
        $jobQueue->aggregateAvailableSlot($event)->shouldBeCalled();

        // Create
        $create               = new Create($event, null);
        $create->category     = $category;
        $create->begin        = $begin;
        $create->end          = $end;
        $create->name         = 'name';
        $create->translations = [
            'fr' => [
                'title'       => 'titre',
                'description' => 'description',
            ],
            'en' => [
                'title'       => 'title',
                'description' => 'description',
            ],
        ];

        // Handler
        $handler = new CreateHandler(
            $massRepository->reveal(),
            $jobQueue->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleNotBlocking()
    {
        $event    = EventFactory::createEvent();
        $category = new Category($event, 'Conference', 'title', '#123123', '#312312');
        $begin    = new \DateTime('2016-10-10 10:00');
        $end      = new \DateTime('2016-10-10 12:00');

        // Expected
        $expected = new Mass(
            $event,
            $category,
            'name',
            $begin,
            $end,
            false
        );
        $expected->createTranslation('fr', 'titre', 'description');
        $expected->createTranslation('en', 'title', 'description');

        // Mock
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->create($expected)->shouldBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->aggregateEventUsersFullUnavailability($event)->shouldNotBeCalled();
        $jobQueue->aggregateAvailableSlot($event)->shouldNotBeCalled();

        // Create
        $create               = new Create($event, null);
        $create->category     = $category;
        $create->begin        = $begin;
        $create->end          = $end;
        $create->blocking     = false;
        $create->name         = 'name';
        $create->translations = [
            'fr' => [
                'title'       => 'titre',
                'description' => 'description',
            ],
            'en' => [
                'title'       => 'title',
                'description' => 'description',
            ],
        ];

        // Handler
        $handler = new CreateHandler(
            $massRepository->reveal(),
            $jobQueue->reveal()
        );
        $handler->handle($create);
    }

    public function testDispatchHandle()
    {
        $event    = EventFactory::createEvent();
        $category = new Category($event, 'Conference', 'title', '#123123', '#312312');
        $begin    = new \DateTime('2016-10-10 10:00');
        $end      = new \DateTime('2016-10-10 12:00');

        // Expected
        $expected = new Mass(
            $event,
            $category,
            'name',
            $begin,
            $end,
            true,
            true,
            [
                ['from' => new \DateTime('2016-10-10 10:00'), 'to' => new \DateTime('2016-10-10 11:00')],
                ['from' => new \DateTime('2016-10-10 11:00'), 'to' => new \DateTime('2016-10-10 12:00')],
            ]
        );
        $expected->createTranslation('fr', 'titre', 'description');
        $expected->createTranslation('en', 'title', 'description');

        // Mock
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->create($expected)->shouldBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->aggregateEventUsersFullUnavailability($event)->shouldBeCalled();
        $jobQueue->aggregateAvailableSlot($event)->shouldBeCalled();

        // Create
        $create               = new Create($event, null);
        $create->category     = $category;
        $create->begin        = $begin;
        $create->end          = $end;
        $create->name         = 'name';
        $create->translations = [
            'fr' => [
                'title'       => 'titre',
                'description' => 'description',
            ],
            'en' => [
                'title'       => 'title',
                'description' => 'description',
            ],
        ];

        $create->dispatch = true;
        $create->timeSlots = [
            ['from' => new \DateTime('2016-10-10 10:00'), 'to' => new \DateTime('2016-10-10 11:00')],
            ['from' => new \DateTime('2016-10-10 11:00'), 'to' => new \DateTime('2016-10-10 12:00')],
        ];

        // Handler
        $handler = new CreateHandler($massRepository->reveal(), $jobQueue->reveal());
        $handler->handle($create);
    }

    public static function provideInvalidTimeSlots()
    {
        return [
            [
                new \DateTime('2016-10-10 10:00'),
                new \DateTime('2016-10-10 12:00'),
                [['from' => new \DateTime('2016-10-09 10:00'), 'to' => new \DateTime('2016-10-10 11:00')]],
            ],
            [
                new \DateTime('2016-10-10 10:00'),
                new \DateTime('2016-10-10 12:00'),
                [['from' => new \DateTime('2016-10-10 10:00'), 'to' => new \DateTime('2016-10-11 11:00')]],
            ],
            [
                new \DateTime('2016-10-10 10:00'),
                new \DateTime('2016-10-10 12:00'),
                [['from' => new \DateTime('2016-10-12 10:00'), 'to' => new \DateTime('2016-10-12 11:00')]],
            ],
            [
                new \DateTime('2016-10-10 10:00'),
                new \DateTime('2016-10-10 12:00'),
                [['from' => new \DateTime('2016-10-10 11:00'), 'to' => new \DateTime('2016-10-10 10:00')]],
            ],
        ];
    }

    /**
     * @dataProvider provideInvalidTimeSlots
     *
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param array              $timeSlots
     */
    public function testInvalidTimeSlotException(\DateTimeInterface $begin, \DateTimeInterface $end, array $timeSlots)
    {
        $this->expectException(InvalidTimeSlotException::class);

        $event    = EventFactory::createEvent();
        $category = new Category($event, 'Conference', 'title', '#123123', '#312312');

        // Mock
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->create()->shouldNotBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->aggregateEventUsersFullUnavailability($event)->shouldNotBeCalled();
        $jobQueue->aggregateAvailableSlot($event)->shouldNotBeCalled();

        // Create
        $create               = new Create($event, null);
        $create->category     = $category;
        $create->begin        = $begin;
        $create->end          = $end;
        $create->name         = 'name';
        $create->translations = [
            'fr' => [
                'title'       => 'titre',
                'description' => 'description',
            ],
            'en' => [
                'title'       => 'title',
                'description' => 'description',
            ],
        ];

        $create->dispatch = true;
        $create->timeSlots = $timeSlots;

        // Handler
        $handler = new CreateHandler($massRepository->reveal(), $jobQueue->reveal());
        $handler->handle($create);
    }
}
