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
use Proximum\Vimeet\Domain\Model\MailRecipientInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

abstract class AbstractSubstitutionsProvider
{
    const TAG_EVENT_NAME          = '%event%';
    const TAG_FIRSTNAME           = '%firstname%';
    const TAG_LASTNAME            = '%lastname%';
    const TAG_PARTICIPATION_TYPE  = '%participationType%';

    /**
     * @var array
     */
    protected $placeholders = [
        self::TAG_EVENT_NAME,
        self::TAG_FIRSTNAME,
        self::TAG_LASTNAME,
        self::TAG_PARTICIPATION_TYPE
    ];

    /**
     * @param MailRecipientInterface $recipient
     * @param Sheet                  $sheet
     * @param string                 $locale
     * @param array                  $placeholders
     *
     * @return String[]
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
    protected function getSubstitution($placeholder, Sheet $sheet, $locale)
    {
        $event = $sheet->getEvent();

        switch ($placeholder) {
            case self::TAG_EVENT_NAME:
                return $event->getTitle();
            case self::TAG_FIRSTNAME:
                return $sheet->getOwner()->getFirstName();
            case self::TAG_LASTNAME:
                return $sheet->getOwner()->getLastName();
            case self::TAG_PARTICIPATION_TYPE:
                return $sheet->getType()->getTitle($locale);
        }

        throw new InvalidMessagePlaceholderException($placeholder);
    }

    /**
     * @param string $messageTitle
     * @param string $messageBody
     *
     * @return array
     */
    public function findPlaceholders($messageTitle, $messageBody)
    {
        $foundPlaceholders = [];
        
        foreach ($this->placeholders as $placeholder) {
            if (false !== strpos($messageBody, $placeholder)) {
                $foundPlaceholders[] = $placeholder;
            }
            if (false !== strpos($messageTitle, $placeholder)) {
                $foundPlaceholders[] = $placeholder;
            }
        }

        return $foundPlaceholders;
    }
}
