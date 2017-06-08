<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Psr\Log\LoggerInterface;

class MonologSMSSenderAdapter implements SMSSenderInterface
{
    const MESSAGE_LOGGED = 'SMS send to %s with message: %s';

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param SMS $sms
     */
    public function send(SMS $sms)
    {
        $this->logger->info(
            sprintf(self::MESSAGE_LOGGED, $sms->getReceiver(), $sms->getMessage())
        );
    }
}
