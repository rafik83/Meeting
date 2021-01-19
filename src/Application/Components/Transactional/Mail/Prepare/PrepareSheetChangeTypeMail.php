<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareSheetChangeTypeMailView;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\SheetChangeTypeCustomizedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\SheetChangeTypeMail;

class PrepareSheetChangeTypeMail extends AbstractPrepareMailService
{
    public function prepare(PrepareSheetChangeTypeMailView $prepareMail): ?AbstractMail
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

            return new SheetChangeTypeCustomizedMail(
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

        $mail = new SheetChangeTypeMail(
            $prepareMail->event,
            $prepareMail->sheet,
            $this->eventSenderGuesser->generate($prepareMail->event),
            $prepareMail->user->getEmail(),
            $prepareMail->locale,
            $prepareMail->user,
            $prepareMail->fromTypeTitle,
            $prepareMail->sheet->getType()->getTitle($prepareMail->locale),
            $participantMailView
        );

        return $mail;
    }
}
