<?php

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Participate
{
    /** @var Happening */
    public $happening;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $createdBy;

    /** @var Participant[] */
    public $participants;

    /** @var null|string */
    public $question;

    /** @var null|string */
    public $invitationCode;

    /** @var bool */
    public $isUpdate;

    /**
     * @param Happening   $happening
     * @param Sheet       $sheet
     * @param User        $createdBy
     * @param array       $participants
     * @param null|string $question
     * @param null|string $invitationCode
     * @param bool        $isUpdate
     */
    public function __construct(
        Happening $happening,
        Sheet $sheet,
        User $createdBy,
        array $participants,
        ?string $question = null,
        ?string $invitationCode = null,
        bool $isUpdate = false
    ) {
        $this->happening      = $happening;
        $this->sheet          = $sheet;
        $this->createdBy      = $createdBy;
        $this->participants   = $participants;
        $this->question       = $question;
        $this->invitationCode = $invitationCode;
        $this->isUpdate       = $isUpdate;
    }
}
