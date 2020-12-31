<?php

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\User;

class CategoryViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var string */
    public $categoryType;

    /** @var string */
    public $locale;

    /** @var null|StaticFormulation */
    public $staticFormulation;

    /**
     * @param Sheet                  $sheet
     * @param User                   $user
     * @param string                 $categoryType
     * @param string                 $locale
     * @param null|StaticFormulation $staticFormulation
     */
    public function __construct(
        Sheet $sheet,
        User $user,
        $categoryType,
        $locale,
        ?StaticFormulation $staticFormulation = null
    ) {
        $this->sheet = $sheet;
        $this->categoryType = $categoryType;
        $this->user = $user;
        $this->locale = $locale;
        $this->staticFormulation = $staticFormulation;
    }
}
