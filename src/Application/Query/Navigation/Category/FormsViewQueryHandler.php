<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Model\StaticFormulation;

class FormsViewQueryHandler
{
    public function __construct()
    {
    }

    public function handle(FormsViewQuery $formsViewQuery)
    {
        $linksView = [];

        $linksView[] = new LinkView('Mon formulaire', '/url');

        return new CategoryView(
            $this->getTitle($formsViewQuery->staticFormulation, $formsViewQuery->locale),
            Category::FORMS_ICON,
            $linksView,
            true
        );
    }

    private function getTitle(?StaticFormulation $staticFormulation, string $locale): string
    {
        if (null !== $staticFormulation) {
            return $staticFormulation->getTitle($locale);
        }

        return Category::FORMS;
    }
}
