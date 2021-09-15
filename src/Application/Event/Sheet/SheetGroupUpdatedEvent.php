<?php

namespace Proximum\Vimeet\Application\Event\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Group;

class SheetGroupUpdatedEvent extends AbstractGroupEvent
{
    /**
     * @var bool
     */
    private $isManagerChanged;

    /**
     * SheetGroupCreatedEvent constructor.
     *
     * @param Group $group
     * @param bool  $isManagerChanged
     */
    public function __construct(Group $group, $isManagerChanged)
    {
        parent::__construct($group);

        $this->isManagerChanged = $isManagerChanged;
    }

    /**
     * @return bool
     */
    public function isManagerChanged()
    {
        return $this->isManagerChanged;
    }
}
