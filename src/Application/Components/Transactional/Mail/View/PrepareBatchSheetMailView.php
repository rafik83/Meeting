<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class PrepareBatchSheetMailView extends AbstractPrepareMail
{
    public function __construct(Event $event, User $user, string $type, string $locale, Sheet $sheet)
    {
        parent::__construct($event, $user, $type, $locale, $sheet);
    }
}
