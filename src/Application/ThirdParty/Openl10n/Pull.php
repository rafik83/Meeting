<?php

namespace Proximum\Vimeet\Application\ThirdParty\Openl10n;

use Proximum\Vimeet\Application\Command\Command;

class Pull implements Command
{
    /** @var string */
    public $emailToNotify;

    /** @var string */
    public $locale;

    public function __construct(string $emailToNotify, string $locale)
    {
        $this->emailToNotify = $emailToNotify;
        $this->locale = $locale;
    }
}
