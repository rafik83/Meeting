<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Messaging;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Messaging\MessageContextProxyInterface;

class MessageContext implements Context
{
    /** @var MessageContextProxyInterface */
    private $messageContextProxy;

    public function __construct(MessageContextProxyInterface $messageContextProxy)
    {
        $this->messageContextProxy = $messageContextProxy;
    }

    /**
     * @Given /^there is an emailing message with the name "(?P<name>[^"]+)" for this event$/
     *
     * @param string $name
     */
    public function createInEvent(string $name): void
    {
        $event = $this->messageContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $message = $this->messageContextProxy->getMessageManager()->create($event, $name);
        $this->messageContextProxy->getStorage()->set('message_message', $message);
    }
}
