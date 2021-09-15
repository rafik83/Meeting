<?php

namespace Proximum\Vimeet\Application\Query\Badge;

class UserBadgeAndPlanningByEventView
{
    /** @var UserBadgeByEventView */
    public $userBadgeByEventView;

    /** @var string */
    public $planning;

    public function __construct(UserBadgeByEventView $userBadgeByEventView, string $planning)
    {
        $this->userBadgeByEventView = $userBadgeByEventView;
        $this->planning = $planning;
    }
}
