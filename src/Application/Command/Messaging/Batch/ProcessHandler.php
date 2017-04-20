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
        $this->sendGridApiAdapter->send($process->message, $this->getReceivers($process));
    }

    /**
     * @param Process $process
     *
     * @return array
     */
    private function getReceivers(Process $process)
    {
        $receivers = [];

        foreach ($process->sheets as $sheet) {
            $locale = $sheet->getOwnerLocale();

            // get subject and body placeholders
            $placeholders = $this->substitutionsProvider->findPlaceholders(
                $process->message->getSubject($locale),
                $process->message->getContent($locale)
            );

            // replace placeholders by content
            $substitutions = $this->substitutionsProvider->getSubstitutions(
                $sheet->getOwner(),
                $sheet,
                $locale,
                $placeholders
            );

            $email = $sheet->getOwner()->getEmail();
            $index = $email . $sheet->getId();
            $receiver[$index] = new ReceiverView(
                $email,
                $substitutions,
                $locale
            );
        }

        return $receivers;
    }
}
