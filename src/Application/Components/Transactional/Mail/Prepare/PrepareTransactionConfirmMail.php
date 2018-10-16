<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareTransactionConfirmMailView;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Transaction\TransactionConfirmCustomizedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Transaction\TransactionConfirmMail;

class PrepareTransactionConfirmMail
{
    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var SubstitutionHandler */
    private $substitutionHandler;

    /** @var EventSender */
    private $eventSenderGuesser;

    /** @var ParticipantMailViewQueryHandler */
    private $participantMailViewQueryHandler;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        SubstitutionHandler $substitutionHandler,
        EventSender $eventSenderGuesser,
        ParticipantMailViewQueryHandler $participantMailViewQueryHandler
    ) {
        $this->messageRepository = $messageRepository;
        $this->substitutionHandler = $substitutionHandler;
        $this->eventSenderGuesser = $eventSenderGuesser;
        $this->participantMailViewQueryHandler = $participantMailViewQueryHandler;
    }

    public function prepare(PrepareTransactionConfirmMailView $prepareMail): ?AbstractMail
    {
        $message = $this->messageRepository->getOneByEventAndTypeAndAssociatedType(
            $prepareMail->event,
            $prepareMail->type,
            $prepareMail->sheet->getType()
        );

        if ($message instanceof Message) {
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
