<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
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
     * @var SheetTemplate
     */
    public $sheetTemplate;

    /**
     * @var Package
     */
    public $package;

    /**
     * @var RegistrationTemplate
     */
    public $registrationTemplate;

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
     * @var int
     */
    public $rank;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * Create constructor.
     *
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Event $event, $locale)
    {
        $this->event  = $event;
        $this->locale = $locale;

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title'       => '',
                'description' => '',
            ];
        }
    }
}
