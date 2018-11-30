<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Application\Components\Messaging\MessageFactory;

class SendEmailingByTypeHandler
{
    /** @var MessageFactory */
    private $messageFactory;

    /** @var ProcessHandler */
    private $processHandler;

    public function __construct(
        MessageFactory $messageFactory,
        ProcessHandler $processHandler
    ) {
        $this->messageFactory = $messageFactory;
        $this->processHandler = $processHandler;
    }

    public function handle(SendEmailingByType $sendEmailingByType)
    {
        $message = $this->messageFactory->create(
            $sendEmailingByType->event,
            $sendEmailingByType->messageId,
            $sendEmailingByType->sendEmailToTeam
        );

        $this->processHandler->handle(new Process($message, $sendEmailingByType->sheets));
    }
}
