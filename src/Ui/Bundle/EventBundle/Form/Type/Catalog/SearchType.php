<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\CatalogConstant;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractSearchType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $locale = $options['locale'];
        $event  = $options['event'];

        $builder
            ->add(SearchFields::ORDER_BY, ChoiceType::class, [
                'label'    => 'form.search.orderBy.label',
                'expanded' => true,
                'choices'  => [
                    'form.search.orderBy.relevance'          => Constant::ORDER_BY_RELEVANCE,
                    'form.search.orderBy.alphabetical'       => Constant::ORDER_BY_ALPHABETICAL,
                    'form.search.orderBy.dateAddedToCatalog' => Constant::ORDER_BY_DATE_ADDED_TO_CATALOG,
                ],
            ])
        ;
        if ($options['filterBySheetVisit']) {
            $builder
                ->add(SearchFields::FILTER_BY_SHEET_VISIT, ChoiceType::class, [
                    'label' => 'form.search.sheetVisit.label',
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => CatalogConstant::getAllSheetVisitChoices(),
                    'choice_label' => function ($choice) {
                        return 'form.search.sheetVisit.choice.' . $choice;
                    },
                ])
            ;
        }

        $objectiveFilters = [];
        if (in_array(Nomenclature::OBJECTIVE_SUPPLY, $options['objectiveFilters'])) {
            $objectiveFilters['form.search.objective.supply'] = Nomenclature::OBJECTIVE_SUPPLY;
        }

        if (in_array(Nomenclature::OBJECTIVE_NEED, $options['objectiveFilters'])) {
            $objectiveFilters['form.search.objective.need'] = Nomenclature::OBJECTIVE_NEED;
        }

        if (!empty($options['objectiveFilters'])) {
            $builder->
                add(
                    SearchFields::FILTER_OBJECTIVE, ChoiceType::class, [
                        'label'    => 'form.search.objective.label',
                        'expanded' => true,
                        'multiple' => true,
                        'choices'  => $objectiveFilters,
                    ]
                );
        }

        if (true === $options['filterByAvailableSlotIds']) {
            $everyone = CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_EVERYONE;
            $available = CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_AVAILABLE;
            $slotFilter = CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_SLOT;

            $everyoneTranslation = $this->translator->trans(
                sprintf('form.search.availableSlot.choice.%s', $everyone),
                [],
                'forms',
                $locale
            );
            $availableTranslation = $this->translator->trans(
                sprintf('form.search.availableSlot.choice.%s', $available),
                [],
                'forms',
                $locale
            );

            $filterAvailableChoices = [
                $everyoneTranslation => $everyone,
                $availableTranslation => $available,
            ];

            if (true === $options['filterBySpecificSlot'] && $options['specificSlot'] instanceof MeetingSlot) {
                $dayFormatter = new \IntlDateFormatter(
                    $locale,
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE,
                    $event->getTimeZone()
                );
                $hourFormatter = new \IntlDateFormatter(
                    $locale,
                    \IntlDateFormatter::NONE,
                    \IntlDateFormatter::SHORT,
                    $event->getTimeZone()
                );

                /** @var MeetingSlot $slot */
                $slot = $options['specificSlot'];
                $slotTranslation = $this->translator->trans(
                    sprintf('form.search.availableSlot.choice.%s', $slotFilter),
                    [
                        '%day%'  => $dayFormatter->format($slot->getBegin()) ?? '',
                        '%time%' => $hourFormatter->format($slot->getBegin()) ?? '',
                    ],
                    'forms',
                    $locale
                );

                $filterAvailableChoices[$slotTranslation] = $slotFilter;

                $builder->add(SearchFields::FILTER_BY_SPECIFIC_SLOT, HiddenType::class, [
                    'data' => $slot->getId(),
                ]);
            }

            $builder
                ->add(SearchFields::FILTER_AVAILABLE_SLOT_IDS, ChoiceType::class, [
                    'choices'  => $filterAvailableChoices,
                    'expanded' => true,
                    'multiple' => false,
                    'label'    => 'form.search.availableSlot.label',
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'filterByAvailableSlotIds' => false,
            'filterBySpecificSlot' => false,
            'specificSlot' => null,
            'objectiveFilters' => [],
            'filterBySheetVisit' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_search';
    }
}
