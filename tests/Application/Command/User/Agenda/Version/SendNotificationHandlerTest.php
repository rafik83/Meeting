<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Agenda\Version;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\MailNotificationCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\MailNotificationCommandHandler;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\SMSNotificationCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\SMSNotificationCommandHandler;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\SendNotification;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\SendNotificationHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;

class SendNotificationHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $diffVerbalizer, $SMSNotificationCommandHandler, $mailNotificationCommandHandler, $event, $sheet, $user, $plannerJobRepository;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
        $this->diffVerbalizer = $this->prophesize(DiffVerbalizer::class);
        $this->SMSNotificationCommandHandler = $this->prophesize(SMSNotificationCommandHandler::class);
        $this->mailNotificationCommandHandler = $this->prophesize(MailNotificationCommandHandler::class);
        $this->plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);
    }

    public function testHandle()
    {
        // Context
        $currentVersion = $this->prophesize(Version::class);
        $diff = [];
        $verbalizedDiff = 'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.';
        $phone = $this->prophesize(User\UserEventPhone::class);
        $phone->getPhone()->willReturn('+123123123');
        $this->user->getLocale()->willReturn('fr');
        $this->event->getAvailableLocale('fr')->willReturn('fr');
        $this->sheet->getId()->willReturn(3);

        $plannerJob = $this->prophesize(PlannerJob::class);
        $plannerJob->isCompleted()->willReturn(true);
        $this->plannerJobRepository->findLastByEvent($this->event)->willReturn($plannerJob);

        // Expected
        $this->diffVerbalizer
            ->verbalizeDiff(
                $currentVersion,
                [],
                'fr'
            )->shouldBeCalled()
            ->willReturn($verbalizedDiff)
        ;
        $this->mailNotificationCommandHandler
            ->handle(new MailNotificationCommand(
                $this->event->reveal(),
                $this->user->reveal(),
                $this->sheet->reveal(),
                'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.'
            ))
            ->shouldBeCalled()
        ;

        $this->SMSNotificationCommandHandler
            ->handle(new SMSNotificationCommand(
                $this->event->reveal(),
                $this->user->reveal(),
                $this->sheet->reveal(),
                'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.'
            ))
            ->shouldBeCalled()
        ;

        // Handler
        $sendNotificationHandler = new SendNotificationHandler(
            $this->diffVerbalizer->reveal(),
            $this->mailNotificationCommandHandler->reveal(),
            $this->SMSNotificationCommandHandler->reveal(),
            $this->plannerJobRepository->reveal()
        );
        $sendNotificationHandler->handle(
            new SendNotification(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                $currentVersion->reveal(),
                $diff
            )
        );
    }

    public function testPlannerJobNotCompleteHandle()
    {
        // Context
        $currentVersion = $this->prophesize(Version::class);
        $diff = [];
        $phone = $this->prophesize(User\UserEventPhone::class);
        $phone->getPhone()->willReturn('+123123123');
        $this->user->getLocale()->willReturn('fr');
        $this->event->getAvailableLocale('fr')->willReturn('fr');
        $this->sheet->getId()->willReturn(3);

        $plannerJob = $this->prophesize(PlannerJob::class);
        $plannerJob->isCompleted()->willReturn(false);
        $this->plannerJobRepository->findLastByEvent($this->event)->willReturn($plannerJob);

        // Expected
        $this->diffVerbalizer
            ->verbalizeDiff(
                $currentVersion,
                [],
                'fr'
            )->shouldNotBeCalled();
        ;
        $this->mailNotificationCommandHandler
            ->handle(new MailNotificationCommand(
                $this->event->reveal(),
                $this->user->reveal(),
                $this->sheet->reveal(),
                ''
            ))
            ->shouldNotBeCalled()
        ;

        $this->SMSNotificationCommandHandler
            ->handle(new SMSNotificationCommand(
                $this->event->reveal(),
                $this->user->reveal(),
                $this->sheet->reveal(),
                ''
            ))
            ->shouldNotBeCalled()
        ;

        // Handler
        $sendNotificationHandler = new SendNotificationHandler(
            $this->diffVerbalizer->reveal(),
            $this->mailNotificationCommandHandler->reveal(),
            $this->SMSNotificationCommandHandler->reveal(),
            $this->plannerJobRepository->reveal()
        );
        $sendNotificationHandler->handle(
            new SendNotification(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                $currentVersion->reveal(),
                $diff
            )
        );
    }
}
