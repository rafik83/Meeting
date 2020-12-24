<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

class LeniUserCustomDataQuery
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var Type */
    public $type;

    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    public function __construct(Event $event, User $user, Type $type, Sheet $sheet, string $locale)
    {
        $this->user = $user;
        $this->type = $type;
        $this->event = $event;
        $this->sheet = $sheet;
        $this->locale = $locale;
    }
}
