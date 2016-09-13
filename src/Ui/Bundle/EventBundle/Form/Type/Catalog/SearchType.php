<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use Proximum\Vimeet\Domain\Model\Sheet\Constant;
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
        $builder
            ->add('orderBy', ChoiceType::class, [
                'label'    => 'form.search.orderBy.label',
                'expanded' => true,
                'choices'  => [
                    'form.search.orderBy.alphabetical'       => Constant::ORDER_BY_ALPHABETICAL,
                    'form.search.orderBy.dateAddedToCatalog' => Constant::ORDER_BY_DATE_ADDED_TO_CATALOG,
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label'        => 'form.search.type.label',
                'expanded'     => true,
                'multiple'     => true,
                'choices'      => $options['typeViews'],
                'choice_value' => function (TypeView $typeView) {
                    return $typeView->id;
                },
                'choice_label' => function (TypeView $typeView) {
                    return $typeView->title;
                },
            ]);
    }

    /**
     * {@inheridoc}
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
        return 'catalog_search';
    }
}
