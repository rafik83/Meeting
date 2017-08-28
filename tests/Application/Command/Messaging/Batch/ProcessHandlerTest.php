<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Application\Adapter\SendGridApiAdapterInterface;
use Proximum\Vimeet\Application\Command\Messaging\Batch\Process;
use Proximum\Vimeet\Application\Command\Messaging\Batch\ProcessHandler;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Domain\Messaging\Emailing\SubstitutionResolver;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ProcessHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale('fr')->willReturn('fr');
        $event->getAvailableLocale('en')->willReturn('en');
        $event->getAvailableLocale('de')->willReturn('en');

        $message = $this->prophesize(Message::class);
        $message->isSendEmailToBillingInfo()->willReturn(true);

        $user1 = $this->prophesize(User::class);
        $user1->getEmail()->willReturn('email1@example.net');
        $user2 = $this->prophesize(User::class);
        $user2->getEmail()->willReturn('email2@example.net');
        $user3 = $this->prophesize(User::class);
        $user3->getEmail()->willReturn('email3@example.net');

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->willReturn(1);
        $sheet1->getEvent()->willReturn($event->reveal());
        $sheet1->getOwner()->willReturn($user1->reveal());
        $sheet1->getOwnerLocale()->willReturn('fr');
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->willReturn(2);
        $sheet2->getEvent()->willReturn($event->reveal());
        $sheet2->getOwner()->willReturn($user2->reveal());
        $sheet2->getOwnerLocale()->willReturn('en');
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getId()->willReturn(3);
        $sheet3->getEvent()->willReturn($event->reveal());
        $sheet3->getOwner()->willReturn($user3->reveal());
        $sheet3->getOwnerLocale()->willReturn('de');

        $billingInfo1 = $this->prophesize(BillingInfo::class);
        $billingInfo1->getSheet()->willReturn($sheet1->reveal());
        $billingInfo1->getEmail()->willReturn('emailBilling1@example.net');
        $billingInfo2 = $this->prophesize(BillingInfo::class);
        $billingInfo2->getSheet()->willReturn($sheet2->reveal());
        $billingInfo2->getEmail()->willReturn('emailBilling2@example.net');
        $billingInfo3 = $this->prophesize(BillingInfo::class);
        $billingInfo3->getSheet()->willReturn($sheet3->reveal());
        $billingInfo3->getEmail()->willReturn('emailBilling3@example.net');

        $sheets = [$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()];

        // Expected
        $receiver1 = new ReceiverView('email1@example.net', ['title' => 'test 1'], 'fr');
        $receiver1bis = new ReceiverView('emailBilling1@example.net', ['title' => 'test 1'], 'fr');
        $receiver2 = new ReceiverView('email2@example.net', ['title' => 'test 2'], 'en');
        $receiver2bis = new ReceiverView('emailBilling2@example.net', ['title' => 'test 2'], 'en');
        $receiver3 = new ReceiverView('email3@example.net', ['title' => 'test 3'], 'en');
        $receiver3bis = new ReceiverView('emailBilling3@example.net', ['title' => 'test 3'], 'en');

        // Mock
        $sendGridApiAdapter = $this->prophesize(SendGridApiAdapterInterface::class);
        $sendGridApiAdapter
            ->send(
                $message->reveal(),
                [
                    'emailBilling1@example.net1' => $receiver1bis,
                    'email1@example.net1' => $receiver1,
                    'emailBilling2@example.net2' => $receiver2bis,
                    'email2@example.net2' => $receiver2,
                    'emailBilling3@example.net3' => $receiver3bis,
                    'email3@example.net3' => $receiver3,
                ]
            )
            ->shouldBeCalled();

        $substitutionResolver = $this->prophesize(SubstitutionResolver::class);
        $substitutionResolver->getSubstitutions($sheet1->reveal(), 'fr')->shouldBeCalled()->willReturn(['title' => 'test 1']);
        $substitutionResolver->getSubstitutions($sheet2->reveal(), 'en')->shouldBeCalled()->willReturn(['title' => 'test 2']);
        $substitutionResolver->getSubstitutions($sheet3->reveal(), 'en')->shouldBeCalled()->willReturn(['title' => 'test 3']);

        $billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $billingInfoRepository->getBySheets($sheets)->shouldBeCalled()->willReturn([
            $billingInfo1->reveal(),
            $billingInfo2->reveal(),
            $billingInfo3->reveal(),
        ]);

        // Handler
        $handler = new ProcessHandler(
            $sendGridApiAdapter->reveal(),
            $substitutionResolver->reveal(),
            $billingInfoRepository->reveal()
        );
        $handler->handle(new Process($message->reveal(), $sheets));
    }
}
