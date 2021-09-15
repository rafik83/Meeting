<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Indicator;

use Proximum\Vimeet\Application\View\Agenda\Admin\Indicator\SheetIndicatorsView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetIndicatorsLazyLoadViewQueryHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepositoryInterface;

    /**
     * @var SheetIndicatorsViewQueryHandler
     */
    private $sheetIndicatorsViewQueryHandler;

    /**
     * @param SheetRepositoryInterface        $sheetRepositoryInterface
     * @param SheetIndicatorsViewQueryHandler $sheetIndicatorsViewQueryHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepositoryInterface,
        SheetIndicatorsViewQueryHandler $sheetIndicatorsViewQueryHandler
    ) {
        $this->sheetRepositoryInterface        = $sheetRepositoryInterface;
        $this->sheetIndicatorsViewQueryHandler = $sheetIndicatorsViewQueryHandler;
    }

    /**
     * @param SheetIndicatorsLazyLoadViewQuery $query
     *
     * @return SheetIndicatorsView[]
     */
    public function handle(SheetIndicatorsLazyLoadViewQuery $query)
    {
        $indicators = [];
        $sheets     = $this->sheetRepositoryInterface->findByIds($query->sheets);

        foreach ($sheets as $sheet) {
            // Avoid returning sheets from other event
            if ($sheet->getEvent() === $query->event) {
                $indicators[$sheet->getId()] = $this->sheetIndicatorsViewQueryHandler->handle(new SheetIndicatorsViewQuery($sheet));
            }
        }

        return $indicators;
    }
}
