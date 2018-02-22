<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\View;

class SSOComexposiumView
{
    /** @var string */
    public $salon;

    /** @var string */
    public $sessionSalon;

    /** @var string */
    public $application;

    /** @var string */
    public $locale;

    /** @var string */
    public $email;

    public function __construct(
        string $salon,
        string $sessionSalon,
        string $application,
        string $locale,
        string $email
    ) {
        $this->salon = $salon;
        $this->sessionSalon = $sessionSalon;
        $this->application = $application;
        $this->locale = $locale;
        $this->email = $email;
    }
}
