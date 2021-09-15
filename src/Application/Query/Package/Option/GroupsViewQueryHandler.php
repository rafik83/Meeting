<?php

namespace Proximum\Vimeet\Application\Query\Package\Option;

use Proximum\Vimeet\Application\View\Package\GroupsView;
use Proximum\Vimeet\Domain\Model\PackageGroup;

class GroupsViewQueryHandler
{
    /**
     * @var GroupViewQueryHandler
     */
    private $groupViewQueryHandler;

    /**
     * @param GroupViewQueryHandler $groupViewQueryHandler
     */
    public function __construct(GroupViewQueryHandler $groupViewQueryHandler)
    {
        $this->groupViewQueryHandler = $groupViewQueryHandler;
    }

    /**
     * @param GroupsViewQuery $groupsViewQuery
     *
     * @return GroupsView
     */
    public function handle(GroupsViewQuery $groupsViewQuery)
    {
        $groupViewQueryHandler = $this->groupViewQueryHandler;

        return new GroupsView(array_map(function (PackageGroup $group) use ($groupViewQueryHandler, $groupsViewQuery) {
            return $groupViewQueryHandler->handle(
                new GroupViewQuery(
                    $groupsViewQuery->sheet,
                    $group,
                    $groupsViewQuery->locale
                )
            );
        }, $groupsViewQuery->sheet->getType()->getPackage()->getGroups()));
    }
}
