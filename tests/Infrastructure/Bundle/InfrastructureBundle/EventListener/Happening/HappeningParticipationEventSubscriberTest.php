<?php
/*
 * This file is part of the PhpStorm project.
 *
 * Copyright (C) PhpStorm
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\EventListener\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Event\Happening\HappeningParticipationAutomaticallyUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\Happening\HappeningParticipationView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Happening\HappeningParticipationEventSubscriber;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening\HappeningParticipationAutomaticallyUpdatedMail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class HappeningParticipationEventSubscriberTest extends TestCase
{
    public function testOnParticipationAutomaticallyUpdated(): void
    {
        $mailer = $this->prophesize(MailerInterface::class);
        $request = $this->prophesize(Request::class);
        $requestStack = $this->prophesize(RequestStack::class);
        $happening = $this->prophesize(Happening::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getOwner()->shouldBeCalled()->willReturn($user->reveal());
        $user->getEmail()->shouldBeCalled()->willReturn('owner@mail.fr');

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getEmail()->shouldBeCalled()->willReturn('aa@aa.fr');
        $participant2 = $this->prophesize(Participant::class);
        $participant2->getEmail()->shouldBeCalled()->willReturn('bb@bb.fr');
        $participant3 = $this->prophesize(Participant::class);
        $participant3->getEmail()->shouldBeCalled()->willReturn('cc@cc.fr');

        $requestStack->getCurrentRequest()
            ->shouldBeCalled()
            ->willReturn($request->reveal());

        $request->getLocale()
            ->shouldBeCalled()
            ->willReturn('fr');

        $addedParticipants = [
            $participant1->reveal(),
            $participant2->reveal(),
        ];

        $removedParticipants = [
            $participant3->reveal(),
        ];

        $happeningParticipationView = [
            new HappeningParticipationView(
                $happening->reveal(),
                $addedParticipants,
                $removedParticipants
            )
        ];

        $mail = new HappeningParticipationAutomaticallyUpdatedMail(
            $happeningParticipationView,
            'sender@mail.fr',
            'owner@mail.fr',
            'fr'
        );
        $mail->addReceiver('aa@aa.fr');
        $mail->addReceiver('bb@bb.fr');
        $mail->addReceiver('cc@cc.fr');

        $mailer->send($mail)->shouldBeCalled();

        $event = new HappeningParticipationAutomaticallyUpdatedEvent($happeningParticipationView, $sheet->reveal());
        $subscriber = new HappeningParticipationEventSubscriber(
            $mailer->reveal(),
            $requestStack->reveal(),
            'sender@mail.fr'
        );
        $subscriber->onParticipationAutomaticallyUpdated($event);
    }
}
