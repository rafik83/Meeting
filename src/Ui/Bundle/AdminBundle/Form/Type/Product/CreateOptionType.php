<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product;

use Proximum\Vimeet\Application\Command\Product\CreateOption;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ui\Bundle\AdminBundle\Form\Type\Product\AbstractOptionType;

class CreateOptionType extends AbstractOptionType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreateOption::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_create_option';
    }
}
