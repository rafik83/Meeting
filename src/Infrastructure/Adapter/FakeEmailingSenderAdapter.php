<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\EmailingSenderInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Psr\Log\LoggerInterface;

class FakeEmailingSenderAdapter implements EmailingSenderInterface
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function send(Message $message, array $receivers)
    {
        $this->logger->info('Send emailing', [$message, $receivers]);
    }
}
