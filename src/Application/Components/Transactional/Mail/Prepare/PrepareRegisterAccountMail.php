<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\RegisterAccountCustomizedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\RegisterAccountMail;

class PrepareRegisterAccountMail extends AbstractPrepareMailService
{
    public function prepare(PrepareUserRegisteredMailView $prepareMail): ?AbstractMail
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

            return new RegisterAccountCustomizedMail(
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

        $mail = new RegisterAccountMail(
            $prepareMail->event,
            $this->eventSenderGuesser->generate($prepareMail->event),
            $prepareMail->user->getEmail(),
            $prepareMail->locale,
            $participantMailView
        );

        return $mail;
    }
}
