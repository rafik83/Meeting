<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareOrderConfirmedMailView;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Order\OrderConfirmedCustomizedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Order\OrderConfirmMail;

class PrepareOrderConfirmedMail extends AbstractPrepareMailService
{
    public function prepare(PrepareOrderConfirmedMailView $prepareMail): ?AbstractMail
    {
        $message = $this->messageRepository->getOneByEventAndTypeAndAssociatedType(
            $prepareMail->event,
            $prepareMail->type,
            $prepareMail->sheet->getType()
        );

        if ($message instanceof Message) {
            if (!$message->isEnabled()) {
                return null;
            }

            $result = $this->substitutionHandler->handle($prepareMail, $message);

            return new OrderConfirmedCustomizedMail(
                $prepareMail->event,
                $this->eventSenderGuesser->generate($prepareMail->event),
                $prepareMail->user->getEmail(),
                $prepareMail->locale,
                $result->subject,
                $result->content
            );
        }

        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery($prepareMail->sheet, $prepareMail->user)
        );

        $mail = new OrderConfirmMail(
            $this->eventSenderGuesser->generate($prepareMail->event),
            $prepareMail->user->getEmail(),
            $prepareMail->locale,
            $prepareMail->order,
            $participantMailView
        );

        return $mail;
    }
}
