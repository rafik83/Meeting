<?php

namespace Proximum\Vimeet\Tests\Application\Command\VideoConference;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\VideoConference\SetVisioTested;
use Proximum\Vimeet\Application\Command\VideoConference\SetVisioTestedHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantVisioTestedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class SetVisioTestedHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $dateTime = new \DateTime;

        $userEventExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $userEventExtraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                Type::VISIO_TESTED,
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $delayedEventDispatcher
            ->dispatch(Events::PARTICIPANT_VISIO_TESTED, new ParticipantVisioTestedEvent($user->reveal(), $event->reveal()))
            ->shouldBeCalled();

        $userEventExtraDataRepository
            ->add(new ExtraData($user->reveal(), $event->reveal(), Type::VISIO_TESTED, '1', $dateTime))
            ->shouldBeCalled()
        ;

        $setVisioTestedHandler = new SetVisioTestedHandler($userEventExtraDataRepository->reveal(), $dateTime, $delayedEventDispatcher->reveal());
        $setVisioTestedHandler->handle(new SetVisioTested($event->reveal(), $user->reveal()));
    }
}
