<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

abstract class AbstractPrepareMail
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var string */
    public $type;

    /** @var string */
    public $locale;

    /** @var null|Sheet */
    public $sheet;

    public function __construct(
        Event $event,
        User $user,
        string $type,
        string $locale,
        ?Sheet $sheet
    ) {
        $this->event = $event;
        $this->user = $user;
        $this->type = $type;
        $this->locale = $locale;
        $this->sheet = $sheet;
    }

    public function hasSheet(): bool
    {
        return $this->sheet instanceof Sheet;
    }
}
