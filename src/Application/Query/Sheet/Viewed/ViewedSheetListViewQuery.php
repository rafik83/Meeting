<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Viewed;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ViewedSheetListViewQuery
{
    /** @var User */
    public $user;

    /** @var Sheet[] */
    public $sheets;

    /**
     * @param User    $user
     * @param Sheet[] $sheets
     */
    public function __construct(User $user, array $sheets)
    {
        $this->user   = $user;
        $this->sheets = $sheets;
    }
}
