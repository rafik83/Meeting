<?php

namespace Proximum\Vimeet\Application\Query\Package\Option;

use Proximum\Vimeet\Domain\Model\PackageGroup;
use Proximum\Vimeet\Domain\Model\Sheet;

class GroupViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var PackageGroup
     */
    public $group;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Sheet        $sheet
     * @param PackageGroup $group
     * @param string       $locale
     */
    public function __construct(Sheet $sheet, PackageGroup $group, $locale)
    {
        $this->sheet  = $sheet;
        $this->group  = $group;
        $this->locale = $locale;
    }
}
