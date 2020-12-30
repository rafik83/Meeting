<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCompleteProfileMailView;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\CompleteProfileCustomizedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\CompleteProfileMail;

class PrepareUserCompleteProfileMail extends AbstractPrepareMailService
{
    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        SubstitutionHandler $substitutionHandler,
        EventSender $eventSenderGuesser,
        ParticipantMailViewQueryHandler $participantMailViewQueryHandler,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        parent::__construct(
            $messageRepository,
            $substitutionHandler,
            $eventSenderGuesser,
            $participantMailViewQueryHandler
        );

        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    public function prepare(PrepareUserCompleteProfileMailView $prepareMail): ?AbstractMail
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

            return new CompleteProfileCustomizedMail(
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

        $eventActivateAccountAlreadyKnownUrl = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $prepareMail->event,
            'event_activate_account_already_known',
            ['participant' => $prepareMail->participant->getId()]
        );

        $mail = new CompleteProfileMail(
            $prepareMail->participant,
            $this->eventSenderGuesser->generate($prepareMail->event),
            $prepareMail->user->getEmail(),
            $prepareMail->locale,
            $participantMailView,
            $eventActivateAccountAlreadyKnownUrl
        );

        return $mail;
    }
}
