<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    /**
     * {@inheridoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typeViews                 = $options['typeViews'];
        $organizationCategoryViews = $options['organizationCategoryViews'];

        $builder
            ->add('orderBy', ChoiceType::class, [
                'label'    => 'form.search.orderBy.label',
                'expanded' => true,
                'choices'  => [
                    'form.search.orderBy.alphabetical'       => Constant::ORDER_BY_ALPHABETICAL,
                    'form.search.orderBy.dateAddedToCatalog' => Constant::ORDER_BY_DATE_ADDED_TO_CATALOG,
                ],
            ]);

        // show type facette only if there is more than one filter
        if (count($typeViews) > 1) {
            $builder
                ->add(
                    'type',
                    ChoiceType::class,
                    [
                        'label'        => 'form.search.type.label',
                        'expanded'     => true,
                        'multiple'     => true,
                        'choices'      => $typeViews,
                        'choice_value' => function (TypeView $typeView) {
                            return $typeView->id;
                        },
                        'choice_label' => function (TypeView $typeView) {
                            return $typeView->title;
                        },
                    ]
                );
        }

        $builder->add('organizationCategory', ChoiceType::class, [
            'choices'      => $organizationCategoryViews,
            'choice_value' => function (OrganizationCategoryView $organizationCategoryView = null) {
                if ($organizationCategoryView !== null) {
                    return $organizationCategoryView->key;
                }

                return null;
            },
            'choice_label' => function (OrganizationCategoryView $organizationCategoryView = null) {
                if ($organizationCategoryView !== null) {
                    return $organizationCategoryView->title;
                }

                return null;
            },
            'required'     => false,
            'multiple'     => true,
            'attr'         => [
                'class'               => 'form-control select2',
                'data-disallow-clear' => 'true',
            ],
        ]);
    }

    /**
     * {@inheridoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['typeViews', 'organizationCategoryViews']);
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
        return 'catalog_search';
    }
}
