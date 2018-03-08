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

    /** @var null|string */
    public $email;

    /** @var bool */
    public $showLogin;

    /**
     * @param Event       $event
     * @param string      $locale
     * @param null|string $email
     * @param bool        $showLogin
     */
    public function __construct(Event $event, string $locale, ?string $email, bool $showLogin)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->email = $email;
        $this->showLogin = $showLogin;
    }
}
