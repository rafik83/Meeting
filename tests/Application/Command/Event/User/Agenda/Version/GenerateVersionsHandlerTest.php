<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\User\Agenda\Version;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Event\User\Agenda\Version\GenerateVersions;
use Proximum\Vimeet\Application\Command\Event\User\Agenda\Version\GenerateVersionsHandler;
use Proximum\Vimeet\Application\Exception\Event\User\Agenda\Version\VersionsAlreadyGenerated;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;

class GenerateVersionsHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $userRepository;

    /** @var ObjectProphecy */
    private $versionRepository;

    /** @var ObjectProphecy */
    private $generator;

    /** @var ObjectProphecy */
    private $dateTime;

    /** @var ObjectProphecy */
    private $eventRepository;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->versionRepository = $this->prophesize(VersionRepositoryInterface::class);
        $this->generator = $this->prophesize(Generator::class);
        $this->dateTime = new \DateTime();
    }

    public function testHandleException()
    {
        $this->expectException(VersionsAlreadyGenerated::class);

        $this->event->isUserAgendaVersionsGenerated()->willReturn(true);
        $this->event->getId()->willReturn(1);

        $command = new GenerateVersionsHandler(
            $this->eventRepository->reveal(),
            $this->userRepository->reveal(),
            $this->versionRepository->reveal(),
            $this->generator->reveal(),
            $this->dateTime
        );
        $command->handle(new GenerateVersions($this->event->reveal()));
    }

    public function testHandle()
    {
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $user3 = $this->prophesize(User::class);

        $this->event->isUserAgendaVersionsGenerated()->willReturn(false);

        // Expected
        $this->userRepository
            ->findByEventAndInCatalog($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$user1->reveal(), $user2->reveal(), $user3->reveal()]);

        $this->generator
            ->generate($this->event->reveal(), $user1->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->generator
            ->generate($this->event->reveal(), $user2->reveal())
            ->shouldBeCalled()
            ->willReturn(['version 2'])
        ;

        $this->generator
            ->generate($this->event->reveal(), $user3->reveal())
            ->shouldBeCalled()
            ->willReturn(['version 3'])
        ;

        $this->versionRepository
            ->add(new Version($this->event->reveal(), $user1->reveal(), [], $this->dateTime))
            ->shouldBeCalled()
        ;

        $this->versionRepository
            ->add(new Version($this->event->reveal(), $user2->reveal(), ['version 2'], $this->dateTime))
            ->shouldBeCalled()
        ;

        $this->versionRepository
            ->add(new Version($this->event->reveal(), $user3->reveal(), ['version 3'], $this->dateTime))
            ->shouldBeCalled()
        ;

        $this->event->setUserAgendaVersionsGenerated(true)->shouldBeCalled();
        $this->eventRepository->set($this->event->reveal())->shouldBeCalled();

        $command = new GenerateVersionsHandler(
            $this->eventRepository->reveal(),
            $this->userRepository->reveal(),
            $this->versionRepository->reveal(),
            $this->generator->reveal(),
            $this->dateTime
        );
        $command->handle(new GenerateVersions($this->event->reveal()));
    }
}
