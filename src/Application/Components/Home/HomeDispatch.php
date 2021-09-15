<?php

namespace Proximum\Vimeet\Application\Components\Home;

use Proximum\Vimeet\Application\View\Home\HomeDispatchView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class HomeDispatch
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, GroupRepositoryInterface $groupRepository)
    {
        $this->sheetRepository = $sheetRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return null|HomeDispatchView
     */
    public function handle(Event $event, User $user): ?HomeDispatchView
    {
        // User is a manager of Sheets group
        $group = $this->groupRepository->getByEventAndManager($event, $user);

        if (null !== $group) {
            return new HomeDispatchView(HomeDispatchView::TYPE_GROUP, $group);
        }

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        // User has multiple sheets
        if (count($sheets) > 1) {
            return new HomeDispatchView(HomeDispatchView::TYPE_MULTIPLE_SHEETS);
        }

        /** @var Sheet $sheet */
        $sheet = reset($sheets);

        // User has a sheet
        if (false !== $sheet) {
            return new HomeDispatchView(HomeDispatchView::TYPE_ONE_SHEET, $sheet);
        }

        return null;
    }
}
