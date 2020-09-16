<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class AddClick implements Command
{
    /** @var User */
    public $user;

    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $objectId;

    /** @var int|null for collections only, index of link clicked */
    public $index;

    public function __construct(User $user, Sheet $sheet, string $objectId, ?int $index)
    {
        $this->user  = $user;
        $this->sheet = $sheet;
        $this->objectId = $objectId;
        $this->index = $index;
    }
}
