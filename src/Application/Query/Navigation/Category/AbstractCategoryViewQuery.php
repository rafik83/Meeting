<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\User;

abstract class AbstractCategoryViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var string */
    public $locale;

    /** @var null|StaticFormulation */
    public $staticFormulation;

    /**
     * @param Sheet                  $sheet
     * @param User                   $user
     * @param string                 $locale
     * @param null|StaticFormulation $staticFormulation
     */
    public function __construct(
        Sheet $sheet,
        User $user,
        $locale,
        ?StaticFormulation $staticFormulation = null
    ) {
        $this->sheet = $sheet;
        $this->user = $user;
        $this->locale = $locale;
        $this->staticFormulation = $staticFormulation;
    }
}
