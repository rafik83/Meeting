<?php

namespace Proximum\Vimeet\Ui\Flash;

class TransMessage
{
    /**
     * @var string
     */
    public $message;

    /**
     * @var array
     */
    public $arguments;

    /**
     * @var string
     */
    public $domain;

    /**
     * @var string
     */
    public $locale;

    /**
     * TransMessage constructor.
     *
     * @param string $message
     * @param array  $arguments
     * @param string $domain
     * @param string $locale
     */
    public function __construct($message, array $arguments = [], $domain = 'flashes', $locale = null)
    {
        $this->message   = $message;
        $this->arguments = $arguments;
        $this->domain    = $domain;
        $this->locale    = $locale;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->message;
    }
}
