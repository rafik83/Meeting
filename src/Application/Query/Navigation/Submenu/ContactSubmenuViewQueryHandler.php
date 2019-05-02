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

class ContactSubmenuViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * ContactSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder
    ) {
        $this->navigationBuilder = $navigationBuilder;
    }

    /**
     * @param ContactSubmenuViewQuery $query
     *
     * @return SubmenuButtonView[]
     */
    public function handle(ContactSubmenuViewQuery $query)
    {
        $buttonViews = [];

        $contactTitle = 'navigation.category.contact';

        if (isset($query->staticFormulationsIndexedByCategory[Category::CONTACT])) {
            $contactTitle = $query->staticFormulationsIndexedByCategory[Category::CONTACT]->getTitle($query->locale);
        }

        $buttonViews[] = new SubmenuButtonView(
            Category::CONTACT_ICON,
            $contactTitle,
            $this->navigationBuilder->getRoute(
                'event_contact_index',
                [
                    'sheet' => $query->sheet->getId(),
                ]
            ),
            Route::isContact($query->route),
            false,
            true
        );

        return $buttonViews;
    }
}
