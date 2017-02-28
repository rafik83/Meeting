<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Proximum\Vimeet\Domain\Model\Sheet\Constant;
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
            if (null === $value) {
                continue;
            }

            if (is_array($value) && empty($value)) {
                continue;
            }

            if (!isset($formView->children[$filter])) {
                continue;
            }

            /** @var FormView $field */
            $field = $formView->children[$filter];

            // If field has nested form type inside of type BooleanFilterType
            if ($field->vars['name'] === Constant::BOOLEAN_FILTER) {
                foreach ($field->children as $childrenRow) {
                    try {
                        list($label, $value) = $this->handleFormRow($childrenRow, $locale);
                        $selectedFilters[$label] = $value;
                    } catch (\Exception $exception) {
                        continue;
                    }
                }
            } else {
                try {
                    list($label, $value) = $this->handleFormRow($field, $locale);
                    $selectedFilters[$label] = $value;
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }

        return $selectedFilters;
    }

    /**
     * @param FormView $field
     * @param string   $locale
     *
     * @return array
     * @throws \Exception
     */
    private function handleFormRow(FormView $field, $locale)
    {
        $isCheckbox = isset($field->vars['checked']);

        // Ignore unchecked checkboxes:
        if ($isCheckbox && false === $field->vars['checked']) {
            throw new \Exception();
        }

        $value = $field->vars['value'];

        if (empty($value)) {
            throw new \InvalidArgumentException('Missing value');
        }

        if (isset($field->vars['choices'])) {
            $values = (array) $value;
            $value  = '';
            foreach ($field->vars['choices'] as $choice) {
                foreach ($values as $currentValue) {
                    if ($choice->value === $currentValue) {
                        $value .= ($value !== '' ? ', ' : '') . $this->translator->trans($choice->label, [], null, $locale);
                    }
                }
            }
        }

        if ($isCheckbox) {
            $value = $this->translator->trans('boolean.yes');
        }

        $label = $this->translator->trans($field->vars['label'], [], $field->vars['translation_domain'], $locale);

        return [$label, $value];
    }
}
