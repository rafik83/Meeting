<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Group\Admin;

use Proximum\Vimeet\Application\View\Sheet\Group\Admin\GroupView;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;

class GroupListViewQueryHandler
{
    /** @var AdminGroupViewQueryHandler */
    private $groupViewQueryHandler;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /**
     * GroupListViewQueryHandler constructor.
     *
     * @param AdminGroupViewQueryHandler $groupViewQueryHandler
     * @param GroupRepositoryInterface   $groupRepository
     */
    public function __construct(
        AdminGroupViewQueryHandler $groupViewQueryHandler,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->groupViewQueryHandler = $groupViewQueryHandler;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @return GroupView[]
     */
    public function handle(GroupListViewQuery $groupListViewQuery): array
    {
        $groupViews = [];
        $sheetsGroups = $this->groupRepository->getAllByEventOrderedByTitle($groupListViewQuery->event);

        if (!empty($sheetsGroups)) {
            foreach ($sheetsGroups as $group) {
                $groupViews[] = $this->groupViewQueryHandler->handle(new AdminGroupViewQuery(
                    $group,
                    $groupListViewQuery->admin
                ));
            }
        }

        return $groupViews;
    }
}
