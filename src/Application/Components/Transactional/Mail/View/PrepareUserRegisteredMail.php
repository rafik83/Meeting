<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareUserRegisteredMail extends AbstractPrepareMail
{
    public function __construct(
        Event $event,
        User $user,
        string $locale
    ) {
        parent::__construct($event, $user, Constant::TRANSACTIONAL_MAIL_KEY_USER_REGISTERED, $locale, null);
    }
}
