<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template;
use Proximum\Vimeet\Domain\Model\Sheet\Template as SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;

class Create
{
    /**
     * @var Type
     */
    public $type;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var Template
     */
    public $template;

    /**
     * @var SheetTemplate
     */
    public $sheetTemplate;

    /**
     * @var array
     */
    public $validationCriteria = [];

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var string
     */
    public $locale;

    /**
     * @var DateTimeInterface
     */
    public $createdAt;

    /**
     * Create constructor.
     *
     * @param Event             $event
     * @param string            $locale
     * @param DateTimeInterface $createdAt
     */
    public function __construct(Event $event, $locale, DateTimeInterface $createdAt)
    {
        $this->event     = $event;
        $this->locale    = $locale;
        $this->createdAt = $createdAt;

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title' => '',
            ];
        }
    }
}
