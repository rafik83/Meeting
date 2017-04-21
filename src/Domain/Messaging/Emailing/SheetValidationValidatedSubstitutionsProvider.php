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

class SheetValidationValidatedSubstitutionsProvider extends AbstractSubstitutionsProvider
{
    const TAG_SHEET_LINK = '%sheetLink%';

    /**
     * @var EventUrlGenerator
     */
    private $eventUrlGenerator;

    /**
     * SheetValidatedSubstitutionsProvider constructor.
     *
     * @param EventUrlGenerator $eventUrlGenerator
     */
    public function __construct(EventUrlGenerator $eventUrlGenerator)
    {
        $this->eventUrlGenerator = $eventUrlGenerator;

        $this->placeholders = array_merge($this->placeholders, [
            self::TAG_SHEET_LINK,
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

            $event = $sheet->getEvent();

            switch ($placeholder) {
                case self::TAG_SHEET_LINK:
                    return $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_sheet_default', [
                        'sheet'   => $sheet->getId(),
                        '_locale' => $locale,
                    ]);
                default:
                    throw new InvalidMessagePlaceholderException($placeholder);
            }
        }
    }
}
