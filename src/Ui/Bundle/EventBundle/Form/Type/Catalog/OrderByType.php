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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderByType extends AbstractType
{
    /**
     * {@inheridoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderBy', ChoiceType::class, [
                'expanded' => true,
                'choices'  => [
                    'form.catalog_order_by.order.alphabetical'       => Constant::ORDER_BY_ALPHABETICAL,
                    'form.catalog_order_by.order.dateAddedToCatalog' => Constant::ORDER_BY_DATE_ADDED_TO_CATALOG,
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
