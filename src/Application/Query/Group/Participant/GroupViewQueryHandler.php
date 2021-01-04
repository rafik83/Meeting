<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Application\View\Sheet\Group\Participant\GroupView;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GroupViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetsViewQueryHandler $sheetsViewQueryHandler */
    private $sheetsViewQueryHandler;

    /** @var DayRepositoryInterface */
    private $dayRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetsViewQueryHandler   $sheetsViewQueryHandler
     * @param DayRepositoryInterface   $dayRepository
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetsViewQueryHandler $sheetsViewQueryHandler,
        DayRepositoryInterface $dayRepository
    ) {
        $this->sheetRepository        = $sheetRepository;
        $this->sheetsViewQueryHandler = $sheetsViewQueryHandler;
        $this->dayRepository = $dayRepository;
    }

    /**
     * @param GroupViewQuery $groupViewQuery
     *
     * @return GroupView
     */
    public function handle(GroupViewQuery $groupViewQuery)
    {
        $sheets     = $this->sheetRepository->getByGroup($groupViewQuery->group);
        $eventDays  = $this->dayRepository->findByEvent($groupViewQuery->event);
        $sheetViews = $this->sheetsViewQueryHandler->handle(new SheetsViewQuery($sheets, $eventDays));

        return new GroupView(
            $groupViewQuery->group->getId(),
            $groupViewQuery->group->getTitle(),
            $sheetViews,
            $eventDays
        );
    }
}
