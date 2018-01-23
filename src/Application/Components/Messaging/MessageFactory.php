<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Messaging;

use Proximum\Vimeet\Application\Command\Messaging\Batch\CreateMessage;
use Proximum\Vimeet\Application\Command\Messaging\Batch\CreateMessageHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;

class MessageFactory
{
    /**
     * @var CreateMessageHandler
     */
    private $createMessageHandler;

    /**
     * MessageFactory constructor.
     *
     * @param CreateMessageHandler $createMessageHandler
     */
    public function __construct(CreateMessageHandler $createMessageHandler)
    {
        $this->createMessageHandler = $createMessageHandler;
    }

    /**
     * @param Event   $event
     * @param string  $messageId
     * @param bool    $sendEmailToTeam
     *
     * @return Message
     * @throw \InvalidArgumentException
     */
    public function create(Event $event, $messageId, $sendEmailToTeam = false)
    {
        switch ($messageId) {
            case Events::SHEET_REFUSED:
                $command = new CreateMessage(
                    $event,
                    Events::SHEET_REFUSED,
                    'mail.sheet.refused.subject',
                    'MailBundle:Mail:Sheet/sheetRefused.html.twig',
                    $sendEmailToTeam
                );
                break;
            case Events::SHEET_VALIDATED:
                $command = new CreateMessage(
                    $event,
                    Events::SHEET_VALIDATED,
                    'mail.sheet.validated.subject',
                    'MailBundle:Mail:Sheet/sheetValidated.html.twig',
                    $sendEmailToTeam
                );
                break;
            case Events::SHEET_VALIDATION_VALIDATE:
                $command = new CreateMessage(
                    $event,
                    Events::SHEET_VALIDATION_VALIDATE,
                    'mail.sheet.validation.validate.subject',
                    'MailBundle:Mail:Sheet/sheetValidationValidate.html.twig',
                    $sendEmailToTeam
                );
                break;
            case Events::SHEET_VALIDATION_DRAFT:
                $command = new CreateMessage(
                    $event,
                    Events::SHEET_VALIDATION_DRAFT,
                    'mail.sheet.validation.draft.subject',
                    'MailBundle:Mail:Sheet/sheetValidationDraft.html.twig',
                    $sendEmailToTeam
                );
                break;
            case Events::SHEET_INVOICED:
                $command = new CreateMessage(
                    $event,
                    Events::SHEET_INVOICED,
                    'mail.sheet.invoiced.subject',
                    'MailBundle:Mail:Invoice/sheetInvoiced.html.twig',
                    $sendEmailToTeam,
                    true
                );
                break;
            default:
                throw new \InvalidArgumentException('Undefined message type');
        }

        return $this->createMessageHandler->handle($command);
    }
}
