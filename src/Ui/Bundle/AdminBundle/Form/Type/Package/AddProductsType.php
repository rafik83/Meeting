<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddProductsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('packageData', AddProductsByStepType::class, [
                'packageTemplate' => $options['packageTemplate'],
                'productTemplate' => $options['productTemplate'],
                'locale'          => $options['locale'],
                'sheet'           => $options['sheet'],
                'cart'            => $options['cart'],
                'label'           => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => 'Proximum\Vimeet\Application\Command\Package\AddProducts',
            'csrf_token_id' => 'update_sheet_package_products',
        ]);

        $resolver->setRequired(['productTemplate', 'packageTemplate', 'locale', 'cart']);
        $resolver->setDefined(['sheet']);
    }
}
