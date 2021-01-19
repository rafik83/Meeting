<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Agenda\Version\Notification;

use PhpParser\Node\Arg;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\MailNotificationCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\MailNotificationCommandHandler;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\NotifyUserOfChangedVersionCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\NotifyUserOfChangedVersionCommandHandler;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\SMSNotificationCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\SMSNotificationCommandHandler;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffChecker;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class NotifyUserOfChangedVersionCommandHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $extraDataRepository,
        $sheetGuesser,
        $versionRepository,
        $diffGenerator,
        $diffChecker,
        $diffVerbalizer,
        $mailNotificationCommandHandler,
        $SMSNotificationCommandHandler,
        $user,
        $event
    ;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function setUp()
    {
        $this->extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->sheetGuesser = $this->prophesize(SheetGuesser::class);
        $this->versionRepository = $this->prophesize(VersionRepositoryInterface::class);
        $this->diffGenerator = $this->prophesize(Generator::class);
        $this->diffChecker = $this->prophesize(DiffChecker::class);
        $this->diffVerbalizer = $this->prophesize(DiffVerbalizer::class);
        $this->mailNotificationCommandHandler = $this->prophesize(MailNotificationCommandHandler::class);
        $this->SMSNotificationCommandHandler = $this->prophesize(SMSNotificationCommandHandler::class);

        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->user->getLocale()->shouldBeCalled()->willReturn('fr');
        $this->event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');

        $this->dateTime = new \DateTime('2018-10-10 10:00:00.000');
    }

    public function testHandleNoSheet()
    {
        $this->sheetGuesser->getUserSheet($this->user->reveal(), $this->event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willThrow(SheetNotFoundException::class)
        ;

        $this->extraDataRepository->removeForUserAndEventAndName($this->user->reveal(), $this->event->reveal(), Type::PLANNING_MODIFIED)
            ->shouldBeCalled()
        ;

        $this->versionRepository->getLastVersionByEventAndUser(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->diffGenerator->generate(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->diffChecker->hasDiff(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->diffVerbalizer->verbalizeDiff(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->SMSNotificationCommandHandler->handle(Argument::any())->shouldNotBeCalled();
        $this->mailNotificationCommandHandler->handle(Argument::any())->shouldNotBeCalled();

        $handler = new NotifyUserOfChangedVersionCommandHandler(
            $this->extraDataRepository->reveal(),
            $this->sheetGuesser->reveal(),
            $this->versionRepository->reveal(),
            $this->diffGenerator->reveal(),
            $this->diffChecker->reveal(),
            $this->diffVerbalizer->reveal(),
            $this->mailNotificationCommandHandler->reveal(),
            $this->SMSNotificationCommandHandler->reveal(),
            $this->dateTime
        );

        $handler->handle(new NotifyUserOfChangedVersionCommand($this->event->reveal(), $this->user->reveal()));
    }

    public function testHandleNoDiff()
    {
        $sheet = $this->prophesize(Sheet::class);
        $version = $this->prophesize(User\Agenda\Version::class);
        $this->sheetGuesser->getUserSheet($this->user->reveal(), $this->event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($sheet->reveal())
        ;

        $this->extraDataRepository
            ->removeForUserAndEventAndName($this->user->reveal(), $this->event->reveal(), Type::PLANNING_MODIFIED)
            ->shouldBeCalled()
        ;

        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($version->reveal())
        ;
        $this->diffGenerator->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(['this is a diff'])
        ;
        $this->diffChecker
            ->hasDiff($version->reveal(), ['this is a diff'])
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->diffVerbalizer->verbalizeDiff(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->SMSNotificationCommandHandler->handle(Argument::any())->shouldNotBeCalled();
        $this->mailNotificationCommandHandler->handle(Argument::any())->shouldNotBeCalled();

        $handler = new NotifyUserOfChangedVersionCommandHandler(
            $this->extraDataRepository->reveal(),
            $this->sheetGuesser->reveal(),
            $this->versionRepository->reveal(),
            $this->diffGenerator->reveal(),
            $this->diffChecker->reveal(),
            $this->diffVerbalizer->reveal(),
            $this->mailNotificationCommandHandler->reveal(),
            $this->SMSNotificationCommandHandler->reveal(),
            $this->dateTime
        );

        $handler->handle(new NotifyUserOfChangedVersionCommand($this->event->reveal(), $this->user->reveal()));
    }

    public function testHandleNoSMS()
    {
        $smsActivationDate = new \DateTime('2018-10-20 10:00:00.000');
        $configuration = $this->prophesize(Event\Configuration::class);
        $this->event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $configuration->getSmsActivationDate()->shouldBeCalled()->willReturn($smsActivationDate);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getUserLocale($this->user->reveal())->willReturn('fr');
        $version = $this->prophesize(User\Agenda\Version::class);
        $this->sheetGuesser->getUserSheet($this->user->reveal(), $this->event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($sheet->reveal())
        ;

        $this->extraDataRepository
            ->removeForUserAndEventAndName($this->user->reveal(), $this->event->reveal(), Type::PLANNING_MODIFIED)
            ->shouldBeCalled()
        ;

        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($version->reveal())
        ;
        $this->diffGenerator->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(['this is a diff'])
        ;
        $this->diffChecker
            ->hasDiff($version->reveal(), ['this is a diff'])
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->diffVerbalizer
            ->verbalizeDiff($version->reveal(), ['this is a diff'], 'fr')
            ->shouldBeCalled()
            ->willReturn('Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.')
        ;

        $this->SMSNotificationCommandHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->mailNotificationCommandHandler
            ->handle(
                new MailNotificationCommand(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    $sheet->reveal(),
                    'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.'
                )
            )
            ->shouldBeCalled()
        ;

        $version = new User\Agenda\Version($this->event->reveal(), $this->user->reveal(), ['this is a diff'], $this->dateTime);
        $this->versionRepository->add($version)->shouldBeCalled();

        $handler = new NotifyUserOfChangedVersionCommandHandler(
            $this->extraDataRepository->reveal(),
            $this->sheetGuesser->reveal(),
            $this->versionRepository->reveal(),
            $this->diffGenerator->reveal(),
            $this->diffChecker->reveal(),
            $this->diffVerbalizer->reveal(),
            $this->mailNotificationCommandHandler->reveal(),
            $this->SMSNotificationCommandHandler->reveal(),
            $this->dateTime
        );

        $handler->handle(new NotifyUserOfChangedVersionCommand($this->event->reveal(), $this->user->reveal()));
    }

    public function testHandle()
    {
        $smsActivationDate = new \DateTime('2018-10-10 09:00:00.000');
        $configuration = $this->prophesize(Event\Configuration::class);
        $this->event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $configuration->getSmsActivationDate()->shouldBeCalled()->willReturn($smsActivationDate);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getUserLocale($this->user->reveal())->willReturn('fr');
        $version = $this->prophesize(User\Agenda\Version::class);
        $this->sheetGuesser->getUserSheet($this->user->reveal(), $this->event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($sheet->reveal())
        ;

        $this->extraDataRepository
            ->removeForUserAndEventAndName($this->user->reveal(), $this->event->reveal(), Type::PLANNING_MODIFIED)
            ->shouldBeCalled()
        ;

        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($version->reveal())
        ;
        $this->diffGenerator->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(['this is a diff'])
        ;
        $this->diffChecker
            ->hasDiff($version->reveal(), ['this is a diff'])
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->diffVerbalizer
            ->verbalizeDiff($version->reveal(), ['this is a diff'], 'fr')
            ->shouldBeCalled()
            ->willReturn('Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.')
        ;

        $this->SMSNotificationCommandHandler
            ->handle(
                new SMSNotificationCommand(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    $sheet->reveal(),
                    'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.'
                )

            )
            ->shouldBeCalled();

        $this->mailNotificationCommandHandler
            ->handle(
                new MailNotificationCommand(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    $sheet->reveal(),
                    'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.'
                )
            )
            ->shouldBeCalled()
        ;

        $version = new User\Agenda\Version($this->event->reveal(), $this->user->reveal(), ['this is a diff'], $this->dateTime);
        $this->versionRepository->add($version)->shouldBeCalled();

        $handler = new NotifyUserOfChangedVersionCommandHandler(
            $this->extraDataRepository->reveal(),
            $this->sheetGuesser->reveal(),
            $this->versionRepository->reveal(),
            $this->diffGenerator->reveal(),
            $this->diffChecker->reveal(),
            $this->diffVerbalizer->reveal(),
            $this->mailNotificationCommandHandler->reveal(),
            $this->SMSNotificationCommandHandler->reveal(),
            $this->dateTime
        );

        $handler->handle(new NotifyUserOfChangedVersionCommand($this->event->reveal(), $this->user->reveal()));
    }
}
