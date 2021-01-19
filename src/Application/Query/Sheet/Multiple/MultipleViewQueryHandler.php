<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Multiple;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Sheet\Multiple\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class MultipleViewQueryHandler
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
     * @param MultipleViewQuery $multipleViewQuery
     *
     * @return SheetView[]
     */
    public function handle(MultipleViewQuery $multipleViewQuery)
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($multipleViewQuery->user, $multipleViewQuery->event);

        $sheetViews = array_map(function (Sheet $sheet) {
            return new SheetView($sheet->getId(), $this->sheetInfoGuesser->guessSheetTitle($sheet));
        }, $sheets);

        usort($sheetViews, function (SheetView $one, SheetView $other) {
            return strcasecmp($one->title, $other->title);
        });

        return $sheetViews;
    }
}
