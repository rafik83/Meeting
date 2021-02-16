<?php

namespace Proximum\Vimeet\Application\Command\Admin;

class ForgottenPassword
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param string $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }
}
