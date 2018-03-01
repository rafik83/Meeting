<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class SSOComexposiumViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var string */
    public $email;

    /**
     * @param Event       $event
     * @param string      $locale
     * @param string      $email
     */
    public function __construct(Event $event, string $locale, string $email)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->email = $email;
    }
}
