<?php

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class TipTranslationViewByUserQuery
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var string */
    public $context;

    /** @var string */
    public $locale;

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $context
     * @param string $locale
     */
    public function __construct(Event $event, User $user, $context, $locale)
    {
        $this->event = $event;
        $this->user = $user;
        $this->context = $context;
        $this->locale = $locale;
    }
}
