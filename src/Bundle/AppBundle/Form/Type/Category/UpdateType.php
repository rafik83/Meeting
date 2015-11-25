<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Category;

use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends CategoryType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Proximum\Vimeet\Application\Command\Category\Update',
            'intention'  => 'category_update',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'category_update';
    }
}
