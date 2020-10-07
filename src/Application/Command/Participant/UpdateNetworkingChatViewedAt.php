<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class UpdateNetworkingChatViewedAt
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /**
     * @param Sheet    $sheet
     * @param User     $user
     */
    public function __construct(
        Sheet $sheet,
        User $user
    ) {
        $this->sheet = $sheet;
        $this->user = $user;
    }
}
