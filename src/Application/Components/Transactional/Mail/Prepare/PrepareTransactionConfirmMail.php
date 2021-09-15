<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareTransactionConfirmMailView;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Transaction\TransactionConfirmCustomizedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Transaction\TransactionConfirmMail;

class PrepareTransactionConfirmMail extends AbstractPrepareMailService
{
    public function prepare(PrepareTransactionConfirmMailView $prepareMail): ?AbstractMail
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

            return new TransactionConfirmCustomizedMail(
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

        $mail = new TransactionConfirmMail(
            $prepareMail->transaction,
            $prepareMail->user,
            $this->eventSenderGuesser->generate($prepareMail->event),
            $prepareMail->user->getEmail(),
            $prepareMail->locale,
            $participantMailView
        );

        return $mail;
    }
}
