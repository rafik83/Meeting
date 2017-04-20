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
use Proximum\Vimeet\Domain\Model\Sheet;

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
     * @param Sheet[] $sheets
     * @param string  $messageId
     *
     * @return Message
     * @throw \InvalidArgumentException
     */
    public function create(Event $event, array $sheets, $messageId)
    {
        switch ($messageId) {
            case Events::SHEET_VALIDATED:
                $command = new CreateMessage(
                    $event,
                    $sheets,
                    Events::SHEET_VALIDATED,
                    'mail.sheet.validation.validate.subject', 'MailBundle:Mail:Sheet/sheetValidationValidate.html.twig'
                );
                break;
            default:
                throw new \InvalidArgumentException('Unknow message type');
        }

        return $this->createMessageHandler->handle($command);
    }
}
