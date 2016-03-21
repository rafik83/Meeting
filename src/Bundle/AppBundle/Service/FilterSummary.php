<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Service;

use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface as SymfonyTranslatorInterface;

class FilterSummary
{
    /**
     * @var SymfonyTranslatorInterface
     */
    private $translator;

    /**
     * @param SymfonyTranslatorInterface $translator
     */
    public function __construct(SymfonyTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param FormView $formView
     * @param array    $filters
     * @param string   $locale
     *
     * @return array
     */
    public function getFilters(FormView $formView, array $filters, $locale)
    {
        $selectedFilters = [];

        foreach ($filters as $filter => $value) {
            if ($value === null) {
                continue;
            }

            if (!isset($formView->children[$filter])) {
                continue;
            }

            $field = $formView->children[$filter];
            $value = $field->vars['value'];

            if (isset($field->vars['choices'])) {
                foreach ($field->vars['choices'] as $choice) {
                    if ($choice->value === $value) {
                        $value = $this->translator->trans($choice->label, [], null, $locale);
                    }
                }
            }

            $label = $this->translator->trans($field->vars['label'], [], $field->vars['translation_domain'], $locale);

            $selectedFilters[$label] = $value;
        }

        return $selectedFilters;
    }
}
