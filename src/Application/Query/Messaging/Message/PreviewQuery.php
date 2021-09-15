<?php

namespace Proximum\Vimeet\Application\Query\Messaging\Message;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Messaging\Message;

class PreviewQuery implements Query
{
    /** @var Message */
    public $message;

    /** @var string */
    public $locale;

    /**
     * @param Message $message
     * @param string  $locale
     */
    public function __construct(Message $message, $locale)
    {
        $this->message = $message;
        $this->locale  = $locale;
    }
}
