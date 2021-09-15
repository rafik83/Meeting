<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\SMS;

use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Psr\Log\LoggerInterface;

class MonologProvider implements SMSProviderInterface
{
    private const MESSAGE_LOGGED = 'SMS sent to %s with message: %s';

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function canSend(SMS $sms): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage(SMS $sms): void
    {
        $this->logger->info(
            sprintf(self::MESSAGE_LOGGED, $sms->getReceiver(), $sms->getMessage())
        );
    }
}
