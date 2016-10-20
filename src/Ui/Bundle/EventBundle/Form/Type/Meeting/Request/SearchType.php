<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\View\TypeView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
            ]);

        if (count($options['typeViews']) > 1) {
            $builder
                ->add('type', ChoiceType::class, [
                    'label'        => 'form.search.type.label',
                    'expanded'     => true,
                    'multiple'     => true,
                    'choices'      => $options['typeViews'],
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['typeViews']);
        $resolver->setDefaults([
            'required'        => false,
            'method'          => 'GET',
            'csrf_protection' => false,
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
     * @param TypeView[] $typeViews
     *
     * @return array
     */
    public static function getDefaultFilters($typeViews = [])
    {
        $defaultFilters = [
            'orderBy' => Sheet\Constant::ORDER_BY_ALPHABETICAL,
            'state'   => Meeting\Constant::FILTER_STATE_ALL,
        ];

        // Allow to filters by type if there are more than 1
        if (count($typeViews) > 1) {
            $defaultFilters['type'] = array_values(self::transformTypeViews($typeViews));
        }

        return $defaultFilters;
    }

    /**
     * @param TypeView[] $typeViews
     *
     * @return array
     */
    public static function transformTypeViews($typeViews)
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
}
