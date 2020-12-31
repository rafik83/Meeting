<?php

namespace Proximum\Vimeet\Application\Command\Register;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\TypeView;

class RegisterNewUser
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var TypeView
     */
    public $typeView;

    /**
     * @param string   $email
     * @param string   $locale
     * @param Event    $event
     * @param TypeView $type
     */
    public function __construct($email, $locale, Event $event, TypeView $type)
    {
        $this->email    = $email;
        $this->locale   = $locale;
        $this->event    = $event;
        $this->typeView = $type;
    }
}
