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
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
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
        $handler = new UpdateHandler($massRepository->reveal(), $jobQueue->reveal());
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
        $jobQueue->aggregateEventUsersFullUnavailability($event)->shouldNotBeCalled();
        $jobQueue->aggregateAvailableSlot($event)->shouldNotBeCalled();

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
        $handler = new UpdateHandler($massRepository->reveal(), $jobQueue->reveal());
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
        $handler = new UpdateHandler($massRepository->reveal(), $jobQueue->reveal());
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
        $handler = new UpdateHandler($massRepository->reveal(), $jobQueue->reveal());
        $handler->handle($update);
    }
}
