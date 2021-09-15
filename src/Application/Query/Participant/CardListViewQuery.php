<?php

namespace Proximum\Vimeet\Application\Query\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Application\Query\Query;

class CardListViewQuery implements Query
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var User
     */
    public $user;

    /**
     * @var bool
     */
    public $editable;

    public bool $showMeetOnline;

    /**
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     * @param bool   $editable
     */
    public function __construct(Sheet $sheet, User $user, $locale, $editable = true, bool $showMeetOnline = false)
    {
        $this->sheet    = $sheet;
        $this->user     = $user;
        $this->locale   = $locale;
        $this->editable = $editable;
        $this->showMeetOnline = $showMeetOnline;
    }
}
