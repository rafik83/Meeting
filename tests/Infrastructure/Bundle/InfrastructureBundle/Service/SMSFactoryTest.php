<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Service;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Meeting\Constant;
use Proximum\Vimeet\Domain\Model\Sheet;
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
}
