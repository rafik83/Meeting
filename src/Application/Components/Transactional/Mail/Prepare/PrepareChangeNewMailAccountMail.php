<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareChangeNewMailAccountView;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ChangeNewMailAdressCustomizedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ChangeNewMailAddressMail;

class PrepareChangeNewMailAccountMail extends AbstractPrepareMailService
{
    public function prepare(PrepareChangeNewMailAccountView $prepareMail): ?AbstractMail
    {
        $message = $this->messageRepository->getOneByEventAndType(
            $prepareMail->event,
            $prepareMail->type
        );

        if ($message instanceof Message) {
            if (!$message->isEnabled()) {
                return null;
            }

            $result = $this->substitutionHandler->handle($prepareMail, $message);

            return new ChangeNewMailAdressCustomizedMail(
                $prepareMail->event,
                $this->eventSenderGuesser->generate($prepareMail->event),
                $prepareMail->changeMailToken->getMail(),
                $prepareMail->locale,
                $prepareMail->changeMailToken->getToken(),
                $result->subject,
                $result->content
            );

        }

        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery($prepareMail->sheet, $prepareMail->user)
        );

        return new ChangeNewMailAddressMail(
            $prepareMail->event,
            $this->eventSenderGuesser->generate($prepareMail->event),
            $prepareMail->changeMailToken->getMail(),
            $prepareMail->locale,
            $prepareMail->changeMailToken->getToken(),
            $prepareMail->user,
            $participantMailView
        );
    }
}
