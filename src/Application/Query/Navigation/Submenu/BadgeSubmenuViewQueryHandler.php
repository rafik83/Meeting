<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Badge\AvailableChecker;

class BadgeSubmenuViewQueryHandler
{
    /** @var AvailableChecker */
    private $availableChecker;

    /** @var RouterInterface */
    private $router;

    public function __construct(AvailableChecker $availableChecker, RouterInterface $router)
    {
        $this->availableChecker = $availableChecker;
        $this->router = $router;
    }

    public function handle(BadgeSubmenuViewQuery $query): ?SubmenuButtonView
    {
        if (!$this->availableChecker->isSatisfiedBy($query->sheet)) {
            return null;
        }

        $badgeTitle = Category::BADGE;

        if (null !== $query->staticFormulation) {
            $badgeTitle = $query->staticFormulation->getTitle($query->locale);
        }

        return new SubmenuButtonView(
            Category::BADGE_ICON,
            $badgeTitle,
            $this->router->generate(
                'event_sheet_user_badge',
                [
                    'sheet' => $query->sheet->getId(),
                    'user' => $query->user->getId(),
                ]
            ),
            Route::isBadge($query->route),
            null,
            true
        );
    }
}
