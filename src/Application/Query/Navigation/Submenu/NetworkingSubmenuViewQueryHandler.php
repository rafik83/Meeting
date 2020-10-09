<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;


use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class NetworkingSubmenuViewQueryHandler
{
    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /** @var NetworkingAccessChecker */
    private $networkingAccessChecker;

    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        NetworkingAccessChecker $networkingAccessChecker
    )
    {
        $this->navigationBuilder = $navigationBuilder;
        $this->networkingAccessChecker = $networkingAccessChecker;
    }

    public function handle(NetworkingSubmenuViewQuery $query): ?SubmenuButtonView
    {

        if ($this->networkingAccessChecker->allowedToAccess($query->event)) {
            $networkingTitle = 'navigation.category.networking';

            if (isset($query->staticFormulationsIndexedByCategory[Category::NETWORKING])) {
                $networkingTitle = $query->staticFormulationsIndexedByCategory[Category::NETWORKING]->getTitle($query->locale);
            }

            return new SubmenuButtonView(
                Category::NETWORKING_ICON,
                $networkingTitle,
                $this->navigationBuilder->getRoute('event_networking_index', ['sheet' => $query->sheet->getId()]),
                Route::isNetworking($query->route),
                null,
                true
            );
        }
        return null;
    }
}
