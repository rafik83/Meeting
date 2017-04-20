<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\Batch;

use Proximum\Vimeet\Domain\Messaging\InvalidMessagePlaceholderException;
use Proximum\Vimeet\Domain\Model\MailRecipientInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetValidatedSubstitutionsProvider extends AbstractSubstitutionsProvider
{
    const TAG_EVENT_NAME          = '%event%';
    const TAG_FIRSTNAME           = '%firstname%';
    const TAG_LASTNAME            = '%lastname%';
    const TAG_CATALOG_ONLINE_DATE = '%catalogOnlineDate%';
    const TAG_SCHEDULE_DATE       = '%scheduleDate%';
    const TAG_PARTICIPATION_TYPE  = '%participationType%';

    /**
     * @var array
     */
    protected $placeholders = [
        self::TAG_EVENT_NAME,
        self::TAG_FIRSTNAME,
        self::TAG_LASTNAME,
        self::TAG_CATALOG_ONLINE_DATE,
        self::TAG_SCHEDULE_DATE,
        self::TAG_PARTICIPATION_TYPE
    ];

    /**
     * {@inheritdoc}
     */
    public function getSubstitutions(MailRecipientInterface $recipient, Sheet $sheet, $locale, $placeholders = [])
    {
        $substitutions = [];

        foreach ($placeholders as $placeholder) {
            $substitutions[$placeholder] = $this->getSubstitution($placeholder, $sheet, $locale);
        }

        return $substitutions;
    }

    /**
     * @param string $placeholder
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return string
     */
    private function getSubstitution($placeholder, Sheet $sheet, $locale)
    {
        $event         = $sheet->getEvent();
        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $event->getTimeZone()
        );

        switch ($placeholder) {
            case self::TAG_EVENT_NAME:
                return $event->getTitle();
            case self::TAG_FIRSTNAME:
                return $sheet->getOwner()->getFirstName();
            case self::TAG_LASTNAME:
                return $sheet->getOwner()->getLastName();
            case self::TAG_CATALOG_ONLINE_DATE:
                $catalogOnlineDate = $event->getConfiguration()->getCatalogOnlineDate();
                return $catalogOnlineDate !== null ? $dateFormatter->format($catalogOnlineDate) : '';
            case self::TAG_SCHEDULE_DATE:
                $schedulePublishDate = $event->getConfiguration()->getSchedulePublishDate();
                return $schedulePublishDate !== null ? $dateFormatter->format($schedulePublishDate) : '';
        }

        throw new InvalidMessagePlaceholderException($placeholder);
    }
}
