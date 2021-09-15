<?php

namespace Proximum\Vimeet\Application\ThirdParty\Openl10n;

use Proximum\Vimeet\Application\Command\Command;

class Push implements Command
{
    /** @var string[] */
    public $locales = [];

    public function __construct(array $locales = [])
    {
        $this->locales = $locales;

        if (empty($locales)) {
            $this->locales = ['fr']; // The reference locale of translations file is "fr"
        }
    }
}
