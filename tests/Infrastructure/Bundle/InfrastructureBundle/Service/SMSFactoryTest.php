<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Service;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Constant;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\SMSFactory;

class SMSFactoryTest extends TestCase
{
    public function testCreateMeetingRequestReceive()
    {
        $phone  = '+3360000000';
        $locale = 'fr';
        $sheet  = $this->prophesize(Sheet::class);
        $event  = $this->prophesize(Event::class);

        $sheet->getId()->willReturn(1);
        $sheet->getEvent()->willReturn($event);
        $event->getTitle()->willReturn('ProximumEvent');

        $eventUrlGenerator = $this->prophesize(EventUrlGeneratorInterface::class);
        $translator        = $this->prophesize(TranslatorInterface::class);

        $eventUrlGenerator->generateEventAbsoluteUrl(
            $event->reveal(),
            'event_meeting_list_request',
            [
                'sheet' => 1,
                'state' => Constant::FILTER_STATE_RECEIVE,
            ]
        )->shouldBeCalled()->willReturn('eventMeetingListLink');

        $smsFactory = new SMSFactory(
            $eventUrlGenerator->reveal(),
            $translator->reveal()
        );

        $translator->trans('event.sms.meeting_request.receive', [
            '%event%' => 'ProximumEvent',
            '%link%'  => 'eventMeetingListLink',
        ], 'messages', $locale)->shouldBeCalled()->willReturn('translatedMessage');

        $expectedSms = new SMS('+3360000000', 'translatedMessage');

        $sms = $smsFactory->createMeetingRequestReceive($phone, $sheet->reveal(), $locale);

        $this->assertEquals($expectedSms, $sms);
    }

    public function testCreatePendingProposition()
    {
        $phone               = '+3360000000';
        $locale              = 'fr';
        $sheet               = $this->prophesize(Sheet::class);
        $event               = $this->prophesize(Event::class);
        $pendingPropositions = 4;

        $sheet->getId()->willReturn(1);
        $sheet->getEvent()->willReturn($event);
        $event->getTitle()->willReturn('ProximumEvent');

        $eventUrlGenerator = $this->prophesize(EventUrlGeneratorInterface::class);
        $translator        = $this->prophesize(TranslatorInterface::class);

        $eventUrlGenerator->generateEventAbsoluteUrl(
            $event->reveal(),
            'event_meeting_list_request',
            array_merge(
                ['sheet' => 1],
                ['state' => 'receive'],
                ['_locale' => $locale]
            )
        )->shouldBeCalled()->willReturn('eventMeetingListLink');

        $translator
            ->trans(
                'event.sms.reminder.pending_meeting_request',
                [
                    '%eventTitle%'                  => 'ProximumEvent',
                    '%countPendingMeetingRequest%'  => 4,
                    '%meetingRequestManagementUrl%' => 'eventMeetingListLink',
                ],
                'messages',
                $locale
            )->shouldBeCalled()->willReturn('translatedMessage');

        $smsFactory = new SMSFactory(
            $eventUrlGenerator->reveal(),
            $translator->reveal()
        );

        $expectedSms = new SMS('+3360000000', 'translatedMessage');

        $sms = $smsFactory->createPendingProposition($phone, $sheet->reveal(), $locale, $pendingPropositions);

        $this->assertEquals($expectedSms, $sms);
    }

