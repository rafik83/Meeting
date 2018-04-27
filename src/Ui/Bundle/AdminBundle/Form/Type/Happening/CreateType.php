<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening;

use Proximum\Vimeet\Application\Command\Happening\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends HappeningType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class'    => Create::class,
            'csrf_token_id' => 'happening_create',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'happening_create';
    }
}
