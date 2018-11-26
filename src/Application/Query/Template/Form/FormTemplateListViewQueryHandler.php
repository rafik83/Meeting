<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Template\Form;

use Proximum\Vimeet\Application\View\Template\Form\FormTemplateListView;
use Proximum\Vimeet\Application\View\Template\Form\FormTemplateView;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;

class FormTemplateListViewQueryHandler
{
    /** @var FormTemplateRepositoryInterface */
    private $formTemplateRepository;

    public function __construct(FormTemplateRepositoryInterface $formTemplateRepository)
    {
        $this->formTemplateRepository = $formTemplateRepository;
    }

    public function handle(FormTemplateListViewQuery $query): FormTemplateListView
    {
        $formTemplates = $this->formTemplateRepository->findByEvent($query->event);
        $formTemplateViews = [];

        foreach ($formTemplates as $formTemplate) {
            $translatedTitles = [];
            foreach ($query->event->getLocales() as $locale) {
                $translatedTitles[$locale] = $formTemplate->getLocalizedTitle($locale);
            }

            $formTemplateViews[] = new FormTemplateView(
                $formTemplate->getId(),
                $formTemplate->getTitle(),
                $formTemplate->isPublished(),
                $translatedTitles,
                $formTemplate->getTypes(),
                $formTemplate->getFallback(),
                $formTemplate->getCreatedAt()
            );
        }

        return new FormTemplateListView($formTemplateViews);
    }
}
