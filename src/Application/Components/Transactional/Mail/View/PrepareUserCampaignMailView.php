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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class PrepareUserCampaignMailView extends AbstractPrepareMail
{
    public function __construct(
        Event $event,
        User $user,
        string $locale,
        Sheet $sheet
    ) {
        parent::__construct($event, $user, '', $locale, $sheet);
    }
}
