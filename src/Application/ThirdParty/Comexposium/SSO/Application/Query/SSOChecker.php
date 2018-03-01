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

class SSOChecker implements Query
{
    /** @var string */
    public $email;

    /** @var string */
    public $token;

    /** @var Event */
    public $event;

    /** @var bool */
    public $isExhibitor;

    /**
     * @param Event  $event
     * @param string $email
     * @param string $token
     * @param bool   $isExhibitor
     */
    public function __construct(Event $event, string $email, string $token, bool $isExhibitor)
    {
        $this->event = $event;
        $this->email = $email;
        $this->token = $token;
        $this->isExhibitor = $isExhibitor;
    }
}
