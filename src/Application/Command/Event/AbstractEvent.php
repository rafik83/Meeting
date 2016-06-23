<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractEvent
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var UploadedFile
     */
    public $logo;

    /**
     * @var array
     */
    public $locales;

    /**
     * @var string
     */
    public $timeZone;

    /**
     * @var string
     */
    public $domain;

    /**
     * @var string
     */
    public $fallback;

    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $mode;

    /**
     * @var float
     */
    public $vat;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var string
     */
    public $leftColor;

    /**
     * @var string
     */
    public $rightColor;

    /**
     * @var string
     */
    public $textColor;
}
