<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\MailBundle\EventListener;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\PrepareHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareTransactionConfirmMailView;
use Proximum\Vimeet\Application\Event\Transaction\TransactionConfirmedEvent;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Ui\Bundle\MailBundle\EventListener\MailEventSubscriber;

class MailEventSubscriberTest extends TestCase
{
    public function testOnTransactionConfirmedNoMailSent(): void
    {
        // data input

        $user = $this->prophesize(User::class);
        $transaction = $this->prophesize(Transaction::class);

        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getUserLocale($user->reveal())->willReturn('fr');

        $transaction->getSheet()->willReturn($sheet->reveal());

        // dependencies

        $mailer = $this->prophesize(MailerInterface::class);
        $eventSender = $this->prophesize(EventSender::class);
        $participantMailViewQueryHandler = $this->prophesize(ParticipantMailViewQueryHandler::class);
        $prepareHandler = $this->prophesize(PrepareHandler::class);

        $prepareHandler->handle(
            new  PrepareTransactionConfirmMailView(
                $event->reveal(),
                $user->reveal(),
                'fr',
                $sheet->reveal(),
                $transaction->reveal()
            )
        )->willReturn(null)
        ;

        // run

        $event = new TransactionConfirmedEvent($user->reveal(), $transaction->reveal());

        $subscriber = new MailEventSubscriber(
            $mailer->reveal(),
            $eventSender->reveal(),
            $participantMailViewQueryHandler->reveal(),
            $prepareHandler->reveal()
        );

        $mailer->send(Argument::any())->shouldNotBeCalled();

        $subscriber->onTransactionConfirmed($event);
    }

    public function testOnTransactionConfirmedMailSent(): void
    {
        // data input

        $user = $this->prophesize(User::class);
        $transaction = $this->prophesize(Transaction::class);

        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getUserLocale($user->reveal())->willReturn('fr');

        $transaction->getSheet()->willReturn($sheet->reveal());

        // dependencies

        $mailer = $this->prophesize(MailerInterface::class);
        $eventSender = $this->prophesize(EventSender::class);
        $participantMailViewQueryHandler = $this->prophesize(ParticipantMailViewQueryHandler::class);
        $prepareHandler = $this->prophesize(PrepareHandler::class);

        $mail = $this->prophesize(AbstractMail::class);

        $prepareHandler->handle(
            new  PrepareTransactionConfirmMailView(
                $event->reveal(),
                $user->reveal(),
                'fr',
                $sheet->reveal(),
                $transaction->reveal()
            )
        )->willReturn($mail->reveal())
        ;

        $mailer->send($mail->reveal())->shouldBeCalled();

        // run

        $event = new TransactionConfirmedEvent($user->reveal(), $transaction->reveal());

        $subscriber = new MailEventSubscriber(
            $mailer->reveal(),
            $eventSender->reveal(),
            $participantMailViewQueryHandler->reveal(),
            $prepareHandler->reveal()
        );

        $subscriber->onTransactionConfirmed($event);
    }
}
