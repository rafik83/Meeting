<?php

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

    /** @var string */
    public $locale;

    /**
     * @param Event  $event
     * @param string $email
     * @param string $token
     * @param bool   $isExhibitor
     * @param string $locale
     */
    public function __construct(Event $event, string $email, string $token, bool $isExhibitor, string $locale)
    {
        $this->event = $event;
        $this->email = $email;
        $this->token = $token;
        $this->isExhibitor = $isExhibitor;
        $this->locale = $locale;
    }
}
