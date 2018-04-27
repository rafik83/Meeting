<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetLinkSubstitution implements SubstituteInterface
{
    /**
     * @var EventUrlGeneratorInterface
     */
    private $eventUrlGenerator;

    /**
     * SheetLinkSubstitution constructor.
     *
     * @param EventUrlGeneratorInterface $eventUrlGenerator
     */
    public function __construct(EventUrlGeneratorInterface $eventUrlGenerator)
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
            '_locale' => $sheet->getOwnerLocale(),
        ]);
    }
}
