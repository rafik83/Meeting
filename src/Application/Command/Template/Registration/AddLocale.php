<?php

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;

class AddLocale
{
    /**
     * @var RegistrationTemplate
     */
    public $template;

    /**
     * @var string
     */
    public $locale;

    /**
     * AddLocale constructor.
     *
     * @param RegistrationTemplate $template
     */
    public function __construct(RegistrationTemplate $template)
    {
        $this->template = $template;
    }
}
