<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Group;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Sheet\Group\GroupView;
use Proximum\Vimeet\Application\View\Sheet\Group\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GroupViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetInfoGuesser         $sheetInfoGuesser
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, SheetInfoGuesser $sheetInfoGuesser)
    {
        $this->sheetRepository = $sheetRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param GroupViewQuery $groupViewQuery
     *
     * @return GroupView
     */
    public function handle(GroupViewQuery $groupViewQuery)
    {
        $sheets = $this->sheetRepository->getByGroup($groupViewQuery->group);

        $sheetViews = array_map(function (Sheet $sheet) {
            return new SheetView($sheet->getId(), $this->sheetInfoGuesser->guessSheetTitle($sheet));
        }, $sheets);

        usort($sheetViews, function (SheetView $one, SheetView $other) {
            return strcasecmp($one->title, $other->title);
        });

        return new GroupView($groupViewQuery->group->getId(), $groupViewQuery->group->getTitle(), $sheetViews);
    }
}
