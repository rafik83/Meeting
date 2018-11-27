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
        $templates = $this->formTemplateRepository->findByEvent($query->event);

        $formTemplateViews = [];
        foreach ($templates as $template) {
            $translatedTitles = [];
            foreach ($query->event->getLocales() as $locale) {
                $translatedTitles[$locale] = $template->getLocalizedTitle($locale);
            }

            $formTemplateViews[] = new FormTemplateView(
                $template->getId(),
                $template->getTitle(),
                $template->getFallback(),
                $template->isPublished(),
                $translatedTitles,
                $template->getCreatedAt()
            );
        }

        return new FormTemplateListView($formTemplateViews);
    }
}
