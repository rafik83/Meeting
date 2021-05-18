<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareMeetingFollowUpView;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Meeting\MeetingFollowUpCustomizedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Meeting\MeetingFollowUpMail;

class PrepareMeetingFollowUpMail extends AbstractPrepareMailService
{
    public function prepare(PrepareMeetingFollowUpView $prepareMail): ?AbstractMail
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

            return new MeetingFollowUpCustomizedMail(
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

        return new MeetingFollowUpMail(
            $prepareMail->event,
            $this->eventSenderGuesser->generate($prepareMail->event),
            $prepareMail->user->getEmail(),
            $prepareMail->locale,
            $participantMailView,
            $prepareMail->sheet->getId(),
            $prepareMail->sheet->getTitle(),
            $prepareMail->evaluatingSheetTitle,
            $prepareMail->evaluation,
            $prepareMail->metParticipants
        );
    }
}
