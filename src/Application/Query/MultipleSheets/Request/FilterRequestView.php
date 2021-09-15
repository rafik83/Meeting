<?php

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class FilterRequestView
{
    const NO_PREFERENCE = 'no_preference';

    /** @var Sheet|null */
    public $otherSheet;

    /** @var string|null */
    public $type;

    /** @var string|null */
    public $state;

    /** @var Sheet|null */
    public $sheetConcerned;

    /** @var User|string|null */
    public $user;
}
