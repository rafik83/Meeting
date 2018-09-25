<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Transactional\Mail;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Generic\GenericMailViewQuery;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Generic\GenericMailViewQueryHandler;
use Proximum\Vimeet\Application\Query\Transactional\Mail\TransactionalMailListViewQuery;
use Proximum\Vimeet\Application\Query\Transactional\Mail\TransactionalMailListViewQueryHandler;
use Proximum\Vimeet\Application\View\Transactional\Mail\Generic\GenericMailView;
use Proximum\Vimeet\Application\View\Transactional\Mail\TransactionalMailListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class TransactionalMailListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $locale = 'fr';
        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale($locale)->shouldBeCalled()->willReturn('en');

        $genericMailViewQueryHandler = $this->prophesize(GenericMailViewQueryHandler::class);

        $genericMail1 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_PRE_REGISTERED, 'TRANSACTIONAL_MAIL_KEY_PRE_REGISTERED');
        $genericMail2 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_USER_ACTIVATE_ACCOUNT, 'TRANSACTIONAL_MAIL_KEY_USER_ACTIVATE_ACCOUNT');
        $genericMail3 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_USER_CHANGE_NEW_MAIL, 'TRANSACTIONAL_MAIL_KEY_USER_CHANGE_NEW_MAIL');
        $genericMail4 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_USER_CHANGE_OLD_MAIL, 'TRANSACTIONAL_MAIL_KEY_USER_CHANGE_OLD_MAIL');
        $genericMail5 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_USER_COMPLETE_PROFILE, 'TRANSACTIONAL_MAIL_KEY_USER_COMPLETE_PROFILE');
        $genericMail6 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_USER_REGISTERED, 'TRANSACTIONAL_MAIL_KEY_USER_REGISTERED');
        $genericMail7 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_USER_RESET_PASSWORD, 'TRANSACTIONAL_MAIL_KEY_USER_RESET_PASSWORD');
        $genericMail8 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_USER_CHANGED_PASSWORD_CONFIRMED, 'TRANSACTIONAL_MAIL_KEY_USER_CHANGED_PASSWORD_CONFIRMED');
        $genericMail9 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_SHEET_TYPE_CHANGED, 'TRANSACTIONAL_MAIL_KEY_SHEET_TYPE_CHANGED');
        $genericMail10 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_SHEET_GROUP_CREATED, 'TRANSACTIONAL_MAIL_KEY_SHEET_GROUP_CREATED');
        $genericMail11 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_PARTICIPANT_ADDED_CONFIRMATION, 'TRANSACTIONAL_MAIL_KEY_PARTICIPANT_ADDED_CONFIRMATION');
        $genericMail12 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_THIRD_PARTY_COMEXPOSIUM_PARTICIPANT_ADDED_CONFIRMATION, 'TRANSACTIONAL_MAIL_KEY_THIRD_PARTY_COMEXPOSIUM_PARTICIPANT_ADDED_CONFIRMATION');
        $genericMail13 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED, 'TRANSACTIONAL_MAIL_KEY_HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED');
        $genericMail14 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_ORDER_CONFIRMED, 'TRANSACTIONAL_MAIL_KEY_ORDER_CONFIRMED');
        $genericMail15 = new GenericMailView(Constant::TRANSACTIONAL_MAIL_KEY_TRANSACTION_CONFIRMED, 'TRANSACTIONAL_MAIL_KEY_TRANSACTION_CONFIRMED');

        $genericMailsByKey = [
            Constant::TRANSACTIONAL_MAIL_KEY_PRE_REGISTERED => $genericMail1,
            Constant::TRANSACTIONAL_MAIL_KEY_USER_ACTIVATE_ACCOUNT => $genericMail2,
            Constant::TRANSACTIONAL_MAIL_KEY_USER_CHANGE_NEW_MAIL => $genericMail3,
            Constant::TRANSACTIONAL_MAIL_KEY_USER_CHANGE_OLD_MAIL => $genericMail4,
            Constant::TRANSACTIONAL_MAIL_KEY_USER_COMPLETE_PROFILE => $genericMail5,
            Constant::TRANSACTIONAL_MAIL_KEY_USER_REGISTERED => $genericMail6,
            Constant::TRANSACTIONAL_MAIL_KEY_USER_RESET_PASSWORD => $genericMail7,
            Constant::TRANSACTIONAL_MAIL_KEY_USER_CHANGED_PASSWORD_CONFIRMED => $genericMail8,
            Constant::TRANSACTIONAL_MAIL_KEY_SHEET_TYPE_CHANGED => $genericMail9,
            Constant::TRANSACTIONAL_MAIL_KEY_SHEET_GROUP_CREATED => $genericMail10,
            Constant::TRANSACTIONAL_MAIL_KEY_PARTICIPANT_ADDED_CONFIRMATION => $genericMail11,
            Constant::TRANSACTIONAL_MAIL_KEY_THIRD_PARTY_COMEXPOSIUM_PARTICIPANT_ADDED_CONFIRMATION => $genericMail12,
            Constant::TRANSACTIONAL_MAIL_KEY_HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED => $genericMail13,
            Constant::TRANSACTIONAL_MAIL_KEY_ORDER_CONFIRMED => $genericMail14,
            Constant::TRANSACTIONAL_MAIL_KEY_TRANSACTION_CONFIRMED => $genericMail15,
        ];

        foreach (Constant::TRANSACTIONAL_MAIL_LIST as $key => $data) {
            $genericMailViewQueryHandler->handle(new GenericMailViewQuery('en', $key, $data))
                ->shouldBeCalled()
                ->willReturn($genericMailsByKey[$key]);
        }

        $handler = new TransactionalMailListViewQueryHandler($genericMailViewQueryHandler->reveal());
        $query = new TransactionalMailListViewQuery($event->reveal(), $locale);
        $result = $handler->handle($query);

        $genericMails = [
            $genericMail1,
            $genericMail2,
            $genericMail3,
            $genericMail4,
            $genericMail5,
            $genericMail6,
            $genericMail7,
            $genericMail8,
            $genericMail9,
            $genericMail10,
            $genericMail11,
            $genericMail12,
            $genericMail13,
            $genericMail14,
            $genericMail15,
        ];
        $expected = new TransactionalMailListView($genericMails);

        $this->assertEquals($expected, $result);
    }
}
