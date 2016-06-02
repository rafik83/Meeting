<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreatePlan
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var UploadedFile
     */
    public $file;

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var int
     */
    public $availabilityCurrent;

    /**
     * @var int
     */
    public $availabilityMax;

    /**
     * @var array
     */
    public $features = [];

    /**
     * @var array
     */
    public $productIncluded = [];

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title'       => null,
                'heading'     => null,
                'description' => null,
                'addon'       => null,
            ];
        }
    }
}
