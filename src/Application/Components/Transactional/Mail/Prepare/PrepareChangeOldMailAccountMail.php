<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareChangeOldMailAccountView;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ChangeOldMailAdressCustomizedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ChangeOldMailAddressMail;

class PrepareChangeOldMailAccountMail extends AbstractPrepareMailService
{
    public function prepare(PrepareChangeOldMailAccountView $prepareMail): ?AbstractMail
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

            return new ChangeOldMailAdressCustomizedMail(
                $prepareMail->event,
                $this->eventSenderGuesser->generate($prepareMail->event),
                $prepareMail->user->getEmail(),
                $prepareMail->locale,
                $prepareMail->changeMailToken->getMail(),
                $result->subject,
                $result->content
            );
        }

        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery($prepareMail->sheet, $prepareMail->user)
        );

        return new ChangeOldMailAddressMail(
            $prepareMail->event,
            $this->eventSenderGuesser->generate($prepareMail->event),
            $prepareMail->user->getEmail(),
            $prepareMail->locale,
            $prepareMail->changeMailToken->getMail(),
            $participantMailView
        );
    }
}
