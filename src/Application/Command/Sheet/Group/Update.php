<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Group;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class Update implements Command
{
    /** @var Group */
    public $group;

    /** @var string */
    public $email;

    /** @var string */
    public $title;

    /** @var bool */
    public $forceSheetTitle;

    /**
     * @param Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
        $this->email = $group->getManager()->getEmail();
        $this->title = $group->getTitle();
        $this->forceSheetTitle = $group->hasSheetTitleForced();
    }
}
