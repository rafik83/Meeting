<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;


use Proximum\Vimeet\Application\Adapter\SendGridApiAdapterInterface;

class ProcessHandler
{
    /**
     * @var SendGridApiAdapterInterface
     */
    private $sendGridApiAdapter;

    /**
     * ProcessHandler constructor.
     *
     * @param SendGridApiAdapterInterface $sendGridApiAdapter
     */
    public function __construct(SendGridApiAdapterInterface $sendGridApiAdapter)
    {
        $this->sendGridApiAdapter = $sendGridApiAdapter;
    }

    /**
     * @param Process $process
     */
    public function handle(Process $process)
    {
        $this->sendGridApiAdapter->send($process->message, []);
    }
}
