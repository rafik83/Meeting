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

class CatalogOnlineDateSubstitution implements Substitute
{
    /**
     * {@inheritdoc}
     */
    public function getValue(Sheet $sheet, $locale)
    {
        $event         = $sheet->getEvent();
        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $event->getTimeZone()
        );

        $catalogOnlineDate = $event->getConfiguration()->getCatalogOnlineDate();

        return $catalogOnlineDate !== null ? $dateFormatter->format($catalogOnlineDate) : '';
    }
}
