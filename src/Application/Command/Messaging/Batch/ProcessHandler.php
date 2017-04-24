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
use Proximum\Vimeet\Domain\Messaging\Emailing\SubstitutionResolver;

class ProcessHandler
{
    /**
     * @var SendGridApiAdapterInterface
     */
    private $sendGridApiAdapter;

    /**
     * @var SubstitutionResolver
     */
    private $substitutionResolver;

    /**
     * ProcessHandler constructor.
     *
     * @param SendGridApiAdapterInterface $sendGridApiAdapter
     * @param SubstitutionResolver        $substitutionResolver
     */
    public function __construct(
        SendGridApiAdapterInterface $sendGridApiAdapter,
        SubstitutionResolver $substitutionResolver
    ) {
        $this->sendGridApiAdapter   = $sendGridApiAdapter;
        $this->substitutionResolver = $substitutionResolver;
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
     * @return array string => ReceiverView
     */
    private function getReceivers(Process $process)
    {
        $receivers = [];

        foreach ($process->sheets as $sheet) {
            $locale = $sheet->getOwnerLocale();

            // replace all placeholders by content
            $substitutions = $this->substitutionResolver->getSubstitutions($sheet, $locale);

            $email = $sheet->getOwner()->getEmail();
            $index = $email . $sheet->getId();
            $receivers[$index] = new ReceiverView(
                $email,
                $substitutions,
                $locale
            );
        }

        return $receivers;
    }
}
