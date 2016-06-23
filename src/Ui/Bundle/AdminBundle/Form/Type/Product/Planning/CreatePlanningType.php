<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Planning;

use Proximum\Vimeet\Application\Command\Product\Planning\CreatePlanning;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\AbstractCreateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreatePlanningType extends AbstractCreateType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => CreatePlanning::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_create_planning';
    }
}
