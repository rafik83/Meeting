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
use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Domain\Messaging\Batch\AbstractSubstitutionsProvider;
use Proximum\Vimeet\Domain\Model\Sheet;

class ProcessHandler
{
    /**
     * @var SendGridApiAdapterInterface
     */
    private $sendGridApiAdapter;

    /**
     * @var AbstractSubstitutionsProvider
     */
    private $substitutionsProvider;

    /**
     * ProcessHandler constructor.
     *
     * @param SendGridApiAdapterInterface   $sendGridApiAdapter
     * @param AbstractSubstitutionsProvider $substitutionsProvider
     */
    public function __construct(
        SendGridApiAdapterInterface $sendGridApiAdapter,
        AbstractSubstitutionsProvider $substitutionsProvider
    ) {
        $this->sendGridApiAdapter    = $sendGridApiAdapter;
        $this->substitutionsProvider = $substitutionsProvider;
    }

    /**
     * @param Process $process
     */
    public function handle(Process $process)
    {
        $placeholders = [
            '%event%',
            '%catalogOnlineDate%',
            '%scheduleDate%',
            '%firstname%',
            '%lastname%',
            '%participationType%',
        ];

        $this->sendGridApiAdapter->send(
            $process->message,
            $this->getReceivers($process->sheets, $process->locale)
        );
    }

    /**
     * @param Sheet[] $sheets
     * @param string  $locale
     *
     * @return array
     */
    private function getReceivers(array $sheets, $locale)
    {
        $receivers = [];

        foreach ($sheets as $sheet) {
            $email            = $sheet->getOwner()->getEmail();
            $receiver[$email] = new ReceiverView(
                $email,
                [],
                $locale
            );
        }

        return $receivers;
    }
}
