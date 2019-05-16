<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Sheet\Product\CanScanParticipant;

class ContactsSubmenuViewQueryHandler
{
    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    /** @var Merger */
    private $merger;

    /** @var CanScanParticipant */
    private $canScanParticipant;

    /**
     * @param NavigationBuilderInterface $navigationBuilder
     * @param EventOpenAccessChecker     $eventOpenAccessChecker
     * @param Merger                     $merger
     * @param CanScanParticipant         $canScanParticipant
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        EventOpenAccessChecker $eventOpenAccessChecker,
        Merger $merger,
        CanScanParticipant $canScanParticipant
    ) {
        $this->navigationBuilder = $navigationBuilder;
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
        $this->merger = $merger;
        $this->canScanParticipant = $canScanParticipant;
    }

    public function handle(ContactsSubmenuViewQuery $query): ?SubmenuButtonView
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

        $contactTitle = 'navigation.category.contact';

        if (isset($query->staticFormulationsIndexedByCategory[Category::CONTACT_LIST])) {
            $contactTitle = $query->staticFormulationsIndexedByCategory[Category::CONTACT_LIST]->getTitle($query->locale);
        }

        return new SubmenuButtonView(
            Category::CONTACT_LIST_ICON,
            $contactTitle,
            $this->navigationBuilder->getRoute(
                'event_contact_index',
                [
                    'sheet' => $query->sheet->getId(),
                ]
            ),
            Route::isContactList($query->route),
            false,
            true
        );
    }
}
