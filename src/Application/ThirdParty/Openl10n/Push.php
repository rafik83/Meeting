<?php

namespace Proximum\Vimeet\Application\ThirdParty\Openl10n;

use Proximum\Vimeet\Application\Command\Command;

class Push implements Command
{
    /** @var string[] */
    public $locale;

    public function __construct(array $locale)
    {
        $this->locale = $locale;
    }
}
