<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability\Mass;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Update;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
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
        $type2       = $this->prophesize(Type::class);
        $oldCategory = new Category($event, 'Conference', 'old title', '#AABBCC', '#CCBBAA');
        $category    = new Category($event, 'Conference', 'title', '#123123', '#312312');
        $oldBegin    = new \DateTime('2016-10-10 10:00:00.000');
        $oldEnd      = new \DateTime('2016-10-10 12:00:00.000');
        $begin       = new \DateTime('2016-10-12 10:00:00.000');
        $end         = new \DateTime('2016-10-12 12:00:00.000');

        // Existing
        $existing = new Mass(
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
        $existing->createTranslation('fr', 'vieux titre', 'vieille description');
        $existing->createTranslation('en', 'old title', 'old description');

        // Expected
        $expected = new Mass(
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
        $expected->createTranslation('fr', 'titre', 'description');
        $expected->createTranslation('en', 'title', 'description');

        // Mock
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->update($expected)->shouldBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->aggregateEventUsersFullUnavailability($event)->shouldBeCalled();
        $jobQueue->aggregateAvailableSlot($event)->shouldBeCalled();

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEventWithDispatch($event, $existing);

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $massAssignmentRepository = $this->prophesize(MassAssignmentRepository::class);

        // Create
        $update               = new Update($existing);
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

    public function testHandleWithoutChangeOnDateAndBlocking()
    {
        $event    = EventFactory::createEvent();
        $oldCategory = new Category($event, 'Conference', 'old title', '#AABBCC', '#CCBBAA');
        $category    = new Category($event, 'Conference', 'title', '#123123', '#312312');
        $oldBegin    = new \DateTime('2016-10-10 10:00:00.000');
        $oldEnd      = new \DateTime('2016-10-10 12:00:00.000');
        $begin       = new \DateTime('2016-10-10 10:00:00.000');
        $end         = new \DateTime('2016-10-10 12:00:00.000');

        // Existing
        $existing = new Mass(
            $event,
            $oldCategory,
            'old name',
            $oldBegin,
            $oldEnd,
            true
        );
        $existing->createTranslation('fr', 'vieux titre', 'vieille description');
        $existing->createTranslation('en', 'old title', 'old description');

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
        $massRepository->update($expected)->shouldBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->aggregateEventUsersFullUnavailability($event)->shouldBeCalled();
        $jobQueue->aggregateAvailableSlot($event)->shouldBeCalled();

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEventWithDispatch($event, $existing);

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $massAssignmentRepository = $this->prophesize(MassAssignmentRepository::class);

        // Create
        $update               = new Update($existing);
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

        // Handler
        $handler = new UpdateHandler($massRepository->reveal(), $jobQueue->reveal(), $userRepository->reveal(), $typeRepository->reveal(), $massAssignmentRepository->reveal());
        $handler->handle($update);
    }

    public function testHandleWithoutChangeOnDateButBlocking()
    {
        $event    = EventFactory::createEvent();
        $oldCategory = new Category($event, 'Conference', 'old title', '#AABBCC', '#CCBBAA');
        $category    = new Category($event, 'Conference', 'title', '#123123', '#312312');
        $oldBegin    = new \DateTime('2016-10-10 10:00:00.000');
        $oldEnd      = new \DateTime('2016-10-10 12:00:00.000');
        $begin       = new \DateTime('2016-10-10 10:00:00.000');
        $end         = new \DateTime('2016-10-10 12:00:00.000');

        // Existing
        $existing = new Mass(
            $event,
            $oldCategory,
            'old name',
            $oldBegin,
            $oldEnd,
            false
        );
        $existing->createTranslation('fr', 'vieux titre', 'vieille description');
        $existing->createTranslation('en', 'old title', 'old description');

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
        $massRepository->update($expected)->shouldBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->aggregateEventUsersFullUnavailability($event)->shouldBeCalled();
        $jobQueue->aggregateAvailableSlot($event)->shouldBeCalled();

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEventWithDispatch($event, $existing);

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $massAssignmentRepository = $this->prophesize(MassAssignmentRepository::class);

        // Create
        $update               = new Update($existing);
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
        $existing = new Mass(
            $event,
            $oldCategory,
            'old name',
            $oldBegin,
            $oldEnd,
            false
        );
        $existing->createTranslation('fr', 'vieux titre', 'vieille description');
        $existing->createTranslation('en', 'old title', 'old description');

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
                ['from' => new \DateTime('2016-10-12 10:00'), 'to' => new \DateTime('2016-10-12 11:00')],
                ['from' => new \DateTime('2016-10-12 11:00'), 'to' => new \DateTime('2016-10-12 12:00')],
            ]
        );
        $expected->createTranslation('fr', 'titre', 'description');
        $expected->createTranslation('en', 'title', 'description');

        // Mock
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->update($expected)->shouldBeCalled();

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->aggregateEventUsersFullUnavailability($event)->shouldBeCalled();
        $jobQueue->aggregateAvailableSlot($event)->shouldBeCalled();

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEventWithDispatch($event, $existing);

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $massAssignmentRepository = $this->prophesize(MassAssignmentRepository::class);

        // Create
        $update               = new Update($existing);
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
