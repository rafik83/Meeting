<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\Batch;

use Proximum\Vimeet\Domain\Model\MailRecipientInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

abstract class AbstractSubstitutionsProvider
{
    /**
     * @param MailRecipientInterface $recipient
     * @param Sheet                  $sheet
     * @param string                 $locale
     * @param array                  $placeholders
     *
     * @return String[]
     */
    abstract public function getSubstitutions(
        MailRecipientInterface $recipient,
        Sheet $sheet,
        $locale,
        $placeholders = []
    );
}
