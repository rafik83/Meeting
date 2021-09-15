<?php

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability\Mass;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Update;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Repository\Unavailability\MassAssignmentRepository;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $type1       = $this->prophesize(Type::class);
        $type1->getId()->willReturn(111);
        $type2       = $this->prophesize(Type::class);
        $type2->getId()->willReturn(222);
        $oldCategory = new Category($event, 'Conference', 'old title', '#AABBCC', '#CCBBAA');
        $category    = new Category($event, 'Conference', 'title', '#123123', '#312312');
        $oldBegin    = new \DateTime('2016-10-10 10:00:00.000');
        $oldEnd      = new \DateTime('2016-10-10 12:00:00.000');
        $begin       = new \DateTime('2016-10-12 10:00:00.000');
        $end         = new \DateTime('2016-10-12 12:00:00.000');

        // Existing
        $existingMass = new Mass(
            $event,
            $oldCategory,
            'old name',
            $oldBegin,
            $oldEnd,
            false,
            false,
            [],
            [$type1->reveal(), $type2->reveal()]
        );
        $existingMass->createTranslation('fr', 'vieux titre', 'vieille description');
        $existingMass->createTranslation('en', 'old title', 'old description');

        // Expected
        $expectedMass = new Mass(
            $event,
            $category,
            'name',
            $begin,
            $end,
            true,
            false,
            [],
            [$type2->reveal()]
        );
        $expectedMass->createTranslation('fr', 'titre', 'description');
        $expectedMass->createTranslation('en', 'title', 'description');

        // Mock
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->update($expectedMass)->shouldBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->aggregateEventUsersFullUnavailability($event)->shouldBeCalled();
        $jobQueue->aggregateAvailableSlot($event)->shouldBeCalled();

        $user1 = $this->prophesize(User::class);
        $user1->getId()->shouldBeCalled()->willReturn(1);

        $user2 = $this->prophesize(User::class);
        $user2->getId()->shouldBeCalled()->willReturn(2);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEventWithDispatch($event, $existingMass)->shouldBeCalled()->willReturn([$user1->reveal(), $user2->reveal()]);

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->getTypesByUserIds($event, [1])->shouldBeCalled()->willReturn([$type1->reveal()]);
        $typeRepository->getTypesByUserIds($event, [2])->shouldBeCalled()->willReturn([$type2->reveal()]);

        $massAssignmentRepository = $this->prophesize(MassAssignmentRepository::class);
        $massAssignmentRepository->removeByUserAndMass($user1->reveal(), $existingMass)->shouldBeCalled();
        $massAssignmentRepository->removeByUserAndMass($user2->reveal(), $existingMass)->shouldNotBeCalled();

        // Create
        $update               = new Update($existingMass);
        $update->category     = $category;
        $update->begin        = $begin;
        $update->end          = $end;
        $update->name         = 'name';
        $update->blocking     = true;
        $update->types        = [$type2->reveal()];
        $update->translations = [
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
        $handler = new UpdateHandler($massRepository->reveal(), $jobQueue->reveal(), $userRepository->reveal(), $typeRepository->reveal(), $massAssignmentRepository->reveal());
        $handler->handle($update);
    }

    public function testHandleDispatch()
    {
        $event    = EventFactory::createEvent();
        $oldCategory = new Category($event, 'Conference', 'old title', '#AABBCC', '#CCBBAA');
        $category    = new Category($event, 'Conference', 'title', '#123123', '#312312');
        $oldBegin    = new \DateTime('2016-10-10 10:00');
        $oldEnd      = new \DateTime('2016-10-10 12:00');
        $begin       = new \DateTime('2016-10-12 10:00');
        $end         = new \DateTime('2016-10-12 12:00');

        // Existing
        $existingMass = new Mass(
            $event,
            $oldCategory,
            'old name',
            $oldBegin,
            $oldEnd,
            false
        );
        $existingMass->createTranslation('fr', 'vieux titre', 'vieille description');
        $existingMass->createTranslation('en', 'old title', 'old description');

        // Expected
        $expectedMass = new Mass(
            $event,
            $category,
            'name',
            $begin,
            $end,
            true,
            true,
            [
                ['from' => new \DateTime('2016-10-12 10:00'), 'to' => new \DateTime('2016-10-12 11:00')],
                ['from' => new \DateTime('2016-10-12 11:00'), 'to' => new \DateTime('2016-10-12 12:00')],
            ]
        );
        $expectedMass->createTranslation('fr', 'titre', 'description');
        $expectedMass->createTranslation('en', 'title', 'description');

        // Mock
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->update($expectedMass)->shouldBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->aggregateEventUsersFullUnavailability($event)->shouldBeCalled();
        $jobQueue->aggregateAvailableSlot($event)->shouldBeCalled();

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEventWithDispatch($event, $existingMass)->shouldBeCalled()->willReturn([]);

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $massAssignmentRepository = $this->prophesize(MassAssignmentRepository::class);

        // Create
        $update               = new Update($existingMass);
        $update->category     = $category;
        $update->begin        = $begin;
        $update->end          = $end;
        $update->name         = 'name';
        $update->blocking     = true;
        $update->translations = [
            'fr' => [
                'title'       => 'titre',
                'description' => 'description',
            ],
            'en' => [
                'title'       => 'title',
                'description' => 'description',
            ],
        ];
        $update->dispatch     = true;
        $update->timeSlots    = [
            ['from' => new \DateTime('2016-10-12 10:00'), 'to' => new \DateTime('2016-10-12 11:00')],
            ['from' => new \DateTime('2016-10-12 11:00'), 'to' => new \DateTime('2016-10-12 12:00')],
        ];

        // Handler
        $handler = new UpdateHandler($massRepository->reveal(), $jobQueue->reveal(), $userRepository->reveal(), $typeRepository->reveal(), $massAssignmentRepository->reveal());
        $handler->handle($update);
    }
}
