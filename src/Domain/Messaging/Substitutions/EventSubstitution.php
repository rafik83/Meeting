<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Sheet;

class EventSubstitution implements SubstituteInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValue(Sheet $sheet, $locale)
    {
        return $sheet->getEvent()->getTitle();
    }
}
