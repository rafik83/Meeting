<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareUserRegisteredMailView extends AbstractPrepareMail
{
    public function __construct(
        Event $event,
        User $user,
        string $locale
    ) {
        parent::__construct($event, $user, Constant::TRANSACTIONAL_MAIL_KEY_USER_REGISTERED, $locale, null);
    }
}
