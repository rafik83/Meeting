<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Transactional\Mail;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\RegisterAccountCustomizedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\RegisterAccountMail;

class PrepareHandler
{
    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var ParticipantMailViewQueryHandler */
    private $participantMailViewQueryHandler;

    /** @var EventSender */
    private $eventSenderGuesser;

    /** @var SubstitutionHandler */
    private $substitutionHandler;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        ParticipantMailViewQueryHandler $participantMailViewQueryHandler,
        SubstitutionHandler $substitutionHandler,
        EventSender $eventSenderGuesser
    ) {
        $this->messageRepository = $messageRepository;
        $this->participantMailViewQueryHandler = $participantMailViewQueryHandler;
        $this->eventSenderGuesser = $eventSenderGuesser;
        $this->substitutionHandler = $substitutionHandler;
    }

    public function handle(AbstractPrepareMail $prepareMail): ?AbstractMail
    {
        switch ($prepareMail->type) {
            case Constant::TRANSACTIONAL_MAIL_KEY_USER_REGISTERED:
                $message = $this->messageRepository->getOneByEventAndType(
                    $prepareMail->event,
                    $prepareMail->type
                );

                if ($message instanceof Message) {
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
            default: return null;
        }
    }
}
