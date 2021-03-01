<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\View\Happening\Admin\HappeningDayView;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningListViewQueryHandler
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var HappeningViewQueryHandler */
    private $happeningHandler;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        HappeningViewQueryHandler $happeningHandler
    ) {
        $this->happeningRepository = $happeningRepository;
        $this->happeningHandler = $happeningHandler;
    }

    public function handle(HappeningListViewQuery $query): array
    {
        $list = $this->happeningRepository->findListByEvent($query->event, $query->locale);

        $happeningDaysView = [];
        $dayIndex = -1;
        $day = null;

        foreach ($list as $happening) {            
            if ($day !== $happening->getBegin()->format("d/m/Y")) {
                $dayIndex++;
                $happeningDaysView[$dayIndex] = new HappeningDayView($happening->getBegin(), []);
            }
            $happeningDaysView[$dayIndex]->happeningListView[] = $this->happeningHandler->handle(new HappeningViewQuery($happening, $query->locale));
            
            $day = $happening->getBegin()->format("d/m/Y");
        }

        
        return $happeningDaysView;
    }
}