    public function testCreateSentMeetingRequestApproved()
    {
        $meeting     = $this->prophesize(Meeting::class);
        $event       = $this->prophesize(Event::class);
        $fromSheet   = $this->prophesize(Sheet::class);
        $toSheet     = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $slot        = $this->prophesize(MeetingSlot::class);
        $spot        = $this->prophesize(Spot::class);

        $locale = 'fr';
        $phone  = '+3306000000';
        $meeting->getEvent()->willReturn($event->reveal());
        $fromSheet->getId()->willReturn(1);
        $participant->getId()->willReturn(2);
        $toSheet->getTitle()->willReturn('Elao');
        $event->getTitle()->willReturn('Proximum Event');
        $meeting->getSlot()->willReturn($slot->reveal());
        $meeting->getSpot()->willReturn($spot->reveal());
        $slot->getBegin()->willReturn(new \DateTime('2017-01-01 09:00:00'));
        $spot->getReference()->willReturn('G30');
        $event->getTimeZone()->willReturn('UTC');

        $eventUrlGenerator = $this->prophesize(EventUrlGeneratorInterface::class);
        $translator        = $this->prophesize(TranslatorInterface::class);

        $eventUrlGenerator->generateEventAbsoluteUrl(
            $event->reveal(),
            'event_agenda_participant',
            [
                'sheet'       => 1,
                'participant' => 2,
            ]
        )->shouldBeCalled()->willReturn('eventLink');

        $translator->trans('event.sms.receive_meeting_request.approved', [
            '%event%' => 'Proximum Event',
            '%sheet%' => 'Elao',
            '%date%'  => '01/01/2017',
            '%time%'  => '09:00',
            '%spot%'  => 'G30',
            '%link%'  => 'eventLink',
        ], 'messages', $locale)
            ->shouldBeCalled()
            ->willReturn('translatedMessage');

        $smsFactory = new SMSFactory(
            $eventUrlGenerator->reveal(),
            $translator->reveal()
        );

        // Expected
        $expectedSMS = new SMS('+3306000000', 'translatedMessage');

        $sms = $smsFactory->createSentMeetingRequestApproved(
            $phone,
            $meeting->reveal(),
            $fromSheet->reveal(),
            $toSheet->reveal(),
            $participant->reveal(),
            $locale
        );

        $this->assertEquals($expectedSMS, $sms);
    }

    public function testCreateReceiveMeetingRequestApproved()
    {
        $meeting     = $this->prophesize(Meeting::class);
        $event       = $this->prophesize(Event::class);
        $fromSheet   = $this->prophesize(Sheet::class);
        $toSheet     = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $slot        = $this->prophesize(MeetingSlot::class);
        $spot        = $this->prophesize(Spot::class);

        $locale = 'fr';
        $phone  = '+3306000000';
        $meeting->getEvent()->willReturn($event->reveal());
        $toSheet->getId()->willReturn(1);
        $participant->getId()->willReturn(2);
        $fromSheet->getTitle()->willReturn('Proximum');
        $event->getTitle()->willReturn('Proximum Event');
        $meeting->getSlot()->willReturn($slot->reveal());
        $meeting->getSpot()->willReturn($spot->reveal());
        $slot->getBegin()->willReturn(new \DateTime('2017-01-01 09:00:00'));
        $spot->getReference()->willReturn('G30');
        $event->getTimeZone()->willReturn('UTC');

        $eventUrlGenerator = $this->prophesize(EventUrlGeneratorInterface::class);
        $translator        = $this->prophesize(TranslatorInterface::class);

        $eventUrlGenerator->generateEventAbsoluteUrl(
            $event->reveal(),
            'event_agenda_participant',
            [
                'sheet'       => 1,
                'participant' => 2,
            ]
        )->shouldBeCalled()->willReturn('eventLink');

        $translator->trans('event.sms.sent_meeting_request.approved', [
            '%event%' => 'Proximum Event',
            '%sheet%' => 'Proximum',
            '%date%'  => '01/01/2017',
            '%time%'  => '09:00',
            '%spot%'  => 'G30',
            '%link%'  => 'eventLink',
        ], 'messages', $locale)
            ->shouldBeCalled()
            ->willReturn('translatedMessage');

        $smsFactory = new SMSFactory(
            $eventUrlGenerator->reveal(),
            $translator->reveal()
        );

        // Expected
        $expectedSMS = new SMS('+3306000000', 'translatedMessage');

        $sms = $smsFactory->createReceiveMeetingRequestApproved(
            $phone,
            $meeting->reveal(),
            $toSheet->reveal(),
            $fromSheet->reveal(),
            $participant->reveal(),
            $locale
        );

        $this->assertEquals($expectedSMS, $sms);
    }
}
