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
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;

class FormsViewQueryHandler
{
    /** @var FormTemplateRepositoryInterface */
    private $formTemplateRepository;

    public function __construct(FormTemplateRepositoryInterface $formTemplateRepository)
    {
        $this->formTemplateRepository = $formTemplateRepository;
    }

    public function handle(FormsViewQuery $formsViewQuery): ?CategoryView
    {
        $type = $formsViewQuery->sheet->getType();
        $formTemplateViews = $this->formTemplateRepository->getFormTemplateViewByType($type, $formsViewQuery->locale);

        if (empty($formTemplateViews)) {
            return null;
        }

        $linksView = [];

        foreach ($formTemplateViews as $formTemplateView) {
            $linksView[] = new LinkView($formTemplateView->title, '/url');
        }

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
