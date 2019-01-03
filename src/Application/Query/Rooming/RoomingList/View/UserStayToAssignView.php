<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

class UserStayToAssignView extends AbstractUserStayView
{
    public function isAssigned(): bool
    {
        return false;
    }
}
