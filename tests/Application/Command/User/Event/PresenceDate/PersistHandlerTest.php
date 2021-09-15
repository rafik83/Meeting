<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Event\PresenceDate;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\User\Event\PresenceDate\Persist;
use Proximum\Vimeet\Application\Command\User\Event\PresenceDate\PersistHandler;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\PresenceDate;
use Proximum\Vimeet\Domain\Repository\User\Event\PresenceDateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateObject\DateTime;

class PersistHandlerTest extends TestCase
{
    public function test_previous_presence_date_not_exists(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $arrival = new \DateTime('2018-12-18');
        $departure = new \DateTime('2018-12-20');

        $departureObject = new DateTime('dateKey1', 'datetime', ['format' => 'datetime'], 'fr', 'fr');
        $departureObject->setDatetime($departure);

        $arrivalObject = new DateTime('dateKey2', 'datetime', ['format' => 'datetime'], 'fr', 'fr');
        $arrivalObject->setDatetime($arrival);

        $block = $this->prophesize(Block::class);
        $block
            ->getObjectByTag(Tag::PARTICIPANT_DEPARTURE_DATE)
            ->shouldBeCalled()
            ->willReturn($departureObject)
        ;
        $block
            ->getObjectByTag(Tag::PARTICIPANT_ARRIVAL_DATE)
            ->shouldBeCalled()
            ->willReturn($arrivalObject)
        ;

        $presenceDateRepository = $this->prophesize(PresenceDateRepositoryInterface::class);
        $presenceDateRepository
            ->getByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $presenceDateRepository
            ->add(new PresenceDate($user->reveal(), $event->reveal(), $arrival, $departure, true, true))
            ->shouldBeCalled()
        ;

        $presenceDateRepository
            ->remove(Argument::any())
            ->shouldNotBeCalled()
        ;

        $persistHandler = new PersistHandler($presenceDateRepository->reveal());
        $persistHandler->handle(new Persist($event->reveal(), $user->reveal(), $block->reveal()));
    }

    public function test_previous_presence_date_not_set(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $block = $this->prophesize(Block::class);
        $block
            ->getObjectByTag(Tag::PARTICIPANT_DEPARTURE_DATE)
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $block
            ->getObjectByTag(Tag::PARTICIPANT_ARRIVAL_DATE)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $presenceDateRepository = $this->prophesize(PresenceDateRepositoryInterface::class);
        $presenceDateRepository
            ->getByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $presenceDateRepository
            ->add(Argument::any())
            ->shouldNotBeCalled()
        ;

        $persistHandler = new PersistHandler($presenceDateRepository->reveal());
        $persistHandler->handle(new Persist($event->reveal(), $user->reveal(), $block->reveal()));
    }

    public function test_previous_presence_exists(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $previousArrival = new \DateTime('2018-12-18');
        $arrival = new \DateTime('2018-12-19');
        $departure = new \DateTime('2018-12-20');
        $presenceDate = new PresenceDate($user->reveal(), $event->reveal(), $previousArrival, $departure, true, true);

        $arrivalObject = new DateTime('dateKey2', 'datetime', ['format' => 'date'], 'fr', 'fr');
        $arrivalObject->setDatetime($arrival);

        $departureObject = new DateTime('dateKey1', 'datetime', ['format' => 'datetime'], 'fr', 'fr');
        $departureObject->setDatetime($departure);

        $block = $this->prophesize(Block::class);
        $block
            ->getObjectByTag(Tag::PARTICIPANT_DEPARTURE_DATE)
            ->shouldBeCalled()
            ->willReturn($departureObject)
        ;
        $block
            ->getObjectByTag(Tag::PARTICIPANT_ARRIVAL_DATE)
            ->shouldBeCalled()
            ->willReturn($arrivalObject)
        ;

        $presenceDateRepository = $this->prophesize(PresenceDateRepositoryInterface::class);
        $presenceDateRepository
            ->getByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($presenceDate)
        ;

        $presenceDateRepository
            ->remove($presenceDate)
            ->shouldBeCalled()
        ;

        $expected = new PresenceDate($user->reveal(), $event->reveal(), $arrival, $departure, false, true);
        $presenceDateRepository
            ->add($expected)
            ->shouldBeCalled()
        ;

        $persistHandler = new PersistHandler($presenceDateRepository->reveal());
        $persistHandler->handle(new Persist($event->reveal(), $user->reveal(), $block->reveal()));
    }

    public function test_with_one_date(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $previousArrival = new \DateTime('2018-12-18');
        $arrival = new \DateTime('2018-12-19');
        $presenceDate = new PresenceDate($user->reveal(), $event->reveal(), $previousArrival, null, true, false);

        $arrivalObject = new DateTime('dateKey2', 'datetime', ['format' => 'date'], 'fr', 'fr');
        $arrivalObject->setDatetime($arrival);

        $departureObject = new DateTime('dateKey1', 'datetime', ['format' => 'datetime'], 'fr', 'fr');
        $departureObject->setDatetime(null);

        $block = $this->prophesize(Block::class);
        $block
            ->getObjectByTag(Tag::PARTICIPANT_DEPARTURE_DATE)
            ->shouldBeCalled()
            ->willReturn($departureObject)
        ;
        $block
            ->getObjectByTag(Tag::PARTICIPANT_ARRIVAL_DATE)
            ->shouldBeCalled()
            ->willReturn($arrivalObject)
        ;

        $presenceDateRepository = $this->prophesize(PresenceDateRepositoryInterface::class);
        $presenceDateRepository
            ->getByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($presenceDate)
        ;

        $presenceDateRepository
            ->remove($presenceDate)
            ->shouldBeCalled()
        ;

        $expected = new PresenceDate($user->reveal(), $event->reveal(), $arrival, null, false, false);
        $presenceDateRepository
            ->add($expected)
            ->shouldBeCalled()
        ;

        $persistHandler = new PersistHandler($presenceDateRepository->reveal());
        $persistHandler->handle(new Persist($event->reveal(), $user->reveal(), $block->reveal()));
    }

    public function test_previous_presence_exists_un_set(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $previousArrival = new \DateTime('2018-12-18');
        $previousDeparture = new \DateTime('2018-12-20');
        $presenceDate = new PresenceDate($user->reveal(), $event->reveal(), $previousArrival, $previousDeparture, true, true);

        $departureObject = new DateTime('dateKey1', 'datetime', ['format' => 'datetime'], 'fr', 'fr');
        $departureObject->setDatetime(null);

        $arrivalObject = new DateTime('dateKey2', 'datetime', ['format' => 'datetime'], 'fr', 'fr');
        $arrivalObject->setDatetime(null);

        $block = $this->prophesize(Block::class);
        $block
            ->getObjectByTag(Tag::PARTICIPANT_DEPARTURE_DATE)
            ->shouldBeCalled()
            ->willReturn($departureObject)
        ;
        $block
            ->getObjectByTag(Tag::PARTICIPANT_ARRIVAL_DATE)
            ->shouldBeCalled()
            ->willReturn($arrivalObject)
        ;

        $presenceDateRepository = $this->prophesize(PresenceDateRepositoryInterface::class);
        $presenceDateRepository
            ->getByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($presenceDate)
        ;

        $presenceDateRepository
            ->remove($presenceDate)
            ->shouldBeCalled()
        ;

        $persistHandler = new PersistHandler($presenceDateRepository->reveal());
        $persistHandler->handle(new Persist($event->reveal(), $user->reveal(), $block->reveal()));
    }
}
