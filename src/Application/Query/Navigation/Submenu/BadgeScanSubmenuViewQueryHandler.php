<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Scan\CanScanParticipant;

class BadgeScanSubmenuViewQueryHandler
{
    /** @var RouterInterface */
    private $router;

    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    /** @var CanScanParticipant */
    private $canScanParticipant;

    public function __construct(
        RouterInterface $router,
        EventOpenAccessChecker $eventOpenAccessChecker,
        CanScanParticipant $canScanParticipant
    ) {
        $this->router = $router;
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
        $this->canScanParticipant = $canScanParticipant;
    }

    public function handle(BadgeScanSubmenuViewQuery $query): ?SubmenuButtonView
    {
        if (!$this->eventOpenAccessChecker->allowedToAccess($query->event)) {
            return null;
        }

        if (!$this->canScanParticipant->isSatisfiedBy($query->sheet)) {
            return null;
        }

        $badgeScanTitle = Category::BADGE_SCAN;

        if (null !== $query->staticFormulation) {
            $badgeScanTitle = $query->staticFormulation->getTitle($query->locale);
        }

        return new SubmenuButtonView(
            Category::BADGE_SCAN_ICON,
            $badgeScanTitle,
            $this->router->generate(
                'event_sheet_user_badge_scan',
                [
                    'sheet' => $query->sheet->getId(),
                ]
            ),
            Route::isBadgeScan($query->route),
            null,
            true
        );
    }
}
