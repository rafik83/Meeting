<?php

namespace Proximum\Vimeet\Ui\Flash;

class TranschoiceMessage extends TransMessage
{
    /**
     * @var int
     */
    public $count;

    /**
     * TranschoiceMessage constructor.
     *
     * @param string $message
     * @param int    $count
     * @param array  $arguments
     * @param string $domain
     * @param string $locale
     */
    public function __construct($message, $count, array $arguments = [], $domain = 'flashes', $locale = null)
    {
        parent::__construct($message, $arguments, $domain, $locale);

        $this->count = $count;
    }
}
