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
use Proximum\Vimeet\Domain\Scan\CanScanParticipant;

class ContactsSubmenuViewQueryHandler
{
    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    /** @var CanScanParticipant */
    private $canScanParticipant;

    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        EventOpenAccessChecker $eventOpenAccessChecker,
        CanScanParticipant $canScanParticipant
    ) {
        $this->navigationBuilder = $navigationBuilder;
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
        $this->canScanParticipant = $canScanParticipant;
    }

    public function handle(ContactsSubmenuViewQuery $query): ?SubmenuButtonView
    {
        if (!$this->eventOpenAccessChecker->allowedToAccess($query->event)) {
            return null;
        }

        if ($query->sheet->isInInternalCatalog() !== true && $this->canScanParticipant->isSatisfiedBy($query->sheet) !== true) {
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
            null,
            true
        );
    }
}
