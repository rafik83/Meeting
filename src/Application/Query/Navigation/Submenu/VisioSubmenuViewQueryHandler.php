<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class VisioSubmenuViewQueryHandler
{
    /** @var NavigationBuilderInterface */
    private $navigationBuilder;
    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        \DateTimeInterface $dateTime
    ) {
        $this->navigationBuilder = $navigationBuilder;
        $this->dateTime = $dateTime;
    }

    public function handle(VisioSubmenuViewQuery $query): ?SubmenuButtonView
    {
        $enableDate = $query->event->getConfiguration()->getEnableVisioTestMenuButtonDate();

        if (null === $enableDate || $enableDate > $this->dateTime) {
            return null;
        }

        $subMenuTitle = Category::VISIO;

        if (null !== $query->staticFormulation) {
            $subMenuTitle = $query->staticFormulation->getTitle($query->locale);
        }

        return new SubmenuButtonView(
            Category::VISIO_ICON,
            $subMenuTitle,
            $this->navigationBuilder->getRoute(
                Route::VISIO_TEST_SHEET_CREATE_TEST,
                ['sheet' => $query->sheet->getId()]
            ),
            Route::isVisioTestConfigurationWithSheetContext($query->route),
            null,
            true
        );
    }
}
