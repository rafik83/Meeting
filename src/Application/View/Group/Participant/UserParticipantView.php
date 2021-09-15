<?php

namespace Proximum\Vimeet\Application\View\Group\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;

class UserParticipantView
{
    /** @var int */
    public $userId;

    /** @var string */
    public $email;

    /** @var string */
    public $fullname;

    /** @var Sheet[] */
    public $sheets;

    /**
     * @param int     $userId
     * @param string  $email
     * @param string  $fullname
     * @param Sheet[] $sheets
     */
    public function __construct($userId, $email, $fullname, array $sheets)
    {
        $this->userId   = $userId;
        $this->email    = $email;
        $this->fullname = $fullname;
        $this->sheets   = $sheets;
    }

    /**
     * @param Sheet $sheet
     */
    public function addSheet(Sheet $sheet)
    {
        $this->sheets[] = $sheet;
    }
}
