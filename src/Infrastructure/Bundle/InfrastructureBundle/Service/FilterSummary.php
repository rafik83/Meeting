<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Sheet\FilledFilter;
use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface as SymfonyTranslatorInterface;

class FilterSummary
{
    /**
     * @var SymfonyTranslatorInterface
     */
    private $translator;

    /** @var \IntlDateFormatter|null */
    private $dateFormatter;

    /**
     * @param SymfonyTranslatorInterface $translator
     */
    public function __construct(SymfonyTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param FormView   $formView
     * @param array      $filters
     * @param null|Event $event
     * @param string     $locale
     *
     * @return array
     */
    public function getFilters(FormView $formView, array $filters, ?Event $event, $locale): array
    {
        $selectedFilters = [];

        foreach ($filters as $filter => $value) {
            if (null === $value) {
                continue;
            }

            if (\is_array($value) && empty($value)) {
                continue;
            }

            if (!isset($formView->children[$filter])) {
                continue;
            }

            /** @var FormView $field */
            $field = $formView->children[$filter];

            // If field has nested form type inside of type BooleanFilterType
            if (Constant::BOOLEAN_FILTER === $field->vars['name']) {
                foreach ($field->children as $childrenRow) {
                    try {
                        list($label, $value) = $this->handleFormRow($childrenRow, $event, $locale);
                        $selectedFilters[$label] = $value;
                    } catch (\Exception $exception) {
                        continue;
                    }
                }
            } elseif (Constant::FILLED_FILTER === $field->vars['name']) {
                foreach ($field->children as $childrenRow) {
                    try {
                        list($label, $value) = $this->handleFormRow($childrenRow, $event, $locale);

                        if (empty($value)) {
                            continue;
                        }

                        $selectedFilters[$label] = $value;
                    } catch (\Exception $exception) {
                        continue;
                    }
                }
            } else {
                try {
                    list($label, $value) = $this->handleFormRow($field, $event, $locale);

                    // In case of empty data, we do not show the selected filter in the summary
                    if (empty($value)) {
                        continue;
                    }

                    $selectedFilters[$label] = $value;
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }

        return $selectedFilters;
    }

    /**
     * @param FormView   $field
     * @param null|Event $event
     * @param string     $locale
     *
     * @throws \Exception
     *
     * @return array
     */
    private function handleFormRow(FormView $field, ?Event $event, $locale): array
    {
        $isCheckbox = isset($field->vars['checked']);

        // Ignore unchecked checkboxes:
        if ($isCheckbox && false === $field->vars['checked']) {
            throw new \Exception();
        }

        $value = $field->vars['value'];

        if ('' === $value) {
            throw new \InvalidArgumentException('Missing value');
        }

        if (isset($field->vars['choices'])) {
            $values = (array) $value;
            $value  = '';
            foreach ($field->vars['choices'] as $choice) {
                foreach ($values as $currentValue) {
                    if ($choice->value === $currentValue) {
                        $domain = \in_array($choice->value, FilledFilter::FILLED_FILTERS, true) ? 'forms' : null;

                        $value .= ('' !== $value ? ', ' : '') . $this->translator->trans($choice->label, [], $domain, $locale);
                    }
                }
            }
        }

        if ($isCheckbox) {
            $value = $this->translator->trans('boolean.yes');
        }

        if ($value instanceof \DateTimeInterface) {
            $this->initiateDateFormatter($event, $locale);

            $value = $this->dateFormatter->format($value);
        }

        if (\is_array($value)) {
            if ($this->hasNotNullValues($value)) {
                $subLabels = [];
                $subValues = [];

                foreach ($field->children as $subField) {
                    list($subLabel, $subValue) = $this->handleFormRow($subField, $event, $locale);

                    $subLabels[] = $subLabel;
                    $subValues[] = $subValue;
                }

                $values = [];

                foreach ($subLabels as $key => $subLabel) {
                    $values[] = sprintf('%s: %s', $subLabel, $subValues[$key]);
                }

                $value = implode(', ', $values);
            } else {
                $value = '';
            }
        }

        $label = $this->translator->trans($field->vars['label'], [], $field->vars['translation_domain'], $locale);

        return [$label, $value];
    }

    private function hasNotNullValues(array $values): bool
    {
        foreach ($values as $element) {
            if (null !== $element) {
                return true;
            }
        }

        return false;
    }

    private function initiateDateFormatter(?Event $event, string $locale): void
    {
        if ($this->dateFormatter instanceof \IntlDateFormatter) {
            return;
        }

        $timeZone = date_default_timezone_get();

        if ($event instanceof Event) {
            $timeZone = $event->getTimeZone();
        }

        $this->dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::SHORT,
            $timeZone
        );
    }
}
