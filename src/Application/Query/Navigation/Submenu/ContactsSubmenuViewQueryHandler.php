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
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class ContactsSubmenuViewQueryHandler
{
    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /**
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder
    ) {
        $this->navigationBuilder = $navigationBuilder;
    }

    public function handle(ContactsSubmenuViewQuery $query): ?SubmenuButtonView
    {
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
