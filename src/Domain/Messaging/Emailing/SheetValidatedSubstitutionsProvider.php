<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\Emailing;

use Proximum\Vimeet\Domain\Messaging\InvalidMessagePlaceholderException;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Adapter\EventUrlGenerator;

class SheetValidatedSubstitutionsProvider extends AbstractSubstitutionsProvider
{
    const TAG_CATALOG_ONLINE_DATE = '%catalogOnlineDate%';
    const TAG_SCHEDULE_DATE       = '%scheduleDate%';

    /**
     * SheetValidatedSubstitutionsProvider constructor.
     */
    public function __construct()
    {
        $this->placeholders = array_merge($this->placeholders, [
            self::TAG_CATALOG_ONLINE_DATE,
            self::TAG_SCHEDULE_DATE,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubstitution($placeholder, Sheet $sheet, $locale)
    {
        try {
            return parent::getSubstitution($placeholder, $sheet, $locale);
        } catch (InvalidMessagePlaceholderException $exception) {

            $event         = $sheet->getEvent();
            $dateFormatter = \IntlDateFormatter::create(
                $locale,
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::NONE,
                $event->getTimeZone()
            );

            switch ($placeholder) {
                case self::TAG_CATALOG_ONLINE_DATE:
                    $catalogOnlineDate = $event->getConfiguration()->getCatalogOnlineDate();
                    return $catalogOnlineDate !== null ? $dateFormatter->format($catalogOnlineDate) : '';
                case self::TAG_SCHEDULE_DATE:
                    $schedulePublishDate = $event->getConfiguration()->getSchedulePublishDate();
                    return $schedulePublishDate !== null ? $dateFormatter->format($schedulePublishDate) : '';
                default:
                    throw new InvalidMessagePlaceholderException($placeholder);
            }
        }
    }
}
