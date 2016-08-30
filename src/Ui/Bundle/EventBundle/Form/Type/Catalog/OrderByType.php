<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderByType extends AbstractType
{
    const ORDER_BY_ALPHABETICAL          = 'alphabetical';
    const ORDER_BY_DATE_ADDED_TO_CATALOG = 'dateAddedToCatalog';

    /**
     * {@inheridoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderBy', ChoiceType::class, [
                'expanded' => true,
                'choices'  => [
                    'form.catalog_order_by.order.alphabetical'       => self::ORDER_BY_ALPHABETICAL,
                    'form.catalog_order_by.order.dateAddedToCatalog' => self::ORDER_BY_DATE_ADDED_TO_CATALOG,
                ],
            ]);
    }

    /**
     * {@inheridoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
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
        return 'catalog_order_by';
    }
}
