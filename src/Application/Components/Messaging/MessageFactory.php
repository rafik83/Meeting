<?php

namespace Proximum\Vimeet\Application\Components\Messaging;

use Proximum\Vimeet\Application\Command\Messaging\Batch\CreateMessage;
use Proximum\Vimeet\Application\Command\Messaging\Batch\CreateMessageHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class MessageFactory
{
    /** @var CreateMessageHandler */
    private $createMessageHandler;

    public function __construct(CreateMessageHandler $createMessageHandler)
    {
        $this->createMessageHandler = $createMessageHandler;
    }

    public function create(Event $event, string $messageId): Message
    {
        switch ($messageId) {
            case Events::SHEET_REFUSED:
                $command = new CreateMessage(
                    $event,
                    Constant::TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED,
                    Constant::TRANSACTIONAL_MAIL_SHEET_REFUSED_SUBJECT,
                    Constant::TRANSACTIONAL_MAIL_SHEET_REFUSED_TEMPLATE,
                    true
                );
                break;
            case Events::SHEET_VALIDATED:
                $command = new CreateMessage(
                    $event,
                    Constant::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATED,
                    Constant::TRANSACTIONAL_MAIL_SHEET_VALIDATED_SUBJECT,
                    Constant::TRANSACTIONAL_MAIL_SHEET_VALIDATED_TEMPLATE,
                    true
                );
                break;
            case Events::SHEET_VALIDATION_VALIDATE:
                $command = new CreateMessage(
                    $event,
                    Constant::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_VALIDATE,
                    Constant::TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_SUBJECT,
                    Constant::TRANSACTIONAL_MAIL_SHEET_VALIDATION_VALIDATE_TEMPLATE,
                    true
                );
                break;
            case Events::SHEET_VALIDATION_DRAFT:
                $command = new CreateMessage(
                    $event,
                    Constant::TRANSACTIONAL_MAIL_KEY_SHEET_VALIDATION_DRAFT,
                    Constant::TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_SUBJECT,
                    Constant::TRANSACTIONAL_MAIL_SHEET_VALIDATION_DRAFT_TEMPLATE,
                    true
                );
                break;
            case Events::SHEET_INVOICED:
                $command = new CreateMessage(
                    $event,
                    Constant::TRANSACTIONAL_MAIL_KEY_SHEET_INVOICED,
                    Constant::TRANSACTIONAL_MAIL_SHEET_INVOICED_SUBJECT,
                    Constant::TRANSACTIONAL_MAIL_SHEET_INVOICED_TEMPLATE,
                    true,
                    true
                );
                break;
            default:
                throw new \InvalidArgumentException('Undefined message type');
        }

        return $this->createMessageHandler->handle($command);
    }
}
