<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Agenda\Version;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notify;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\NotifyHandler;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\SendNotification;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\SendNotificationHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffChecker;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;

class NotifyHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sendNotificationHandler;

    /** @var ObjectProphecy */
    private $versionRepository;

    /** @var ObjectProphecy */
    private $generator;

    /** @var ObjectProphecy */
    private $diffChecker;

    /** @var \DateTime */
    private $dateTime;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $sheet;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sendNotificationHandler = $this->prophesize(SendNotificationHandler::class);
        $this->versionRepository = $this->prophesize(VersionRepositoryInterface::class);
        $this->generator = $this->prophesize(Generator::class);
        $this->diffChecker = $this->prophesize(DiffChecker::class);
        $this->dateTime = new \DateTime();
    }

    public function testHandleNoOldVersion()
    {
        // Mock
        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(null);
        $this->generator
            ->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(['this is the new version']);

        $this->diffChecker
            ->hasDiff(
                new Version($this->event->reveal(), $this->user->reveal(), [], $this->dateTime),
                ['this is the new version']
            )->shouldBeCalled()
            ->willReturn(true);

        $this->sendNotificationHandler
            ->handle(new SendNotification(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                new Version($this->event->reveal(), $this->user->reveal(), [], $this->dateTime),
                ['this is the new version']
            ))->shouldBeCalled();

        $this->versionRepository
            ->add(new Version($this->event->reveal(), $this->user->reveal(), ['this is the new version'], $this->dateTime))
            ->shouldBeCalled();

        // Handler
        $notifyHandler = new NotifyHandler(
            $this->sendNotificationHandler->reveal(),
            $this->versionRepository->reveal(),
            $this->generator->reveal(),
            $this->diffChecker->reveal(),
            $this->dateTime
        );
        $notifyHandler->handle(new Notify($this->event->reveal(), $this->sheet->reveal(), $this->user->reveal()));
    }

    public function testHandleWithVersion()
    {
        $version = new Version($this->event->reveal(), $this->user->reveal(), ['old version'], $this->dateTime);
        // Mock
        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($version);
        $this->generator
            ->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(['this is the new version']);

        $this->diffChecker
            ->hasDiff(
                $version,
                ['this is the new version']
            )->shouldBeCalled()
            ->willReturn(true);

        $this->sendNotificationHandler
            ->handle(new SendNotification(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                $version,
                ['this is the new version']
            ))->shouldBeCalled();

        $this->versionRepository
            ->add(new Version($this->event->reveal(), $this->user->reveal(), ['this is the new version'], $this->dateTime))
            ->shouldBeCalled();

        // Handler
        $notifyHandler = new NotifyHandler(
            $this->sendNotificationHandler->reveal(),
            $this->versionRepository->reveal(),
            $this->generator->reveal(),
            $this->diffChecker->reveal(),
            $this->dateTime
        );
        $notifyHandler->handle(new Notify($this->event->reveal(), $this->sheet->reveal(), $this->user->reveal()));
    }

    public function testHandle()
    {
        $version = new Version($this->event->reveal(), $this->user->reveal(), ['old version'], $this->dateTime);
        // Mock
        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($version);
        $this->generator
            ->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(['old version']);

        $this->diffChecker
            ->hasDiff(
                $version,
                ['old version']
            )->shouldBeCalled()
            ->willReturn(false);

        $this->sendNotificationHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->versionRepository->add(Argument::any())->shouldNotBeCalled();

        // Handler
        $notifyHandler = new NotifyHandler(
            $this->sendNotificationHandler->reveal(),
            $this->versionRepository->reveal(),
            $this->generator->reveal(),
            $this->diffChecker->reveal(),
            $this->dateTime
        );
        $notifyHandler->handle(new Notify($this->event->reveal(), $this->sheet->reveal(), $this->user->reveal()));
    }
}
