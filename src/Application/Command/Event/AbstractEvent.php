<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractEvent
{
    /** @var string */
    public $title;

    /** @var UploadedFile */
    public $logo;

    /** @var array */
    public $locales;

    /** @var string */
    public $timeZone;

    /** @var string */
    public $domain;

    /** @var string */
    public $fallback;

    /** @var string */
    public $country;

    /** @var string */
    public $mode;

    /** @var float */
    public $vat;

    /** @var string */
    public $currency;

    /** @var string */
    public $organiserName;

    /** @var string|null */
    public $emailTeam;

    /** @var null|Prefix */
    public $invoicePrefix;

    /** @var bool */
    public $visible;

    /** @var bool */
    public $welcomeEnabled;

    /** @var bool */
    public $disabledEmailChanging = false;

    /** @var bool */
    public $disabledPasswordChanging = false;

    /** @var bool */
    public $autoArchiveWebinar = false;
}
