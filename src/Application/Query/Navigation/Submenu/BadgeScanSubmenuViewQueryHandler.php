<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Sheet\Product\CanScanParticipant;

class BadgeScanSubmenuViewQueryHandler
{
    /** @var RouterInterface */
    private $router;

    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    /** @var Merger */
    private $merger;

    /** @var CanScanParticipant */
    private $canScanParticipant;

    public function __construct(
        RouterInterface $router,
        EventOpenAccessChecker $eventOpenAccessChecker,
        Merger $merger,
        CanScanParticipant $canScanParticipant
    ) {
        $this->router = $router;
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
        $this->merger = $merger;
        $this->canScanParticipant = $canScanParticipant;
    }

    public function handle(BadgeScanSubmenuViewQuery $query): ?SubmenuButtonView
    {
        $options = [];
        $order = $this->merger->getMergedOrders($query->sheet);
        if(null !== $order) {
            $options = $order->getOptions();
        }

        $hasScanOption = false;
        foreach ($options as $option) {
            if($this->canScanParticipant->isSatisfiedBy($option)) {
                $hasScanOption = true;
            }
        }

        if (!$query->sheet->getType()->canScanParticipant() || !$hasScanOption || !$this->eventOpenAccessChecker->allowedToAccess($query->event)) {
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
            false,
            true
        );
    }
}
