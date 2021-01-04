<?php

namespace Proximum\Vimeet\Application\Command\User\Phone;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class SendCode
{
    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    /** @var string */
    public $phone;

    /** @var bool */
    public $accepted;

    /** @var string */
    public $locale;

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $phone
     * @param string $locale
     */
    public function __construct(User $user, Event $event, $phone, $locale)
    {
        $this->user = $user;
        $this->event = $event;
        $this->phone = $phone;
        $this->locale = $locale;
        $this->accepted = false;
    }
}
