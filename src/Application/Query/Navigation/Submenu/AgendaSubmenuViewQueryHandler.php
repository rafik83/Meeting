<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class AgendaSubmenuViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * @var HappeningsAccessChecker
     */
    private $happeningsAccessChecker;

    /**
     * @var AgendaAccessChecker
     */
    private $agendaAccessChecker;

    /**
     * CatalogSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     * @param HappeningsAccessChecker    $happeningsAccessChecker
     * @param AgendaAccessChecker        $agendaAccessChecker
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        HappeningsAccessChecker $happeningsAccessChecker,
        AgendaAccessChecker $agendaAccessChecker
    ) {
        $this->navigationBuilder       = $navigationBuilder;
        $this->happeningsAccessChecker = $happeningsAccessChecker;
        $this->agendaAccessChecker     = $agendaAccessChecker;
    }

    /**
     * @param AgendaSubmenuViewQuery $query
     *
     * @return SubmenuButtonView[]
     */
    public function handle(AgendaSubmenuViewQuery $query)
    {
        $buttonViews = [];

        if ($this->agendaAccessChecker->allowedToAccess($query->event)) {
            $agendaTitle = 'agenda.title';

            if (isset($query->staticFormulationsIndexedByCategory[Category::PLANNING])) {
                $agendaTitle = $query->staticFormulationsIndexedByCategory[Category::PLANNING]->getTitle($query->locale);
            }

            $buttonViews[] = new SubmenuButtonView(
                Category::AGENDA_ICON,
                $agendaTitle,
                $this->navigationBuilder->getRoute('event_agenda', ['sheet' => $query->sheet->getId()]),
                Route::isAgenda($query->route),
                null,
                true
            );
        }

        if ($this->happeningsAccessChecker->allowedToAccess($query->event)) {
            $programTitle = 'program.title';

            if (isset($query->staticFormulationsIndexedByCategory[Category::PROGRAM])) {
                $programTitle = $query->staticFormulationsIndexedByCategory[Category::PROGRAM]->getTitle($query->locale);
            }

            $buttonViews[] = new SubmenuButtonView(
                Category::PROGRAM_ICON,
                $programTitle,
                $this->navigationBuilder->getRoute('happening_program', ['sheet' => $query->sheet->getId()]),
                Route::isProgram($query->route),
                null,
                false
            );
        }

        return $buttonViews;
    }
}
