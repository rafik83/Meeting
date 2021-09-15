<?php

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Unavailability\Remove;
use Proximum\Vimeet\Application\Command\Unavailability\RemoveHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Unavailability\RemoveUnavailabilityEvent;
use Proximum\Vimeet\Application\Exception\Unavailability\CanNotDeleteUnavailabilityException;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RemoveHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Context
        $event = EventFactory::createEvent();
        $user = new User('email@email.com', 'salt', 'password', 'fr');

        //Actual unavailability
        $unavailability = new Unavailability(
            $user,
            $event,
            new \DateTime('2016-01-15 09:00:00'),
            new \DateTime('2016-01-15 11:00:00')
        );

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->remove($unavailability)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $eventDispatcher
            ->dispatch(Events::UNAVAILABILITY_REMOVED, new RemoveUnavailabilityEvent($user, $event))
            ->shouldBeCalled()
        ;

        $handler = new RemoveHandler($unavailabilityRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle(new Remove($unavailability));
    }

    public function testThrowCanNotDeleteUnavailabilityExceptionHandle()
    {
        $this->expectException(CanNotDeleteUnavailabilityException::class);

        $event = EventFactory::createEvent();
        $user = new User('email@email.com', 'salt', 'password', 'fr');

        $unavailability = new Unavailability(
            $user,
            $event,
            new \DateTime('2016-01-15 09:00:00'),
            new \DateTime('2016-01-15 11:00:00'),
            null,
            Unavailability::CREATED_BY_SYSTEM
        );

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $handler = new RemoveHandler($unavailabilityRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle(new Remove($unavailability));
    }
}
