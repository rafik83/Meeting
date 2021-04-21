<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;


use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;

class CustomLinkSubmenuViewQuery implements Query
{
    public Sheet $sheet;

    public string $locale;

    public function __construct(Sheet $sheet, string $locale)
    {
        $this->sheet = $sheet;
        $this->locale = $locale;
    }
}
