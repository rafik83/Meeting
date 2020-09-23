<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\CategoryView;
use Proximum\Vimeet\Domain\View\TypeView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event  = $options['event'];
        $locale = $options['locale'];
        $hasCategory = \count($options['categoryViews']) >= 1;

        $builder
            ->add('orderBy', ChoiceType::class, [
                'label'    => 'form.search.orderBy.label',
                'expanded' => true,
                'choices'  => [
                    'form.search.orderBy.alphabetical' => Sheet\Constant::ORDER_BY_ALPHABETICAL,
                    'form.search.orderBy.createdAt'    => Sheet\Constant::ORDER_BY_CREATED_AT,
                ],
            ])
            ->add('state', ChoiceType::class, [
                'label'        => 'form.search.meeting.state.label',
                'expanded'     => true,
                'multiple'     => false,
                'choices'      => Meeting\Constant::getAllStates(),
                'choice_value' => function ($state) {
                    return $state;
                },
                'choice_label' => function ($state) {
                    return 'form.search.meeting.state.' . $state;
                },
            ])
            ->add('sheetVisit', ChoiceType::class, [
                'label' => 'form.search.meeting.sheetVisit.label',
                'expanded' => true,
                'multiple' => false,
                'choices' => Meeting\Constant::getAllSheetVisitChoices(),
                'choice_value' => function ($state) {
                    return $state;
                },
                'choice_label' => function ($choice) {
                    return 'form.search.meeting.sheetVisit.choice.' . $choice;
                }
            ])
        ;

        if (\count($options['categoryViews']) > 1) {
            $builder
                ->add('category', ChoiceType::class, [
                    'label'    => 'form.search.type.label',
                    'expanded' => true,
                    'multiple' => true,
                    'choices'  => $options['categoryViews'],
                ]);
        } elseif (!$hasCategory && \count($options['typeViews']) > 1) {
            $builder
                ->add('type', ChoiceType::class, [
                    'label'    => 'form.search.type.label',
                    'expanded' => true,
                    'multiple' => true,
                    'choices'  => $options['typeViews'],
                ]);
        }

        if (true === $options['filterAvailableSlot']) {
            $everyone = Meeting\Constant::FILTER_AVAILABLE_SLOT_IDS_EVERYONE;
            $available = Meeting\Constant::FILTER_AVAILABLE_SLOT_IDS_AVAILABLE;
            $slotFilter = Meeting\Constant::FILTER_AVAILABLE_SLOT_IDS_SLOT;

            $everyoneTranslation = $this->translator->trans(
                sprintf('form.search.meeting.availableSlot.choice.%s', $everyone),
                [],
                'forms',
                $locale
            );
            $availableTranslation = $this->translator->trans(
                sprintf('form.search.meeting.availableSlot.choice.%s', $available),
                [],
                'forms',
                $locale
            );

            $filterAvailableChoices = [
                $everyoneTranslation  => $everyone,
                $availableTranslation => $available,
            ];

            if ($options['specificSlot'] instanceof MeetingSlot) {
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
                    sprintf('form.search.meeting.availableSlot.choice.%s', $slotFilter),
                    [
                        '%day%'  => $dayFormatter->format($slot->getBegin()) ?? '',
                        '%time%' => $hourFormatter->format($slot->getBegin()) ?? '',
                    ],
                    'forms',
                    $locale
                );

                $filterAvailableChoices[$slotTranslation] = $slotFilter;

                $builder->add('slot_id', HiddenType::class, [
                    'data' => $slot->getId(),
                ]);
            }

            $builder
                ->add('availableSlot', ChoiceType::class, [
                    'choices'  => $filterAvailableChoices,
                    'expanded' => true,
                    'multiple' => false,
                    'label'    => 'form.search.meeting.availableSlot.label',
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'typeViews',
            'categoryViews',
            'event',
            'locale',
        ]);

        $resolver->setDefaults([
            'specificSlot'        => null,
            'csrf_protection'     => false,
            'filterAvailableSlot' => false,
            'method'              => 'GET',
            'required'            => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }

    /**
     * @param TypeView[]     $typeViews
     * @param CategoryView[] $categoryViews
     *
     * @return array
     */
    public static function getDefaultFilters($typeViews = [], $categoryViews = []): array
    {
        $defaultFilters = [
            'availableSlot' => Meeting\Constant::FILTER_AVAILABLE_SLOT_IDS_EVERYONE,
            'disabled' => false,
            'orderBy'=> Sheet\Constant::ORDER_BY_ALPHABETICAL,
            'state' => Meeting\Constant::FILTER_STATE_ALL,
            'sheetVisit' => 'all',
        ];

        // Allow to filters by type if there are more than 1
        if (\count($typeViews) > 1) {
            $defaultFilters['type'] = array_values(self::transformTypeViews($typeViews));
        }
        if (\count($categoryViews) > 1) {
            $defaultFilters['category'] = array_values(self::transformCategoryViews($categoryViews));
        }

        return $defaultFilters;
    }

    /**
     * @param TypeView[] $typeViews
     *
     * @return array
     */
    public static function transformTypeViews(array $typeViews): array
    {
        $typeViews = array_combine(
            array_map(function (TypeView $typeView) {
                return $typeView->title;
            }, $typeViews),
            array_map(function (TypeView $typeView) {
                return $typeView->id;
            }, $typeViews)
        );

        return $typeViews;
    }

    /**
     * @param CategoryView[] $categoryViews
     *
     * @return array
     */
    public static function transformCategoryViews(array $categoryViews): array
    {
        $categoryViews = array_combine(
            array_map(function (CategoryView $categoryView) {
                return $categoryView->title;
            }, $categoryViews),
            array_map(function (CategoryView $categoryView) {
                return $categoryView->id;
            }, $categoryViews)
        );

        return $categoryViews;
    }
}
