<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Adapter\EventUrlGenerator;

class SheetLinkSubstitution implements SubstituteInterface
{
    /**
     * @var EventUrlGenerator
     */
    private $eventUrlGenerator;

    /**
     * SheetLinkSubstitution constructor.
     *
     * @param EventUrlGenerator $eventUrlGenerator
     */
    public function __construct(EventUrlGenerator $eventUrlGenerator)
    {
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(Sheet $sheet, $locale)
    {
        return $this->eventUrlGenerator->generateEventAbsoluteUrl($sheet->getEvent(), 'event_sheet_default', [
            'sheet'   => $sheet->getId(),
            '_locale' => $locale,
        ]);
    }
}
