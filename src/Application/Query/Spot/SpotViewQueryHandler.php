<?php

namespace Proximum\Vimeet\Application\Query\Spot;

use Proximum\Vimeet\Application\View\Spot\SpotView;

class SpotViewQueryHandler
{
    /**
     * @var SheetViewQueryHandler
     */
    private $sheetViewQueryHandler;

    /**
     * @param SheetViewQueryHandler $sheetViewQueryHandler
     */
    public function __construct(SheetViewQueryHandler $sheetViewQueryHandler)
    {
        $this->sheetViewQueryHandler = $sheetViewQueryHandler;
    }

    /**
     * @param SpotViewQuery $query
     *
     * @return SpotView
     */
    public function handle(SpotViewQuery $query)
    {
        $view = new SpotView(
            $query->spot->getId(),
            $query->spot->getReference(),
            $query->spot->getSize(),
            $query->spot->getMeetingCapacity(),
            $query->spot->getSeatCapacity(),
            $query->spot->isActive(),
            $query->spot->hasUnavailability(),
            $query->spot->getPriority(),
            $query->spot->isVisio()
        );

        foreach ($query->spot->getSheets() as $sheet) {
            $view->addSheet($this->sheetViewQueryHandler->handle(new SheetViewQuery($sheet, $query->locale)));
        }

        return $view;
    }
}
