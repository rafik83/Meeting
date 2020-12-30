<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

class Create
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $locale;

    /**
     * Create constructor.
     *
     * @param string $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }
}
